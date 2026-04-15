<?php $active_portal = $active_portal ?? ''; ?>
<nav class="portal-nav">
  <div class="portal-nav-brand">
    <img src="../images/COJ.png" alt="COJ"/>
    <span>Parent Portal</span>
  </div>
  <div class="portal-nav-links">
    <a href="dashboard.php" class="<?= $active_portal==='dashboard'?'active':'' ?>"><i class="bi bi-grid-fill"></i> Dashboard</a>
    <a href="requirements.php" class="<?= $active_portal==='requirements'?'active':'' ?>"><i class="bi bi-folder2-open"></i> Requirements</a>
    <a href="soa.php" class="<?= $active_portal==='soa'?'active':'' ?>"><i class="bi bi-receipt"></i> Statement of Account</a>
    <a href="clearance.php" class="<?= $active_portal==='clearance'?'active':'' ?>"><i class="bi bi-patch-check-fill"></i> Clearance</a>
  </div>
  <div class="portal-nav-user">
    <i class="bi bi-person-circle"></i>
    <span><?= htmlspecialchars($parent_name) ?></span>
    <a href="logout.php" class="portal-logout"><i class="bi bi-box-arrow-right"></i></a>
  </div>
</nav>
