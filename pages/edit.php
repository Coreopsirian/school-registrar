!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Edit Student</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../css/edit.css">

<style>
/* Page layout - center everything */
body {
  font-family: 'Inter', sans-serif;
  background: #f0f2f8;
  margin: 0;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Form container */
.form-container {
  width: 420px;
  background: white;
  padding: 25px;
  border-radius: 15px;
  box-shadow: 0 2px 12px rgba(61,74,138,0.10);
}

/* Form title */
.form-container h2 {
  margin-bottom: 15px;
  font-size: 20px;
}

/* Form spacing */
.form-group {
  margin-bottom: 12px;
}

/* Labels */
label {
  display: block;
  margin-bottom: 4px;
  font-weight: 500;
  font-size: 14px;
}

/* Inputs */
input, select {
  width: 95%;
  padding: 8px;
  border-radius: 6px;
  border: 1px solid #dde3f0;
}

/* Update button */
.btn-submit {
  background: #494C8A;
  color: white;
  border: none;
  padding: 10px;
  width: 100%;
  border-radius: 6px;
  margin-top: 10px;
  cursor: pointer;
}

/* Back button */
.btn-back {
  background: gray;
  color: white;
  border: none;
  padding: 10px;
  width: 100%;
  border-radius: 6px;
  margin-top: 8px;
  cursor: pointer;
}
</style>
</head>

<body>

<div class="form-container">
<h2>Edit Student</h2>

<!-- Form sends data to update_student.php -->
<form action="update_student.php" method="POST">

  <!-- Hidden ID (used by PHP to know which student to update) -->
  <input type="hidden" name="student_id">

  <!-- Student Name (Required) -->
  <div class="form-group">
    <label>Student Name *</label>
    <input type="text" name="name" required>
  </div>

  <!-- Student Number (Required) -->
  <div class="form-group">
    <label>Student ID *</label>
    <input type="text" name="student_number" required>
  </div>

  <!-- Grade and Section (Required) -->
  <div class="form-group">
    <label>Grade & Section *</label>
    <input type="text" name="grade_section" required>
  </div>

  <!-- City -->
  <div class="form-group">
    <label>City *</label>
    <input type="text" name="city" required>
  </div>

  <!-- Contact -->
  <div class="form-group">
    <label>Contact *</label>
    <input type="text" name="contact" required>
  </div>

  <!-- Status Dropdown -->
  <div class="form-group">
    <label>Status *</label>
    <select name="status" required>
      <option value="">Select Status</option>
      <option value="Active">Active</option>
      <option value="Inactive">Inactive</option>
    </select>
  </div>

  <!-- Submit Button -->
  <button type="submit" class="btn-submit">
    Update Student
  </button>

</form>

<!-- Back button to Students page -->
<button class="btn-back"
onclick="location.href='Frontend.html'">
  Back to Students
</button>

</div>

</body>
</html>
