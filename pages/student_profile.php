<?php
include('../mysql/db.php');
session_start();

if (!isset($_SESSION['name'])) {
  header('Location: ../index.php');
  exit();
}

if (empty($_GET['id'])) {
  header('Location: students.php');
  exit();
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("
  SELECT s.*, g.name as grade_name, sec.name as section_name
  FROM students s
  LEFT JOIN grade_levels g ON s.grade_level_id = g.id
  LEFT JOIN sections sec ON s.section_id = sec.id
  WHERE s.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
  header('Location: students.php');
  exit();
}

$fullname = htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']);
$grade    = htmlspecialchars($student['grade_name'] ?? 'N/A');
$section  = htmlspecialchars($student['section_name'] ?? 'N/A');
$lrn      = htmlspecialchars($student['lrn']);
$city     = htmlspecialchars($student['city'] ?? '—');
$contact  = htmlspecialchars($student['contact_number'] ?? '—');
$type     = $student['student_type'];
$photo    = !empty($student['photo']) ? 'uploads/' . htmlspecialchars($student['photo']) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $fullname ?> — Profile</title>
  <link rel="icon" type="image/png" href="../images/COJ.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="../css/profile.css">
</head>
<body>

  <!-- SIDEBAR -->
  <aside id="sidebar">
    <div class="sidebar-logo-box">
      <img src="../images/COJ.png" alt="School Logo"/>
      <div class="logo-text">
        <div class="school-name">Catholic<br/>Progressive School</div>
        <div class="school-sub">Registrar System</div>
      </div>
    </div>
    <div class="sidebar-toggle">
      <button class="toggle-btn" id="toggleBtn">&#9664;</button>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-item" data-href="dashboard.php" data-label="Dashboard"><span class="nav-icon"><i class="bi bi-bar-chart-fill"></i></span><span class="nav-text">Dashboard</span></div>
      <div class="nav-item active" data-href="students.php" data-label="Students"><span class="nav-icon"><i class="bi bi-people-fill"></i></span><span class="nav-text">Students</span></div>
      <div class="nav-item" data-href="attendance.php" data-label="Attendance"><span class="nav-icon"><i class="bi bi-calendar-check-fill"></i></span><span class="nav-text">Attendance</span></div>
      <div class="nav-item" data-href="reports.php" data-label="Reports"><span class="nav-icon"><i class="bi bi-file-earmark-text-fill"></i></span><span class="nav-text">Reports</span></div>
      <div class="nav-item" data-href="notes.php" data-label="Notes"><span class="nav-icon"><i class="bi bi-journal-text"></i></span><span class="nav-text">Notes</span></div>
    </nav>
    <div class="sidebar-footer">
      <a href="../logout.php" class="logout-btn">
        <span class="logout-icon"><i class="bi bi-box-arrow-right"></i></span>
        <span class="btn-text">Log out</span>
      </a>
    </div>
  </aside>

  <!-- MAIN -->
  <div id="main">
    <div id="topbar">
      <div class="topbar-left">
        <div class="page-title">Student Profile</div>
        <div class="page-sub"><a href="students.php" class="back-link"><i class="bi bi-arrow-left"></i> Back to Students</a></div>
      </div>
      <div class="topbar-actions">
        <a href="students.php?edit_id=<?= $student['id'] ?>" class="btn-profile-edit"><i class="bi bi-pencil-fill"></i> Edit</a>
        <button onclick="window.print()" class="btn-profile-print"><i class="bi bi-printer-fill"></i> Print</button>
      </div>
    </div>

    <div id="page-container">
      <div class="profile-layout">

        <!-- LEFT: Avatar + quick info -->
        <div class="profile-card">
          <div class="profile-avatar">
            <?php if ($photo): ?>
              <img src="<?= $photo ?>" alt="Student Photo"/>
            <?php else: ?>
              <div class="avatar-placeholder"><i class="bi bi-person-fill"></i></div>
            <?php endif; ?>
          </div>
          <div class="profile-name"><?= $fullname ?></div>
          <div class="profile-lrn">LRN: <?= $lrn ?></div>
          <span class="profile-badge <?= $type === 'new' ? 'badge-new' : 'badge-old' ?>">
            <?= ucfirst($type) ?> Student
          </span>

          <div class="profile-quick">
            <div class="quick-item">
              <span class="quick-icon"><i class="bi bi-mortarboard-fill"></i></span>
              <div>
                <div class="quick-label">Grade</div>
                <div class="quick-value"><?= $grade ?></div>
              </div>
            </div>
            <div class="quick-item">
              <span class="quick-icon"><i class="bi bi-grid-fill"></i></span>
              <div>
                <div class="quick-label">Section</div>
                <div class="quick-value"><?= $section ?></div>
              </div>
            </div>
            <div class="quick-item">
              <span class="quick-icon"><i class="bi bi-geo-alt-fill"></i></span>
              <div>
                <div class="quick-label">City</div>
                <div class="quick-value"><?= $city ?></div>
              </div>
            </div>
            <div class="quick-item">
              <span class="quick-icon"><i class="bi bi-telephone-fill"></i></span>
              <div>
                <div class="quick-label">Contact</div>
                <div class="quick-value"><?= $contact ?></div>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT: Details -->
        <div class="profile-details">

          <div class="detail-section">
            <div class="detail-section-title"><i class="bi bi-person-lines-fill"></i> Personal Information</div>
            <div class="detail-grid">
              <div class="detail-item">
                <div class="detail-label">First Name</div>
                <div class="detail-value"><?= htmlspecialchars($student['first_name']) ?></div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Middle Name</div>
                <div class="detail-value"><?= htmlspecialchars($student['middle_name'] ?: '—') ?></div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Last Name</div>
                <div class="detail-value"><?= htmlspecialchars($student['last_name']) ?></div>
              </div>
              <div class="detail-item">
                <div class="detail-label">LRN</div>
                <div class="detail-value"><?= $lrn ?></div>
              </div>
              <div class="detail-item">
                <div class="detail-label">City / Address</div>
                <div class="detail-value"><?= $city ?></div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Contact Number</div>
                <div class="detail-value"><?= $contact ?></div>
              </div>
            </div>
          </div>

          <div class="detail-section">
            <div class="detail-section-title"><i class="bi bi-mortarboard-fill"></i> Academic Information</div>
            <div class="detail-grid">
              <div class="detail-item">
                <div class="detail-label">Grade Level</div>
                <div class="detail-value"><?= $grade ?></div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Section</div>
                <div class="detail-value"><?= $section ?></div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Student Type</div>
                <div class="detail-value"><?= ucfirst($type) ?></div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <script src="../js/nav.js"></script>
</body>
</html>
