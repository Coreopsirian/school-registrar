<?php
session_start();
include('../mysql/db.php');
if (!isset($_SESSION['name'])) { header('Location: ../index.php'); exit(); }

$active_sy = $conn->query("SELECT * FROM school_years WHERE is_active=1 LIMIT 1")->fetch_assoc();
$sy_id     = $active_sy['id'] ?? 0;

// Verify a document
if (isset($_GET['verify'])) {
  $rid = intval($_GET['verify']);
  $conn->prepare("UPDATE student_requirements SET status='verified', verified_by=?, verified_at=NOW() WHERE id=?")
       ->bind_param("ii", $_SESSION['user_id'], $rid) || null;
  $stmt = $conn->prepare("UPDATE student_requirements SET status='verified', verified_by=?, verified_at=NOW() WHERE id=?");
  $stmt->bind_param("ii", $_SESSION['user_id'], $rid);
  $stmt->execute();
  header("Location: requirements.php?success=Document verified"); exit();
}

// Reject a document
if (isset($_GET['reject'])) {
  $rid = intval($_GET['reject']);
  $stmt = $conn->prepare("UPDATE student_requirements SET status='missing', file_path=NULL, submitted_at=NULL WHERE id=?");
  $stmt->bind_param("i", $rid);
  $stmt->execute();
  header("Location: requirements.php?success=Document rejected"); exit();
}

$search = $_GET['search'] ?? '';
$searchParam = "%$search%";

