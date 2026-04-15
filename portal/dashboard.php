<?php
$active_portal = 'dashboard';
require_once 'includes/auth.php';

$active_sy = $conn->query("SELECT * FROM school_years WHERE is_active=1 LIMIT 1")->fetch_assoc();
$sy_id     = $active_sy['id'] ?? 0;

$student = $conn->query("
  SELECT s.*, g.name as grade, sec.name as section
  FROM students s
  LEFT JOIN grade_levels g ON s.grade_level_id = g.id
  LEFT JOIN sections sec ON s.section_id = sec.id
  WHERE s.id = $student_id
")->fetch_assoc();

$enrollment = $conn->query("SELECT * FROM enrollments WHERE student_id=$student_id AND school_year_id=$sy_id LIMIT 1")->fetch_assoc();

// Requirements summary
$req_summary = $conn->query("
  SELECT
    SUM(sr.status='verified') as verified,
    SUM(sr.status='submitted') as submitted,
    SUM(sr.status='missing' OR sr.id IS NULL) as missing
  FROM requirements r
  LEFT JOIN student_requirements sr ON sr.requirement_id=r.id AND sr.student_id=$student_id AND sr.school_year_id=$sy_id
  WHERE r.is_required=1 AND (r.student_type='both' OR r.student_type='{$student['student_type']}')
")->fetch_assoc();

// Payment summary
$pay_summary = $conn->query("
  SELECT COALESCE(SUM(amount_paid),0) as paid, COALESCE(SUM(balance),0) as balance
  FROM payments WHERE student_id=$student_id
")->fetch_assoc();

// Clearance
$clearance = $conn->query("SELECT * FROM clearance WHERE student_id=$student_id AND school_year_id=$sy_id LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard — Parent Portal</title>
  <link rel="icon" type="image/x-icon" href="../images/COJ.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/portal.css">
</head>
<body>
<?php include('includes/nav.php'); ?>

<div class="portal-container">
  <div class="portal-page-header">
    <h2>Welcome, <?= htmlspecialchars(explode(' ', $parent_name)[0]) ?>!</h2>
    <p>SY <?= htmlspecialchars($active_sy['label'] ?? '') ?> — Enrollment Overview</p>
  </div>

  <!-- Student card -->
  <div class="portal-student-card">
    <div class="portal-student-avatar">
      <?php if (!empty($student['photo'])): ?>
        <img src="../pages/uploads/<?= htmlspecialchars($student['photo']) ?>" alt="Photo"/>
      <?php else: ?>
        <i class="bi bi-person-fill"></i>
      <?php endif; ?>
    </div>
    <div class="portal-student-info">
      <div class="portal-student-name"><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . ($student['middle_name'] ?? '')) ?></div>
      <div class="portal-student-meta">LRN: <?= htmlspecialchars($student['lrn']) ?> · <?= htmlspecialchars($student['grade'] ?? '—') ?> · <?= htmlspecialchars($student['section'] ?? '—') ?></div>
    </div>
    <div>
      <?php $es = $enrollment['status'] ?? 'not enrolled'; ?>
      <span class="portal-enroll-badge badge-<?= $es ?>">
        <?= ucfirst($es) ?>
      </span>
    </div>
  </div>

  <!-- Summary cards -->
  <div class="portal-summary-grid">
    <a href="requirements.php" class="portal-summary-card">
      <div class="psc-icon" style="background:#eef0f8;color:var(--primary)"><i class="bi bi-folder2-open"></i></div>
      <div class="psc-body">
        <div class="psc-val"><?= ($req_summary['verified'] ?? 0) ?> / <?= (($req_summary['verified'] ?? 0) + ($req_summary['submitted'] ?? 0) + ($req_summary['missing'] ?? 0)) ?></div>
        <div class="psc-label">Documents Verified</div>
      </div>
      <?php if (($req_summary['missing'] ?? 0) > 0): ?>
        <span class="psc-alert"><?= $req_summary['missing'] ?> missing</span>
      <?php endif; ?>
    </a>

    <a href="soa.php" class="portal-summary-card">
      <div class="psc-icon" style="background:#dcfce7;color:#166534"><i class="bi bi-cash-coin"></i></div>
      <div class="psc-body">
        <div class="psc-val">₱<?= number_format($pay_summary['paid'] ?? 0, 0) ?></div>
        <div class="psc-label">Total Paid</div>
      </div>
      <?php if (($pay_summary['balance'] ?? 0) > 0): ?>
        <span class="psc-alert">₱<?= number_format($pay_summary['balance'], 0) ?> balance</span>
      <?php endif; ?>
    </a>

    <a href="clearance.php" class="portal-summary-card">
      <?php
        $cl_done = $clearance
          ? ($clearance['library_status']==='cleared' && $clearance['registrar_status']==='cleared' && $clearance['finance_status']==='cleared')
          : false;
      ?>
      <div class="psc-icon" style="background:<?= $cl_done ? '#dcfce7' : '#fef9c3' ?>;color:<?= $cl_done ? '#166534' : '#92400e' ?>">
        <i class="bi bi-<?= $cl_done ? 'patch-check-fill' : 'hourglass-split' ?>"></i>
      </div>
      <div class="psc-body">
        <div class="psc-val"><?= $cl_done ? 'Cleared' : 'Pending' ?></div>
        <div class="psc-label">Clearance Status</div>
      </div>
    </a>
  </div>
</div>
</body>
</html>
