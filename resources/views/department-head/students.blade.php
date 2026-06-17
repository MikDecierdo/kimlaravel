@extends('layouts.department-head')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/shared.css') }}">
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/students.css') }}">
@endpush

@section('dept-head-content')

<header>
    <div class="header-title">
        <h1>Manage Students</h1>
        <p>{{ $isCsgHead ? 'All Departments' : ($department . ' Department') }} • <span id="studentCount">{{ $students->count() }}</span> Students</p>
    </div>
    @unless($isCsgHead)
    <div style="display:flex;gap:0.6rem;align-items:center;">
        <button class="btn-primary btn-hover" onclick="openImportModal()" style="background: linear-gradient(135deg, #2d6a4f 0%, #40916c 100%); transition: all 0.3s;">
            <i class="fa-solid fa-file-excel"></i> Import Excel
        </button>
        <button class="btn-primary btn-hover" onclick="openAddModal()" style="background: linear-gradient(135deg, #800020 0%, #A0153E 100%); transition: all 0.3s;">
            <i class="fa-solid fa-plus"></i> Add Student
        </button>
    </div>
    @endunless
</header>

<div class="students-container">

    @if($students->count() > 0)
    <!-- Filter Bar -->
    <div class="filter-card">
        <div class="filter-left">
            <div class="filter-group">
                <label>Date From</label>
                <input type="date" id="filterDateFrom" class="filter-input">
            </div>
            <div class="filter-group">
                <label>Date To</label>
                <input type="date" id="filterDateTo" class="filter-input">
            </div>
            <div class="filter-group">
                <label>Year Level</label>
                <select id="filterYearLevel" class="filter-select">
                    <option value="">All Year Levels</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
            </div>
            <div class="filter-actions">
                <button onclick="applyFilters()" class="btn-apply">Apply Filters</button>
                <button onclick="resetFilters()" class="btn-reset">Reset</button>
            </div>
        </div>
        <div class="search-wrapper">
            <label style="font-size:0.8rem;font-weight:600;color:#1f2937;">Search</label>
            <input type="text" id="searchInput" class="search-input" placeholder="Search students..." oninput="applyFilters()">
        </div>
    </div>

    <!-- Table -->
    <div class="table-wrapper">
        <table class="students-tbl">
            <thead>
                <tr>
                    <th onclick="sortTable(0)">NO. <span class="sort-icon">&#x21C5;</span></th>
                    <th onclick="sortTable(1)">STUDENT ID <span class="sort-icon">&#x21C5;</span></th>
                    <th onclick="sortTable(2)">FULL NAME <span class="sort-icon">&#x21C5;</span></th>
                    <th onclick="sortTable(3)">YEAR LEVEL <span class="sort-icon">&#x21C5;</span></th>
                    @if($isCsgHead)
                    <th onclick="sortTable(4)">DEPARTMENT <span class="sort-icon">&#x21C5;</span></th>
                    <th onclick="sortTable(5)">STATUS <span class="sort-icon">&#x21C5;</span></th>
                    <th onclick="sortTable(6)">REGISTERED DATE <span class="sort-icon">&#x21C5;</span></th>
                    @else
                    <th onclick="sortTable(4)">STATUS <span class="sort-icon">&#x21C5;</span></th>
                    <th onclick="sortTable(5)">REGISTERED DATE <span class="sort-icon">&#x21C5;</span></th>
                    <th>ACTIONS</th>
                    @endif
                </tr>
            </thead>
            <tbody id="studentsTbody">
                @foreach($students as $student)
                @php
                    $currentStatus = $student->student->status ?? 'active';
                @endphp
                <tr
                    data-no="{{ $loop->iteration }}"
                    data-student-id="{{ strtolower($student->student_id) }}"
                    data-name="{{ strtolower(trim($student->name . ' ' . $student->middle_name . ' ' . $student->last_name)) }}"
                    data-year="{{ strtolower($student->year_level ?? '') }}"
                    data-status="{{ $currentStatus }}"
                    data-date="{{ $student->created_at->format('Y-m-d') }}"
                >
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $student->student_id }}</strong></td>
                    <td>{{ trim($student->name . ' ' . $student->middle_name . ' ' . $student->last_name) }}</td>
                    <td>{{ $student->year_level ?? 'N/A' }}</td>
                    @if($isCsgHead)
                    <td>{{ $student->department ?? 'N/A' }}</td>
                    @endif
                    <td><span class="{{ $currentStatus === 'active' ? 'status-active' : 'status-inactive' }}">{{ ucfirst($currentStatus) }}</span></td>
                    <td>{{ $student->created_at->format('M d, Y') }}</td>
                    @unless($isCsgHead)
                    <td style="white-space:nowrap;">
                        <button class="btn-tbl-update" onclick="openEditModal({{ $student->id }})">Update</button>
                        @if($currentStatus === 'active')
                        <button class="btn-tbl-delete" onclick="confirmToggleStatus({{ $student->id }}, '{{ $currentStatus }}')">Disable</button>
                        @else
                        <button class="btn-tbl-update" onclick="confirmToggleStatus({{ $student->id }}, '{{ $currentStatus }}')">Enable</button>
                        @endif
                    </td>
                    @endunless
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
        <div class="empty-state" style="background:white;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
            <i class="fa-solid fa-inbox"></i>
            <p>No students found{{ $isCsgHead ? ' across all departments' : (' in ' . $department . ' department') }}.</p>
            @unless($isCsgHead)
            <p style="font-size: 0.9rem;">Click "Add Student" to create your first student.</p>
            @endunless
        </div>
    @endif
