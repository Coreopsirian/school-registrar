
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Add Student</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../css/add.css">
    <link rel="stylesheet" href="../css/styles.css">

</head>

<body>

<!-- ===== ADD / EDIT STUDENT MODAL ===== -->
 <div class="modal-overlay open" id="student-modal">
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
  <script src="../js/add.js"></script>
</body>
</html>
