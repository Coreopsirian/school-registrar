<?php
session_start();
include('../mysql/db.php');

if (!isset($_SESSION['name'])) { header('Location: ../index.php'); exit(); }
if (empty($_GET['id']))        { header('Location: teachers.php'); exit(); }

$id   = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM teachers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$teacher) { header('Location: teachers.php'); exit(); }

// Attendance summary (last 30 days)
$summary = $conn->query("
  SELECT
    SUM(status='present') as present,
    SUM(status='absent')  as absent,
    SUM(status='late')    as late,
    COUNT(*)              as total
  FROM teacher_attendance
  WHERE teacher_id = $id
    AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
")->fetch_assoc();

// Recent attendance records
$records = $conn->query("
  SELECT date, status, remarks FROM teacher_attendance
  WHERE teacher_id = $id
  ORDER BY date DESC
  LIMIT 10
");

$fullname = htmlspecialchars($teacher['last_name'] . ', ' . $teacher['first_name'] . ' ' . ($teacher['middle_name'] ?? ''));
$photo    = !empty($teacher['photo']) ? 'uploads/' . htmlspecialchars($teacher['photo']) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $fullname ?> — Teacher Profile</title>
  <link rel="icon" type="image/png" href="../images/COJ.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="../css/profile.css">
  <link rel="stylesheet" href="../css/teacher_profile.css">
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
      <div class="nav-item active" data-href="teachers.php" data-label="Teachers"><span class="nav-icon"><i class="bi bi-person-workspace"></i></span><span class="nav-text">Teachers</span></div>
      <div class="nav-item" data-href="attendance.php" data-label="Attendance"><span class="nav-icon"><i class="bi bi-calendar-check-fill"></i></span><span class="nav-text">Attendance</span></div>
      <div class="nav-item" data-href="reports.php" data-label="Reports"><span class="nav-icon"><i class="bi bi-file-earmark-text-fill"></i></span><span class="nav-text">Reports</span></div>
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
        <div class="page-title">Teacher Profile</div>
        <div class="page-sub"><a href="teachers.php" class="back-link"><i class="bi bi-arrow-left"></i> Back to Teachers</a></div>
      </div>
      <div class="topbar-actions">
        <a href="teachers.php?edit_id=<?= $teacher['id'] ?>" class="btn-profile-edit"><i class="bi bi-pencil-fill"></i> Edit</a>
        <button onclick="window.print()" class="btn-profile-print"><i class="bi bi-printer-fill"></i> Print</button>
      </div>
    </div>

    <div id="page-container">
      <div class="profile-layout">

        <!-- LEFT -->
        <div class="profile-card">
          <div class="profile-avatar">
            <?php if ($photo): ?>
              <img src="<?= $photo ?>" alt="Teacher Photo"/>
            <?php else: ?>
              <div class="avatar-placeholder"><i class="bi bi-person-fill"></i></div>
            <?php endif; ?>
          </div>
          <div class="profile-name"><?= $fullname ?></div>
          <div class="profile-lrn"><?= htmlspecialchars($teacher['subject'] ?? 'No subject assigned') ?></div>
          <span class="profile-badge badge-teacher">Teacher</span>

          <div class="profile-quick">
            <div class="quick-item">
              <span class="quick-icon"><i class="bi bi-building"></i></span>
              <div>
                <div class="quick-label">Department</div>
                <div class="quick-value"><?= htmlspecialchars($teacher['department'] ?? '—') ?></div>
              </div>
            </div>
            <div class="quick-item">
              <span class="quick-icon"><i class="bi bi-envelope-fill"></i></span>
              <div>
                <div class="quick-label">Email</div>
                <div class="quick-value"><?= htmlspecialchars($teacher['email'] ?? '—') ?></div>
              </div>
            </div>
            <div class="quick-item">
              <span class="quick-icon"><i class="bi bi-telephone-fill"></i></span>
              <div>
                <div class="quick-label">Contact</div>
                <div class="quick-value"><?= htmlspecialchars($teacher['contact_number'] ?? '—') ?></div>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT -->
        <div class="profile-details">

          <div class="detail-section">
            <div class="detail-section-title"><i class="bi bi-person-lines-fill"></i> Personal Information</div>
            <div class="detail-grid">
              <div class="detail-item"><div class="detail-label">First Name</div><div class="detail-value"><?= htmlspecialchars($teacher['first_name']) ?></div></div>
              <div class="detail-item"><div class="detail-label">Middle Name</div><div class="detail-value"><?= htmlspecialchars($teacher['middle_name'] ?: '—') ?></div></div>
              <div class="detail-item"><div class="detail-label">Last Name</div><div class="detail-value"><?= htmlspecialchars($teacher['last_name']) ?></div></div>
              <div class="detail-item"><div class="detail-label">Email</div><div class="detail-value"><?= htmlspecialchars($teacher['email'] ?? '—') ?></div></div>
              <div class="detail-item"><div class="detail-label">Contact</div><div class="detail-value"><?= htmlspecialchars($teacher['contact_number'] ?? '—') ?></div></div>
              <div class="detail-item"><div class="detail-label">Subject</div><div class="detail-value"><?= htmlspecialchars($teacher['subject'] ?? '—') ?></div></div>
              <div class="detail-item"><div class="detail-label">Department</div><div class="detail-value"><?= htmlspecialchars($teacher['department'] ?? '—') ?></div></div>
            </div>
          </div>

          <!-- Attendance Summary -->
          <div class="detail-section">
            <div class="detail-section-title"><i class="bi bi-calendar-check-fill"></i> Attendance Summary (Last 30 Days)</div>
            <div class="att-summary-grid">
              <div class="att-summary-card" style="border-top:3px solid var(--color-success)">
                <div class="att-summary-val" style="color:var(--color-success)"><?= $summary['present'] ?? 0 ?></div>
                <div class="att-summary-label">Present</div>
              </div>
              <div class="att-summary-card" style="border-top:3px solid var(--color-danger)">
                <div class="att-summary-val" style="color:var(--color-danger)"><?= $summary['absent'] ?? 0 ?></div>
                <div class="att-summary-label">Absent</div>
              </div>
              <div class="att-summary-card" style="border-top:3px solid var(--color-warning)">
                <div class="att-summary-val" style="color:var(--color-warning)"><?= $summary['late'] ?? 0 ?></div>
                <div class="att-summary-label">Late</div>
              </div>
              <div class="att-summary-card" style="border-top:3px solid var(--accent)">
                <?php
                  $total = $summary['total'] ?? 0;
                  $rate  = $total > 0 ? round((($summary['present'] ?? 0) / $total) * 100) : 0;
                ?>
                <div class="att-summary-val" style="color:var(--accent)"><?= $rate ?>%</div>
                <div class="att-summary-label">Rate</div>
              </div>
            </div>
          </div>

          <!-- Recent Records -->
          <div class="detail-section">
            <div class="detail-section-title"><i class="bi bi-clock-history"></i> Recent Attendance</div>
            <table class="att-records-table">
              <thead><tr><th>Date</th><th>Status</th><th>Remarks</th></tr></thead>
              <tbody>
                <?php while ($rec = $records->fetch_assoc()):
                  $cls = match($rec['status']) { 'present' => 'status-present', 'absent' => 'status-absent', 'late' => 'status-late', default => '' };
                ?>
                <tr>
                  <td><?= date('M j, Y', strtotime($rec['date'])) ?></td>
                  <td><span class="status-badge <?= $cls ?>"><?= ucfirst($rec['status']) ?></span></td>
                  <td><?= htmlspecialchars($rec['remarks'] ?: '—') ?></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>

  <script src="../js/nav.js"></script>
</body>
</html>
