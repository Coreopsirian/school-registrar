<?php
session_start();
include('../mysql/db.php');
if (!isset($_SESSION['name'])) { header('Location: ../index.php'); exit(); }

$user_id = $_SESSION['user_id'] ?? 0;
$view    = $_GET['view']    ?? 'inbox';   // inbox | sent | compose | detail
$msg_id  = intval($_GET['id'] ?? 0);

// ── Handle compose (send) ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
  $recipient_id = intval($_POST['recipient_id']);
  $subject      = trim($_POST['subject'] ?? '');
  $body         = trim($_POST['body']    ?? '');

  if ($recipient_id > 0 && $subject !== '' && $body !== '') {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_id, subject, body) VALUES (?,?,?,?)");
    $stmt->bind_param("iiss", $user_id, $recipient_id, $subject, $body);
    $stmt->execute();
    header("Location: messages.php?view=sent&success=Message sent"); exit();
  } else {
    $compose_error = "All fields are required.";
    $view = 'compose';
  }
}

// ── Handle delete ────────────────────────────────────────────
if (isset($_GET['delete_id'])) {
  $did = intval($_GET['delete_id']);
  // Only allow deleting own messages (sender or recipient)
  $conn->query("DELETE FROM messages WHERE id=$did AND (sender_id=$user_id OR recipient_id=$user_id)");
  header("Location: messages.php?view=inbox&success=Message deleted"); exit();
}

