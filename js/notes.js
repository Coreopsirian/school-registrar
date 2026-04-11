(function () {
  var notes      = [];
  var selectedId = null;
  var isNew      = false;
  var isDirty    = false;   // tracks unsaved changes
  var autoSaveTimer = null;

  // DOM refs
  var container    = document.getElementById('notesContainer');
  var countLabel   = document.getElementById('notesCountLabel');
  var searchInput  = document.getElementById('notesSearchInput');
  var catFilter    = document.getElementById('notesCategoryFilter');
  var addBtn       = document.getElementById('addNoteBtn');
  var placeholder  = document.getElementById('editorPlaceholder');
  var editorDiv    = document.getElementById('editorContent');
  var titleInput   = document.getElementById('noteTitle');
  var bodyInput    = document.getElementById('noteBody');
  var categorySel  = document.getElementById('noteCategory');
  var timestamp    = document.getElementById('noteTimestamp');
  var charCount    = document.getElementById('charCount');
  var saveBtn      = document.getElementById('saveNoteBtn');
  var deleteBtn    = document.getElementById('deleteNoteBtn');
  var confirmOverlay  = document.getElementById('notesConfirmOverlay');
  var confirmDelBtn   = document.getElementById('confirmDeleteBtn');
  var cancelDelBtn    = document.getElementById('cancelDeleteBtn');

  // ── Fetch notes from DB ──────────────────────────
  function fetchNotes() {
    var search = searchInput.value;
    var cat    = catFilter.value;
    var url    = 'notes_fetch.php?search=' + encodeURIComponent(search);
    if (cat) url += '&category=' + encodeURIComponent(cat);

    fetch(url)
      .then(r => r.json())
      .then(function(data) {
        notes = data;
        renderList();
      });
  }

  // ── Render note list ─────────────────────────────
  function renderList() {
    countLabel.textContent = 'All Notes (' + notes.length + ')';
    var emptyState = document.getElementById('emptyState');

    container.innerHTML = '';
    container.appendChild(emptyState);

    if (notes.length === 0) {
      emptyState.style.display = 'block';
      return;
    }
    emptyState.style.display = 'none';

    notes.forEach(function(note) {
      var item = document.createElement('div');
      item.className = 'note-item' + (note.id == selectedId ? ' selected' : '');
      item.dataset.id = note.id;

      var preview = note.body ? note.body.replace(/\n/g, ' ').slice(0, 60) : 'No content';

      item.innerHTML =
        '<div class="note-item-header">' +
          '<span class="note-item-title">' + esc(note.title) + '</span>' +
          '<span class="note-item-category cat-' + note.category + '">' + note.category + '</span>' +
        '</div>' +
        '<div class="note-item-preview">' + esc(preview) + '</div>' +
        '<div class="note-item-date">' + formatDate(note.updated_at || note.created_at) + '</div>';

      item.addEventListener('click', function() {
        if (isDirty) {
          if (!confirm('You have unsaved changes. Discard and switch notes?')) return;
        }
        selectNote(note.id);
      });
      container.appendChild(item);
    });
  }

  // ── Select a note ────────────────────────────────
  function selectNote(id) {
    selectedId = id;
    isNew = false;
    isDirty = false;
    var note = notes.find(n => n.id == id);
    if (!note) return;

    titleInput.value      = note.title;
    bodyInput.value       = note.body;
    categorySel.value     = note.category;
    timestamp.textContent = 'Last edited ' + formatDate(note.updated_at);
    charCount.textContent = (note.body || '').length + ' chars';
    deleteBtn.style.display = '';
    updateSaveBtnState();
    showEditor(true);
    renderList();
  }

  // ── New note ─────────────────────────────────────
  addBtn.addEventListener('click', function() {
    if (isDirty) {
      if (!confirm('You have unsaved changes. Discard and create new note?')) return;
    }
    selectedId = null;
    isNew = true;
    isDirty = false;
    titleInput.value      = '';
    bodyInput.value       = '';
    categorySel.value     = 'General';
    timestamp.textContent = '';
    charCount.textContent = '0 chars';
    deleteBtn.style.display = 'none';
    updateSaveBtnState();
    showEditor(true);
    titleInput.focus();
    renderList();
  });

  // ── Track changes ────────────────────────────────
  function markDirty() {
    isDirty = true;
    updateSaveBtnState();
    // Auto-save after 3 seconds of inactivity
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(function() {
      if (isDirty) saveNote(true); // silent auto-save
    }, 3000);
  }

  titleInput.addEventListener('input', markDirty);
  bodyInput.addEventListener('input', function() {
    charCount.textContent = bodyInput.value.length + ' chars';
    markDirty();
  });
  categorySel.addEventListener('change', markDirty);

  // ── Save button state ─────────────────────────────
  function updateSaveBtnState() {
    saveBtn.style.opacity = isDirty ? '1' : '0.6';
    saveBtn.title = isDirty ? 'Save (Ctrl+S)' : 'No changes';
  }

  // ── Ctrl+S shortcut ──────────────────────────────
  document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
      e.preventDefault();
      if (editorDiv.style.display !== 'none') saveNote(false);
    }
  });

  // ── Save ─────────────────────────────────────────
  saveBtn.addEventListener('click', function() { saveNote(false); });

  function saveNote(silent) {
    var title    = titleInput.value.trim() || 'Untitled Note';
    var body     = bodyInput.value.trim();
    var category = categorySel.value;

    fetch('notes_save.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: selectedId || 0, title, body, category })
    })
    .then(r => r.json())
    .then(function(res) {
      if (res.success) {
        selectedId = res.id;
        isNew  = false;
        isDirty = false;
        deleteBtn.style.display = '';
        updateSaveBtnState();
        timestamp.textContent = 'Last edited ' + formatDate(new Date().toISOString());
        if (!silent) showToast('Note saved!', 'success');
        fetchNotes();
      }
    });
  }

  // ── Delete ───────────────────────────────────────
  deleteBtn.addEventListener('click', function() {
    confirmOverlay.style.display = 'flex';
  });

  confirmDelBtn.addEventListener('click', function() {
    fetch('notes_delete.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: selectedId })
    })
    .then(r => r.json())
    .then(function(res) {
      if (res.success) {
        selectedId = null;
        isDirty = false;
        confirmOverlay.style.display = 'none';
        showEditor(false);
        showToast('Note deleted.', 'danger');
        fetchNotes();
      }
    });
  });

  cancelDelBtn.addEventListener('click', function() {
    confirmOverlay.style.display = 'none';
  });

  // ── Warn before leaving with unsaved changes ──────
  window.addEventListener('beforeunload', function(e) {
    if (isDirty) {
      e.preventDefault();
      e.returnValue = '';
    }
  });

  // ── Search & filter ───────────────────────────────
  searchInput.addEventListener('input', fetchNotes);
  catFilter.addEventListener('change', fetchNotes);

  // ── Helpers ──────────────────────────────────────
  function showEditor(show) {
    placeholder.style.display = show ? 'none' : 'flex';
    editorDiv.style.display   = show ? 'flex' : 'none';
  }

  function formatDate(iso) {
    if (!iso) return '';
    var d = new Date(iso);
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear()
      + ' · ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  }

  function esc(str) {
    return (str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  function showToast(msg, type) {
    var t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast ' + (type || '');
    setTimeout(function() { t.classList.add('show'); }, 10);
    setTimeout(function() { t.classList.remove('show'); }, 3200);
  }

  // Init
  updateSaveBtnState();
  showEditor(false);
  fetchNotes();
})();
