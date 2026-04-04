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
  <title>Students — School Portal</title>
  <link rel="icon" type="image/png" href="../images/COJ.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="../css/students.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body>

  <!-- ===== SIDEBAR ===== -->
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
      <div class="nav-item" data-href="dashboard.php" data-label="Dashboard">
        <span class="nav-icon"><i class="bi bi-bar-chart-fill"></i></span>
        <span class="nav-text">Dashboard</span>
      </div>
      <div class="nav-item active" data-href="students.php" data-label="Students">
        <span class="nav-icon"><i class="bi bi-people-fill"></i></span>
        <span class="nav-text">Students</span>
      </div>
      <div class="nav-item" data-href="attendance.php" data-label="Attendance">
        <span class="nav-icon"><i class="bi bi-calendar-check-fill"></i></span>
        <span class="nav-text">Attendance</span>
      </div>
      <div class="nav-item" data-href="reports.php" data-label="Reports">
        <span class="nav-icon"><i class="bi bi-file-earmark-text-fill"></i></span>
        <span class="nav-text">Reports</span>
      </div>
      <div class="nav-item" data-href="notes.php" data-label="Notes">
        <span class="nav-icon"><i class="bi bi-journal-text"></i></span>
        <span class="nav-text">Notes</span>
      </div>
    </nav>

    <div class="sidebar-footer">
      <a href="../logout.php" class="logout-btn">
        <span class="logout-icon"><i class="bi bi-box-arrow-right"></i></span>
        <span class="btn-text">Log out</span>
      </a>
    </div>
  </aside>

  <!-- ===== MAIN ===== -->
  <div id="main">

    <div id="topbar">
      <div class="topbar-left">
        <div class="page-title">Student Records</div>
        <div class="page-sub">Manage and view all registered students</div>
      </div>

      <div class="topbar-searchh">       
        <div class="input-group">
          <input type="search" class="form-control rounded" placeholder="Search" aria-label="Search" aria-describedby="search-addon" />
          <button type="button" class="btn btn-outline-primary" data-mdb-ripple-init>search</button>
        </div>
        </div>
    </div>

    <div id="page-container">
      <div id="page-students">

        <!-- Toolbar -->
        <div class="students-toolbar">
          <div class="toolbar-left">
            <div>
              <div class="filter-label">Filter by Grade &amp; Section:</div>
              <select class="filter-select" id="filter-grade">
                <option value="">All Grade &amp; Sections</option>
                <option value="Grade 7">Grade 7</option>
                <option value="Grade 8">Grade 8</option>
                <option value="Grade 9">Grade 9</option>
                <option value="Grade 10">Grade 10</option>
              </select>
            </div>
            <div>
              <div class="filter-label">Filter by Status:</div>
              <select class="filter-select" id="filter-status">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>
          <div class="toolbar-right">
            <button class="btn-export" id="btn-export"><i class="bi bi-download"></i> Export</button>
            <button class="btn-add"    id="btn-add-student">+ Add Students</button>
          </div>
        </div>

  
      

        <!-- Table -->
        <div class="table-card">
          <table id="students-table">
            <thead>
              <tr>
                <th>Photo</th>
                 <th>Name</th>
                <th>LRN</th>
                <th>Grade &amp; Section</th>
                <th>City</th> 
                <th>Contact</th>
                <th>Status</th> <!-- old student, new-->
                <th>Requirement</th>
                <th>Action</th>
              </tr>
            </thead>
            <a class = 'btn btn-primary btn-sm' href='/'>Edit</a>
            <a class = 'btn btn-danger btn-sm' href='/'>Delete</a>

            <tbody id="students-tbody"></tbody>
          </table>
          <div class="pagination-row" id="pagination-row">
            <span id="pagination-info">Showing 0–0 of 0 students</span>
            <div class="pagination-btns" id="pagination-btns"></div>
          </div>
        </div>

      </div>
    </div><!-- /page-container -->
  </div><!-- /main -->

  <!-- ===== ADD / EDIT STUDENT MODAL ===== -->
  <div class="modal-overlay" id="student-modal">
    <div class="modal-box">
      <div class="modal-header">
        <h2 id="modal-title">Add New Student</h2>
        <button class="modal-close" id="modal-close-btn">&times;</button>
      </div>

      <div class="modal-body">
        <div class="photo-upload-area">
          <div class="photo-preview-circle" id="photo-preview-circle" title="Click to upload photo">
            <div class="photo-placeholder-icon" id="photo-placeholder-icon">
              <i class="bi bi-camera-fill"></i>
              <span>Upload</span>
            </div>
            <img id="photo-preview-img" src="" alt="Preview" />
          </div>
          <div class="photo-upload-hint">Click the photo area to upload an image</div>
          <input type="file" id="photo-file-input" accept="image/*" style="display:none" />
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">First Name *</label>
            <input type="text" class="form-input" id="field-firstname" placeholder="e.g. Juan" />
          </div>
          <div class="form-group">
            <label class="form-label">Middle Name</label>
            <input type="text" class="form-input" id="field-middlename" placeholder="e.g. Santos" />
          </div>
          <div class="form-group">
            <label class="form-label">Last Name *</label>
            <input type="text" class="form-input" id="field-lastname" placeholder="e.g. Dela Cruz" />
          </div>
          <div class="form-group">
            <label class="form-label">Student ID *</label>
            <input type="text" class="form-input" id="field-id" placeholder="e.g. 2024-0001" />
          </div>
          <div class="form-group">
            <label class="form-label">Grade &amp; Section *</label>
            <select class="form-select" id="field-grade">
              <option value="">Select Grade &amp; Section</option>
              <option>Grade 7 – Sampaguita</option>
              <option>Grade 7 – Rosal</option>
              <option>Grade 8 – Sampaguita</option>
              <option>Grade 8 – Rosal</option>
              <option>Grade 9 – Sampaguita</option>
              <option>Grade 9 – Rosal</option>
              <option>Grade 10 – Sampaguita</option>
              <option>Grade 10 – Rosal</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">City / Municipality</label>
            <input type="text" class="form-input" id="field-city" placeholder="e.g. Quezon City" />
          </div>
          <div class="form-group">
            <label class="form-label">Contact Number</label>
            <input type="text" class="form-input" id="field-contact" placeholder="e.g. 09XX-XXX-XXXX" />
          </div>
          <div class="form-group">
            <label class="form-label">Date of Birth</label>
            <input type="date" class="form-input" id="field-dob" />
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <select class="form-select" id="field-status">
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>
          <div class="form-group full-width">
            <label class="form-label">Address</label>
            <input type="text" class="form-input" id="field-address" placeholder="Full address..." />
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn-cancel" id="modal-cancel-btn">Cancel</button>
        <button class="btn-save-student" id="modal-save-btn">Save Student</button>
      </div>
    </div>
  </div>

  <!-- Toast -->
  <div class="toast" id="toast"></div>

  <script src="../js/nav.js"></script>
  <script type="module" src="../js/students.js"></script>
  <script type="module">
    import { initStudents } from '../js/students.js';
    initStudents();
  </script>
</body>
</html>
