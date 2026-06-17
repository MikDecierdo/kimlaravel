@extends('layouts.admin')

@section('admin-content')
<style>
.btn-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    opacity: 0.92;
}

.filter-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    padding: 1rem 1.2rem;
    margin-bottom: 1rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.85rem;
    align-items: flex-end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.filter-group label,
.search-group label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.filter-input,
.filter-select,
.search-group input {
    padding: 0.55rem 0.8rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.88rem;
    background: #f9fafb;
    outline: none;
}

.filter-input:focus,
.filter-select:focus,
.search-group input:focus {
    border-color: #800020;
    background: #fff;
}

.search-group {
    flex: 1;
    min-width: 220px;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-apply {
    padding: 0.55rem 1rem;
    background: linear-gradient(135deg, #800020 0%, #A0153E 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
}

.btn-reset {
    padding: 0.55rem 1rem;
    background: #f1f5f9;
    color: #475569;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
}

.results-bar {
    font-size: 0.84rem;
    color: #64748b;
    font-weight: 500;
    margin-bottom: 1rem;
}

.table-wrapper {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
}

.faculty-table {
    width: 100%;
    border-collapse: collapse;
}

.faculty-table thead tr {
    background: #800020;
}

.faculty-table thead th {
    padding: 0.95rem 1rem;
    color: #fff;
    font-weight: 700;
    font-size: 0.78rem;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    text-align: left;
    white-space: nowrap;
    cursor: pointer;
    user-select: none;
}

.faculty-table thead th:hover {
    background: #6d0018;
}

.faculty-table tbody tr {
    border-bottom: 1px solid #f1f5f9;
}

.faculty-table tbody tr:nth-child(even) {
    background: #fafbfc;
}

.faculty-table tbody tr:hover {
    background: #fdf2f4 !important;
}

.faculty-table tbody td {
    padding: 0.85rem 1rem;
    font-size: 0.88rem;
    color: #374151;
    vertical-align: middle;
}

.sort-icon {
    margin-left: 6px;
    opacity: 0.75;
}

.chip-course {
    display: inline-block;
    padding: 0.2rem 0.62rem;
    border-radius: 999px;
    background: rgba(128, 0, 32, 0.08);
    color: #800020;
    font-size: 0.76rem;
    font-weight: 700;
}

.status-active {
    color: #047857;
    font-weight: 700;
}

.status-inactive {
    color: #6b7280;
    font-weight: 700;
}

.btn-row-update {
    padding: 0.3rem 0.85rem;
    border: 2px solid #800020;
    background: #fff;
    color: #800020;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    margin-right: 0.35rem;
}

.btn-row-update:hover {
    background: #800020;
    color: #fff;
}

.btn-row-disable {
    padding: 0.3rem 0.85rem;
    border: 2px solid #800020;
    background: #800020;
    color: #fff;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
}

.btn-row-disable:hover {
    background: #6d0018;
    border-color: #6d0018;
}

.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.9rem 1.2rem;
    border-top: 1px solid #f1f5f9;
    font-size: 0.84rem;
    color: #64748b;
}

.pagination-btns {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.page-btn {
    padding: 0.33rem 0.7rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: #fff;
    color: #374151;
    cursor: pointer;
    font-size: 0.82rem;
}

.page-btn.active {
    background: #800020;
    color: #fff;
    border-color: #800020;
}

.page-btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.55);
    align-items: center;
    justify-content: center;
    overflow-y: auto;
    padding: 2rem 0;
}

.modal.active {
    display: flex;
}

.modal-box {
    background: #fff;
    border-radius: 12px;
    width: 92%;
    max-width: 760px;
    max-height: calc(100vh - 4rem);
    box-shadow: 0 10px 40px rgba(0,0,0,0.22);
    display: flex;
    flex-direction: column;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.2rem 1.4rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.2rem;
    color: #800020;
}

.modal-close {
    border: none;
    background: none;
    font-size: 1.4rem;
    color: #6b7280;
    cursor: pointer;
}

.modal-body {
    padding: 1.2rem 1.4rem;
    overflow-y: auto;
    flex: 1;
    min-height: 0;
}

