@extends('layouts.department-head')

@section('title', 'Faculty Access')

@section('dept-head-content')
<style>
.faculty-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(128, 0, 32, 0.2);
}
.faculty-tag {
    display: inline-flex;
    align-items: center;
    padding: 0.3rem 0.65rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}
.faculty-tag.off {
    background: #f3f4f6;
    color: #374151;
    border-color: #d1d5db;
}
.permission-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.3rem 0.7rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
    background: #f8fafc;
    color: #475569;
    border: 1px solid #e2e8f0;
}
.permission-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.7rem;
}
.permission-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    font-size: 0.88rem;
    color: #800020;
    border: 1.5px solid #800020;
    border-radius: 12px;
    background: #ffffff;
    padding: 0.55rem 0.7rem;
    font-weight: 600;
}
.permission-item input[type="checkbox"] {
    -webkit-appearance: none;
    appearance: none;
    position: relative;
    width: 44px;
    height: 24px;
    border-radius: 999px;
    border: 2px solid #800020;
    background: #ffffff;
    cursor: pointer;
    transition: background-color 0.2s ease, border-color 0.2s ease;
    flex-shrink: 0;
}
.permission-item input[type="checkbox"]::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #800020;
    transition: transform 0.2s ease, background-color 0.2s ease;
}
.permission-item input[type="checkbox"]:checked {
    background: #800020;
    border-color: #800020;
}
.permission-item input[type="checkbox"]:checked::after {
    transform: translateX(20px);
    background: #ffffff;
}
.permission-item input[type="checkbox"]:focus-visible {
    outline: 2px solid #800020;
    outline-offset: 2px;
}
</style>

<header>
    <div class="header-title">
        <h1>Faculty Access</h1>
        <p>Assign faculty who can access the Department Head portal and control their permissions.</p>
    </div>
    <button class="btn-primary btn-hover" onclick="openFacultyModal()" style="background: linear-gradient(135deg, #800020 0%, #A0153E 100%); transition: all 0.3s;">
        <i class="fa-solid fa-user-plus"></i> Add Faculty Access
    </button>
</header>

