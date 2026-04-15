<?php
session_start();
include('../mysql/db.php');
if (!isset($_SESSION['name'])) { header('Location: ../index.php'); exit(); }

$active_sy = $conn->query("SELECT * FROM school_years WHERE is_active=1 LIMIT 1")->fetch_assoc();
$sy_id     = $active_sy['id'] ?? 0;
$role      = $_SESSION['role'] ?? '';

// Handle sign-off
if (isset($_GET['signoff'])) {
  $cid  = intval($_GET['signoff']);
  $dept = $_GET['dept'] ?? '';
  $allowed_depts = ['library','registrar','finance'];

  // Role check — finance can only clear finance, registrar can clear registrar+library
  $can_clear = match($dept) {
    'finance'   => in_array($role, ['finance','superadmin']),
    'registrar' => in_array($role, ['registrar','superadmin']),
    'library'   => in_array($role, ['registrar','superadmin']), // registrar handles library
    default     => false
  };

  if ($can_clear && in_array($dept, $allowed_depts)) {
    $col_status = $dept . '_status';
    $col_by     = $dept . '_by';
    $col_at     = $dept . '_at';
    $uid = $_SESSION['user_id'] ?? 0;
    $conn->query("UPDATE clearance SET `$col_status`='cleared', `$col_by`=$uid, `$col_at`=NOW() WHERE id=$cid");
  }
  header("Location: clearance.php?success=Clearance updated"); exit();
}

// Handle undo sign-off
if (isset($_GET['undo'])) {
  $cid  = intval($_GET['undo']);
  $dept = $_GET['dept'] ?? '';
  $col_status = $dept . '_status';
  $col_by     = $dept . '_by';
  $col_at     = $dept . '_at';
  $conn->query("UPDATE clearance SET `$col_status`='pending', `$col_by`=NULL, `$col_at`=NULL WHERE id=$cid");
  header("Location: clearance.php?success=Sign-off removed"); exit();
}

$search = $_GET['search'] ?? '';
$searchParam = "%$search%";

$clearances = $conn->query("
  SELECT c.*, s.first_name, s.last_name, s.lrn, g.name as grade
  FROM clearance c
  JOIN students s ON c.student_id = s.id
  LEFT JOIN grade_levels g ON s.grade_level_id = g.id
  WHERE c.school_year_id = $sy_id
    AND (s.first_name LIKE '$searchParam' OR s.last_name LIKE '$searchParam' OR s.lrn LIKE '$searchParam')
  ORDER BY s.last_name ASC
");

$success_message = $_GET['success'] ?? '';
$active_page = 'clearance';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Clearance — COJ Portal</title>
  <link rel="icon" type="image/png" href="../images/COJ.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="../css/clearance.css">
</head>
<body>
<?php include('includes/sidebar.php'); ?>

<div id="main">
  <div id="topbar">
    <div class="topbar-left">
      <div class="page-title">Student Clearance</div>
      <div class="page-sub">SY <?= htmlspecialchars($active_sy['label'] ?? '') ?> — Multi-department sign-offs</div>
    </div>
  </div>

  <div id="page-container">
    <?php if ($success_message): ?><div class="alert-success-bar"><?= htmlspecialchars($success_message) ?></div><?php endif; ?>

    <form method="GET" action="clearance.php" style="display:flex;gap:8px;margin-bottom:16px;">
      <div class="search-wrap">
        <i class="bi bi-search search-icon"></i>
        <input type="search" name="search" class="toolbar-search-input" placeholder="Search student name or LRN" value="<?= htmlspecialchars($search) ?>"/>
      </div>
      <button type="submit" class="btn-search"><i class="bi bi-search"></i> Search</button>
      <?php if ($search): ?><a href="clearance.php" class="btn-clear-filters"><i class="bi bi-x-circle"></i> Clear</a><?php endif; ?>
    </form>

    <div class="clearance-table-card">
      <table class="clearance-table">
        <thead>
          <tr>
            <th>Student</th><th>LRN</th><th>Grade</th>
            <th>Library</th><th>Registrar</th><th>Finance</th>
            <th>Overall</th>
          </tr>
        </thead>
        <tbody>
          <?php $count = 0; while ($c = $clearances->fetch_assoc()): $count++;
            $all_cleared = $c['library_status']==='cleared' && $c['registrar_status']==='cleared' && $c['finance_status']==='cleared';
          ?>
          <tr>
            <td style="font-weight:600;"><?= htmlspecialchars($c['last_name'] . ', ' . $c['first_name']) ?></td>
            <td class="td-muted"><?= htmlspecialchars($c['lrn']) ?></td>
            <td><?= htmlspecialchars($c['grade'] ?? '—') ?></td>

            <?php foreach (['library','registrar','finance'] as $dept):
              $status = $c[$dept . '_status'];
              $can_act = match($dept) {
                'finance'   => in_array($role, ['finance','superadmin']),
                default     => in_array($role, ['registrar','superadmin']),
              };
            ?>
            <td>
              <?php if ($status === 'cleared'): ?>
                <span class="cl-badge cl-cleared"><i class="bi bi-check-circle-fill"></i> Cleared</span>
                <?php if ($can_act): ?>
                  <a href="clearance.php?undo=<?= $c['id'] ?>&dept=<?= $dept ?>" class="cl-undo" title="Undo">↩</a>
                <?php endif; ?>
              <?php elseif ($can_act): ?>
                <a href="clearance.php?signoff=<?= $c['id'] ?>&dept=<?= $dept ?>"
                   class="cl-badge cl-pending cl-action"
                   onclick="return confirm('Mark <?= ucfirst($dept) ?> as cleared?')">
                  <i class="bi bi-clock"></i> Pending
                </a>
              <?php else: ?>
                <span class="cl-badge cl-pending"><i class="bi bi-clock"></i> Pending</span>
              <?php endif; ?>
            </td>
            <?php endforeach; ?>

            <td>
              <?php if ($all_cleared): ?>
                <span class="cl-badge cl-cleared"><i class="bi bi-patch-check-fill"></i> Cleared</span>
              <?php else: ?>
                <span class="cl-badge cl-pending">Incomplete</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
          <?php if ($count === 0): ?>
          <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--color-muted);">No enrolled students found for this school year.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script src="../js/nav.js"></script>
</body>
</html>
