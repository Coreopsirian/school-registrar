!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Edit Student</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../css/edit.css">
    <link rel="stylesheet" href="../css/add.css">

</head>

<body>

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
              <option value="old">Old student</option>
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
    
</body>
</html>