<div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
    @forelse($facultyMembers as $faculty)
        @php
            $permissions = is_array($faculty->department_portal_permissions) ? $faculty->department_portal_permissions : [];
        @endphp
        <div class="faculty-card" style="background:white; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.1); transition:all 0.3s; border:1px solid #f1f5f9;">
            <div style="background: linear-gradient(135deg, #800020 0%, #A0153E 100%); padding: 1.4rem 1.2rem; text-align: center; position: relative;">
                <div style="width: 88px; height: 88px; border-radius: 50%; margin: 0 auto; border: 4px solid rgba(255,255,255,0.35); overflow: hidden; background: #d1d5db; display: flex; align-items: center; justify-content: center;">
                    @if($faculty->profile_picture)
                        <img src="{{ $faculty->profile_picture }}" alt="Profile" style="width:100%; height:100%; object-fit:cover;">
                    @else
                        <span style="font-size: 2rem; font-weight: 800; color: #374151;">{{ strtoupper(substr($faculty->name, 0, 1)) }}</span>
                    @endif
                </div>
            </div>

            <div style="padding: 1.2rem 1.2rem 1.3rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:0.5rem; margin-bottom:0.55rem;">
                    <h3 style="margin:0; font-size:1.08rem; color:#1f2937; font-weight:800;">{{ $faculty->name }}</h3>
                    <span class="faculty-tag {{ $faculty->can_access_department_portal ? '' : 'off' }}">{{ $faculty->can_access_department_portal ? 'Access Enabled' : 'Access Disabled' }}</span>
                </div>

                <p style="margin:0 0 0.75rem; font-size:0.85rem; color:#64748b;">
                    <i class="fa-regular fa-envelope" style="color:#800020;"></i>
                    {{ $faculty->email }}
                </p>

                <div style="display:flex; flex-wrap:wrap; gap:0.4rem; margin-bottom:1rem; min-height: 31px;">
                    @forelse($permissionLabels as $key => $label)
                        @if(in_array($key, $permissions, true))
                            <span class="permission-chip">{{ $label }}</span>
                        @endif
                    @empty
                    @endforelse

                    @if(empty($permissions))
                        <span class="permission-chip" style="background:#fef2f2; border-color:#fecaca; color:#b91c1c;">No permissions assigned</span>
                    @endif
                </div>

                <div style="display:flex; gap:0.6rem;">
                    <button onclick="openEditFacultyModal({{ $faculty->id }})" class="btn-hover" style="flex:1; padding:0.55rem; border:2px solid #800020; border-radius:24px; font-weight:700; font-size:0.8rem; letter-spacing:0.04em; cursor:pointer; transition:all 0.25s; background:transparent; color:#800020; text-transform:uppercase;">
                        Update
                    </button>
                    <button onclick="toggleFacultyAccess({{ $faculty->id }})" class="btn-hover" style="flex:1; padding:0.55rem; border:2px solid #800020; border-radius:24px; font-weight:700; font-size:0.8rem; letter-spacing:0.04em; cursor:pointer; transition:all 0.25s; background:#800020; color:white; text-transform:uppercase;">
                        {{ $faculty->can_access_department_portal ? 'Disable' : 'Enable' }}
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div style="grid-column: 1/-1; text-align:center; padding:4rem; color:#888; background:white; border-radius:16px;">
            <i class="fa-solid fa-user-shield" style="font-size:4rem; margin-bottom:1rem; opacity:0.3;"></i>
            <p style="font-size:1.05rem; margin-bottom:0.45rem;">No faculty access accounts yet.</p>
            <p style="font-size:0.9rem; color:#9ca3af;">Click Add Faculty Access to search and authorize an existing faculty account.</p>
        </div>
    @endforelse
</div>