</div>

@unless($isCsgHead)
<!-- Import Students Modal -->
<div id="importModal" class="modal-overlay" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.55);align-items:center;justify-content:center;">
    <div class="modal-box" style="background:#fff;border-radius:14px;padding:2rem;width:100%;max-width:500px;box-shadow:0 8px 40px rgba(0,0,0,0.22);position:relative;">
        <button onclick="closeImportModal()" style="position:absolute;top:1rem;right:1.1rem;background:none;border:none;font-size:1.4rem;cursor:pointer;color:#666;">&times;</button>
        <h2 style="margin:0 0 0.3rem;font-size:1.2rem;color:#800020;"><i class="fa-solid fa-file-excel" style="color:#40916c;"></i> Import Students from Excel</h2>
        <p style="margin:0 0 1.2rem;font-size:0.85rem;color:#666;">Upload an <strong>.xlsx / .xls / .csv</strong> file. Students will be added to the <strong>{{ $department }}</strong> department.</p>

        <div style="margin-bottom:1rem;">
            <a href="{{ route('department-head.students.template') }}" style="font-size:0.84rem;color:#40916c;text-decoration:none;font-weight:600;">
                <i class="fa-solid fa-download"></i> Download Template
            </a>
        </div>

        <form id="importForm" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:0.85rem;font-weight:600;margin-bottom:0.4rem;color:#333;">Select File</label>
                <input type="file" id="excelFile" name="excel_file" accept=".xlsx,.xls,.csv"
                    style="width:100%;border:1.5px solid #ddd;border-radius:8px;padding:0.5rem 0.75rem;font-size:0.9rem;box-sizing:border-box;">
            </div>
            <button type="button" id="importSubmitBtn" onclick="submitImport()"
                style="width:100%;padding:0.65rem;background:linear-gradient(135deg,#800020 0%,#A0153E 100%);color:#fff;border:none;border-radius:8px;font-size:0.95rem;font-weight:600;cursor:pointer;">
                <i class="fa-solid fa-upload"></i> Import Now
            </button>
        </form>

        <!-- Results -->
        <div id="importResults" style="display:none;margin-top:1.2rem;border-top:1px solid #eee;padding-top:1rem;">
            <div id="importSummary" style="font-weight:700;font-size:0.95rem;margin-bottom:0.5rem;"></div>
            <div id="importSkippedList" style="font-size:0.82rem;color:#c07a00;margin-bottom:0.4rem;"></div>
            <div id="importErrorList" style="font-size:0.82rem;color:#c0392b;"></div>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <form method="POST" action="{{ route('department-head.students.store') }}" id="addStudentForm" onsubmit="return prepareSubmit()" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h2>Add New Student</h2>
                <button type="button" class="modal-close" onclick="closeAddModal()">&times;</button>
            </div>
            <div class="modal-body">
                @if($errors->any())
                    <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #ef4444;">
                        <ul style="margin: 0; padding-left: 1.5rem;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <!-- Photo Upload Section -->
                <div style="margin-bottom: 1.5rem; text-align: center;">
                    <div style="display: inline-block; position: relative;">
                        <div id="photoPreview" style="width: 150px; height: 150px; border-radius: 50%; background: linear-gradient(135deg, #e9ecef 0%, #d1d5db 100%); display: flex; align-items: center; justify-content: center; border: 3px solid #800020; overflow: hidden; margin: 0 auto;">
                            <i class="fa-solid fa-user" style="font-size: 4rem; color: #6B7280;"></i>
                        </div>
                        <label for="studentPhoto" style="position: absolute; bottom: 0; right: 0; background: #800020; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid white;">
                            <i class="fa-solid fa-camera"></i>
                        </label>
                        <input type="file" id="studentPhoto" name="photo" accept="image/*" style="display: none;" onchange="previewPhoto(event)">
                    </div>
                    <p style="margin-top: 0.5rem; font-size: 0.85rem; color: #6B7280;">Click camera icon to upload photo (Max of 2MB)</p>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Student ID</label>
                        <input type="text" name="student_id" id="student_id" readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Year Level</label>
                        <select name="year_level" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                            <option value="">Select Year Level</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name <span style="color: #6b7280; font-weight: normal;">(Optional)</span></label>
                        <input type="text" name="middle_name">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="email-wrapper">
                            <input type="text" id="email_username" name="email_username" required placeholder="Enter username" oninput="updateEmail()">
                            <span class="email-domain">@itstudents.com</span>
                        </div>
                        <input type="hidden" name="email" id="email_full">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" value="password" required oninput="checkPasswordMatch()">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fa-solid fa-eye" id="password-icon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password_confirmation" id="password_confirmation" value="password" required oninput="checkPasswordMatch()">
                            <span class="password-match-indicator" id="match-indicator" style="display: none;"></span>
                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                <i class="fa-solid fa-eye" id="password_confirmation-icon"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn-submit">Add Student</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <form method="POST" id="editForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h2>Edit Student</h2>
                <button type="button" class="modal-close" onclick="closeEditModal()">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Photo Upload Section -->
                <div style="margin-bottom: 1.5rem; text-align: center;">
                    <div style="display: inline-block; position: relative;">
                        <div id="editPhotoPreview" style="width: 150px; height: 150px; border-radius: 50%; background: linear-gradient(135deg, #e9ecef 0%, #d1d5db 100%); display: flex; align-items: center; justify-content: center; border: 3px solid #800020; overflow: hidden; margin: 0 auto;">
                            <i class="fa-solid fa-user" style="font-size: 4rem; color: #6B7280;"></i>
                        </div>
                        <label for="editStudentPhoto" style="position: absolute; bottom: 0; right: 0; background: #800020; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid white;">
                            <i class="fa-solid fa-camera"></i>
                        </label>
                        <input type="file" id="editStudentPhoto" name="photo" accept="image/*" style="display: none;" onchange="previewEditPhoto(event)">
                    </div>
                    <p style="margin-top: 0.5rem; font-size: 0.85rem; color: #6B7280;">Click camera icon to change photo (2mb max)</p>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Student ID</label>
                        <input type="text" name="student_id" id="edit_student_id" required readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Year Level</label>
                        <select name="year_level" id="edit_year_level" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                            <option value="">Select Year Level</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" id="edit_first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name <span style="color: #6b7280; font-weight: normal;">(Optional)</span></label>
                        <input type="text" name="middle_name" id="edit_middle_name">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" id="edit_last_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" id="edit_email" required>
                    </div>
                    <div class="form-group">
                        <label>New Password (leave blank to keep current)</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="edit_password">
                            <button type="button" class="password-toggle" onclick="togglePassword('edit_password')">
                                <i class="fa-solid fa-eye" id="edit_password-icon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password_confirmation" id="edit_password_confirmation">
                            <button type="button" class="password-toggle" onclick="togglePassword('edit_password_confirmation')">
                                <i class="fa-solid fa-eye" id="edit_password_confirmation-icon"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-submit">Update Student</button>
            </div>
        </form>
    </div>
