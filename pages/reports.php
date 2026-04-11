<?php
session_start();
include('../mysql/db.php');

if (!isset($_SESSION['name'])) {
  header('Location: ../index.php');
  exit();
}

// Filters
$month = $_GET['month'] ?? date('Y-m');

// ── Student enrollment by grade ──
$enrollment = $conn->query("
  SELECT g.name as grade,
    COUNT(s.id) as total,
    SUM(s.student_type='new') as new_s,
    SUM(s.student_type='old') as old_s
  FROM grade_levels g
  LEFT JOIN students s ON s.grade_level_id = g.id AND s.is_archived = 0
  GROUP BY g.id ORDER BY g.id
");

$total_students = $conn->query("SELECT COUNT(*) as c FROM students WHERE is_archived=0")->fetch_assoc()['c'];

// ── Teacher attendance summary for selected month ──
$teachers_exist = $conn->query("SHOW TABLES LIKE 'teachers'")->num_rows > 0;
$teacher_report = [];
if ($teachers_exist) {
  $res = $conn->query("
    SELECT t.id, t.first_name, t.last_name, t.subject,
      SUM(a.status='present') as present,
      SUM(a.status='absent')  as absent,
      SUM(a.status='late')    as late,
      COUNT(a.id)             as total_days
    FROM teachers t
    LEFT JOIN teacher_attendance a ON a.teacher_id = t.id
      AND DATE_FORMAT(a.date, '%Y-%m') = '$month'
    WHERE t.is_archived = 0
    GROUP BY t.id ORDER BY t.last_name
  ");
  while ($row = $res->fetch_assoc()) $teacher_report[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reports</title>
  <link rel="icon" type="image/png" href="../images/COJ.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="../css/reports.css">
</head>
<body>

  <aside id="sidebar">
    <div class="sidebar-logo-box">
      <img src="../images/COJ.png" alt="School Logo"/>
      <div class="logo-text">
        <div class="school-name">Catholic<br/>Progressive School</div>
        <div class="school-sub">Registrar System</div>
      </div>
    </div>
    <div class="sidebar-toggle"><button class="toggle-btn" id="toggleBtn">&#9664;</button></div>
    <nav class="sidebar-nav">
      <div class="nav-item" data-href="dashboard.php" data-label="Dashboard"><span class="nav-icon"><i class="bi bi-grid-fill"></i></span><span class="nav-text">Dashboard</span></div>
      <div class="nav-item" data-href="students.php" data-label="Students"><span class="nav-icon"><i class="bi bi-people-fill"></i></span><span class="nav-text">Students</span></div>
      <div class="nav-item" data-href="teachers.php" data-label="Teachers"><span class="nav-icon"><i class="bi bi-person-workspace"></i></span><span class="nav-text">Teachers</span></div>
      <div class="nav-item" data-href="attendance.php" data-label="Attendance"><span class="nav-icon"><i class="bi bi-calendar-check-fill"></i></span><span class="nav-text">Attendance</span></div>
      <div class="nav-item active" data-href="reports.php" data-label="Reports"><span class="nav-icon"><i class="bi bi-file-earmark-text-fill"></i></span><span class="nav-text">Reports</span></div>
      <div class="nav-item" data-href="notes.php" data-label="Notes"><span class="nav-icon"><i class="bi bi-journal-text"></i></span><span class="nav-text">Notes</span></div>
      <?php if (($_SESSION['role'] ?? '') === 'superadmin'): ?>
      <div class="nav-item" data-href="users.php" data-label="Users"><span class="nav-icon"><i class="bi bi-shield-lock-fill"></i></span><span class="nav-text">Users</span></div>
      <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
      <a href="../logout.php" class="logout-btn">
        <span class="logout-icon"><i class="bi bi-box-arrow-right"></i></span>
        <span class="btn-text">Log out</span>
      </a>
    </div>
  </aside>

  <div id="main">
    <div id="topbar">
      <div class="topbar-left">
        <div class="page-title">Reports</div>
        <div class="page-sub">School enrollment and attendance summaries</div>
      </div>
      <div class="topbar-actions">
        <button class="btn-export-report" onclick="exportEnrollment()"><i class="bi bi-download"></i> Export Enrollment</button>
        <button class="btn-export-report" onclick="exportAttendance()"><i class="bi bi-download"></i> Export Attendance</button>
      </div>
    </div>

    <div id="page-container">

      <!-- ENROLLMENT REPORT -->
      <div class="report-section">
        <div class="report-section-header">
          <span><i class="bi bi-people-fill"></i> Student Enrollment by Grade</span>
          <span class="report-total">Total: <?= $total_students ?> students</span>
        </div>
        <table class="report-table" id="enrollment-table">
          <thead>
            <tr><th>Grade Level</th><th>New Students</th><th>Old Students</th><th>Total</th></tr>
          </thead>
          <tbody>
            <?php while ($e = $enrollment->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($e['grade']) ?></td>
              <td><?= $e['new_s'] ?></td>
              <td><?= $e['old_s'] ?></td>
              <td><strong><?= $e['total'] ?></strong></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
          <tfoot>
            <tr><td><strong>Total</strong></td><td colspan="2"></td><td><strong><?= $total_students ?></strong></td></tr>
          </tfoot>
        </table>
      </div>

      <!-- ATTENDANCE REPORT -->
      <div class="report-section" style="margin-top:24px">
        <div class="report-section-header">
          <span><i class="bi bi-calendar-check-fill"></i> Teacher Attendance Summary</span>
          <form method="GET" action="reports.php" class="month-form">
            <label>Month:</label>
            <input type="month" name="month" value="<?= htmlspecialchars($month) ?>" onchange="this.form.submit()"/>
          </form>
        </div>

        <?php if (empty($teacher_report)): ?>
          <div class="report-empty">No attendance data for this month.</div>
        <?php else: ?>
        <table class="report-table" id="attendance-table">
          <thead>
            <tr><th>Teacher</th><th>Subject</th><th>Present</th><th>Absent</th><th>Late</th><th>Total Days</th><th>Rate</th></tr>
          </thead>
          <tbody>
            <?php foreach ($teacher_report as $tr):
              $rate = $tr['total_days'] > 0 ? round(($tr['present'] / $tr['total_days']) * 100) : 0;
            ?>
            <tr>
              <td><?= htmlspecialchars($tr['last_name'] . ', ' . $tr['first_name']) ?></td>
              <td><?= htmlspecialchars($tr['subject'] ?? '—') ?></td>
              <td style="color:var(--color-success);font-weight:600"><?= $tr['present'] ?></td>
              <td style="color:var(--color-danger);font-weight:600"><?= $tr['absent'] ?></td>
              <td style="color:var(--color-warning);font-weight:600"><?= $tr['late'] ?></td>
              <td><?= $tr['total_days'] ?></td>
              <td>
                <div class="rate-bar-wrap">
                  <div class="rate-bar" style="width:<?= $rate ?>%"></div>
                  <span><?= $rate ?>%</span>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>

    </div>
  </div>

  <script src="../js/nav.js"></script>
  <script>
    function exportEnrollment() {
      const wb = XLSX.utils.book_new();
      const ws = XLSX.utils.table_to_sheet(document.getElementById('enrollment-table'));
      XLSX.utils.book_append_sheet(wb, ws, 'Enrollment');
      XLSX.writeFile(wb, 'enrollment_report.xlsx');
    }

    function exportAttendance() {
      const tbl = document.getElementById('attendance-table');
      if (!tbl) { alert('No attendance data to export.'); return; }
      const wb = XLSX.utils.book_new();
      const ws = XLSX.utils.table_to_sheet(tbl);
      XLSX.utils.book_append_sheet(wb, ws, 'Attendance');
      XLSX.writeFile(wb, 'attendance_report.xlsx');
    }
  </script>
</body>
</html>
