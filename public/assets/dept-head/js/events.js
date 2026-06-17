/* ============================================================
   Department Head — Events JS
   PHP data bridge (in blade):
     var updateUrlTemplate = '{{ route(...) }}';
     var storeUrl          = '{{ route(...) }}';
   ============================================================ */

// ── Add Event modal ──────────────────────────────────────
function openAddEventModal() {
    document.getElementById('addEventModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    var today = new Date().toISOString().split('T')[0];
    document.getElementById('event_date_hidden').value = today;
}

function closeAddEventModal() {
    document.getElementById('addEventModal').style.display = 'none';
    document.getElementById('addEventForm').reset();
    document.getElementById('imagePreviewContainer').classList.remove('active');
    document.body.style.overflow = 'auto';
}

// ── Edit Event modal ─────────────────────────────────────
function closeEditEventModal() {
    document.getElementById('editEventModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ── Image preview (Add) ──────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    var imageInput = document.getElementById('imageInput');
    if (imageInput) {
        imageInput.addEventListener('change', function (e) {
            var file = e.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('imagePreviewContainer').classList.add('active');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ── Image preview (Edit) ──────────────────────────────
    var editImageInput = document.getElementById('editImageInput');
    if (editImageInput) {
        editImageInput.addEventListener('change', function (e) {
            var file = e.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('editImagePreview').src = e.target.result;
                    document.getElementById('editImagePreviewContainer').classList.add('active');
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

function removeImage() {
    document.getElementById('imageInput').value = '';
    document.getElementById('imagePreviewContainer').classList.remove('active');
}

function removeEditImage() {
    document.getElementById('editImageInput').value = '';
    document.getElementById('editImagePreviewContainer').classList.remove('active');
}

// ── Date picker (Add) ────────────────────────────────────
function showDatePicker() {
    Swal.fire({
        html: '<div style="text-align:center;padding:0.25rem 0 0.5rem;">'
            + '<h2 style="font-size:1.1rem;font-weight:800;color:#1f2937;margin-bottom:1rem;">Set Event Date</h2>'
            + '<input type="date" id="swal-date-input" class="swal2-input" style="width:85%;border:1.5px solid #d1d5db;border-radius:8px;padding:0.5rem 0.75rem;font-size:0.95rem;"></div>',
        showCancelButton: true,
        confirmButtonText: 'Set Date',
        cancelButtonText: 'Cancel',
        buttonsStyling: false,
        customClass: { confirmButton: 'swal-btn-solid', cancelButton: 'swal-btn-outline', popup: 'swal-app-popup', actions: 'swal-app-actions' },
        preConfirm: function () {
            var date = document.getElementById('swal-date-input').value;
            if (!date) { Swal.showValidationMessage('Please select a date'); }
            return date;
        }
    }).then(function (result) {
        if (result.isConfirmed) {
            document.getElementById('event_date_hidden').value = result.value;
            _swalOK('Date Set!', 'Event date: ' + new Date(result.value).toLocaleDateString());
        }
    });
}

// ── Date picker (Edit) ───────────────────────────────────
function showEditDatePicker() {
    var currentDate = document.getElementById('edit_event_date').value;
    Swal.fire({
        html: '<div style="text-align:center;padding:0.25rem 0 0.5rem;">'
            + '<h2 style="font-size:1.1rem;font-weight:800;color:#1f2937;margin-bottom:1rem;">Set Event Date</h2>'
            + '<input type="date" id="swal-edit-date-input" class="swal2-input" style="width:85%;border:1.5px solid #d1d5db;border-radius:8px;padding:0.5rem 0.75rem;font-size:0.95rem;" value="' + currentDate + '"></div>',
        showCancelButton: true,
        confirmButtonText: 'Set Date',
        cancelButtonText: 'Cancel',
        buttonsStyling: false,
        customClass: { confirmButton: 'swal-btn-solid', cancelButton: 'swal-btn-outline', popup: 'swal-app-popup', actions: 'swal-app-actions' },
        preConfirm: function () {
            var date = document.getElementById('swal-edit-date-input').value;
            if (!date) { Swal.showValidationMessage('Please select a date'); }
            return date;
        }
    }).then(function (result) {
        if (result.isConfirmed) {
            document.getElementById('edit_event_date').value = result.value;
            _swalOK('Date Updated!', 'Event date: ' + new Date(result.value).toLocaleDateString());
        }
    });
}

// ── Submit: Add Event ────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    var addForm = document.getElementById('addEventForm');
    if (addForm) {
        addForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            fetch(storeUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData
            })
            .then(function (res) {
                if (!res.ok && res.status !== 422) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    _swalOK('Posted!', data.message).then(function () { closeAddEventModal(); location.reload(); });
                } else {
                    var msg = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'Failed to post event.');
                    _swalErr(msg);
                }
            })
            .catch(function (error) {
                console.error('Post error:', error);
                _swalErr('An error occurred: ' + error.message);
            });
        });
    }
});

// ── Update Event ─────────────────────────────────────────
function doUpdateEvent() {
    var eventId = (document.getElementById('edit_event_id').value || '').trim();
    if (!eventId) { _swalErr('Could not identify the event. Please close and try again.'); return; }

    var title       = document.getElementById('edit_title').value.trim();
    var description = document.getElementById('edit_description').value.trim();
    var eventDate   = document.getElementById('edit_event_date').value;
    var department  = document.getElementById('edit_department').value;

    if (!title)       { _swalWarn('Required', 'Please enter an event title.');   return; }
    if (!description) { _swalWarn('Required', 'Please enter a description.');    return; }
    if (!eventDate)   { _swalWarn('Required', 'Please set an event date.');      return; }

    var formData = new FormData();
    formData.append('_token',      csrfToken);
    formData.append('_method',     'POST');
    formData.append('title',       title);
    formData.append('description', description);
    formData.append('event_date',  eventDate);
    formData.append('department',  department);

    var imageFile = document.getElementById('editImageInput').files[0];
    if (imageFile) formData.append('image', imageFile);

    var btn = document.getElementById('updateButton');
    if (btn) { btn.disabled = true; btn.textContent = 'Saving...'; }

    var updateUrl = updateUrlTemplate.replace('__ID__', eventId);

    fetch(updateUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: formData
    })
    .then(function (res) {
        if (!res.ok && res.status !== 422) throw new Error('HTTP ' + res.status);
        return res.json();
    })
    .then(function (data) {
        if (btn) { btn.disabled = false; btn.textContent = 'Update post'; }
        if (data.success) {
            _swalOK('Updated!', data.message).then(function () { closeEditEventModal(); location.reload(); });
        } else {
            var msg = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'Update failed.');
            _swalErr(msg);
        }
    })
    .catch(function (err) {
        if (btn) { btn.disabled = false; btn.textContent = 'Update post'; }
        console.error('Update error:', err);
        _swalErr('An error occurred: ' + err.message);
    });
}

