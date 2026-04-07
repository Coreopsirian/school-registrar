<?php
include('../mysql/db.php'); 
session_start();

if (!isset($_SESSION['name'])) {
  header('Location: ../index.php');
  exit();
}

//search varibale
$search = $_GET['search'] ?? '';
$searchParam = "%$search%";
//pagiantion limit and get
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

//error/success
$error__message = $_GET['error'] ?? '';
$success_message = $_GET['success'] ?? '';

$search = $_GET['search'] ?? '';
$searchParam = "%$search%";


//count w search filter
$countSql = "SELECT COUNT(*) as total FROM students s
    LEFT JOIN grade_levels g ON s.grade_level_id = g.id
    LEFT JOIN sections sec ON s.section_id = sec.id
    WHERE s.first_name LIKE ? OR s.middle_name LIKE ? OR s.last_name LIKE ? OR s.lrn LIKE ?";

$countStmt = $conn->prepare($countSql);
$countStmt->bind_param("ssss", $searchParam, $searchParam, $searchParam,$searchParam);
$countStmt->execute();
$total_records = $countStmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);


//search filter for student either by name or lrn 
$sql = "SELECT s.*, g.name as grade_name, sec.name as section_name 
        FROM students s
        LEFT JOIN grade_levels g ON s.grade_level_id = g.id
        LEFT JOIN sections sec ON s.section_id = sec.id
        WHERE s.first_name LIKE ? OR s.last_name LIKE ? OR s.middle_name LIKE ?  OR s.lrn LIKE ?
        ORDER BY s.last_name ASC
        LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $searchParam, $searchParam, $searchParam, $searchParam);
$stmt->execute();
$result = $stmt->get_result();


  //  message form add.php  
$error_message   = $_GET['error'] ?? '';
$success_message = $_GET['success'] ?? '';