<div class="modal-overlay" id="facultyModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; overflow-y: auto; padding: 2rem 0;">
    <div class="modal" style="background:white; padding:2rem; border-radius:12px; width:90%; max-width:760px; margin:auto; max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.4rem;">
            <h2 style="margin:0;">Add Faculty Access</h2>
            <button onclick="closeFacultyModal()" style="background:none; border:none; font-size:1.2rem; cursor:pointer; color:var(--text-muted);">&times;</button>
        </div>

        <form id="addFacultyForm" onsubmit="handleAddFaculty(event)">
            <input type="hidden" id="selectedFacultyId">

            <div style="margin-bottom: 1rem;">
                <label style="display:block; margin-bottom:0.5rem; font-size:0.9rem; font-weight:600;">Search Faculty</label>
                <input type="text" id="facultySearchInput" oninput="renderFacultySearchResults()" placeholder="Type faculty name, email, or faculty ID" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;">
            </div>

            <div id="facultySearchResults" style="max-height:220px; overflow-y:auto; border:1px solid #e5e7eb; border-radius:10px; background:#fff; margin-bottom:1rem;"></div>

            <div id="selectedFacultyInfo" style="display:none; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:1rem; margin-bottom:1rem;">
                <div style="display:flex; gap:1rem; align-items:center; margin-bottom:0.9rem;">
                    <div id="selectedFacultyAvatar" style="width:62px; height:62px; border-radius:50%; overflow:hidden; background:#e5e7eb; display:flex; align-items:center; justify-content:center; border:2px solid #800020;"></div>
                    <div>
                        <div id="selectedFacultyName" style="font-size:1rem; font-weight:700; color:#1f2937;"></div>
                        <div id="selectedFacultyEmail" style="font-size:0.86rem; color:#64748b;"></div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.7rem;">
                    <div>
                        <div style="font-size:0.72rem; text-transform:uppercase; color:#6b7280; font-weight:700; letter-spacing:0.05em;">Faculty ID</div>
                        <div id="selectedFacultyEmployeeId" style="font-size:0.9rem; color:#1f2937; font-weight:600;"></div>
                    </div>
                    <div>
                        <div style="font-size:0.72rem; text-transform:uppercase; color:#6b7280; font-weight:700; letter-spacing:0.05em;">Department</div>
                        <div id="selectedFacultyDepartment" style="font-size:0.9rem; color:#1f2937; font-weight:600;"></div>
                    </div>
                </div>
            </div>

            <div style="margin-bottom:1.2rem;">
                <label style="display:block; margin-bottom:0.55rem; font-size:0.9rem; font-weight:600;">Authorize Actions</label>
                <div class="permission-grid">
                    @foreach($permissionLabels as $key => $label)
                        <label class="permission-item">
                            <span>{{ $label }}</span>
                            <input type="checkbox" name="facultyPermissions[]" value="{{ $key }}">
                        </label>
                    @endforeach
                </div>
            </div>

            @if(($assignableFaculties ?? collect())->count() === 0)
                <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:0.8rem 0.9rem; color:#9a3412; font-size:0.86rem; margin-bottom:1rem;">
                    No faculty accounts found in your department. Ask admin to create faculty accounts first.
                </div>
            @endif

            <button type="submit" class="btn-primary" style="width:100%; justify-content:center;" @if(($assignableFaculties ?? collect())->count() === 0) disabled @endif>
                Assign Faculty Access
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editFacultyModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; overflow-y: auto; padding: 2rem 0;">
    <div class="modal" style="background:white; padding:2rem; border-radius:12px; width:90%; max-width:760px; margin:auto; max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.4rem;">
            <h2 style="margin:0;">Update Faculty Access</h2>
            <button onclick="closeEditFacultyModal()" style="background:none; border:none; font-size:1.2rem; cursor:pointer; color:var(--text-muted);">&times;</button>
        </div>

        <form id="editFacultyForm" onsubmit="handleUpdateFaculty(event)">
            <input type="hidden" id="editFacultyId">

            <div style="display:flex; flex-direction:column; align-items:center; margin-bottom:1.2rem;">
                <div id="editFacultyPhotoPreview" onclick="document.getElementById('editFacultyPhotoInput').click()" style="width:96px; height:96px; border-radius:50%; background:#f1f5f9; border:3px dashed #800020; cursor:pointer; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                    <div id="editFacultyPhotoPlaceholder" style="text-align:center; color:#800020;">
                        <i class="fa-solid fa-camera" style="font-size:1.4rem;"></i>
                        <p style="font-size:0.68rem; margin:4px 0 0; font-weight:700;">Change Photo</p>
                    </div>
                    <img id="editFacultyPhotoImg" src="" alt="" style="display:none; width:100%; height:100%; object-fit:cover;">
                </div>
                <input type="file" id="editFacultyPhotoInput" accept="image/*" style="display:none" onchange="previewEditFacultyPhoto(event)">
                <p style="font-size:0.78rem; color:#6b7280; margin-top:0.45rem;">Optional profile picture (max 2MB)</p>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div style="margin-bottom:1rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-size:0.9rem; font-weight:500;">Name</label>
                    <input type="text" id="editFacultyName" required style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-size:0.9rem; font-weight:500;">Email</label>
                    <input type="email" id="editFacultyEmail" required style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-size:0.9rem; font-weight:500;">Password <span style="color:#888; font-weight:normal;">(Optional)</span></label>
                    <input type="password" id="editFacultyPassword" minlength="8" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-size:0.9rem; font-weight:500;">Confirm Password</label>
                    <input type="password" id="editFacultyPasswordConfirmation" minlength="8" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;">
                </div>
            </div>

            <div style="margin-bottom:1.2rem;">
                <label style="display:block; margin-bottom:0.55rem; font-size:0.9rem; font-weight:600;">Authorize Actions</label>
                <div class="permission-grid">
                    @foreach($permissionLabels as $key => $label)
                        <label class="permission-item">
                            <span>{{ $label }}</span>
                            <input type="checkbox" name="editFacultyPermissions[]" value="{{ $key }}">
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; justify-content:center;">Update Faculty Access</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
const facultyData = @json($facultyMembers);
const assignableFaculties = @json($assignableFaculties ?? []);
let selectedFaculty = null;