// ── Events Filter / Sort ─────────────────────────────────
function evtApplyFilters() {
    var dateFrom = document.getElementById('evtDateFrom').value;
    var dateTo   = document.getElementById('evtDateTo').value;
    var sortBy   = document.getElementById('evtSortReactions').value;
    var search   = document.getElementById('evtSearch').value.toLowerCase().trim();

    var tbody       = document.getElementById('eventsTbody');
    var rows        = Array.from(tbody.querySelectorAll('tr[data-no]'));
    var visibleRows = [];

    rows.forEach(function (row) {
        var rowDate  = row.dataset.date;
        var rowTitle = row.dataset.title;
        var matchDate   = (!dateFrom || rowDate >= dateFrom) && (!dateTo || rowDate <= dateTo);
        var matchSearch = !search || rowTitle.indexOf(search) !== -1;
        if (matchDate && matchSearch) {
            row.style.display = '';
            visibleRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    var noRow = tbody.querySelector('tr.evt-no-results');
    if (visibleRows.length === 0) {
        if (!noRow) {
            noRow = document.createElement('tr');
            noRow.className = 'evt-no-results';
            noRow.innerHTML = '<td colspan="9" style="padding:2rem;text-align:center;color:#888;"><i class="fa-solid fa-magnifying-glass" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:0.5rem;"></i>No matching events found.</td>';
            tbody.appendChild(noRow);
        }
        noRow.style.display = '';
    } else if (noRow) {
        noRow.style.display = 'none';
    }

    if (sortBy && visibleRows.length > 0) {
        visibleRows.sort(function (a, b) {
            return parseInt(b.dataset[sortBy] || 0) - parseInt(a.dataset[sortBy] || 0);
        });
        visibleRows.forEach(function (row) { tbody.appendChild(row); });
    }

    var counter = 1;
    Array.from(tbody.querySelectorAll('tr[data-no]')).forEach(function (row) {
        if (row.style.display !== 'none') row.cells[0].textContent = counter++;
    });

    var badge = document.getElementById('eventCount');
    if (badge) badge.textContent = visibleRows.length;
}

function evtResetFilters() {
    document.getElementById('evtDateFrom').value      = '';
    document.getElementById('evtDateTo').value        = '';
    document.getElementById('evtSortReactions').value = '';
    document.getElementById('evtSearch').value        = '';

    var tbody = document.getElementById('eventsTbody');
    var rows  = Array.from(tbody.querySelectorAll('tr[data-no]'));
    rows.sort(function (a, b) { return parseInt(a.dataset.no) - parseInt(b.dataset.no); });
    rows.forEach(function (row) { tbody.appendChild(row); });

    evtApplyFilters();

    var badge = document.getElementById('eventCount');
    if (badge) badge.textContent = rows.length;
}

var evtSortDirs = {};
function evtSortTable(colIndex) {
    var tbody = document.getElementById('eventsTbody');
    var rows  = Array.from(tbody.querySelectorAll('tr[data-no]'));

    evtSortDirs[colIndex] = evtSortDirs[colIndex] === 'asc' ? 'desc' : 'asc';
    var dir = evtSortDirs[colIndex];

    rows.sort(function (a, b) {
        if ([0, 5, 6, 7].indexOf(colIndex) !== -1) {
            var aVal = parseInt((a.cells[colIndex] ? a.cells[colIndex].textContent.trim() : 0)) || 0;
            var bVal = parseInt((b.cells[colIndex] ? b.cells[colIndex].textContent.trim() : 0)) || 0;
            return dir === 'asc' ? aVal - bVal : bVal - aVal;
        }
        if (colIndex === 2) {
            return dir === 'asc' ? new Date(a.dataset.date) - new Date(b.dataset.date) : new Date(b.dataset.date) - new Date(a.dataset.date);
        }
        if (colIndex === 4) {
            var aD = a.dataset.updated ? new Date(a.dataset.updated) : new Date(0);
            var bD = b.dataset.updated ? new Date(b.dataset.updated) : new Date(0);
            return dir === 'asc' ? aD - bD : bD - aD;
        }
        var aT = a.cells[colIndex] ? a.cells[colIndex].textContent.trim() : '';
        var bT = b.cells[colIndex] ? b.cells[colIndex].textContent.trim() : '';
        return dir === 'asc' ? aT.localeCompare(bT) : bT.localeCompare(aT);
    });

    rows.forEach(function (row) { tbody.appendChild(row); });

    document.querySelectorAll('#eventsTable thead th .sort-icon').forEach(function (icon) { icon.innerHTML = '&#x21C5;'; });
    var th = document.querySelectorAll('#eventsTable thead th')[colIndex];
    if (th) {
        var icon = th.querySelector('.sort-icon');
        if (icon) icon.textContent = dir === 'asc' ? '↑' : '↓';
    }
}

// ── Auto-open Add modal from dashboard shortcut ──────────
if (new URLSearchParams(window.location.search).get('action') === 'add') {
    window.addEventListener('DOMContentLoaded', function () { openAddEventModal(); });
}

// ── Close modal on outside click ─────────────────────────
window.onclick = function (event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
};

// ── Delegated edit / delete button handlers ──────────────
document.addEventListener('DOMContentLoaded', function () {
    var tbody = document.getElementById('eventsTbody');
    if (!tbody) return;

    tbody.addEventListener('click', function (e) {
        var editBtn   = e.target.closest('.evt-edit-btn');
        var deleteBtn = e.target.closest('.evt-delete-btn');

        if (editBtn) {
            e.stopPropagation();
            var d = editBtn.dataset;
            document.getElementById('edit_event_id').value    = d.id;
            document.getElementById('edit_title').value       = d.title;
            document.getElementById('edit_department').value  = d.department;
            document.getElementById('edit_event_date').value  = d.date;
            document.getElementById('edit_description').value = d.description;

            if (d.image) {
                document.getElementById('editImagePreview').src = d.image;
                document.getElementById('editImagePreviewContainer').classList.add('active');
            } else {
                document.getElementById('editImagePreviewContainer').classList.remove('active');
            }

            document.getElementById('editEventModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        if (deleteBtn) {
            e.stopPropagation();
            var id    = deleteBtn.dataset.id;
            var title = deleteBtn.dataset.title || 'this event';
            Swal.fire({
                html: '<div style="text-align:center;padding:0.5rem 0;">'
                    + '<div style="width:70px;height:70px;background:#800020;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">'
                    + '<i class="fa-solid fa-trash" style="font-size:1.8rem;color:white;"></i></div>'
                    + '<h2 style="font-size:1.3rem;font-weight:800;color:#1f2937;margin-bottom:0.5rem;">Delete Event?</h2>'
                    + '<p style="color:#6b7280;font-size:0.92rem;margin-bottom:0.25rem;">You are about to delete:</p>'
                    + '<p style="color:#800020;font-weight:700;font-size:0.95rem;word-break:break-word;">&ldquo;' + title + '&rdquo;</p>'
                    + '<p style="color:#9ca3af;font-size:0.82rem;margin-top:0.5rem;">This action cannot be undone.</p></div>',
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-trash" style="margin-right:6px;"></i>Yes, Delete',
                cancelButtonText: 'Cancel',
                buttonsStyling: false,
                focusCancel: true,
                customClass: { popup: 'swal-app-popup', confirmButton: 'swal-btn-outline', cancelButton: 'swal-btn-solid', actions: 'swal-app-actions' }
            }).then(function (result) {
                if (result.isConfirmed) {
                    fetch('/department-head/events/' + id, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                    })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.success) {
                            _swalOK('Deleted!', data.message).then(function () { location.reload(); });
                        } else {
                            _swalErr(data.message);
                        }
                    })
                    .catch(function () { _swalErr('An error occurred. Please try again.'); });
                }
            });
        }
    });
});
