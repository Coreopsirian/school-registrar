<?php
session_start();

if (!isset($_SESSION['name'])) {
  header('Location: ../index.php');
  exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Attendance — School Portal</title>
  <link rel="icon" type="image/png" href="../images/COJ.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="../css/pages.css">
</head>
<body>

  <aside id="sidebar">
    <div class="sidebar-logo-box">
    <img src="../images/COJ.png" alt="School Logo" />
      <div class="logo-text">
        <div class="school-name">Catholic<br/>Progressive School</div>
        <div class="school-sub">Registrar System</div>
      </div>
    </div>
    <div class="sidebar-toggle">
      <button class="toggle-btn" id="toggleBtn">&#9664;</button>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-item" data-href="dashboard.php"  data-label="Dashboard"><span class="nav-icon"><i class="bi bi-bar-chart-fill"></i></span><span class="nav-text">Dashboard</span></div>
      <div class="nav-item" data-href="students.php"   data-label="Students"><span class="nav-icon"><i class="bi bi-people-fill"></i></span><span class="nav-text">Students</span></div>
      <div class="nav-item active" data-href="attendance.php" data-label="Attendance"><span class="nav-icon"><i class="bi bi-calendar-check-fill"></i></span><span class="nav-text">Attendance</span></div>
      <div class="nav-item" data-href="reports.php"    data-label="Reports"><span class="nav-icon"><i class="bi bi-file-earmark-text-fill"></i></span><span class="nav-text">Reports</span></div>
      <div class="nav-item" data-href="notes.php"      data-label="Notes"><span class="nav-icon"><i class="bi bi-journal-text"></i></span><span class="nav-text">Notes</span></div>
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
        <div class="page-title">Attendance</div>
        <div class="page-sub">Track daily faculty attendance</div>
      </div>

      <div class="topbar-searchh">
        
        <div class="input-group">
          <input type="search" class="form-control rounded" placeholder="Search" aria-label="Search" aria-describedby="search-addon" />
          <button type="button" class="btn btn-outline-primary" data-mdb-ripple-init>search</button>
        </div>
        </div>

      </div>

    <div id="page-container">
      <div id="page-attendance">
        <div class="page-section-title">Attendance</div>
        <div class="stats-row">
          <div class="stat-card">
            <div class="stat-label">Present</div>
            <div class="stat-value" style="color:var(--color-success)">0</div>
            <div class="stat-sub">students</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Absent</div>
            <div class="stat-value" style="color:var(--color-danger)">0</div>
            <div class="stat-sub">students</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Late</div>
            <div class="stat-value" style="color:var(--color-warning)">0</div>
            <div class="stat-sub">students</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Rate</div>
            <div class="stat-value">0%</div>
            <div class="stat-sub">attendance</div>
          </div>
        </div>
        <div class="att-empty">No attendance data yet. Add students to start tracking.</div>
      </div>
    </div>
  </div>

  <script src="../js/nav.js"></script>
</body>
</html>