.modal-footer {
    position: sticky;
    bottom: 0;
    left: 0;
    padding: 1rem 1.4rem 1.2rem;
    border-top: 1px solid #e5e7eb;
    background: #fff;
    display: flex;
    justify-content: flex-end;
    gap: 0.6rem;
    z-index: 1;
    flex-shrink: 0;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.95rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.form-group label {
    font-size: 0.86rem;
    font-weight: 600;
    color: #1f2937;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 0.68rem 0.75rem;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.92rem;
}

.password-field {
    position: relative;
}

.password-field input {
    padding-right: 2.6rem;
}

.toggle-password-btn {
    position: absolute;
    right: 0.7rem;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    color: #6b7280;
    cursor: pointer;
    padding: 0;
    font-size: 0.95rem;
}

.toggle-password-btn:hover {
    color: #800020;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #800020;
}

.form-note {
    font-size: 0.78rem;
    color: #6b7280;
}

.btn-cancel {
    padding: 0.62rem 1.1rem;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    background: #fff;
    color: #374151;
    font-weight: 600;
    cursor: pointer;
}

.btn-submit {
    padding: 0.62rem 1.1rem;
    border: none;
    border-radius: 8px;
    background: linear-gradient(135deg, #800020 0%, #A0153E 100%);
    color: #fff;
    font-weight: 700;
    cursor: pointer;
}

.profile-picker {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 1rem;
}

.profile-preview {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    border: 3px dashed #800020;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    cursor: pointer;
}

.profile-preview i {
    font-size: 1.8rem;
    color: #800020;
}

.import-box {
    margin-top: 1rem;
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
}

@media (max-width: 860px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<header>
    <div class="header-title">
        <h1>Manage Faculties</h1>
        <p><span id="facultyCount">{{ $faculties->count() }}</span> faculty accounts</p>
    </div>
    <div style="display:flex;gap:0.6rem;align-items:center;">
        <button class="btn-primary btn-hover" onclick="openImportModal()" style="background: linear-gradient(135deg, #2d6a4f 0%, #40916c 100%); transition: all 0.3s;">
            <i class="fa-solid fa-file-excel"></i> Import Excel
        </button>
        <button class="btn-primary btn-hover" onclick="openAddModal()" style="background: linear-gradient(135deg, #800020 0%, #A0153E 100%); transition: all 0.3s;">
            <i class="fa-solid fa-plus"></i> Add Faculty
        </button>
    </div>
</header>

@if($faculties->count() > 0)
<div class="filter-card">
    <div class="filter-group">
        <label>Date From</label>
        <input type="date" id="filterDateFrom" class="filter-input">
    </div>
    <div class="filter-group">
        <label>Date To</label>
        <input type="date" id="filterDateTo" class="filter-input">
    </div>
    <div class="filter-group">
        <label>Course</label>
        <select id="filterCourse" class="filter-select">
            <option value="">All Courses</option>
            @foreach($courses as $course)
                <option value="{{ strtolower($course) }}">{{ $course }}</option>
            @endforeach
        </select>
    </div>
    <div class="search-group">
        <label>Search</label>
        <input type="text" id="searchInput" placeholder="Search faculty..." oninput="applyFilters()">
    </div>
    <div class="filter-actions">
        <button class="btn-apply" onclick="applyFilters()">Apply Filters</button>
        <button class="btn-reset" onclick="resetFilters()">Reset</button>
    </div>
</div>
<div class="results-bar" id="resultsBar"></div>

<div class="table-wrapper">
    <table class="faculty-table">
        <thead>
            <tr>
                <th onclick="sortTable(0)">No. <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="sortTable(1)">Faculty ID <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="sortTable(2)">Full Name <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="sortTable(3)">Course <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="sortTable(4)">Email <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="sortTable(5)">Status <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="sortTable(6)">Access Type <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="sortTable(7)">Registered Date <span class="sort-icon">&#x21C5;</span></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="facultyTbody">
            @foreach($faculties as $faculty)
            @php
                $accessEnabled = (bool) $faculty->can_access_faculty_system;
                $statusLabel = $accessEnabled ? 'active' : 'inactive';
                if ((bool) $faculty->is_department_head) {
                    $accessTypeLabel = 'Department Head → full control';
                    $accessTypeTone = 'background:#fef2f2;color:#991b1b;border-color:#fecaca;';
                } elseif ((bool) $faculty->can_access_department_portal) {
                    $accessTypeLabel = 'Authorized Faculty → given access';
                    $accessTypeTone = 'background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;';
                } else {
                    $accessTypeLabel = 'Faculty → normal access';
                    $accessTypeTone = 'background:#f8fafc;color:#475569;border-color:#e2e8f0;';
                }

                $fullName = trim($faculty->name . ' ' . ($faculty->middle_name ?? '') . ' ' . ($faculty->last_name ?? ''));
            @endphp
            <tr
                data-no="{{ $loop->iteration }}"
                data-employee-id="{{ strtolower($faculty->employee_id ?? '') }}"
                data-name="{{ strtolower($fullName) }}"
                data-course="{{ strtolower($faculty->department ?? '') }}"
                data-email="{{ strtolower($faculty->email ?? '') }}"
                data-status="{{ $statusLabel }}"
                data-access="{{ $accessEnabled ? '1' : '0' }}"
                data-access-types="{{ strtolower($accessTypeLabel) }}"
                data-date="{{ optional($faculty->created_at)->format('Y-m-d') }}"
            >
                <td>{{ $loop->iteration }}</td>
                <td><strong>{{ $faculty->employee_id ?? 'N/A' }}</strong></td>
                <td>{{ $fullName !== '' ? $fullName : 'N/A' }}</td>
                <td><span class="chip-course">{{ $faculty->department ?? 'N/A' }}</span></td>
                <td>{{ $faculty->email }}</td>
                <td>
                    <span class="{{ $statusLabel === 'active' ? 'status-active' : 'status-inactive' }}">{{ ucfirst($statusLabel) }}</span>
                </td>
                <td>
                    <span style="display:inline-flex; align-items:center; padding:0.22rem 0.55rem; border-radius:999px; border:1px solid; font-size:0.73rem; font-weight:700; white-space:nowrap; {{ $accessTypeTone }}">
                        {{ $accessTypeLabel }}
                    </span>
                </td>
                <td>{{ optional($faculty->created_at)->format('M d, Y') }}</td>
                <td style="white-space:nowrap;">
                    <button type="button" class="btn-row-update" onclick="openEditModal({{ $faculty->id }})">Update</button>
                    @if($accessEnabled)
                        <button type="button" class="btn-row-disable" onclick="confirmToggleStatus({{ $faculty->id }}, true)">Disable</button>
                    @else
                        <button type="button" class="btn-row-update" onclick="confirmToggleStatus({{ $faculty->id }}, false)">Enable</button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="table-footer">
        <span id="tableInfo"></span>
        <div class="pagination-btns" id="tablePagination"></div>
    </div>
</div>
@else
<div style="background:white;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.08);padding:3rem 2rem;text-align:center;color:#6b7280;">
    <i class="fa-solid fa-chalkboard-user" style="font-size:3rem;opacity:0.35;margin-bottom:0.8rem;"></i>
    <p style="margin:0;font-size:1rem;">No faculty records found.</p>
    <p style="margin:0.35rem 0 0;font-size:0.86rem;">Click Add Faculty or Import Excel to create entries.</p>
</div>
@endif

<div class="modal" id="importModal">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <h2><i class="fa-solid fa-file-excel" style="color:#40916c;"></i> Import Faculties</h2>
            <button type="button" class="modal-close" onclick="closeImportModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p style="margin-top:0;font-size:0.86rem;color:#6b7280;">Upload an .xlsx, .xls, or .csv file. Faculty ID will be auto-generated using YYYYDD plus daily sequence (example: 2026041).</p>
            <div style="margin-bottom:1rem;">
                <a href="{{ route('admin.faculties.template') }}" style="font-size:0.84rem;color:#2d6a4f;text-decoration:none;font-weight:600;">
                    <i class="fa-solid fa-download"></i> Download Template
                </a>
            </div>
            <form id="importForm" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>Select File</label>
                    <input type="file" id="excelFile" name="excel_file" accept=".xlsx,.xls,.csv">
                </div>
                <button type="button" id="importSubmitBtn" class="btn-submit" style="width:100%;" onclick="submitImport()">
                    <i class="fa-solid fa-upload"></i> Import Now
                </button>
            </form>

            <div class="import-box" id="importResults" style="display:none;">
                <div id="importSummary" style="font-weight:700;font-size:0.94rem;margin-bottom:0.45rem;"></div>
                <div id="importSkippedList" style="font-size:0.82rem;color:#a16207;margin-bottom:0.45rem;"></div>
                <div id="importErrorList" style="font-size:0.82rem;color:#b91c1c;"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="addModal">
    <div class="modal-box">
        <form method="POST" id="addFacultyForm" action="{{ route('admin.faculties.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h2>Add Faculty</h2>
                <button type="button" class="modal-close" onclick="closeAddModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="profile-picker">
                    <div class="profile-preview" onclick="document.getElementById('addPhotoInput').click()" id="addPhotoPreview">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                    <input type="file" id="addPhotoInput" name="photo" accept="image/*" style="display:none;" onchange="previewPhoto(event, 'addPhotoPreview')">
                    <p class="form-note">Click to upload photo (optional, max 2MB)</p>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Faculty ID</label>
                        <input type="text" value="Auto-generated (YYYYDD + sequence)" readonly style="background:#f3f4f6;cursor:not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Course</label>
                        <select name="department" required>
                            <option value="">Select Course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course }}">{{ $course }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name <span class="form-note">(Optional)</span></label>
                        <input type="text" name="middle_name">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <div class="password-field">
                            <input type="password" name="password" id="add_password" minlength="8" value="password" required>
                            <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('add_password', this)" aria-label="Show password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="password-field">
                            <input type="password" name="password_confirmation" id="add_password_confirmation" minlength="8" value="password" required>
                            <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('add_password_confirmation', this)" aria-label="Show confirm password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn-submit">Add Faculty</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="editModal">
    <div class="modal-box">
        <form method="POST" id="editFacultyForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h2>Update Faculty</h2>
                <button type="button" class="modal-close" onclick="closeEditModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="profile-picker">
                    <div class="profile-preview" onclick="document.getElementById('editPhotoInput').click()" id="editPhotoPreview">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                    <input type="file" id="editPhotoInput" name="photo" accept="image/*" style="display:none;" onchange="previewPhoto(event, 'editPhotoPreview')">
                    <p class="form-note">Click to change photo (optional, max 2MB)</p>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Faculty ID</label>
                        <input type="text" id="edit_employee_id" readonly style="background:#f3f4f6;cursor:not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Course</label>
                        <select name="department" id="edit_department" required>
                            <option value="">Select Course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course }}">{{ $course }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" id="edit_first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name <span class="form-note">(Optional)</span></label>
                        <input type="text" name="middle_name" id="edit_middle_name">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" id="edit_last_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" id="edit_email" required>
                    </div>
                    <div class="form-group">
                        <label>New Password <span class="form-note">(Optional)</span></label>
                        <div class="password-field">
                            <input type="password" name="password" id="edit_password" minlength="8">
                            <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('edit_password', this)" aria-label="Show password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <div class="password-field">
                            <input type="password" name="password_confirmation" id="edit_password_confirmation" minlength="8">
                            <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('edit_password_confirmation', this)" aria-label="Show confirm password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-submit">Update Faculty</button>
            </div>
        </form>
    </div>