//fetch student for edit modal
$edit_student = null;
if (!empty($_GET['edit_id'])) {
  $edit_id = intval($_GET['edit_id']);
  $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
  $stmt->bind_param("i", $edit_id);
  $stmt->execute();
  $edit_student = $stmt->get_result()->fetch_assoc();
  $stmt->close();
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <script src = "https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
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
            <form method="GET" action="students.php" class="input-group">
                  <!--page number shown-->
                <?php if(!empty($search)): ?>
                    <input type="hidden" name="page" value="1">
                  <?php endif; ?>
         
                <input type="search" 
                name="search"          
                class="form-control rounded" 
                placeholder="Search name or LRN" 
                value="<?= htmlspecialchars($search ?? '') ?>"
                aria-label="Search" />
              <button type="submit" class="btn btn-outline-primary">Search</button>
      
         </form>
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
            <!-- Replace the export button -->
            <a href="export.php?search=<?= urlencode($search) ?>" 
              class="btn-export" id="btn-export">
              <i class="bi bi-download"></i> Export
              </a>
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
              //read data of each row; uses internal id for edit/delete links
              while($row = $result->fetch_assoc()):
                $badge = $row['student_type'] === 'new' ? 'badge_active' : 'badge_inactive';

              ?>
                <tr>
                <!-- photo of students -->
                  <td>
                    <?php if(!empty($row['photo'])): ?>
                      <img src = "uploads/<?= htmlspecialchars($row['photo'])?>" width="60" class="student-pics"/>
                      <?php endif; ?>
                  </td>
              <!-- students name -->
              <td>
                <div class="student-name">
                  <?= htmlspecialchars($row['last_name'].', '. $row['first_name'].' '. $row['middle_name']) ?>
                </div>
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
                <td><span class="badge <?= $badge ?>"><?= $row['student_type'] ?></span></td>

              <!-- Action -->
              <td>
                <a class="btn-edit" href="students.php?edit_id=<?= $row['id'] ?>">Edit</a>
                <a class="btn-delete" href="delete.php?id=<?= $row['id'] ?>">Delete</a>
              </td>
            </tr>

              <?php endwhile; ?>
            </tbody>
          </table>

        <!-- Pagination  -->
<div class="pagination-row" id="pagination-row">
  <span id="pagination-info">
    Showing <?= min($offset + 1, $total_records) ?>–<?= min($offset + $limit, $total_records) ?> of <?= $total_records ?> students
  </span>

  <nav>
    <ul class="pagination">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="students.php?page=<?= $i ?>">
            <?= $i ?>
          </a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
</div>

      </div>
    </div>
  </div>
 
<!-- ADD NEW STUDENT BUTON -->
  <div class="student-modal-overlay" id="student-modal">
    <div class="student-modal-box">
      <div class="student-modal-header">
        <h2 id="student-modal-title">Add New Student</h2>
        <button class="student-modal-close" id="modal-close-btn">&times;</button>
      </div>

      <!-- MODAL FORM -->
      <form action="add.php" method="POST" enctype="multipart/form-data">
         
      <div class="student-modal-body">
             <?php // check if erro rmssg is not empty
            if(!empty($error_message)){
              echo "
              <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                <strong>$error_message</strong>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
            }
          ?>

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


          <div class="photo-upload-area">
              <label class="form-label">Student Photo</label>
              <input type="file" name="photo" class="form-input" accept="image/*" id="photo-file-input" />
            </div>


          <input type="hidden" name="id" value="<?= $edit_student['id'] ?? '' ?>">

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">First Name *</label>
            <input type="text" class="form-input" name ="first_name" id="field-firstname" />
          </div>
          <div class="form-group">
            <label class="form-label">Middle Name</label>
            <input type="text" class="form-input"  name ="middle_name" id="field-middlename"  />
          </div>
          <div class="form-group">
            <label class="form-label">Last Name *</label>
            <input type="text" class="form-input"  name ="last_name" id="field-lastname" />
          </div>
          <div class="form-group">
            <label class="form-label">LRN *</label>
            <input type="text" class="form-input"  name ="lrn" id="field-id"  />
          </div>
          <div class="form-group">
            <!-- to follow setion from COJ -->
                           
            <!-- Grade -->
            <label class="form-label">Grade &amp; Section *</label>
            <select class="form-select" name="grade_level_id" id="field-grade">
              <option value="">Select Grade </option>
              <option value="1">Grade 7</option>
              <option value="2">Grade 8</option>
              <option value="3">Grade 9</option>
               <option value="4">Grade 10</option>
            </select>
                <!-- Section -->
              <select class="form-select" name="section_id" id="field-sectionn">
              <option value="">Select Section</option>
              <option value="1">Newton</option>
              <option value="2">Einstein</option>
                <option value="3">Curie</option>
              <option value="4">Franklin</option>
            </select>

           <!-- to follow setion from COJ (plaeholder ^)-->

          </div>
          <div class="form-group">
            <label class="form-label">City</label>
            <input type="text" class="form-input" id="field-city" name = "city"  placeholder="e.g. Quezon City" />
          </div>
          <div class="form-group">
            <label class="form-label">Contact</label>
            <input type="text" class="form-input" id="field-contact" name = "contact_number" placeholder="e.g. 09XX-XXX-XXXX" />
          </div>
         
          <div class="form-group">
            <label class="form-label">Status</label>
            <select  name = "status" class="form-select" id="field-status"  >
              <option value="old" style="color:black;">Old student</option>
              <option value="new">New student</option>
            </select>
          </div>
          
        </div>
      </div>
      
 

      <div class="student-modal-footer">
        <button type="button" class="btn-cancel" id="modal-cancel-btn">Cancel</button>
        <button type="submit" class="btn-save-student" id="modal-save-btn">Save Student</button>
      </div>
    </div>
                </form>
  </div>

  <!-- EDIT STUDENT MODAL -->
   <div class="student-modal-overlay" id="edit-modal">
  <div class="student-modal-box">
    <div class="student-modal-header">
      <h2>Edit Student</h2>
      <a href="students.php" class="student-modal-close">&times;</a>
    </div>

    <form action="edit.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= $edit_student['id']    ?? '' ?>">
      <input type="hidden" name="existing_photo" value="<?= htmlspecialchars($edit_student['photo'] ?? '') ?>">

      <div class="student-modal-body">

        <?php if (!empty($error_message) && !empty($_GET['edit_id'])): ?>
          <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($error_message) ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <div class="photo-upload-area">
          <label class="form-label">Student Photo</label>
          <?php if (!empty($edit_student['photo'])): ?>
            <img src="uploads/<?= htmlspecialchars($edit_student['photo']) ?>" class="student-pics" style="display:block;margin-bottom:8px;"/>
          <?php endif; ?>
          <input type="file" name="photo" class="form-input" accept="image/*"/>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">First Name *</label>
            <input type="text" class="form-input" name="first_name"
                   value="<?= htmlspecialchars($edit_student['first_name'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">Middle Name</label>
            <input type="text" class="form-input" name="middle_name"
                   value="<?= htmlspecialchars($edit_student['middle_name'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">Last Name *</label>
            <input type="text" class="form-input" name="last_name"
                   value="<?= htmlspecialchars($edit_student['last_name'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">LRN *</label>
            <input type="text" class="form-input" name="lrn"
                   value="<?= htmlspecialchars($edit_student['lrn'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">Grade &amp; Section *</label>
            <select class="form-select" name="grade_level_id">
              <option value="">Select Grade</option>
              <option value="1" <?= ($edit_student['grade_level_id'] ?? '') == 1 ? 'selected' : '' ?>>Grade 7</option>
              <option value="2" <?= ($edit_student['grade_level_id'] ?? '') == 2 ? 'selected' : '' ?>>Grade 8</option>
              <option value="3" <?= ($edit_student['grade_level_id'] ?? '') == 3 ? 'selected' : '' ?>>Grade 9</option>
              <option value="4" <?= ($edit_student['grade_level_id'] ?? '') == 4 ? 'selected' : '' ?>>Grade 10</option>
            </select>
            <select class="form-select" name="section_id">
              <option value="">Select Section</option>
              <option value="1" <?= ($edit_student['section_id'] ?? '') == 1 ? 'selected' : '' ?>>Newton</option>
              <option value="2" <?= ($edit_student['section_id'] ?? '') == 2 ? 'selected' : '' ?>>Einstein</option>
              <option value="3" <?= ($edit_student['section_id'] ?? '') == 3 ? 'selected' : '' ?>>Curie</option>
              <option value="4" <?= ($edit_student['section_id'] ?? '') == 4 ? 'selected' : '' ?>>Franklin</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">City</label>
            <input type="text" class="form-input" name="city" placeholder="e.g. Quezon City"
                   value="<?= htmlspecialchars($edit_student['city'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">Contact</label>
            <input type="text" class="form-input" name="contact_number" placeholder="e.g. 09XX-XXX-XXXX"
                   value="<?= htmlspecialchars($edit_student['contact_number'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="old" <?= ($edit_student['student_type'] ?? '') === 'old' ? 'selected' : '' ?>>Old Student</option>
              <option value="new" <?= ($edit_student['student_type'] ?? '') === 'new' ? 'selected' : '' ?>>New Student</option>
            </select>
          </div>
        </div>
      </div>

      <div class="student-modal-footer">
        <a href="students.php" class="btn-cancel">Cancel</a>
        <button type="submit" class="btn-save-student">Save Changes</button>
      </div>
    </form>
  </div>
</div>

  <!-- Toast -->
  <div class="toast" id="toast"></div>
  <script  src="../js/nav.js"></script>
  <script  src="../js/students.js"></script>
  <script  src="../js/add.js"></script>



  <!-- Keep modal open if there's an error or success message -->
  <?php if(!empty($error_message) || !empty($success_message)): ?>
  <script>
    document.getElementById('student-modal').classList.add('open');
  </script>
  <?php endif; ?>


  <!-- auto open edit modal -->
  <?php if (!empty($_GET['edit_id'])): ?>
<script>
  document.getElementById('edit-modal').classList.add('open');
</script>
<?php endif; ?>
</body>
</html>
