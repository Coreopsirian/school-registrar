<?php
include('../mysql/db.php');
if (session_id() == "") session_start();

if (!isset($_SESSION['name'])) {
  header('location: ../index.php');
  exit();
}

// ── Student stats ──────────────────────────────────────────
$total  = $conn->query("SELECT COUNT(*) as c FROM students WHERE is_archived=0")->fetch_assoc()['c'];
$new_s  = $conn->query("SELECT COUNT(*) as c FROM students WHERE student_type='new' AND is_archived=0")->fetch_assoc()['c'];
$old_s  = $conn->query("SELECT COUNT(*) as c FROM students WHERE student_type='old' AND is_archived=0")->fetch_assoc()['c'];

// ── Teacher + attendance stats ─────────────────────────────
$total_teachers = 0;
$att_today = ['present' => 0, 'absent' => 0, 'late' => 0];
$teachers_exist = $conn->query("SHOW TABLES LIKE 'teachers'")->num_rows > 0;
$today = date('Y-m-d');

if ($teachers_exist) {
  $total_teachers = $conn->query("SELECT COUNT(*) as c FROM teachers WHERE is_archived=0")->fetch_assoc()['c'];
  $att_row = $conn->query("
    SELECT
      SUM(status='present') as present,
      SUM(status='absent')  as absent,
      SUM(status='late')    as late
    FROM teacher_attendance WHERE date='$today'
  ")->fetch_assoc();
  $att_today = [
    'present' => (int)($att_row['present'] ?? 0),
    'absent'  => (int)($att_row['absent']  ?? 0),
    'late'    => (int)($att_row['late']    ?? 0),
  ];
}

$att_rate = $total_teachers > 0
  ? round(($att_today['present'] / $total_teachers) * 100)
  : 0;

// ── Students per grade ─────────────────────────────────────
$grade_labels = [];
$grade_counts = [];
$grade_res = $conn->query("
  SELECT g.name as grade, COUNT(s.id) as total
  FROM grade_levels g
  LEFT JOIN students s ON s.grade_level_id = g.id AND s.is_archived = 0
  GROUP BY g.id ORDER BY g.id
");
while ($g = $grade_res->fetch_assoc()) {
  $grade_labels[] = $g['grade'];
  $grade_counts[] = (int)$g['total'];
}

// ── Section enrollment ─────────────────────────────────────
$sections_exist = $conn->query("SHOW TABLES LIKE 'sections'")->num_rows > 0;
$enrollment = [];
if ($sections_exist) {
  $enroll_res = $conn->query("
    SELECT g.name as grade, sec.name as section, COUNT(s.id) as total
    FROM grade_levels g
    CROSS JOIN sections sec
    LEFT JOIN students s ON s.grade_level_id = g.id AND s.section_id = sec.id AND s.is_archived = 0
    GROUP BY g.id, sec.id ORDER BY g.id, sec.id
  ");
  while ($row = $enroll_res->fetch_assoc()) {
    $enrollment[$row['grade']][$row['section']] = (int)$row['total'];
  }
}

// ── Recent students ────────────────────────────────────────
$recent = $conn->query("
  SELECT s.first_name, s.last_name, s.photo, g.name as grade, s.student_type, s.id
  FROM students s
  LEFT JOIN grade_levels g ON s.grade_level_id = g.id
  WHERE s.is_archived = 0
  ORDER BY s.id DESC LIMIT 6
");

// ── All teachers + today attendance ───────────────────────
$all_teachers = null;
if ($teachers_exist) {
  $all_teachers = $conn->query("
    SELECT t.first_name, t.last_name, t.subject, a.status
    FROM teachers t
    LEFT JOIN teacher_attendance a ON a.teacher_id = t.id AND a.date = '$today'
    WHERE t.is_archived = 0
    ORDER BY t.last_name ASC
  ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard</title>
  <link rel="icon" type="image/png" href="../images/COJ.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

  <!-- IMPORTANT: styles.css MUST come before dashboard.css -->
  <link rel="stylesheet" href="../css/styles.css?v=<?= time() ?>">
  <link rel="stylesheet" href="../css/dashboard.css?v=<?= time() ?>">
</head>
<body>

<!-- ===== SIDEBAR ===== -->
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
    <div class="nav-item active" data-href="dashboard.php" data-label="Dashboard">
      <span class="nav-icon"><i class="bi bi-grid-fill"></i></span>
      <span class="nav-text">Dashboard</span>
    </div>
    <div class="nav-item" data-href="students.php" data-label="Students">
      <span class="nav-icon"><i class="bi bi-people-fill"></i></span>
      <span class="nav-text">Students</span>
    </div>
    <div class="nav-item" data-href="teachers.php" data-label="Teachers">
      <span class="nav-icon"><i class="bi bi-person-workspace"></i></span>
      <span class="nav-text">Teachers</span>
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
    <?php if (($_SESSION['role'] ?? '') === 'superadmin'): ?>
    <div class="nav-item" data-href="users.php" data-label="Users">
      <span class="nav-icon"><i class="bi bi-shield-lock-fill"></i></span>
      <span class="nav-text">Users</span>
    </div>
    <?php endif; ?>
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
      <div class="page-title">Dashboard</div>
      <div class="page-sub">Analytics Overview</div>
    </div>
    <div class="topbar-user-chip">
      <i class="bi bi-person-circle"></i>
      <span><?= htmlspecialchars($_SESSION['name']) ?></span>
    </div>
  </div>

  <!-- PAGE CONTENT -->
  <div id="page-container">

    <!-- ══════════════════════════════════════
         ROW 1 — STAT CARDS
    ══════════════════════════════════════ -->
    <div class="stat-grid">

      <div class="stat-card">
        <div class="stat-icon-wrap" style="background:#dbeafe;">
          <i class="bi bi-person-workspace" style="color:#2563eb;"></i>
        </div>
        <div class="stat-body">
          <div class="stat-value"><?= $total_teachers ?></div>
          <div class="stat-label">Teachers</div>
        </div>
        <a href="teachers.php" class="stat-arrow" title="View teachers">
          <i class="bi bi-arrow-right-short"></i>
        </a>
      </div>

      <div class="stat-card">
        <div class="stat-icon-wrap" style="background:#fef9c3;">
          <i class="bi bi-people-fill" style="color:#ca8a04;"></i>
        </div>
        <div class="stat-body">
          <div class="stat-value"><?= $total ?></div>
          <div class="stat-label">Students</div>
        </div>
        <a href="students.php" class="stat-arrow" title="View students">
          <i class="bi bi-arrow-right-short"></i>
        </a>
      </div>

      <div class="stat-card">
        <div class="stat-icon-wrap" style="background:#dcfce7;">
          <i class="bi bi-person-plus-fill" style="color:#16a34a;"></i>
        </div>
        <div class="stat-body">
          <div class="stat-value"><?= $new_s ?></div>
          <div class="stat-label">New Enrollees</div>
        </div>
        <a href="students.php" class="stat-arrow" title="View new students">
          <i class="bi bi-arrow-right-short"></i>
        </a>
      </div>

      <div class="stat-card">
        <div class="stat-icon-wrap" style="background:#fce7f3;">
          <i class="bi bi-calendar2-check-fill" style="color:#db2777;"></i>
        </div>
        <div class="stat-body">
          <div class="stat-value"><?= $att_today['present'] ?></div>
          <div class="stat-label">Present Today</div>
        </div>
        <a href="attendance.php" class="stat-arrow" title="View attendance">
          <i class="bi bi-arrow-right-short"></i>
        </a>
      </div>

    </div>
    <!-- /stat-grid -->

    <!-- ══════════════════════════════════════
         ROW 2 — CHART + SECTION ENROLLMENT
    ══════════════════════════════════════ -->
    <div class="dash-row">

      <!-- Bar chart: Students per Grade -->
      <div class="dash-panel">
        <div class="panel-header">
          <div class="panel-title">
            <i class="bi bi-bar-chart-fill"></i> Students per Grade
          </div>
        </div>
        <div class="panel-body">
          <div class="chart-wrap">
            <canvas id="gradeChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Section enrollment table -->
      <div class="dash-panel">
        <div class="panel-header">
          <div class="panel-title">
            <i class="bi bi-grid-3x3-gap-fill"></i> Section Enrollment
          </div>
        </div>
        <div class="panel-body panel-body-flush">
          <?php if (empty($enrollment)): ?>
            <div class="empty-msg">No section data yet.</div>
          <?php else:
            $sections = array_keys(reset($enrollment));
          ?>
            <table class="enroll-table">
              <thead>
                <tr>
                  <th>Grade</th>
                  <?php foreach ($sections as $sec): ?>
                    <th><?= htmlspecialchars($sec) ?></th>
                  <?php endforeach; ?>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($enrollment as $grade => $secs):
                  $row_total = array_sum($secs);
                ?>
                <tr>
                  <td class="enroll-grade"><?= htmlspecialchars($grade) ?></td>
                  <?php foreach ($secs as $count): ?>
                    <td class="enroll-count"><?= $count ?></td>
                  <?php endforeach; ?>
                  <td class="enroll-total"><?= $row_total ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>

    </div>
    <!-- /dash-row -->

    <!-- ══════════════════════════════════════
         ROW 3 — RECENT STUDENTS + ATTENDANCE
    ══════════════════════════════════════ -->
    <div class="dash-row">

      <!-- Recent registrations -->
      <div class="dash-panel">
        <div class="panel-header">
          <div class="panel-title">
            <i class="bi bi-clock-history"></i> Recent Registrations
          </div>
          <a href="students.php" class="panel-link">View all →</a>
        </div>
        <div class="panel-body panel-body-flush">
          <table class="dash-table">
            <thead>
              <tr>
                <th>Student</th>
                <th>Grade</th>
                <th>Type</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php while ($r = $recent->fetch_assoc()): ?>
              <tr>
                <td>
                  <div class="name-cell">
                    <?php if (!empty($r['photo'])): ?>
                      <img src="uploads/<?= htmlspecialchars($r['photo']) ?>" class="mini-pic"/>
                    <?php else: ?>
                      <div class="mini-avatar"><i class="bi bi-person-fill"></i></div>
                    <?php endif; ?>
                    <span><?= htmlspecialchars($r['last_name'] . ', ' . $r['first_name']) ?></span>
                  </div>
                </td>
                <td class="td-muted"><?= htmlspecialchars($r['grade'] ?? '—') ?></td>
                <td>
                  <span class="type-badge <?= $r['student_type'] === 'new' ? 'badge-new' : 'badge-old' ?>">
                    <?= ucfirst($r['student_type']) ?>
                  </span>
                </td>
                <td>
                  <a href="student_profile.php?id=<?= $r['id'] ?>" class="row-link">
                    <i class="bi bi-arrow-right"></i>
                  </a>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Attendance today -->
      <div class="dash-panel">
        <div class="panel-header">
          <div class="panel-title">
            <i class="bi bi-calendar2-check-fill"></i> Attendance Today
          </div>
          <a href="attendance.php" class="panel-link">Mark →</a>
        </div>
        <div class="panel-body panel-body-flush">

          <!-- 3 summary boxes -->
          <div class="att-summary">
            <div class="att-sum-item att-present">
              <span class="att-sum-val"><?= $att_today['present'] ?></span>
              <span class="att-sum-lbl">Present</span>
            </div>
            <div class="att-sum-item att-absent">
              <span class="att-sum-val"><?= $att_today['absent'] ?></span>
              <span class="att-sum-lbl">Absent</span>
            </div>
            <div class="att-sum-item att-late">
              <span class="att-sum-val"><?= $att_today['late'] ?></span>
              <span class="att-sum-lbl">Late</span>
            </div>
          </div>

          <!-- Rate bar -->
          <?php if ($total_teachers > 0): ?>
          <div class="att-rate-wrap">
            <div class="att-rate-row">
              <span class="att-rate-lbl">Attendance Rate</span>
              <span class="att-rate-val"><?= $att_rate ?>%</span>
            </div>
            <div class="att-rate-track">
              <div class="att-rate-fill" style="width:<?= $att_rate ?>%"></div>
            </div>
          </div>
          <?php endif; ?>

          <!-- Teacher list -->
          <?php if (!$teachers_exist || $total_teachers == 0): ?>
            <div class="empty-msg">No teachers added yet.</div>
          <?php else: ?>
            <div class="att-teacher-list">
              <?php while ($t = $all_teachers->fetch_assoc()):
                $s    = $t['status'] ?? null;
                $dot  = match($s) { 'present' => 'dot-present', 'absent' => 'dot-absent', 'late' => 'dot-late', default => 'dot-none' };
                $bdg  = match($s) { 'present' => 'status-present', 'absent' => 'status-absent', 'late' => 'status-late', default => 'status-none' };
                $lbl  = $s ? ucfirst($s) : 'Not marked';
              ?>
              <div class="att-teacher-row">
                <span class="att-dot <?= $dot ?>"></span>
                <div class="att-teacher-info">
                  <div class="att-teacher-name"><?= htmlspecialchars($t['last_name'] . ', ' . $t['first_name']) ?></div>
                  <div class="att-teacher-sub"><?= htmlspecialchars($t['subject'] ?? '') ?></div>
                </div>
                <span class="att-status-badge <?= $bdg ?>"><?= $lbl ?></span>
              </div>
              <?php endwhile; ?>
            </div>
          <?php endif; ?>

        </div>
      </div>

    </div>
    <!-- /dash-row -->

  </div>
  <!-- /page-container -->
</div>
<!-- /main -->

<script src="../js/nav.js"></script>
<script>
  Chart.defaults.font.family = 'Inter, sans-serif';
  Chart.defaults.color = '#7a869a';

  new Chart(document.getElementById('gradeChart'), {
    type: 'bar',
    data: {
      labels: <?= json_encode($grade_labels) ?>,
      datasets: [{
        label: 'Students',
        data: <?= json_encode($grade_counts) ?>,
        backgroundColor: ['#6366f1', '#22c55e', '#f59e0b', '#ef4444'],
        borderRadius: 8,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: { label: ctx => ' ' + ctx.parsed.y + ' students' }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { stepSize: 1, color: '#94a3b8', font: { size: 11 } },
          grid: { color: 'rgba(0,0,0,0.05)' },
          border: { display: false }
        },
        x: {
          ticks: { color: '#64748b', font: { size: 12, weight: '600' } },
          grid: { display: false },
          border: { display: false }
        }
      }
    }
  });
</script>

</body>
</html>