<?php
$active_portal = 'clearance';
require_once 'includes/auth.php';

$active_sy = $conn->query("SELECT * FROM school_years WHERE is_active=1 LIMIT 1")->fetch_assoc();
$sy_id     = $active_sy['id'] ?? 0;
$student   = $conn->query("SELECT s.*, g.name as grade FROM students s LEFT JOIN grade_levels g ON s.grade_level_id=g.id WHERE s.id=$student_id")->fetch_assoc();
$clearance = $conn->query("SELECT * FROM clearance WHERE student_id=$student_id AND school_year_id=$sy_id LIMIT 1")->fetch_assoc();

$depts = [
  'library'   => ['label' => 'Library',   'icon' => 'bi-book-fill'],
  'registrar' => ['label' => 'Registrar', 'icon' => 'bi-person-lines-fill'],
  'finance'   => ['label' => 'Finance',   'icon' => 'bi-cash-coin'],
];
$all_cleared = $clearance
  && $clearance['library_status']   === 'cleared'
  && $clearance['registrar_status'] === 'cleared'
  && $clearance['finance_status']   === 'cleared';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Clearance — Parent Portal</title>
  <link rel="icon" type="image/x-icon" href="../images/COJ.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/portal.css">
</head>
<body>
<?php include('includes/nav.php'); ?>
<div class="portal-container">
  <div class="portal-page-header">
    <h2>Clearance Status</h2>
    <p><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?> · SY <?= htmlspecialchars($active_sy['label'] ?? '') ?></p>
  </div>

  <?php if ($all_cleared): ?>
  <div class="portal-clearance-complete">
    <i class="bi bi-patch-check-fill"></i>
    <h3>Fully Cleared!</h3>
    <p>All departments have signed off on your clearance for SY <?= htmlspecialchars($active_sy['label'] ?? '') ?>.</p>
  </div>
  <?php elseif (!$clearance): ?>
  <div class="portal-error-msg">
    <i class="bi bi-info-circle-fill"></i>
    Clearance has not been initiated yet. Please ensure your enrollment is approved first.
  </div>
  <?php else: ?>
  <div class="portal-clearance-grid">
    <?php foreach ($depts as $key => $dept):
      $status = $clearance[$key . '_status'] ?? 'pending';
      $at     = $clearance[$key . '_at'] ?? null;
    ?>
    <div class="portal-clearance-card <?= $status === 'cleared' ? 'card-cleared' : 'card-pending' ?>">
      <div class="pcc-icon"><i class="bi <?= $dept['icon'] ?>"></i></div>
      <div class="pcc-label"><?= $dept['label'] ?></div>
      <div class="pcc-status">
        <?php if ($status === 'cleared'): ?>
          <span class="pcc-badge cleared"><i class="bi bi-check-circle-fill"></i> Cleared</span>
          <?php if ($at): ?><div class="pcc-date"><?= date('M j, Y', strtotime($at)) ?></div><?php endif; ?>
        <?php else: ?>
          <span class="pcc-badge pending"><i class="bi bi-hourglass-split"></i> Pending</span>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="portal-req-note" style="margin-top:24px;">
    <i class="bi bi-info-circle-fill"></i>
    Clearance is processed by each department. Contact the school if a sign-off is delayed.
  </div>
  <?php endif; ?>
</div>
</body>
</html>