</div>

<!-- Student Status Form -->
<form id="statusForm" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>
@endunless


@push('scripts')
<script>
const isCsgHead = @json($isCsgHead);
// --- Import Modal ---
if (!isCsgHead) {
function openImportModal() {
    const m = document.getElementById('importModal');
    m.style.display = 'flex';
    document.getElementById('importResults').style.display = 'none';
    document.getElementById('excelFile').value = '';
    document.getElementById('importSummary').textContent = '';
    document.getElementById('importSkippedList').innerHTML = '';
    document.getElementById('importErrorList').innerHTML = '';
}
function closeImportModal() {
    document.getElementById('importModal').style.display = 'none';
}
document.getElementById('importModal').addEventListener('click', function(e) {
    if (e.target === this) closeImportModal();
});
async function submitImport() {
    const fileInput = document.getElementById('excelFile');
    if (!fileInput.files.length) {
        alert('Please select a file first.');
        return;
    }
    const btn = document.getElementById('importSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importing...';

    const formData = new FormData();
    formData.append('excel_file', fileInput.files[0]);
    formData.append('_token', document.querySelector('input[name="_token"]').value);

    try {
        const res = await fetch('{{ route("department-head.students.import") }}', {
            method: 'POST',
            body: formData,
        });
        const data = await res.json();
        const resultsDiv = document.getElementById('importResults');
        const summary   = document.getElementById('importSummary');
        const skipped   = document.getElementById('importSkippedList');
        const errors    = document.getElementById('importErrorList');
        resultsDiv.style.display = 'block';
        if (data.success) {
            summary.style.color = '#2d6a4f';
            summary.textContent = `✔ ${data.imported} student(s) imported successfully.` +
                (data.skipped.length ? ` ${data.skipped.length} skipped.` : '') +
                (data.errors.length  ? ` ${data.errors.length} error(s).`  : '');
            skipped.innerHTML = data.skipped.map(s => `⚠ ${s}`).join('<br>');
            errors.innerHTML  = data.errors.map(e => `✖ ${e}`).join('<br>');
            if (data.imported > 0) {
                setTimeout(() => location.reload(), 2500);
            }
        } else {
            summary.style.color = '#c0392b';
            summary.textContent = '✖ ' + data.message;
        }
    } catch (err) {
        alert('Network error: ' + err);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-upload"></i> Import Now';
    }
}
}
// --- End Import ---

const students = @json($students);
@if(session('success'))
_swalToast('success', '{{ session("success") }}');
@endif
@if(session('error'))
_swalToast('error', '{{ session("error") }}');
@endif
@if($errors->any())
if (document.getElementById('addModal')) {
    document.getElementById('addModal').classList.add('active');
}
@endif
</script>
<script src="{{ asset('assets/dept-head/js/students.js') }}"></script>
@endpush
@endsection
