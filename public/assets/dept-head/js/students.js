/* ============================================================
   Department Head — Students JS
   PHP data bridge (in blade): const students = @json($students)
   Session toasts + errors also handled inline in blade.
   ============================================================ */

// ── Pagination / filter state ────────────────────────────
var ROWS_PER_PAGE = 10;
var currentPage  = 1;
var sortCol      = -1;
var sortDir      = 1;
var filteredRows = [];

function getAllRows() {
    return Array.from(document.querySelectorAll('#studentsTbody tr'));
}

function applyFilters() {
    var search    = (document.getElementById('searchInput')    ? document.getElementById('searchInput').value    : '').toLowerCase();
    var yearLevel = (document.getElementById('filterYearLevel') ? document.getElementById('filterYearLevel').value : '').toLowerCase();
    var dateFrom  =  document.getElementById('filterDateFrom') ? document.getElementById('filterDateFrom').value  : '';
    var dateTo    =  document.getElementById('filterDateTo')   ? document.getElementById('filterDateTo').value    : '';

    filteredRows = getAllRows().filter(function (row) {
        var name = row.dataset.name      || '';
        var sid  = row.dataset.studentId || '';
        var year = row.dataset.year      || '';
        var date = row.dataset.date      || '';
        if (search    && name.indexOf(search)       === -1 && sid.indexOf(search) === -1) return false;
        if (yearLevel && year.indexOf(yearLevel)    === -1) return false;
        if (dateFrom  && date < dateFrom)                   return false;
        if (dateTo    && date > dateTo)                     return false;
        return true;
    });

    currentPage = 1;
    renderTable();
}

function resetFilters() {
    ['searchInput', 'filterYearLevel', 'filterDateFrom', 'filterDateTo'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.value = '';
    });
    applyFilters();
}

function sortTable(col) {
    if (sortCol === col) { sortDir *= -1; } else { sortCol = col; sortDir = 1; }
    filteredRows.sort(function (a, b) {
        var aT = a.cells[col] ? a.cells[col].innerText.trim() : '';
        var bT = b.cells[col] ? b.cells[col].innerText.trim() : '';
        return aT.localeCompare(bT, undefined, { numeric: true }) * sortDir;
    });
    renderTable();
}

function renderTable() {
    var total      = filteredRows.length;
    var totalPages = Math.max(1, Math.ceil(total / ROWS_PER_PAGE));
    if (currentPage > totalPages) currentPage = totalPages;

    getAllRows().forEach(function (r) { r.style.display = 'none'; });
    var start = (currentPage - 1) * ROWS_PER_PAGE;
    var end   = Math.min(start + ROWS_PER_PAGE, total);
    filteredRows.slice(start, end).forEach(function (r) { r.style.display = ''; });

    filteredRows.forEach(function (r, i) { r.cells[0].textContent = i + 1; });

    var infoEl = document.getElementById('tableInfo');
    if (infoEl) {
        infoEl.textContent = total === 0
            ? 'No students found'
            : 'Showing ' + (start + 1) + ' to ' + end + ' of ' + total + ' student' + (total !== 1 ? 's' : '');
    }
    var countEl = document.getElementById('studentCount');
    if (countEl) countEl.textContent = total;

    var pagEl = document.getElementById('tablePagination');
    if (!pagEl) return;
    pagEl.innerHTML = '';

    var prev = document.createElement('button');
    prev.className = 'page-btn'; prev.textContent = 'Previous';
    if (currentPage === 1) prev.disabled = true;
    prev.onclick = function () { currentPage--; renderTable(); };
    pagEl.appendChild(prev);

    for (var p = 1; p <= totalPages; p++) {
        (function (pg) {
            var btn = document.createElement('button');
            btn.className = 'page-btn' + (pg === currentPage ? ' active' : '');
            btn.textContent = pg;
            btn.onclick = function () { currentPage = pg; renderTable(); };
            pagEl.appendChild(btn);
        })(p);
    }

    var next = document.createElement('button');
    next.className = 'page-btn'; next.textContent = 'Next';
    if (currentPage === totalPages) next.disabled = true;
    next.onclick = function () { currentPage++; renderTable(); };
    pagEl.appendChild(next);
}

// ── Modal open / close ───────────────────────────────────
function setAddStudentDefaults(force) {
    var studentIdInput = document.getElementById('student_id');
    var passwordInput = document.getElementById('password');
    var confirmPasswordInput = document.getElementById('password_confirmation');

    if (studentIdInput && (force || !studentIdInput.value.trim())) {
        studentIdInput.value = generateStudentId();
    }
    if (passwordInput && (force || !passwordInput.value)) {
        passwordInput.type = 'password';
        passwordInput.value = 'password';
    }
    if (confirmPasswordInput && (force || !confirmPasswordInput.value)) {
        confirmPasswordInput.type = 'password';
        confirmPasswordInput.value = 'password';
    }
}

