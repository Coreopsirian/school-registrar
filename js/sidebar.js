/* ==========================================================
   sidebar.js — Sidebar toggle & navigation logic
   ========================================================== */

const pageMeta = {
  dashboard:  { title: 'Dashboard',        sub: 'Welcome back Admin!' },
  students:   { title: 'Student Records',  sub: 'Manage and view all registered students' },
  attendance: { title: 'Attendance',        sub: 'Track daily faculty attendance' },
  reports:    { title: 'Reports',           sub: 'View and export school reports' },
  notes:      { title: 'Notes',             sub: 'Manage your notes and reminders' }
};

function initSidebar() {
  const sidebar   = document.getElementById('sidebar');
  const toggleBtn = document.getElementById('toggleBtn');
  const navItems  = document.querySelectorAll('.nav-item');
  const pages     = document.querySelectorAll('.page');

  // Toggle collapse
  toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
  });

  // Page navigation
  navItems.forEach(item => {
    item.addEventListener('click', () => {
      const target = item.getAttribute('data-page');

      navItems.forEach(n => n.classList.remove('active'));
      item.classList.add('active');

      pages.forEach(p => {
        p.classList.toggle('active', p.id === `page-${target}`);
      });

      const meta = pageMeta[target] || { title: target, sub: '' };
      document.getElementById('topbar-title').textContent = meta.title;
      document.getElementById('topbar-sub').textContent   = meta.sub;
    });
  });
}

export
 { 
initSidebar
 };