// ── Mark as read when viewing detail ────────────────────────
$detail_msg = null;
if ($view === 'detail' && $msg_id > 0) {
  $stmt = $conn->prepare("
    SELECT m.*, u_s.name as sender_name, u_r.name as recipient_name
    FROM messages m
    JOIN users u_s ON u_s.id = m.sender_id
    JOIN users u_r ON u_r.id = m.recipient_id
    WHERE m.id = ? AND (m.recipient_id = ? OR m.sender_id = ?)
  ");
  $stmt->bind_param("iii", $msg_id, $user_id, $user_id);
  $stmt->execute();
  $detail_msg = $stmt->get_result()->fetch_assoc();
  if ($detail_msg && $detail_msg['recipient_id'] == $user_id && !$detail_msg['is_read']) {
    $conn->query("UPDATE messages SET is_read=1 WHERE id=$msg_id");
    $detail_msg['is_read'] = 1;
  }
  if (!$detail_msg) { header("Location: messages.php"); exit(); }
}

// ── Fetch inbox ──────────────────────────────────────────────
$inbox = $conn->query("
  SELECT m.id, m.subject, m.body, m.is_read, m.created_at, u.name as sender_name
  FROM messages m
  JOIN users u ON u.id = m.sender_id
  WHERE m.recipient_id = $user_id
  ORDER BY m.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// ── Fetch sent ───────────────────────────────────────────────
$sent = $conn->query("
  SELECT m.id, m.subject, m.body, m.is_read, m.created_at, u.name as recipient_name
  FROM messages m
  JOIN users u ON u.id = m.recipient_id
  WHERE m.sender_id = $user_id
  ORDER BY m.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// ── Unread count ─────────────────────────────────────────────
$unread_count = count(array_filter($inbox, fn($m) => !$m['is_read']));

// ── Users list for compose ───────────────────────────────────
$users_list = $conn->query("SELECT id, name, role FROM users WHERE id != $user_id AND is_active=1 ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$success_message = $_GET['success'] ?? '';
$active_page = 'messages';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Messages — COJ Portal</title>
  <link rel="icon" type="image/png" href="../images/COJ.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="../css/messages.css">
</head>
<body>
<?php include('includes/sidebar.php'); ?>

<div id="main">
  <div id="topbar">
    <div class="topbar-left">
      <div class="page-title">Messages</div>
      <div class="page-sub">Internal staff messaging</div>
    </div>
    <div class="topbar-actions">
      <a href="messages.php?view=compose" class="btn-topbar"><i class="bi bi-pencil-square"></i> Compose</a>
    </div>
  </div>

  <div id="page-container">

    <?php if ($success_message): ?>
      <div class="alert-success-bar"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <div class="messages-layout">

      <!-- Left nav -->
      <div class="msg-nav">
        <a href="messages.php?view=inbox" class="msg-nav-item <?= $view==='inbox'||$view==='detail'?'active':'' ?>">
          <i class="bi bi-inbox-fill"></i> Inbox
          <?php if ($unread_count > 0): ?>
            <span class="msg-unread-badge"><?= $unread_count ?></span>
          <?php endif; ?>
        </a>
        <a href="messages.php?view=compose" class="msg-nav-item <?= $view==='compose'?'active':'' ?>">
          <i class="bi bi-pencil-square"></i> Compose
        </a>
        <a href="messages.php?view=sent" class="msg-nav-item <?= $view==='sent'?'active':'' ?>">
          <i class="bi bi-send-fill"></i> Sent
        </a>
      </div>

      <!-- Right panel -->
      <div class="msg-panel">

        <?php if ($view === 'compose'): ?>
        <!-- ── COMPOSE ── -->
        <div class="msg-panel-header">
          <div class="msg-panel-title"><i class="bi bi-pencil-square"></i> New Message</div>
        </div>
        <div class="msg-compose">
          <?php if (!empty($compose_error)): ?>
            <div style="background:#fdeaea;border:1px solid #f5c6c6;border-radius:8px;padding:10px 14px;font-size:13px;color:#dc2626;margin-bottom:16px;"><?= htmlspecialchars($compose_error) ?></div>
          <?php endif; ?>
          <form method="POST" action="messages.php">
            <input type="hidden" name="action" value="send">
            <div class="form-group">
              <label>To *</label>
              <select name="recipient_id" class="form-input" required>
                <option value="">Select recipient</option>
                <?php foreach ($users_list as $u): ?>
                  <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= ucfirst($u['role']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Subject *</label>
              <input type="text" name="subject" class="form-input" placeholder="Message subject" required
                     value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>"/>
            </div>
            <div class="form-group">
              <label>Message *</label>
              <textarea name="body" placeholder="Write your message here..." required><?= htmlspecialchars($_POST['body'] ?? '') ?></textarea>
            </div>
            <div class="msg-compose-actions">
              <a href="messages.php?view=inbox" class="btn-msg-back"><i class="bi bi-x-lg"></i> Cancel</a>
              <button type="submit" class="btn-msg-compose"><i class="bi bi-send-fill"></i> Send</button>
            </div>
          </form>
        </div>

        <?php elseif ($view === 'detail' && $detail_msg): ?>
        <!-- ── DETAIL ── -->
        <div class="msg-panel-header">
          <div class="msg-panel-title"><i class="bi bi-envelope-open-fill"></i> Message</div>
          <a href="messages.php?view=inbox" class="btn-msg-back"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
        <div class="msg-detail">
          <div class="msg-detail-header">
            <div class="msg-detail-subject"><?= htmlspecialchars($detail_msg['subject']) ?></div>
            <div class="msg-detail-meta">
              <span><strong>From:</strong> <?= htmlspecialchars($detail_msg['sender_name']) ?></span>
              <span><strong>To:</strong> <?= htmlspecialchars($detail_msg['recipient_name']) ?></span>
              <span><strong>Date:</strong> <?= date('M j, Y g:i A', strtotime($detail_msg['created_at'])) ?></span>
            </div>
          </div>
          <div class="msg-detail-body"><?= htmlspecialchars($detail_msg['body']) ?></div>
          <div class="msg-detail-actions">
            <?php if ($detail_msg['recipient_id'] == $user_id): ?>
              <a href="messages.php?view=compose&reply_to=<?= $detail_msg['sender_id'] ?>&subject=<?= urlencode('Re: '.$detail_msg['subject']) ?>"
                 class="btn-msg-compose"><i class="bi bi-reply-fill"></i> Reply</a>
            <?php endif; ?>
            <a href="messages.php?delete_id=<?= $detail_msg['id'] ?>"
               class="btn-msg-back"
               onclick="return confirm('Delete this message?')"><i class="bi bi-trash3-fill"></i> Delete</a>
          </div>
        </div>

        <?php elseif ($view === 'sent'): ?>
        <!-- ── SENT ── -->
        <div class="msg-panel-header">
          <div class="msg-panel-title"><i class="bi bi-send-fill"></i> Sent Messages</div>
        </div>
        <?php if (empty($sent)): ?>
          <div class="msg-empty"><i class="bi bi-send"></i>No sent messages yet.</div>
        <?php else: ?>
          <ul class="msg-list">
            <?php foreach ($sent as $m): ?>
            <li>
              <a href="messages.php?view=detail&id=<?= $m['id'] ?>" class="msg-list-item">
                <div class="msg-avatar"><?= strtoupper(substr($m['recipient_name'],0,1)) ?></div>
                <div>
                  <div class="msg-item-from">To: <?= htmlspecialchars($m['recipient_name']) ?></div>
                  <div class="msg-item-subject"><?= htmlspecialchars($m['subject']) ?></div>
                  <div class="msg-item-preview"><?= htmlspecialchars(mb_substr($m['body'],0,80)) ?>…</div>
                </div>
                <div class="msg-item-date"><?= date('M j', strtotime($m['created_at'])) ?></div>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <?php else: ?>
        <!-- ── INBOX (default) ── -->
        <div class="msg-panel-header">
          <div class="msg-panel-title">
            <i class="bi bi-inbox-fill"></i> Inbox
            <?php if ($unread_count > 0): ?>
              <span class="msg-unread-badge" style="font-size:11px;padding:2px 8px;"><?= $unread_count ?> unread</span>
            <?php endif; ?>
          </div>
        </div>
        <?php if (empty($inbox)): ?>
          <div class="msg-empty"><i class="bi bi-inbox"></i>Your inbox is empty.</div>
        <?php else: ?>
          <ul class="msg-list">
            <?php foreach ($inbox as $m): ?>
            <li>
              <a href="messages.php?view=detail&id=<?= $m['id'] ?>" class="msg-list-item <?= !$m['is_read'] ? 'unread' : '' ?>">
                <div class="msg-avatar"><?= strtoupper(substr($m['sender_name'],0,1)) ?></div>
                <div>
                  <div class="msg-item-from"><?= htmlspecialchars($m['sender_name']) ?></div>
                  <div class="msg-item-subject"><?= htmlspecialchars($m['subject']) ?></div>
                  <div class="msg-item-preview"><?= htmlspecialchars(mb_substr($m['body'],0,80)) ?>…</div>
                </div>
                <div class="msg-item-date"><?= date('M j', strtotime($m['created_at'])) ?></div>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <?php endif; ?>

      </div><!-- .msg-panel -->
    </div><!-- .messages-layout -->
  </div>
</div>

<script src="../js/nav.js"></script>
<?php
// Pre-fill compose if replying
if ($view === 'compose' && isset($_GET['reply_to'], $_GET['subject'])):
?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const sel = document.querySelector('select[name="recipient_id"]');
    if (sel) sel.value = '<?= intval($_GET['reply_to']) ?>';
    const subj = document.querySelector('input[name="subject"]');
    if (subj && !subj.value) subj.value = <?= json_encode($_GET['subject'] ?? '') ?>;
  });
</script>
<?php endif; ?>
</body>
</html>