// Students with requirement summary
$students_req = $conn->query("
  SELECT s.id, s.first_name, s.last_name, s.lrn, g.name as grade,
    COUNT(r.id) as total_req,
    SUM(sr.status='verified') as verified,
    SUM(sr.status='submitted') as submitted,
    SUM(sr.status='missing' OR sr.id IS NULL) as missing
  FROM students s
  LEFT JOIN grade_levels g ON s.grade_level_id = g.id
  LEFT JOIN requirements r ON (r.student_type = 'both' OR r.student_type = s.student_type) AND r.is_required = 1
  LEFT JOIN student_requirements sr ON sr.student_id = s.id AND sr.requirement_id = r.id AND sr.school_year_id = $sy_id
  WHERE s.is_archived = 0
    AND (s.first_name LIKE '$searchParam' OR s.last_name LIKE '$searchParam' OR s.lrn LIKE '$searchParam')
  GROUP BY s.id
  ORDER BY s.last_name ASC
");

// Detail view for one student
$detail_student = null;
$detail_reqs    = [];
if (!empty($_GET['student_id'])) {
  $sid = intval($_GET['student_id']);
  $detail_student = $conn->query("SELECT s.*, g.name as grade FROM students s LEFT JOIN grade_levels g ON s.grade_level_id=g.id WHERE s.id=$sid")->fetch_assoc();

  $req_res = $conn->query("
    SELECT r.id as req_id, r.name, r.description,
           sr.id as sr_id, sr.status, sr.file_path, sr.submitted_at, sr.notes
    FROM requirements r
    LEFT JOIN student_requirements sr ON sr.requirement_id = r.id AND sr.student_id = $sid AND sr.school_year_id = $sy_id
    WHERE r.is_required = 1 AND (r.student_type = 'both' OR r.student_type = '{$detail_student['student_type']}')
    ORDER BY r.sort_order
  ");
  while ($row = $req_res->fetch_assoc()) $detail_reqs[] = $row;
}

$success_message = $_GET['success'] ?? '';
$active_page = 'requirements';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Requirements — COJ Portal</title>
  <link rel="icon" type="image/png" href="../images/COJ.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="../css/requirements.css">
</head>
<body>
<?php include('includes/sidebar.php'); ?>

<div id="main">
  <div id="topbar">
    <div class="topbar-left">
      <div class="page-title">Requirements Tracker</div>
      <div class="page-sub">Verify student document submissions — SY <?= htmlspecialchars($active_sy['label'] ?? '') ?></div>
    </div>
  </div>

  <div id="page-container">
    <?php if ($success_message): ?><div class="alert-success-bar"><?= htmlspecialchars($success_message) ?></div><?php endif; ?>

    <?php if ($detail_student): ?>
    <!-- ── Detail view ── -->
    <div style="margin-bottom:16px;">
      <a href="requirements.php" class="back-link" style="color:var(--color-primary);font-weight:600;">
        <i class="bi bi-arrow-left"></i> Back to all students
      </a>
    </div>
    <div class="req-detail-header">
      <div>
        <div style="font-size:18px;font-weight:700;"><?= htmlspecialchars($detail_student['last_name'] . ', ' . $detail_student['first_name']) ?></div>
        <div style="font-size:13px;color:var(--color-muted);">LRN: <?= htmlspecialchars($detail_student['lrn']) ?> · <?= htmlspecialchars($detail_student['grade'] ?? '') ?></div>
      </div>
    </div>

    <div class="req-list">
      <?php foreach ($detail_reqs as $req): ?>
      <div class="req-item req-<?= $req['status'] ?? 'missing' ?>">
        <div class="req-item-left">
          <div class="req-status-dot dot-<?= $req['status'] ?? 'missing' ?>"></div>
          <div>
            <div class="req-name"><?= htmlspecialchars($req['name']) ?></div>
            <?php if ($req['description']): ?>
              <div class="req-desc"><?= htmlspecialchars($req['description']) ?></div>
            <?php endif; ?>
            <?php if ($req['submitted_at']): ?>
              <div class="req-date">Submitted: <?= date('M j, Y', strtotime($req['submitted_at'])) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <div class="req-item-right">
          <span class="req-badge req-badge-<?= $req['status'] ?? 'missing' ?>">
            <?= ucfirst($req['status'] ?? 'missing') ?>
          </span>
          <?php if (!empty($req['file_path'])): ?>
            <a href="uploads/<?= htmlspecialchars($req['file_path']) ?>" target="_blank" class="btn-view-sm">
              <i class="bi bi-eye-fill"></i> View
            </a>
          <?php endif; ?>
          <?php if (($req['status'] ?? '') === 'submitted'): ?>
            <a href="requirements.php?verify=<?= $req['sr_id'] ?>&student_id=<?= $detail_student['id'] ?>"
               class="btn-verify" onclick="return confirm('Mark as verified?')">
              <i class="bi bi-check-lg"></i> Verify
            </a>
            <a href="requirements.php?reject=<?= $req['sr_id'] ?>&student_id=<?= $detail_student['id'] ?>"
               class="btn-reject" onclick="return confirm('Reject this document?')">
              <i class="bi bi-x-lg"></i> Reject
            </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php else: ?>
    <!-- ── List view ── -->
    <form method="GET" action="requirements.php" style="display:flex;gap:8px;margin-bottom:16px;">
      <div class="search-wrap">
        <i class="bi bi-search search-icon"></i>
        <input type="search" name="search" class="toolbar-search-input" placeholder="Search student name or LRN" value="<?= htmlspecialchars($search) ?>"/>
      </div>
      <button type="submit" class="btn-search"><i class="bi bi-search"></i> Search</button>
      <?php if ($search): ?><a href="requirements.php" class="btn-clear-filters"><i class="bi bi-x-circle"></i> Clear</a><?php endif; ?>
    </form>

    <div class="req-table-card">
      <table class="req-table">
        <thead>
          <tr><th>Student</th><th>LRN</th><th>Grade</th><th>Verified</th><th>Submitted</th><th>Missing</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php $count = 0; while ($s = $students_req->fetch_assoc()): $count++; ?>
          <tr>
            <td style="font-weight:600;"><?= htmlspecialchars($s['last_name'] . ', ' . $s['first_name']) ?></td>
            <td class="td-muted"><?= htmlspecialchars($s['lrn']) ?></td>
            <td><?= htmlspecialchars($s['grade'] ?? '—') ?></td>
            <td style="color:var(--color-success);font-weight:600;"><?= $s['verified'] ?></td>
            <td style="color:var(--color-warning);font-weight:600;"><?= $s['submitted'] ?></td>
            <td style="color:var(--color-danger);font-weight:600;"><?= $s['missing'] ?></td>
            <td>
              <a href="requirements.php?student_id=<?= $s['id'] ?>" class="btn-view-sm">
                <i class="bi bi-folder2-open"></i> View Docs
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
          <?php if ($count === 0): ?>
          <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--color-muted);">No students found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<script src="../js/nav.js"></script>
</body>
</html>
