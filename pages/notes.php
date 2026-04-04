

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
  <title>Notes — School Portal</title>
  <link rel="icon" type="image/png" href="../images/COJ.png">
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
      <div class="nav-item" data-href="attendance.php" data-label="Attendance"><span class="nav-icon"><i class="bi bi-calendar-check-fill"></i></span><span class="nav-text">Attendance</span></div>
      <div class="nav-item" data-href="reports.php"    data-label="Reports"><span class="nav-icon"><i class="bi bi-file-earmark-text-fill"></i></span><span class="nav-text">Reports</span></div>
      <div class="nav-item active" data-href="notes.php" data-label="Notes"><span class="nav-icon"><i class="bi bi-journal-text"></i></span><span class="nav-text">Notes</span></div>
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
        <div class="page-title">Notes</div>
        <div class="page-sub">Manage your notes and reminders</div>
      </div>
      <div class="topbar-spacer"></div>
    </div>

    <div id="page-container">
      <div id="page-notes">
        <div class="page-section-title">Notes</div>
        <div class="notes-layout">
          <div class="notes-list-panel">
            <div class="notes-list-head">
              <span>All Notes (0)</span>
              <button title="New note">+</button>
            </div>
            <div class="notes-empty-list"><p>No notes yet.</p></div>
          </div>
          <div class="notes-editor-panel">
            <div class="editor-toolbar">
              <input class="editor-title-input" id="note-title" type="text" placeholder="Note title..." />
              <div class="editor-actions">
                <button class="btn-sm btn-save">Save</button>
                <button class="btn-sm btn-delete">Delete</button>
              </div>
            </div>
            <div class="editor-meta-row">
              <span>Category:</span>
              <select id="note-category">
                <option>General</option>
                <option>Academic</option>
                <option>Meeting</option>
                <option>Concern</option>
              </select>
            </div>
            <textarea id="note-body" placeholder="Start writing your note here..."></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="../js/nav.js"></script>
</body>
</html>
