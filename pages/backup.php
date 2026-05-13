<?php
session_start();
include('../mysql/db.php');
require_once '../mysql/helpers.php';
if (!isset($_SESSION['name'])) { header('Location: ../index.php'); exit(); }
if ($_SESSION['role'] !== 'superadmin') { header('Location: dashboard.php'); exit(); }

$uid   = $_SESSION['user_id'] ?? 0;
$uname = $conn->real_escape_string($_SESSION['name'] ?? '');

// ── CSV: Students export ──────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'students') {
  $filename = 'coj_students_' . date('Y-m-d') . '.csv';
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  header('Pragma: no-cache');

  $out = fopen('php://output', 'w');
  // BOM for Excel UTF-8
  fputs($out, "\xEF\xBB\xBF");

  fputcsv($out, ['LRN','Last Name','First Name','Middle Name','Grade','School Year',
                 'Student Type','Sex','Birthday','Religion','Province','City/Municipality',
                 'Barangay','Contact Number','Enrollment Status','Reference #','Is SPED']);

  $rows = $conn->query("
    SELECT s.lrn, s.last_name, s.first_name, s.middle_name,
           g.name as grade, sy.label as school_year,
           s.student_type, s.sex, s.birthday, s.religion,
           s.province, s.city_municipality, s.barangay, s.contact_number,
           e.status as enroll_status, e.ref_number,
           s.is_sped
    FROM students s
    LEFT JOIN grade_levels g ON g.id = s.grade_level_id
    LEFT JOIN school_years sy ON sy.id = s.school_year_id
    LEFT JOIN enrollments e ON e.student_id = s.id AND e.school_year_id = sy.id
    WHERE s.is_archived = 0
    ORDER BY s.last_name, s.first_name
  ");
  while ($r = $rows->fetch_assoc()) {
    fputcsv($out, [
      $r['lrn'], $r['last_name'], $r['first_name'], $r['middle_name'],
      $r['grade'], $r['school_year'], $r['student_type'], $r['sex'],
      $r['birthday'], $r['religion'], $r['province'], $r['city_municipality'],
      $r['barangay'], $r['contact_number'], $r['enroll_status'] ?? 'N/A',
      $r['ref_number'] ?? 'N/A', $r['is_sped'] ? 'Yes' : 'No'
    ]);
  }
  fclose($out);

  $conn->query("INSERT INTO audit_log (user_id, user_name, action, target, details) VALUES ($uid, '$uname', 'export_csv', 'students', 'Students CSV exported')");
  exit();
}

// ── CSV: Payments export ──────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'payments') {
  $filename = 'coj_payments_' . date('Y-m-d') . '.csv';
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  header('Pragma: no-cache');

  $out = fopen('php://output', 'w');
  fputs($out, "\xEF\xBB\xBF");

  fputcsv($out, ['Student LRN','Last Name','First Name','Grade','Fee Name','Fee Type',
                 'Amount','Amount Paid','Balance','Status','OR Number','Payment Method','Date Paid']);

  $rows = $conn->query("
    SELECT s.lrn, s.last_name, s.first_name, g.name as grade,
           f.name as fee_name, f.fee_type,
           f.amount, p.amount_paid, p.balance, p.status,
           p.or_number, p.payment_method, p.paid_at
    FROM payments p
    JOIN students s ON s.id = p.student_id
    JOIN fees f ON f.id = p.fee_id
    LEFT JOIN grade_levels g ON g.id = s.grade_level_id
    WHERE s.is_archived = 0
    ORDER BY s.last_name, s.first_name, f.name
  ");
  while ($r = $rows->fetch_assoc()) {
    fputcsv($out, [
      $r['lrn'], $r['last_name'], $r['first_name'], $r['grade'],
      $r['fee_name'], $r['fee_type'], $r['amount'], $r['amount_paid'],
      $r['balance'], $r['status'], $r['or_number'] ?? '',
      $r['payment_method'] ?? '', $r['paid_at'] ?? ''
    ]);
  }
  fclose($out);

  $conn->query("INSERT INTO audit_log (user_id, user_name, action, target, details) VALUES ($uid, '$uname', 'export_csv', 'payments', 'Payments CSV exported')");
  exit();
}

$active_page = 'backup';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Data Export</title>
  <link rel="icon" type="image/png" href="../images/COJ.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<?php include('includes/sidebar.php'); ?>
<div id="main">
  <div id="topbar">
    <div class="topbar-left">
      <div class="page-title">Data Export</div>
      <div class="page-sub">Download CSV reports of student and payment records</div>
    </div>
  </div>
  <div id="page-container">

    <div style="max-width:640px;display:flex;flex-direction:column;gap:16px;">

      <!-- Students CSV -->
      <div style="background:#fff;border:1px solid var(--color-border);border-radius:12px;padding:28px;">
        <div style="display:flex;align-items:flex-start;gap:16px;">
          <div style="font-size:28px;color:var(--color-primary);flex-shrink:0;"><i class="bi bi-people-fill"></i></div>
          <div style="flex:1;">
            <div style="font-size:15px;font-weight:700;margin-bottom:4px;">Student Records</div>
            <p style="font-size:13px;color:var(--color-muted);line-height:1.6;margin-bottom:16px;">
              Exports all active students with their grade, enrollment status, address, and personal details.
              File is date-stamped (e.g. <code>coj_students_2026-05-13.csv</code>).
            </p>
            <a href="backup.php?export=students"
               style="display:inline-flex;align-items:center;gap:8px;padding:10px 22px;background:var(--color-primary);color:#fff;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
              <i class="bi bi-download"></i> Download Students CSV
            </a>
          </div>
        </div>
      </div>

      <!-- Payments CSV -->
      <div style="background:#fff;border:1px solid var(--color-border);border-radius:12px;padding:28px;">
        <div style="display:flex;align-items:flex-start;gap:16px;">
          <div style="font-size:28px;color:#16a34a;flex-shrink:0;"><i class="bi bi-cash-coin"></i></div>
          <div style="flex:1;">
            <div style="font-size:15px;font-weight:700;margin-bottom:4px;">Payment Records</div>
            <p style="font-size:13px;color:var(--color-muted);line-height:1.6;margin-bottom:16px;">
              Exports all payment transactions per student per fee — including OR numbers, amounts paid, balances, and payment methods.
              File is date-stamped (e.g. <code>coj_payments_2026-05-13.csv</code>).
            </p>
            <a href="backup.php?export=payments"
               style="display:inline-flex;align-items:center;gap:8px;padding:10px 22px;background:#16a34a;color:#fff;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
              <i class="bi bi-download"></i> Download Payments CSV
            </a>
          </div>
        </div>
      </div>

      <!-- Weekly reminder -->
      <div style="background:#fef9c3;border:1px solid #fde68a;border-radius:8px;padding:14px 16px;font-size:13px;color:#92400e;">
        <i class="bi bi-calendar-week-fill"></i>
        <strong>Weekly Export Reminder:</strong> Download both CSV files every Friday and save them to a USB drive or Google Drive.
        File names include the date so you always know which version is the latest.
      </div>

    </div>

  </div>
</div>
<script src="../js/nav.js"></script>
</body>
</html>
