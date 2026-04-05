<?php
session_start();

if (!isset($_SESSION['name'])) {
  header('Location: ../index.php');
  exit();
}


//get data from form
$first_name= "";
$middle_name= "";
$last_name= "";
$lrn= "";
$grade_level_id= "";
$section_id= "";
$city = "";
$contact_number = "";
$student_type = "";
$photo = "";

$error_message = "";
$success_message = "";

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $first_name = $_POST['first_name'];
  $middle_name = $_POST['middle_name'];
  $last_name = $_POST['last_name'];
  $lrn = $_POST['lrn'];
  $grade_level_id = $_POST['grade_level_id'];
  $section_id = $_POST['section_id'];
  $city = $_POST['city'];
  $contact_number = $_POST['contact_number'];
  $student_type = $_POST['status']; // status is used for student type

  //CHECKS FOR NO EMPTY FIELD
  do{
    if(empty($first_name) || empty($last_name) || empty($lrn) || empty($grade_level_id) || empty($section_id) || empty($city) || empty($contact_number) || empty($student_type)){
      $error_message = "All fields are required";
      break;
    }
    //add student to database
      $first_name= "";
      $middle_name= "";
      $last_name= "";
      $lrn= "";
      $grade_level_id= "";
      $section_id= "";
      $city = "";
      $contact_number = "";
      $student_type = "";
      $photo = "";

      $success_message = "Student added successfully";

  } while(false);
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
    <link rel="stylesheet" href="../css/add.css">
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
                <th>Action</th>
              </tr>
            </thead>

            <tbody>
            <?php
            //for connection
            $servername = "localhost";
            $email = "root";
            $password = "";
            $database = "school_registrar";

            $conn = new mysqli($servername, $email, $password, $database);

            if ($conn->connect_error) {
              die("Connection failed: " . $conn->connect_error);
            }

             //query db since grades and section r on diff table we use JOIN and ordered alphabetically last name
            $sql = "SELECT s.*, g.name as grade_name, sec.name as section_name FROM students s
              LEFT JOIN grade_levels  g ON s.grade_level_id = g.id
              LEFT JOIN sections sec ON s.section_id = sec.id
              ORDER BY s.last_name ASC";

            $result = $conn-> query($sql);
             //check if query executed prperly
            if(!$result){
              die("Invalid query: " . $conn->error);
            }
              //read data of each row
              //uses internal id for edit/delete links
              while($row= $result->fetch_assoc()) {
                $status = $row['is_active'] ? "Active" : "Inactive";
                //badge used for identification
                $badge = $row['is_active'] ? 'badge_active' : 'badge_inactive';
              ?>
                <tr>
                <!-- photo of students -->
              <td>
                <?php if(!empty($row['photo'])): ?>
                  <img src = "images/<?= htmlspecialchars($row['photo'])?>" class="student-pics"/>
                  <?php endif; ?>
              </td>
              <!-- students name -->
              <td>
                <div class="student-name">
                  <?= htmlspecialchars($row['last_name'].', '. $row['first_name'].' '. $row['middle_name']) ?>
                </div>
                <div class="student-type"><?= $row['student_type']?> Student</div>
              </td>

              <!-- LRN -->
              <td><?= htmlspecialchars($row['lrn']) ?></td>
              <!-- Grade & Section -->
              <td><?= htmlspecialchars($row['grade_name'] . ' - ' . $row['section_name']) ?></td>
              <!-- City -->
              <td><?= htmlspecialchars($row['city']) ?></td>
                 <!-- contact -->
              <td><?= htmlspecialchars($row['contact_number']) ?></td>
              <!-- Status -->
              <td><span class="badge <?= $badge ?>"><?= $status ?></span></td>
              <!-- Action -->
              <td>
                <a class="btn-edit" href="edit.php?id=<?= $row['id'] ?>">Edit</a>
                <a class="btn-delete" href="delete.php?id=<?= $row['id'] ?>">Delete</a>
              </td>
            </tr>

              <?php
              }
              ?>
          
            </tbody>
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
   <?php // check if erro rmssg is not empty
    if(!empty($error_message)){
      echo "
      <div class='alert alert-warning alert-dismissible fade show' role='alert'>
        <strong>$error_message</strong>
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
    }
   ?>

  <div class="student-modal-overlay" id="student-modal">
    <div class="student-modal-box">
      <div class="student-modal-header">
        <h2 id="student-modal-title">Add New Student</h2>
        <button class="student-modal-close" id="modal-close-btn">&times;</button>
      </div>

      <form action="students.php" method="POST" enctype="multipart/form-data">
      <div class="student-modal-body">
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
            <input type="text" class="form-input" name ="first_name" id="field-firstname" value="<?php echo $first_name ?>" />
          </div>
          <div class="form-group">
            <label class="form-label">Middle Name</label>
            <input type="text" class="form-input"  name ="middle_name" id="field-middlename" value="<?php echo $middle_name ?>"  />
          </div>
          <div class="form-group">
            <label class="form-label">Last Name *</label>
            <input type="text" class="form-input"  name ="last_name" id="field-lastname" value="<?php echo $last_name ?>"  />
          </div>
          <div class="form-group">
            <label class="form-label">LRN *</label>
            <input type="text" class="form-input"  name ="lrn" id="field-id" value="<?php echo $lrn?>"  />
          </div>
          <div class="form-group">
            <!-- to follow setion from COJ -->
                           
            <!-- Grade -->
            <label class="form-label">Grade &amp; Section *</label>
            <select class="form-select" name="grade_level_id" id="field-grade">
              <option value="">Select Grade /option>
              <option>Grade 1</option>
              <option>Grade 2</option>
              <option>Grade 3</option>
            </select>
                <!-- Section -->
              <select class="form-select" name="section_id" id="field-sectionn">
              <option value="">Select Section/option>
              <option>Sampaguita</option>
              <option>Rosas</option>
            </select>

           <!-- to follow setion from COJ (AS IS STILL)-->

          </div>
          <div class="form-group">
            <label class="form-label">City</label>
            <input type="text" class="form-input" id="field-city" name = "city" value="<?php echo $city ?>"  placeholder="e.g. Quezon City" />
          </div>
          <div class="form-group">
            <label class="form-label">Contact</label>
            <input type="text" class="form-input" id="field-contact" name = "contact" value="<?php echo $contact_number ?>" placeholder="e.g. 09XX-XXX-XXXX" />
          </div>
         
          <div class="form-group">
            <label class="form-label">Status</label>
            <select  name = "status" class="form-select" id="field-status" value="<?php echo $status ?>" >
              <option value="Old-student">Old student</option>
              <option value="New-student">New student</option>
            </select>
          </div>
          
        </div>
      </div>
      
                  <!--success message -->
      <?php
      if(!empty($success_message)){
        echo "
        <div class='alert alert-success alert-dismissible fade show' role='alert'>
          <strong>$success_message</strong>
          <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
      </div>";
      }
      ?>

      <div class="student-modal-footer">
        <button class="btn-cancel" id="modal-cancel-btn">Cancel</button>
        <button  type="submit" class="btn-save-student" id="modal-save-btn">Save Student</button>
      </div>
    </div>
                </form>
  </div>

  <!-- Toast -->
  <div class="toast" id="toast"></div>

  <script  src="../js/nav.js"></script>
  <script  src="../js/students.js"></script>
    <script  src="../js/add.js"></script>

</body>
</html>
