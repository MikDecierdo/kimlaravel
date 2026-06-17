@extends('layouts.admin')

@section('admin-content')
<style>
.btn-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    opacity: 0.9;
}
.dept-head-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(128, 0, 32, 0.2);
}
</style>

<header>
    <div class="header-title">
        <h1>Department Heads</h1>
        <p>Manage department head accounts</p>
    </div>
    <button class="btn-primary btn-hover" onclick="openModal()" style="background: linear-gradient(135deg, #800020 0%, #A0153E 100%); transition: all 0.3s;">
        <i class="fa-solid fa-plus"></i> Assign Department Head
    </button>
</header>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
    @forelse($departmentHeads as $head)
        <div class="dept-head-card" style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); transition: all 0.3s; border: 1px solid #f1f5f9;">

            <!-- Maroon header with centered profile photo -->
            <div style="background: linear-gradient(135deg, #800020 0%, #A0153E 100%); padding: 2rem 1.5rem 3.5rem; text-align: center; position: relative;">
                <div style="width: 100px; height: 100px; border-radius: 50%; margin: 0 auto; border: 4px solid rgba(255,255,255,0.4); overflow: hidden; background: #d1d5db; display: flex; align-items: center; justify-content: center;">
                    @if($head->profile_picture)
                        <img src="{{ $head->profile_picture }}" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <span style="font-size: 2.5rem; font-weight: 700; color: #4b5563;">
                            {{ strtoupper(substr($head->name, 0, 1)) }}{{ strtoupper(substr($head->last_name ?? 'X', 0, 1)) }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Content area -->
            <div style="padding: 1.25rem 1.5rem 1.5rem; text-align: center; margin-top: -1rem;">

                <!-- Department label -->
                <p style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.08em; color: #800020; text-transform: uppercase; margin: 0 0 0.35rem;">
                    {{ $head->department }} DEPARTMENT HEAD
                </p>

                <!-- Full name -->
                <h3 style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin: 0 0 0.75rem;">
                    {{ $head->name }}
                    @if($head->middle_name && $head->middle_name !== 'none')
                        {{ strtoupper(substr($head->middle_name, 0, 1)) }}.
                    @endif
                    {{ $head->last_name }}
                </h3>

                <!-- EMP ID -->
                <p style="font-size: 0.85rem; color: #64748b; margin: 0 0 0.5rem; font-weight: 500;">
                    EMP ID: {{ $head->employee_id }}
                </p>

                <!-- Email with icon -->
                <div style="display: inline-flex; align-items: center; gap: 0.4rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 20px; padding: 0.35rem 0.85rem; margin-bottom: 1.25rem;">
                    <i class="fa-regular fa-envelope" style="color: #800020; font-size: 0.8rem;"></i>
                    <span style="font-size: 0.82rem; color: #475569;">{{ $head->email }}</span>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 0.75rem;">
                    <button onclick="openEditModal({{ $head->id }})"
                            class="btn-hover" style="flex: 1; padding: 0.6rem; border: 2px solid #800020; border-radius: 25px; font-weight: 700; font-size: 0.85rem; letter-spacing: 0.05em; cursor: pointer; transition: all 0.3s; background: transparent; color: #800020; text-transform: uppercase;">
                        UPDATE
                    </button>
                    <button onclick="unassignDepartmentHeadWithConfirm({{ $head->id }})"
                            class="btn-hover" style="flex: 1; padding: 0.6rem; border: none; border-radius: 25px; font-weight: 700; font-size: 0.85rem; letter-spacing: 0.05em; cursor: pointer; transition: all 0.3s; background: #800020; color: white; text-transform: uppercase;">
                        UNASSIGN
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div style="grid-column: 1/-1; text-align: center; padding: 4rem; color: #888;">
            <i class="fa-solid fa-user-tie" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;"></i>
            <p style="font-size: 1.1rem;">No department heads yet.</p>
            <p style="font-size: 0.9rem;">Add faculties first, then assign one as department head.</p>
        </div>
    @endforelse
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="deptHeadModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; overflow-y: auto; padding: 2rem 0;">
    <div class="modal" style="background: white; padding: 2rem; border-radius: 12px; width: 90%; max-width: 700px; margin: auto; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Assign Department Head</h2>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <form id="addDeptHeadForm" onsubmit="handleAddDeptHead(event)">
            <input type="hidden" id="selectedFacultyId">

            <div style="margin-bottom: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.9rem 1rem;">
                <label for="assignAsCsgHeadToggle" style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; cursor: pointer;">
                    <div>
                        <div style="font-size: 0.92rem; font-weight: 700; color: #1f2937;">Assign CSG Head</div>
                        <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.15rem;">Enable to assign selected faculty as CSG department head regardless of current department.</div>
                    </div>
                    <input type="checkbox" id="assignAsCsgHeadToggle" style="width: 18px; height: 18px; accent-color: #800020; cursor: pointer;">
                </label>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 600;">Search Faculty</label>
                <input type="text" id="facultySearchInput" oninput="renderFacultySearchResults()" placeholder="Type faculty name, email, or faculty ID" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
            </div>

            <div id="facultySearchResults" style="max-height: 220px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 10px; background: #fff; margin-bottom: 1rem;"></div>

            <div id="selectedFacultyInfo" style="display:none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem; margin-bottom: 1rem;">
                <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 0.9rem;">
                    <div id="selectedFacultyAvatar" style="width: 62px; height: 62px; border-radius: 50%; overflow: hidden; background: #e5e7eb; display: flex; align-items: center; justify-content: center; border: 2px solid #800020;"></div>
                    <div>
                        <div id="selectedFacultyName" style="font-size: 1rem; font-weight: 700; color: #1f2937;"></div>
                        <div id="selectedFacultyEmail" style="font-size: 0.86rem; color: #64748b;"></div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 0.7rem;">
                    <div>
                        <div style="font-size: 0.72rem; text-transform: uppercase; color: #6b7280; font-weight: 700; letter-spacing: 0.05em;">Faculty ID</div>
                        <div id="selectedFacultyEmployeeId" style="font-size: 0.9rem; color: #1f2937; font-weight: 600;"></div>
                    </div>
                    <div>
                        <div style="font-size: 0.72rem; text-transform: uppercase; color: #6b7280; font-weight: 700; letter-spacing: 0.05em;">Department</div>
                        <div id="selectedFacultyDepartment" style="font-size: 0.9rem; color: #1f2937; font-weight: 600;"></div>
                    </div>
                </div>
            </div>

            @if(($assignableFaculties ?? collect())->count() === 0)
                <div style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 0.8rem 0.9rem; color: #9a3412; font-size: 0.86rem; margin-bottom: 1rem;">
                    No available faculties to assign. Add faculty accounts first in Manage Faculties.
                </div>
            @endif

            <button type="submit" class="btn-primary" style="width:100%; justify-content: center;" @if(($assignableFaculties ?? collect())->count() === 0) disabled @endif>
                Assign Selected Faculty
            </button>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editDeptHeadModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; overflow-y: auto; padding: 2rem 0;">
    <div class="modal" style="background: white; padding: 2rem; border-radius: 12px; width: 90%; max-width: 700px; margin: auto; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Update Department Head</h2>
            <button onclick="closeEditModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <form id="editDeptHeadForm" onsubmit="handleUpdateDeptHead(event)">
            <input type="hidden" id="editDeptHeadId">

            <!-- Profile Photo Upload -->
            <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 1.5rem;">
                <div id="editPhotoPreview" onclick="document.getElementById('editPhotoInput').click()" style="width: 100px; height: 100px; border-radius: 50%; background: #f1f5f9; border: 3px dashed #800020; cursor: pointer; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative;">
                    <div id="editPhotoPlaceholder" style="text-align: center; color: #800020;">
                        <i class="fa-solid fa-camera" style="font-size: 1.6rem;"></i>
                        <p style="font-size: 0.7rem; margin: 4px 0 0; font-weight: 600;">Change Photo</p>
                    </div>
                    <img id="editPhotoImg" src="" alt="" style="display:none; width:100%; height:100%; object-fit:cover;">
                </div>
                <input type="file" id="editPhotoInput" accept="image/*" style="display:none" onchange="previewEditPhoto(event)">
                <p style="font-size: 0.78rem; color: #6b7280; margin-top: 0.5rem;">Click to change profile photo <span style="color:#6b7280;">(Optional, max 2MB)</span></p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">First Name</label>
                    <input type="text" id="editDeptHeadName" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Middle Name <span style="color: #888; font-weight: normal;">(Optional)</span></label>
                    <input type="text" id="editDeptHeadMiddleName" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Last Name</label>
                    <input type="text" id="editDeptHeadLastName" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Email</label>
                <input type="email" id="editDeptHeadEmail" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Password <span style="color: #888; font-weight: normal;">(Leave blank to keep current)</span></label>
                <input type="password" id="editDeptHeadPassword" minlength="8" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                <small style="color: #888; font-size: 0.8rem;">Minimum 8 characters</small>
            </div>
            <button type="submit" class="btn-primary" style="width:100%; justify-content: center;">Update Department Head</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
const departmentHeadsData = @json($departmentHeads);
const assignableFaculties = @json($assignableFaculties ?? []);
let selectedFaculty = null;

function openModal() {
    document.getElementById('deptHeadModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    selectedFaculty = null;
    document.getElementById('selectedFacultyId').value = '';
    document.getElementById('assignAsCsgHeadToggle').checked = false;
    document.getElementById('facultySearchInput').value = '';
    document.getElementById('selectedFacultyInfo').style.display = 'none';
    renderFacultySearchResults();
}

function closeModal() {
    document.getElementById('deptHeadModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('addDeptHeadForm').reset();
    document.getElementById('assignAsCsgHeadToggle').checked = false;
    selectedFaculty = null;
}

function facultyFullName(faculty) {
    return [faculty.name, faculty.middle_name, faculty.last_name]
        .filter(Boolean)
        .join(' ')
        .replace(/\s+/g, ' ')
        .trim();
}

function renderFacultySearchResults() {
    const container = document.getElementById('facultySearchResults');
    if (!container) return;

    const query = (document.getElementById('facultySearchInput').value || '').toLowerCase().trim();
    const matches = assignableFaculties.filter(f => {
        const full = facultyFullName(f).toLowerCase();
        const email = (f.email || '').toLowerCase();
        const employeeId = (f.employee_id || '').toLowerCase();
        return !query || full.includes(query) || email.includes(query) || employeeId.includes(query);
    });

    if (matches.length === 0) {
        container.innerHTML = '<div style="padding:0.8rem 0.9rem;color:#6b7280;font-size:0.86rem;">No matching faculties found.</div>';
        return;
    }

    container.innerHTML = matches.map(f => {
        const avatar = f.profile_picture
            ? '<img src="' + f.profile_picture + '" alt="Photo" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">'
            : '<div style="width:36px;height:36px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;color:#6b7280;font-size:0.86rem;font-weight:700;">' + (facultyFullName(f).charAt(0) || '?').toUpperCase() + '</div>';

        return '<button type="button" onclick="selectFaculty(' + f.id + ')" style="width:100%;display:flex;align-items:center;justify-content:space-between;gap:0.8rem;padding:0.75rem 0.9rem;background:#fff;border:none;border-bottom:1px solid #f1f5f9;text-align:left;cursor:pointer;">'
            + '<div style="display:flex;align-items:center;gap:0.7rem;">'
            + avatar
            + '<div>'
            + '<div style="font-size:0.9rem;font-weight:700;color:#1f2937;">' + facultyFullName(f) + '</div>'
            + '<div style="font-size:0.8rem;color:#6b7280;">' + (f.email || '') + '</div>'
            + '</div></div>'
            + '<div style="font-size:0.78rem;color:#800020;font-weight:700;">' + (f.employee_id || 'N/A') + '</div>'
            + '</button>';
    }).join('');
}

function selectFaculty(facultyId) {
    const faculty = assignableFaculties.find(f => Number(f.id) === Number(facultyId));
    if (!faculty) return;

    selectedFaculty = faculty;
    document.getElementById('selectedFacultyId').value = faculty.id;
    document.getElementById('facultySearchInput').value = facultyFullName(faculty);

    const avatar = document.getElementById('selectedFacultyAvatar');
    if (faculty.profile_picture) {
        avatar.innerHTML = '<img src="' + faculty.profile_picture + '" alt="Photo" style="width:100%;height:100%;object-fit:cover;">';
    } else {
        avatar.innerHTML = '<span style="font-size:1rem;font-weight:700;color:#4b5563;">' + (facultyFullName(faculty).charAt(0) || '?').toUpperCase() + '</span>';
    }

    document.getElementById('selectedFacultyName').textContent = facultyFullName(faculty) || 'N/A';
    document.getElementById('selectedFacultyEmail').textContent = faculty.email || 'N/A';
    document.getElementById('selectedFacultyEmployeeId').textContent = faculty.employee_id || 'N/A';
    document.getElementById('selectedFacultyDepartment').textContent = faculty.department || 'N/A';
    document.getElementById('selectedFacultyInfo').style.display = 'block';
}

function handleAddDeptHead(e) {
    e.preventDefault();

    const facultyId = document.getElementById('selectedFacultyId').value;
    const assignAsCsgHead = document.getElementById('assignAsCsgHeadToggle').checked;
    if (!facultyId) {
        showToast('Please select a faculty account first.', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('faculty_id', facultyId);
    formData.append('assign_as_csg_head', assignAsCsgHead ? '1' : '0');

    fetch('/admin/department-heads', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw err;
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast(data.message);
            closeModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (error.errors) {
            const firstError = Object.values(error.errors)[0][0];
            showToast(firstError, 'error');
        } else if (error.message) {
            if ((error.message || '').toLowerCase().includes('already has an assigned department head')) {
                _swalWarn('Department Already Assigned', error.message);
            } else {
                showToast(error.message, 'error');
            }
        } else {
            showToast('An error occurred. Please try again.', 'error');
        }
    });
}

function openEditModal(id) {
    const deptHead = departmentHeadsData.find(d => d.id === id);
    if (!deptHead) return;

    document.getElementById('editDeptHeadId').value = deptHead.id;
    document.getElementById('editDeptHeadName').value = deptHead.name;
    document.getElementById('editDeptHeadMiddleName').value = deptHead.middle_name || '';
    document.getElementById('editDeptHeadLastName').value = deptHead.last_name || '';
    document.getElementById('editDeptHeadEmail').value = deptHead.email;

    // Show current profile picture in edit modal
    const editImg = document.getElementById('editPhotoImg');
    const editPlaceholder = document.getElementById('editPhotoPlaceholder');
    if (deptHead.profile_picture) {
        editImg.src = deptHead.profile_picture;
        editImg.style.display = 'block';
        editPlaceholder.style.display = 'none';
    } else {
        editImg.src = '';
        editImg.style.display = 'none';
        editPlaceholder.style.display = 'block';
    }
    document.getElementById('editPhotoInput').value = '';

    document.getElementById('editDeptHeadModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function previewEditPhoto(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('editPhotoImg');
        img.src = e.target.result;
        img.style.display = 'block';
        document.getElementById('editPhotoPlaceholder').style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function closeEditModal() {
    document.getElementById('editDeptHeadModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function handleUpdateDeptHead(e) {
    e.preventDefault();
    
    const id = document.getElementById('editDeptHeadId').value;
    const formData = new FormData();
    formData.append('name', document.getElementById('editDeptHeadName').value);
    formData.append('middle_name', document.getElementById('editDeptHeadMiddleName').value || '');
    formData.append('last_name', document.getElementById('editDeptHeadLastName').value);
    formData.append('email', document.getElementById('editDeptHeadEmail').value);
    
    const password = document.getElementById('editDeptHeadPassword').value;
    if (password) {
        formData.append('password', password);
    }
    const editPhoto = document.getElementById('editPhotoInput').files[0];
    if (editPhoto) formData.append('profile_picture', editPhoto);

    fetch(`/admin/department-heads/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw err;
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast(data.message);
            closeEditModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (error.errors) {
            const firstError = Object.values(error.errors)[0][0];
            showToast(firstError, 'error');
        } else if (error.message) {
            showToast(error.message, 'error');
        } else {
            showToast('An error occurred. Please try again.', 'error');
        }
    });
}

function unassignDepartmentHeadAccess(id) {
    fetch(`/admin/department-heads/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            closeEditModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

function unassignDepartmentHeadWithConfirm(id) {
    _swalConfirm(
        'Unassign Department Head?',
        'This will remove department-head access and return this account to faculty.',
        'Yes, Unassign',
        function () {
            unassignDepartmentHeadAccess(id);
        }
    );
}

window.onclick = function(event) {
    if (event.target.id === 'deptHeadModal') {
        closeModal();
    }
    if (event.target.id === 'editDeptHeadModal') {
        closeEditModal();
    }
}
</script>
@endpush
@endsection