</div>

<form id="statusForm" method="POST" style="display:none;">
    @csrf
    @method('PATCH')
</form>

@push('scripts')
<script>
var faculties = @json($faculties);
var ROWS_PER_PAGE = 10;
var currentPage = 1;
var sortCol = -1;
var sortDir = 1;
var filteredRows = [];

function getAllRows() {
    return Array.from(document.querySelectorAll('#facultyTbody tr'));
}

function applyFilters() {
    var search = (document.getElementById('searchInput') ? document.getElementById('searchInput').value : '').toLowerCase().trim();
    var course = (document.getElementById('filterCourse') ? document.getElementById('filterCourse').value : '').toLowerCase();
    var dateFrom = document.getElementById('filterDateFrom') ? document.getElementById('filterDateFrom').value : '';
    var dateTo = document.getElementById('filterDateTo') ? document.getElementById('filterDateTo').value : '';

    filteredRows = getAllRows().filter(function (row) {
        var name = row.dataset.name || '';
        var employeeId = row.dataset.employeeId || '';
        var email = row.dataset.email || '';
        var rowCourse = row.dataset.course || '';
        var date = row.dataset.date || '';

        if (search && name.indexOf(search) === -1 && employeeId.indexOf(search) === -1 && email.indexOf(search) === -1) {
            return false;
        }
        if (course && rowCourse !== course) {
            return false;
        }
        if (dateFrom && date < dateFrom) {
            return false;
        }
        if (dateTo && date > dateTo) {
            return false;
        }

        return true;
    });

    currentPage = 1;
    renderTable();
}

