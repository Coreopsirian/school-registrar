<?php
// Reusable sidebar — include this in every page
// Set $active_page before including, e.g. $active_page = 'students';
$active_page = $active_page ?? '';
?>
<aside id="sidebar">
  <div class="sidebar-logo-box">
    <img src="../images/COJ.png" alt="School Logo"/>
    <div class="logo-text">
      <div class="school-name">Catholic<br/>Progressive School</div>
      <div class="school-sub">Enrollment System</div>
    </div>
  </div>
  <div class="sidebar-toggle">
    <button class="toggle-btn" id="toggleBtn">&#9664;</button>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-item <?= $active_page==='dashboard'?'active':'' ?>" data-href="dashboard.php" data-label="Dashboard">
      <span class="nav-icon"><i class="bi bi-grid-fill"></i></span><span class="nav-text">Dashboard</span>
    </div>
    <div class="nav-item <?= $active_page==='students'?'active':'' ?>" data-href="students.php" data-label="Students">
      <span class="nav-icon"><i class="bi bi-people-fill"></i></span><span class="nav-text">Students</span>
    </div>
    <div class="nav-item <?= $active_page==='enrollment'?'active':'' ?>" data-href="enrollment.php" data-label="Enrollment">
      <span class="nav-icon"><i class="bi bi-person-check-fill"></i></span><span class="nav-text">Enrollment</span>
    </div>
    <div class="nav-item <?= $active_page==='requirements'?'active':'' ?>" data-href="requirements.php" data-label="Requirements">
      <span class="nav-icon"><i class="bi bi-folder2-open"></i></span><span class="nav-text">Requirements</span>
    </div>
    <div class="nav-item <?= $active_page==='payments'?'active':'' ?>" data-href="payments.php" data-label="Payments">
      <span class="nav-icon"><i class="bi bi-cash-coin"></i></span><span class="nav-text">Payments</span>
    </div>
    <div class="nav-item <?= $active_page==='fees'?'active':'' ?>" data-href="fees.php" data-label="Fees">
      <span class="nav-icon"><i class="bi bi-receipt"></i></span><span class="nav-text">Fees</span>
    </div>
    <div class="nav-item <?= $active_page==='clearance'?'active':'' ?>" data-href="clearance.php" data-label="Clearance">
      <span class="nav-icon"><i class="bi bi-patch-check-fill"></i></span><span class="nav-text">Clearance</span>
    </div>
    <div class="nav-item <?= $active_page==='reports'?'active':'' ?>" data-href="reports.php" data-label="Reports">
      <span class="nav-icon"><i class="bi bi-file-earmark-text-fill"></i></span><span class="nav-text">Reports</span>
    </div>
    <div class="nav-item <?= $active_page==='notes'?'active':'' ?>" data-href="notes.php" data-label="Notes">
      <span class="nav-icon"><i class="bi bi-journal-text"></i></span><span class="nav-text">Notes</span>
    </div>
    <?php
    $unread = 0;
    if (isset($_SESSION['user_id'])) {
      $unread_res = $conn->query("SELECT COUNT(*) as c FROM messages WHERE recipient_id={$_SESSION['user_id']} AND is_read=0");
      if ($unread_res) $unread = $unread_res->fetch_assoc()['c'];
    }
    ?>
    <div class="nav-item <?= $active_page==='messages'?'active':'' ?>" data-href="messages.php" data-label="Messages">
      <span class="nav-icon"><i class="bi bi-chat-dots-fill"></i></span>
      <span class="nav-text">Messages <?php if ($unread > 0): ?><span style="background:#e53e3e;color:#fff;border-radius:999px;font-size:10px;padding:1px 6px;margin-left:4px;"><?= $unread ?></span><?php endif; ?></span>
    </div>
    <div class="nav-item <?= $active_page==='discounts'?'active':'' ?>" data-href="discounts.php" data-label="Discounts">
      <span class="nav-icon"><i class="bi bi-percent"></i></span><span class="nav-text">Discounts</span>
    </div>
    <?php if (($_SESSION['role'] ?? '') === 'superadmin'): ?>
    <div class="nav-item <?= $active_page==='users'?'active':'' ?>" data-href="users.php" data-label="Users">
      <span class="nav-icon"><i class="bi bi-shield-lock-fill"></i></span><span class="nav-text">Users</span>
    </div>
    <div class="nav-item <?= $active_page==='school_years'?'active':'' ?>" data-href="school_years.php" data-label="School Years">
      <span class="nav-icon"><i class="bi bi-calendar2-range-fill"></i></span><span class="nav-text">School Years</span>
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
