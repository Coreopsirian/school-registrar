/* ==========================================================
   app.js — Entry point; bootstraps all modules
   ========================================================== */

import
 { 
    initSidebar 
 } 
  from './sidebar.js';

import 
{ 
    initStudents

 } from './students.js';

document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  initStudents();
});