function resetFilters() {
    ['searchInput', 'filterCourse', 'filterDateFrom', 'filterDateTo'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            el.value = '';
        }
    });
    applyFilters();
}

function sortTable(col) {
    if (sortCol === col) {
        sortDir *= -1;
    } else {
        sortCol = col;
        sortDir = 1;
    }

    filteredRows.sort(function (a, b) {
        var aText = a.cells[col] ? a.cells[col].innerText.trim() : '';
        var bText = b.cells[col] ? b.cells[col].innerText.trim() : '';
        return aText.localeCompare(bText, undefined, { numeric: true }) * sortDir;
    });

    renderTable();
}

function renderTable() {
    var total = filteredRows.length;
    var totalPages = Math.max(1, Math.ceil(total / ROWS_PER_PAGE));
    if (currentPage > totalPages) {
        currentPage = totalPages;
    }

    getAllRows().forEach(function (row) {
        row.style.display = 'none';
    });

    var start = (currentPage - 1) * ROWS_PER_PAGE;
    var end = Math.min(start + ROWS_PER_PAGE, total);

    filteredRows.slice(start, end).forEach(function (row) {
        row.style.display = '';
    });

    filteredRows.forEach(function (row, i) {
        row.cells[0].textContent = i + 1;
    });

    var infoEl = document.getElementById('tableInfo');
    if (infoEl) {
        infoEl.textContent = total === 0
            ? 'No faculties found'
            : 'Showing ' + (start + 1) + ' to ' + end + ' of ' + total + ' facult' + (total !== 1 ? 'ies' : 'y');
    }

    var countEl = document.getElementById('facultyCount');
    if (countEl) {
        countEl.textContent = total;
    }

    var totalRows = getAllRows().length;
    var resultsEl = document.getElementById('resultsBar');
    if (resultsEl) {
        resultsEl.textContent = 'Showing ' + total + ' of ' + totalRows + ' facult' + (totalRows !== 1 ? 'ies' : 'y');
    }

    var pagEl = document.getElementById('tablePagination');
    if (!pagEl) {
        return;
    }

    pagEl.innerHTML = '';

    var prev = document.createElement('button');
    prev.className = 'page-btn';
    prev.textContent = 'Previous';
    prev.disabled = currentPage === 1;
    prev.onclick = function () {
        currentPage--;
        renderTable();
    };
    pagEl.appendChild(prev);

    for (var p = 1; p <= totalPages; p++) {
        (function (page) {
            var btn = document.createElement('button');
            btn.className = 'page-btn' + (page === currentPage ? ' active' : '');
            btn.textContent = page;
            btn.onclick = function () {
                currentPage = page;
                renderTable();
            };
            pagEl.appendChild(btn);
        })(p);
    }

    var next = document.createElement('button');
    next.className = 'page-btn';
    next.textContent = 'Next';
    next.disabled = currentPage === totalPages;
    next.onclick = function () {
        currentPage++;
        renderTable();
    };
    pagEl.appendChild(next);
}