function openAddModal() {
    var modal = document.getElementById('addModal');
    if (!modal) return;
    modal.classList.add('active');

    setAddStudentDefaults(true);
    checkPasswordMatch();
}
function closeAddModal() {
    var modal = document.getElementById('addModal');
    if (!modal) return;
    modal.classList.remove('active');
}

function openEditModal(studentId) {
    if (!document.getElementById('editModal')) return;

    var student = students.find(function (s) { return s.id === studentId; });
    if (!student) return;

    document.getElementById('edit_first_name').value  = student.name        || '';
    document.getElementById('edit_middle_name').value = student.middle_name  || '';
    document.getElementById('edit_last_name').value   = student.last_name    || '';
    document.getElementById('edit_student_id').value  = student.student_id;
    document.getElementById('edit_year_level').value  = student.year_level   || '';
    document.getElementById('edit_email').value       = student.email;
    document.getElementById('edit_password').value    = '';
    document.getElementById('edit_password_confirmation').value = '';

    var photoPreview = document.getElementById('editPhotoPreview');
    photoPreview.innerHTML = student.profile_picture
        ? '<img src="' + student.profile_picture + '" style="width:100%;height:100%;object-fit:cover;">'
        : '<i class="fa-solid fa-user" style="font-size:4rem;color:#6B7280;"></i>';

    document.getElementById('editStudentPhoto').value = '';
    document.getElementById('editForm').action = '/department-head/students/' + studentId;
    document.getElementById('editModal').classList.add('active');
}

function closeEditModal() {
    var modal = document.getElementById('editModal');
    if (!modal) return;
    modal.classList.remove('active');
}

function confirmToggleStatus(studentId, currentStatus) {
    if (!document.getElementById('statusForm')) return;

    var student = students.find(function (s) { return s.id === studentId; });
    if (!student) return;

    var isActive = currentStatus === 'active';
    var action = isActive ? 'Disable' : 'Enable';

    _swalConfirm(
        action + ' Student?',
        'Are you sure you want to ' + action.toLowerCase() + ' ' + student.name + '?',
        'Yes, ' + action,
        function () {
            var form = document.getElementById('statusForm');
            form.action = '/department-head/students/' + studentId + '/status';
            form.submit();
        }
    );
}

// ── Close modal on backdrop click ────────────────────────
document.querySelectorAll('.modal').forEach(function (modal) {
    modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.classList.remove('active');
    });
});

// ── Password visibility toggle ───────────────────────────
function togglePassword(fieldId) {
    var input = document.getElementById(fieldId);
    var icon  = document.getElementById(fieldId + '-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// ── Email with domain ────────────────────────────────────
function updateEmail() {
    var username = document.getElementById('email_username').value;
    document.getElementById('email_full').value = username + '@itstudents.com';
}

function generateStudentId() {
    var now = new Date();
    var year = String(now.getFullYear()).slice(-2);
    var month = String(now.getMonth() + 1).padStart(2, '0');
    var day = String(now.getDate()).padStart(2, '0');
    return year + month + day;
}

// ── Photo preview ────────────────────────────────────────
function previewPhoto(event) {
    var file = event.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById('photoPreview').innerHTML =
            '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
    };
    reader.readAsDataURL(file);
}

function previewEditPhoto(event) {
    var file = event.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById('editPhotoPreview').innerHTML =
            '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
    };
    reader.readAsDataURL(file);
}

// ── Form submission preparation ──────────────────────────
function prepareSubmit() {
    updateEmail();
    var studentIdInput = document.getElementById('student_id');
    var username = document.getElementById('email_username').value.trim();
    if (!username) { _swalErr('Please enter an email username.'); return false; }
    if (studentIdInput && !studentIdInput.value.trim()) {
        studentIdInput.value = generateStudentId();
    }
    return true;
}

// ── Password match indicator ─────────────────────────────
function checkPasswordMatch() {
    var password        = document.getElementById('password').value;
    var confirmPassword = document.getElementById('password_confirmation').value;
    var indicator       = document.getElementById('match-indicator');

    if (confirmPassword.length === 0) { indicator.style.display = 'none'; return; }

    indicator.style.display = 'block';
    if (password === confirmPassword) {
        indicator.innerHTML  = '<i class="fa-solid fa-check-circle"></i>';
        indicator.className  = 'password-match-indicator match';
    } else {
        indicator.innerHTML  = '<i class="fa-solid fa-times-circle"></i>';
        indicator.className  = 'password-match-indicator no-match';
    }
}

// ── Auto-open Add modal from dashboard shortcut ──────────
if (new URLSearchParams(window.location.search).get('action') === 'add') {
    window.addEventListener('DOMContentLoaded', function () { openAddModal(); });
}

// ── Initialize table on DOM load ─────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    filteredRows = getAllRows();
    if (filteredRows.length > 0) renderTable();

    var addModal = document.getElementById('addModal');
    if (addModal && addModal.classList.contains('active')) {
        setAddStudentDefaults(false);
        checkPasswordMatch();
    }
});