function openFacultyModal() {
    document.getElementById('facultyModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    selectedFaculty = null;
    document.getElementById('selectedFacultyId').value = '';
    document.getElementById('facultySearchInput').value = '';
    document.getElementById('selectedFacultyInfo').style.display = 'none';
    renderFacultySearchResults();
}

function closeFacultyModal() {
    document.getElementById('facultyModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('addFacultyForm').reset();
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
    const matches = assignableFaculties.filter(function (faculty) {
        const full = facultyFullName(faculty).toLowerCase();
        const email = (faculty.email || '').toLowerCase();
        const employeeId = (faculty.employee_id || '').toLowerCase();
        return !query || full.includes(query) || email.includes(query) || employeeId.includes(query);
    });

    if (matches.length === 0) {
        container.innerHTML = '<div style="padding:0.8rem 0.9rem;color:#6b7280;font-size:0.86rem;">No matching faculties found.</div>';
        return;
    }

    container.innerHTML = matches.map(function (faculty) {
        const avatar = faculty.profile_picture
            ? '<img src="' + faculty.profile_picture + '" alt="Photo" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">'
            : '<div style="width:36px;height:36px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;color:#6b7280;font-size:0.86rem;font-weight:700;">' + (facultyFullName(faculty).charAt(0) || '?').toUpperCase() + '</div>';

        return '<button type="button" onclick="selectFaculty(' + faculty.id + ')" style="width:100%;display:flex;align-items:center;justify-content:space-between;gap:0.8rem;padding:0.75rem 0.9rem;background:#fff;border:none;border-bottom:1px solid #f1f5f9;text-align:left;cursor:pointer;">'
            + '<div style="display:flex;align-items:center;gap:0.7rem;">'
            + avatar
            + '<div>'
            + '<div style="font-size:0.9rem;font-weight:700;color:#1f2937;">' + facultyFullName(faculty) + '</div>'
            + '<div style="font-size:0.8rem;color:#6b7280;">' + (faculty.email || '') + '</div>'
            + '</div></div>'
            + '<div style="font-size:0.78rem;color:#800020;font-weight:700;">' + (faculty.employee_id || 'N/A') + '</div>'
            + '</button>';
    }).join('');
}

function selectFaculty(facultyId) {
    const faculty = assignableFaculties.find(function (item) {
        return Number(item.id) === Number(facultyId);
    });
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

function previewEditFacultyPhoto(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (e) {
        const img = document.getElementById('editFacultyPhotoImg');
        img.src = e.target.result;
        img.style.display = 'block';
        document.getElementById('editFacultyPhotoPlaceholder').style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function selectedPermissions(selector) {
    return Array.from(document.querySelectorAll(selector + ':checked')).map(function (el) {
        return el.value;
    });
}

function handleAddFaculty(event) {
    event.preventDefault();

    const facultyId = document.getElementById('selectedFacultyId').value;
    if (!facultyId) {
        _swalToast('error', 'Please select a faculty account first.');
        return;
    }

    const formData = new FormData();
    formData.append('faculty_id', facultyId);

    selectedPermissions('input[name="facultyPermissions[]"]').forEach(function (permission) {
        formData.append('permissions[]', permission);
    });

    fetch('/department-head/faculty', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(function (response) {
        return response.json().then(function (payload) {
            if (!response.ok) {
                throw payload;
            }
            return payload;
        });
    })
    .then(function (data) {
        _swalToast('success', data.message || 'Faculty access created successfully.');
        closeFacultyModal();
        setTimeout(function () { location.reload(); }, 900);
    })
    .catch(function (error) {
        const message = error && error.errors
            ? Object.values(error.errors)[0][0]
            : (error && error.message ? error.message : 'An error occurred. Please try again.');
        _swalToast('error', message);
    });
}

function openEditFacultyModal(id) {
    const faculty = facultyData.find(function (item) { return item.id === id; });
    if (!faculty) {
        return;
    }

    document.getElementById('editFacultyId').value = faculty.id;
    document.getElementById('editFacultyName').value = faculty.name || '';
    document.getElementById('editFacultyEmail').value = faculty.email || '';
    document.getElementById('editFacultyPassword').value = '';
    document.getElementById('editFacultyPasswordConfirmation').value = '';

    const permissions = Array.isArray(faculty.department_portal_permissions)
        ? faculty.department_portal_permissions
        : [];

    document.querySelectorAll('input[name="editFacultyPermissions[]"]').forEach(function (checkbox) {
        checkbox.checked = permissions.indexOf(checkbox.value) !== -1;
    });

    const editImg = document.getElementById('editFacultyPhotoImg');
    const editPlaceholder = document.getElementById('editFacultyPhotoPlaceholder');
    if (faculty.profile_picture) {
        editImg.src = faculty.profile_picture;
        editImg.style.display = 'block';
        editPlaceholder.style.display = 'none';
    } else {
        editImg.src = '';
        editImg.style.display = 'none';
        editPlaceholder.style.display = 'block';
    }
    document.getElementById('editFacultyPhotoInput').value = '';

    document.getElementById('editFacultyModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeEditFacultyModal() {
    document.getElementById('editFacultyModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function handleUpdateFaculty(event) {
    event.preventDefault();

    const id = document.getElementById('editFacultyId').value;
    const formData = new FormData();
    formData.append('name', document.getElementById('editFacultyName').value);
    formData.append('email', document.getElementById('editFacultyEmail').value);

    const password = document.getElementById('editFacultyPassword').value;
    const passwordConfirmation = document.getElementById('editFacultyPasswordConfirmation').value;
    if (password) {
        formData.append('password', password);
        formData.append('password_confirmation', passwordConfirmation);
    }

    selectedPermissions('input[name="editFacultyPermissions[]"]').forEach(function (permission) {
        formData.append('permissions[]', permission);
    });

    const photo = document.getElementById('editFacultyPhotoInput').files[0];
    if (photo) {
        formData.append('profile_picture', photo);
    }

    fetch('/department-head/faculty/' + id, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(function (response) {
        return response.json().then(function (payload) {
            if (!response.ok) {
                throw payload;
            }
            return payload;
        });
    })
    .then(function (data) {
        _swalToast('success', data.message || 'Faculty access updated successfully.');
        closeEditFacultyModal();
        setTimeout(function () { location.reload(); }, 900);
    })
    .catch(function (error) {
        const message = error && error.errors
            ? Object.values(error.errors)[0][0]
            : (error && error.message ? error.message : 'An error occurred. Please try again.');
        _swalToast('error', message);
    });
}

function toggleFacultyAccess(id) {
    const faculty = facultyData.find(function (item) { return item.id === id; });
    if (!faculty) {
        return;
    }

    const action = faculty.can_access_department_portal ? 'Disable' : 'Enable';
    _swalConfirm(
        action + ' Faculty Access?',
        'This will ' + action.toLowerCase() + ' portal access for ' + (faculty.name || 'this faculty') + '.',
        'Yes, ' + action,
        function () {
            fetch('/department-head/faculty/' + id + '/access', {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(function (response) {
                return response.json().then(function (payload) {
                    if (!response.ok) {
                        throw payload;
                    }
                    return payload;
                });
            })
            .then(function (data) {
                _swalToast('success', data.message || 'Faculty access updated.');
                setTimeout(function () { location.reload(); }, 900);
            })
            .catch(function (error) {
                const message = error && error.message ? error.message : 'An error occurred. Please try again.';
                _swalToast('error', message);
            });
        }
    );
}
</script>
@endpush
@endsection