function openImportModal() {
    var modal = document.getElementById('importModal');
    if (modal) {
        modal.classList.add('active');
    }
    document.getElementById('excelFile').value = '';
    document.getElementById('importResults').style.display = 'none';
    document.getElementById('importSummary').textContent = '';
    document.getElementById('importSkippedList').innerHTML = '';
    document.getElementById('importErrorList').innerHTML = '';
}

function closeImportModal() {
    var modal = document.getElementById('importModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

async function submitImport() {
    var fileInput = document.getElementById('excelFile');
    if (!fileInput.files.length) {
        _swalErr('Please select a file first.');
        return;
    }

    var btn = document.getElementById('importSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importing...';

    var formData = new FormData();
    formData.append('excel_file', fileInput.files[0]);
    formData.append('_token', csrfToken);

    try {
        var res = await fetch('{{ route('admin.faculties.import') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        });

        var data = await res.json();
        var results = document.getElementById('importResults');
        var summary = document.getElementById('importSummary');
        var skipped = document.getElementById('importSkippedList');
        var errors = document.getElementById('importErrorList');

        results.style.display = 'block';

        if (data.success) {
            summary.style.color = '#166534';
            summary.textContent = 'Imported: ' + data.imported + ' faculty member(s).' +
                (data.skipped.length ? ' Skipped: ' + data.skipped.length + '.' : '') +
                (data.errors.length ? ' Errors: ' + data.errors.length + '.' : '');

            skipped.innerHTML = data.skipped.map(function (item) { return '[WARN] ' + item; }).join('<br>');
            errors.innerHTML = data.errors.map(function (item) { return '[ERROR] ' + item; }).join('<br>');

            if (data.imported > 0) {
                setTimeout(function () { window.location.reload(); }, 1800);
            }
        } else {
            summary.style.color = '#b91c1c';
            summary.textContent = data.message || 'Import failed.';
        }
    } catch (err) {
        _swalErr('Network error while importing file.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-upload"></i> Import Now';
    }
}

function openAddModal() {
    var modal = document.getElementById('addModal');
    if (modal) {
        modal.classList.add('active');
    }

    var passwordInput = document.querySelector('#addModal input[name="password"]');
    var passwordConfirmInput = document.querySelector('#addModal input[name="password_confirmation"]');
    if (passwordInput) {
        passwordInput.type = 'password';
        passwordInput.value = 'password';
    }
    if (passwordConfirmInput) {
        passwordConfirmInput.type = 'password';
        passwordConfirmInput.value = 'password';
    }

    validateAddFacultyPasswords();

    var passwordToggle = document.querySelector('#addModal button[onclick*="add_password"] i');
    var passwordConfirmToggle = document.querySelector('#addModal button[onclick*="add_password_confirmation"] i');
    if (passwordToggle) {
        passwordToggle.className = 'fa-solid fa-eye';
    }
    if (passwordConfirmToggle) {
        passwordConfirmToggle.className = 'fa-solid fa-eye';
    }
}

function closeAddModal() {
    var modal = document.getElementById('addModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

function validateAddFacultyPasswords() {
    var passwordInput = document.getElementById('add_password');
    var passwordConfirmInput = document.getElementById('add_password_confirmation');
    if (!passwordInput || !passwordConfirmInput) {
        return true;
    }

    var isMatch = passwordInput.value === passwordConfirmInput.value;
    var message = isMatch ? '' : 'Password and confirm password must match.';

    passwordConfirmInput.setCustomValidity(message);

    return isMatch;
}

function validateEditFacultyPasswords() {
    var passwordInput = document.getElementById('edit_password');
    var passwordConfirmInput = document.getElementById('edit_password_confirmation');
    if (!passwordInput || !passwordConfirmInput) {
        return true;
    }

    var passwordValue = passwordInput.value || '';
    var confirmValue = passwordConfirmInput.value || '';

    if (passwordValue === '' && confirmValue === '') {
        passwordConfirmInput.setCustomValidity('');
        return true;
    }

    var isMatch = passwordValue === confirmValue;
    var message = isMatch ? '' : 'Password and confirm password must match.';

    passwordConfirmInput.setCustomValidity(message);

    return isMatch;
}

function togglePasswordVisibility(inputId, button) {
    var input = document.getElementById(inputId);
    var icon = button ? button.querySelector('i') : null;

    if (!input || !icon) {
        return;
    }

    var isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    icon.className = isHidden ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
    button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
}

function openEditModal(facultyId) {
    var faculty = faculties.find(function (item) {
        return item.id === facultyId;
    });

    if (!faculty) {
        return;
    }

    document.getElementById('edit_first_name').value = faculty.name || '';
    document.getElementById('edit_middle_name').value = faculty.middle_name || '';
    document.getElementById('edit_last_name').value = faculty.last_name || '';
    document.getElementById('edit_email').value = faculty.email || '';
    document.getElementById('edit_department').value = faculty.department || '';
    document.getElementById('edit_employee_id').value = faculty.employee_id || 'N/A';
    document.getElementById('edit_password').value = '';
    document.getElementById('edit_password_confirmation').value = '';

    var editPreview = document.getElementById('editPhotoPreview');
    if (faculty.profile_picture) {
        editPreview.innerHTML = '<img src="' + faculty.profile_picture + '" style="width:100%;height:100%;object-fit:cover;">';
    } else {
        editPreview.innerHTML = '<i class="fa-solid fa-camera"></i>';
    }

    document.getElementById('editPhotoInput').value = '';
    document.getElementById('editFacultyForm').action = '{{ url('/admin/faculties') }}/' + facultyId;
    document.getElementById('editModal').classList.add('active');

    validateEditFacultyPasswords();
}

function closeEditModal() {
    var modal = document.getElementById('editModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

function confirmToggleStatus(facultyId, accessEnabled) {
    var faculty = faculties.find(function (item) {
        return item.id === facultyId;
    });

    if (!faculty) {
        return;
    }

    var isEnabled = accessEnabled === true || accessEnabled === '1' || accessEnabled === 1;
    var action = isEnabled ? 'Disable' : 'Enable';

    _swalConfirm(
        action + ' Faculty?',
        'Are you sure you want to ' + action.toLowerCase() + ' ' + (faculty.name || 'this faculty') + '?',
        'Yes, ' + action,
        function () {
            var form = document.getElementById('statusForm');
            form.action = '{{ url('/admin/faculties') }}/' + facultyId + '/status';
            form.submit();
        }
    );
}

function previewPhoto(event, previewId) {
    var file = event.target.files[0];
    if (!file) {
        return;
    }

    var reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById(previewId).innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
    };
    reader.readAsDataURL(file);
}

document.querySelectorAll('.modal').forEach(function (modal) {
    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.classList.remove('active');
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var addPasswordInput = document.getElementById('add_password');
    var addPasswordConfirmInput = document.getElementById('add_password_confirmation');
    var addFacultyForm = document.getElementById('addFacultyForm');
    var editPasswordInput = document.getElementById('edit_password');
    var editPasswordConfirmInput = document.getElementById('edit_password_confirmation');
    var editFacultyForm = document.getElementById('editFacultyForm');

    if (addPasswordInput && addPasswordConfirmInput) {
        addPasswordInput.addEventListener('input', validateAddFacultyPasswords);
        addPasswordConfirmInput.addEventListener('input', validateAddFacultyPasswords);
        validateAddFacultyPasswords();
    }

    if (editPasswordInput && editPasswordConfirmInput) {
        editPasswordInput.addEventListener('input', validateEditFacultyPasswords);
        editPasswordConfirmInput.addEventListener('input', validateEditFacultyPasswords);
        validateEditFacultyPasswords();
    }

    if (addFacultyForm) {
        addFacultyForm.addEventListener('submit', function (event) {
            if (!validateAddFacultyPasswords()) {
                event.preventDefault();
                event.stopPropagation();
                var confirmInput = document.getElementById('add_password_confirmation');
                if (confirmInput) {
                    confirmInput.reportValidity();
                }
                return;
            }

            event.preventDefault();

            var submitBtn = addFacultyForm.querySelector('button[type="submit"]');
            var originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Adding...';
            }

            fetch(addFacultyForm.action, {
                method: 'POST',
                body: new FormData(addFacultyForm),
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async function (res) {
                var payload = {};
                try {
                    payload = await res.json();
                } catch (error) {
                    payload = { success: false, message: 'Unexpected server response.' };
                }

                if (!res.ok || !payload.success) {
                    throw payload;
                }

                _swalOK('Faculty Added', payload.message || 'Faculty added successfully.', function () {
                    window.location.reload();
                });
            })
            .catch(function (error) {
                _swalErr(error.message || 'Unable to add faculty right now.');
            })
            .finally(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            });
        });
    }

    if (editFacultyForm) {
        editFacultyForm.addEventListener('submit', function (event) {
            if (!validateEditFacultyPasswords()) {
                event.preventDefault();
                event.stopPropagation();
                var confirmInput = document.getElementById('edit_password_confirmation');
                if (confirmInput) {
                    confirmInput.reportValidity();
                }
            }
        });
    }

    filteredRows = getAllRows();
    if (filteredRows.length) {
        renderTable();
    }

    @if(session('success'))
        _swalToast('success', @json(session('success')));
    @endif

    @if($errors->any())
        _swalErr(@json($errors->first()));
    @endif
});
</script>
@endpush
@endsection
