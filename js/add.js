// Open modal
document.getElementById('btn-add-student').addEventListener('click', () => {
  document.getElementById('student-modal').classList.add('open');
});

// Close modal
document.getElementById('modal-close-btn').addEventListener('click', () => {
  document.getElementById('student-modal').classList.remove('open');
});

document.getElementById('modal-cancel-btn').addEventListener('click', () => {
  document.getElementById('student-modal').classList.remove('open');
});

// Close when clicking outside the box
document.getElementById('student-modal').addEventListener('click', (e) => {
  if (e.target === e.currentTarget) {
    e.currentTarget.classList.remove('open');
  }
});