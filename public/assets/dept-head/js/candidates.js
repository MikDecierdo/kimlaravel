/* ============================================================
   Department Head — Candidates JS
   PHP data bridge (in blade): const electionsData = @json($elections)
   ============================================================ */

// ── Build flat candidate list from elections data ────────
var allCandidates = [];
if (typeof electionsData !== 'undefined') {
    electionsData.forEach(function (election) {
        election.candidates.forEach(function (candidate) {
            allCandidates.push(candidate);
        });
    });
}

// ── Toast shortcut ───────────────────────────────────────
function showToast(message, type) {
    _swalToast(type || 'success', message);
}

var activeElectionContext = null;
var hasCandidateManagePermission = typeof window.canManageCandidates === 'boolean'
    ? window.canManageCandidates
    : true;
var activeElectionStorageKey = 'deptHeadManageCandidatesActiveElection';
var candidateRegistrationsData = [];

function persistActiveElectionContext() {
    try {
        if (!activeElectionContext || !activeElectionContext.id) {
            return;
        }
        window.sessionStorage.setItem(activeElectionStorageKey, String(activeElectionContext.id));
    } catch (err) {
        console.warn('Unable to persist election context:', err);
    }
}

function clearPersistedElectionContext() {
    try {
        window.sessionStorage.removeItem(activeElectionStorageKey);
    } catch (err) {
        console.warn('Unable to clear election context:', err);
    }
}

function getPersistedElectionContextId() {
    try {
        var raw = window.sessionStorage.getItem(activeElectionStorageKey);
        if (!raw) {
            return null;
        }

        var parsed = parseInt(raw, 10);
        return isNaN(parsed) ? null : parsed;
    } catch (err) {
        console.warn('Unable to read election context:', err);
        return null;
    }
}

function reloadWithContext() {
    persistActiveElectionContext();
    location.reload();
}

function parseBool(value) {
    return value === true || value === 1 || value === '1' || value === 'true';
}

function isFinishedDate(value) {
    if (!value) {
        return false;
    }

    var endDate = new Date(value);
    if (isNaN(endDate.getTime())) {
        return false;
    }

    return endDate.getTime() < Date.now();
}

function isViewOnlyElection(election) {
    if (!election) {
        return false;
    }

    if (typeof election.is_view_only !== 'undefined') {
        return parseBool(election.is_view_only);
    }

    return !parseBool(election.is_active) || isFinishedDate(election.end_date);
}

function getDeptVariants(dept) {
    var normalized = String(dept || '').trim().toUpperCase();
    var groups = [
        ['BSIT', 'IT', 'ENGINEERING'],
        ['CBAE', 'BSBA', 'PSYCHOLOGY', 'ACCOUNTANCY'],
        ['CTE', 'EDUC'],
        ['CHTM', 'NURSING'],
        ['CRIM'],
        ['SHS']
    ];

    for (var i = 0; i < groups.length; i++) {
        if (groups[i].indexOf(normalized) !== -1) {
            return groups[i];
        }
    }

    return normalized ? [normalized] : [];
}

function sameDept(a, b) {
    var aVariants = getDeptVariants(a);
    var bVariants = getDeptVariants(b);

    return aVariants.some(function (variant) {
        return bVariants.indexOf(variant) !== -1;
    });
}

function setActiveElectionContext(electionId) {
    var election = electionsData.find(function (e) { return e.id === electionId; }) || null;

    if (!election) {
        activeElectionContext = null;
        clearPersistedElectionContext();
        return activeElectionContext;
    }

    var card = document.querySelector('.nav-card[data-election-id="' + electionId + '"]');
    var context = Object.assign({}, election);

    if (card) {
        context.is_view_only = card.dataset.viewOnly === '1';
        context.is_finished = card.dataset.finished === '1';
    }

    activeElectionContext = context;
    persistActiveElectionContext();
    return activeElectionContext;
}

function syncContextActionMode(isViewOnly) {
    var addBtn = document.getElementById('contextAddCandidateBtn');
    var registrationBtn = document.getElementById('contextRegistrationBtn');
    var viewOnlyBtn = document.getElementById('contextViewOnlyBtn');
    var isCsgElection = activeElectionContext
        && String(activeElectionContext.department || '').trim().toUpperCase() === 'CSG';
    var hideRegistrationList = isViewOnly && isCsgElection && !window.isCsgDepartmentHead;

    if (!addBtn || !viewOnlyBtn || !registrationBtn) {
        return;
    }

    if (!hasCandidateManagePermission) {
        addBtn.style.display = 'none';
        registrationBtn.style.display = 'none';
        viewOnlyBtn.style.display = 'inline-flex';
        return;
    }

    if (isViewOnly) {
        addBtn.style.display = 'none';
        registrationBtn.style.display = hideRegistrationList ? 'none' : 'inline-flex';
        viewOnlyBtn.style.display = 'inline-flex';
    } else {
        addBtn.style.display = 'inline-flex';
        registrationBtn.style.display = 'inline-flex';
        viewOnlyBtn.style.display = 'none';
    }
}

function syncCandidateContextFields() {
    var electionIdInput = document.getElementById('candElectionId');
    var electionNameInput = document.getElementById('candCurrentElection');
    var departmentInput = document.getElementById('candDept');

    if (!activeElectionContext) {
        electionIdInput.value = '';
        electionNameInput.value = '';
        departmentInput.value = '';
        return;
    }

    electionIdInput.value = activeElectionContext.id;
    electionNameInput.value = activeElectionContext.election_name || '';
    departmentInput.value = activeElectionContext.department || '';
}

function loadPositionsForActiveElection() {
    var positionSelect = document.getElementById('candPosition');
    positionSelect.innerHTML = '<option value="">Select Position</option>';

    if (!activeElectionContext) {
        positionSelect.innerHTML = '<option value="">Select Election First</option>';
        return;
    }

    var positions = Array.isArray(activeElectionContext.positions) ? activeElectionContext.positions : [];
    var normalizedPositions = positions
        .map(function (position) { return String(position || '').trim(); })
        .filter(function (position) { return position.length > 0; });

    if (normalizedPositions.length === 0) {
        positionSelect.innerHTML = '<option value="">No positions available</option>';
        return;
    }

    normalizedPositions.forEach(function (position) {
        var option         = document.createElement('option');
        option.value       = position;
        option.textContent = position;
        positionSelect.appendChild(option);
    });
}

function loadPartylistOptionsForActiveElection(selectedValue) {
    var addSelect = document.getElementById('candPartylist');
    var editSelect = document.getElementById('editCandPartylist');
    var teams = activeElectionContext && Array.isArray(activeElectionContext.partylist_teams)
        ? activeElectionContext.partylist_teams
        : [];

    [addSelect, editSelect].forEach(function (select) {
        if (!select) {
            return;
        }

        select.innerHTML = '<option value="">Independent / No Partylist</option>';

        teams.forEach(function (team) {
            var name = String(team && team.name ? team.name : '').trim();
            if (!name) {
                return;
            }

            var option = document.createElement('option');
            option.value = name;
            option.textContent = team.tagline ? name + ' - ' + String(team.tagline) : name;
            select.appendChild(option);
        });

        if (selectedValue && Array.from(select.options).some(function (option) { return option.value === selectedValue; })) {
            select.value = selectedValue;
        }
    });
}

// ── Add candidate modal ──────────────────────────────────
function openModal() {
    if (!hasCandidateManagePermission) {
        showToast('You do not have permission to add candidates.', 'error');
        return;
    }

    if (!activeElectionContext) {
        showToast('Select an election first before adding a candidate.', 'error');
        return;
    }

    if (isViewOnlyElection(activeElectionContext)) {
        showToast('This election is view-only. Candidate changes are disabled.', 'error');
        return;
    }

    syncCandidateContextFields();
    loadPositionsForActiveElection();
    loadPartylistOptionsForActiveElection('');
    loadStudentsData(activeElectionContext ? activeElectionContext.id : null).finally(function () {
        document.getElementById('candidateModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    });
}

function closeModal() {
    document.getElementById('candidateModal').style.display = 'none';
    document.getElementById('addCandidateForm').reset();
    document.getElementById('candElectionId').value = activeElectionContext ? String(activeElectionContext.id) : '';
    document.getElementById('candCurrentElection').value = activeElectionContext ? (activeElectionContext.election_name || '') : '';
    document.getElementById('candDept').value = activeElectionContext ? (activeElectionContext.department || '') : '';
    document.getElementById('candPosition').innerHTML = '<option value="">Select Position</option>';
    loadPositionsForActiveElection();
    loadPartylistOptionsForActiveElection('');
    document.getElementById('selectedUserId').value = '';
    document.getElementById('studentSearch').value  = '';
    document.getElementById('candFullName').value   = '';
    document.getElementById('candStudentId').value  = '';
    document.getElementById('candYearLevel').value  = '';
    document.getElementById('studentDropdown').style.display = 'none';
    document.getElementById('candidatePhotoPreview').innerHTML = '<i class="fa-solid fa-user" style="font-size:4rem;color:#6B7280;"></i>';
    document.getElementById('charCount').textContent = '0';
    document.body.style.overflow = 'auto';
}

function updateCharCount() {
    var textarea = document.getElementById('candAdvocacy');
    document.getElementById('charCount').textContent = textarea.value.length;
}

function updateEditCharCount() {
    var textarea = document.getElementById('editCandAdvocacy');
    document.getElementById('editCharCount').textContent = textarea.value.length;
}

// ── Edit candidate modal ─────────────────────────────────
function openEditModal(id) {
    if (!hasCandidateManagePermission) {
        showToast('You do not have permission to update candidates.', 'error');
        return;
    }

    if (isViewOnlyElection(activeElectionContext)) {
        showToast('This election is view-only. Candidate updates are disabled.', 'error');
        return;
    }

    var candidate = allCandidates.find(function (c) { return c.id === id; });
    if (!candidate) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Candidate not found!' });
        return;
    }

    var modal = document.getElementById('editCandidateModal');
    if (!modal) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Modal element not found!' });
        return;
    }

    document.getElementById('editCandId').value = candidate.id;
    loadPartylistOptionsForActiveElection(candidate.partylist || '');

    var fullName = candidate.first_name;
    if (candidate.middle_name) fullName += ' ' + candidate.middle_name;
    fullName += ' ' + candidate.last_name;
    document.getElementById('editCandName').value            = fullName;
    document.getElementById('editCandPositionDisplay').value = candidate.position;
    document.getElementById('editCandPartylist').value = candidate.partylist || '';
    document.getElementById('editCandAdvocacy').value        = candidate.description || '';
    updateEditCharCount();

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('editCandidateModal').style.display = 'none';
    document.getElementById('editCandidateForm').reset();
    document.getElementById('editCharCount').textContent = '0';
    document.body.style.overflow = 'auto';
}

// ── Update candidate (advocacy only) ────────────────────
function handleUpdateCandidate(e) {
    e.preventDefault();
    var id       = document.getElementById('editCandId').value;
    var formData = new FormData();
    formData.append('_method',     'PUT');
    formData.append('description', document.getElementById('editCandAdvocacy').value);
    formData.append('partylist',   document.getElementById('editCandPartylist').value);

    fetch('/department-head/candidates/' + id, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body: formData
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
        if (data.success) {
            showToast(data.message);
            closeEditModal();
            setTimeout(function () { reloadWithContext(); }, 1000);
        } else {
            showToast(data.message || 'An error occurred', 'error');
        }
    })
    .catch(function (error) {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

// ── Add candidate ────────────────────────────────────────
function handleAddCandidate(e) {
    e.preventDefault();

    if (!hasCandidateManagePermission) {
        showToast('You do not have permission to add candidates.', 'error');
        return;
    }

    if (!activeElectionContext) {
        showToast('No election context is active. Select an election first.', 'error');
        return;
    }

    if (isViewOnlyElection(activeElectionContext)) {
        showToast('This election is view-only. Candidate changes are disabled.', 'error');
        return;
    }

    var userId = document.getElementById('selectedUserId').value;
    if (!userId) { showToast('Please select a student from the search results', 'error'); return; }

    var electionId = document.getElementById('candElectionId').value;
    var position   = document.getElementById('candPosition').value;
    if (!electionId || !position) { showToast('Please fill in all required fields', 'error'); return; }
    var partylist  = document.getElementById('candPartylist').value;

    var formData = new FormData();
    formData.append('campus_election_id', electionId);
    formData.append('user_id',            userId);
    formData.append('position',           position);
    formData.append('description',        document.getElementById('candAdvocacy').value);
    formData.append('partylist',          partylist);

    fetch('/department-head/candidates', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: formData
    })
    .then(function (res) {
        return res.text().then(function (text) {
            var payload;
            try {
                payload = text ? JSON.parse(text) : {};
            } catch (err) {
                payload = {
                    success: false,
                    message: 'Unexpected server response. Please refresh and try again.'
                };
            }

            return {
                ok: res.ok,
                data: payload
            };
        });
    })
    .then(function (result) {
        var data = result.data || {};
        if (result.ok && data.success) {
            showToast(data.message);
            closeModal();
            document.getElementById('addCandidateForm').reset();
            document.getElementById('selectedUserId').value = '';
            document.getElementById('candFullName').value   = '';
            document.getElementById('candYearLevel').value  = '';
            if (activeElectionContext) {
                loadCandidateRegistrations();
                setTimeout(function () { reloadWithContext(); }, 600);
            }
        } else {
            if (data.errors) {
                var firstErr = Object.values(data.errors)[0];
                showToast(Array.isArray(firstErr) ? firstErr[0] : firstErr, 'error');
            } else {
                showToast(data.message || 'An error occurred', 'error');
            }
        }
    })
    .catch(function (error) {
        console.error('Add candidate error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

function openRegistrationModal() {
    if (!hasCandidateManagePermission) {
        showToast('You do not have permission to review candidate registrations.', 'error');
        return;
    }

    if (!activeElectionContext) {
        showToast('Select an election first before opening the registration list.', 'error');
        return;
    }

    var isCsgElection = String(activeElectionContext.department || '').trim().toUpperCase() === 'CSG';
    if (isCsgElection && !window.isCsgDepartmentHead) {
        showToast('Registration List is not available for CSG elections.', 'error');
        return;
    }

    document.getElementById('registrationModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    document.getElementById('registrationSearchInput').value = '';
    loadCandidateRegistrations();
}

function closeRegistrationModal() {
    document.getElementById('registrationModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function loadCandidateRegistrations() {
    if (!activeElectionContext) {
        candidateRegistrationsData = [];
        renderRegistrationList();
        return;
    }

    var container = document.getElementById('registrationListContainer');
    if (container) {
        container.innerHTML = '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:1rem;color:#475569;">Loading registrations...</div>';
    }

    fetch('/department-head/candidate-registrations?campus_election_id=' + encodeURIComponent(activeElectionContext.id), {
        method: 'GET',
        headers: { 'Accept': 'application/json' }
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
        if (data.success) {
            candidateRegistrationsData = Array.isArray(data.registrations) ? data.registrations : [];
            renderRegistrationList();
            return;
        }

        candidateRegistrationsData = [];
        renderRegistrationList();
        showToast(data.message || 'Failed to load registrations.', 'error');
    })
    .catch(function (error) {
        console.error('Registration load error:', error);
        candidateRegistrationsData = [];
        renderRegistrationList();
        showToast('Failed to load registrations.', 'error');
    });
}

function renderRegistrationList() {
    var container = document.getElementById('registrationListContainer');
    if (!container) return;

    var query = (document.getElementById('registrationSearchInput').value || '').toLowerCase().trim();
    var rows = candidateRegistrationsData.filter(function (registration) {
        if (!query) return true;

        var student = registration.student || {};
        var haystack = [
            student.name,
            student.student_id,
            student.year_level,
            student.department,
            student.email,
            registration.requested_position,
            registration.election_name,
            registration.election_department
        ].map(function (value) { return String(value || '').toLowerCase(); }).join(' ');

        return haystack.indexOf(query) !== -1;
    });

    if (rows.length === 0) {
        container.innerHTML = '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:1rem;color:#475569;">No pending registrations found for this election.</div>';
        return;
    }

    container.innerHTML = rows.map(function (registration) {
        var student = registration.student || {};
        var positions = Array.isArray(registration.position_stats) ? registration.position_stats : [];

        var positionsHtml = positions.length
            ? positions.map(function (item) {
                var badgeBg = item.is_requested_position ? '#800020' : '#f1f5f9';
                var badgeColor = item.is_requested_position ? '#fff' : '#334155';
                return '<span style="display:inline-flex;align-items:center;gap:0.35rem;background:' + badgeBg + ';color:' + badgeColor + ';padding:0.35rem 0.6rem;border-radius:999px;font-size:0.76rem;font-weight:700;">'
                    + item.position + ' <span style="opacity:0.85;">(' + item.total_candidates + ')</span></span>';
            }).join(' ')
            : '<span style="font-size:0.8rem;color:#64748b;">No positions configured.</span>';

        return '<div style="border:1px solid #e2e8f0;border-radius:12px;padding:0.95rem 1rem;background:#fff;">'
            + '<div style="display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;flex-wrap:wrap;">'
            + '<div>'
            + '<div style="font-size:1rem;font-weight:800;color:#1f2937;">' + (student.name || 'Unknown Student') + '</div>'
            + '<div style="font-size:0.82rem;color:#64748b;margin-top:0.2rem;">Student ID: ' + (student.student_id || 'N/A') + ' | Year: ' + (student.year_level || 'N/A') + ' | Dept: ' + (student.department || 'N/A') + '</div>'
            + '<div style="font-size:0.82rem;color:#64748b;margin-top:0.2rem;">Election: ' + (registration.election_name || 'N/A') + ' (' + (registration.election_department || 'N/A') + ')</div>'
            + '<div style="font-size:0.82rem;color:#64748b;margin-top:0.2rem;">Requested Position: <strong style="color:#800020;">' + (registration.requested_position || 'N/A') + '</strong></div>'
            + '<div style="font-size:0.82rem;color:#64748b;margin-top:0.2rem;">Submitted: ' + (registration.submitted_at || 'N/A') + '</div>'
            + '</div>'
            + '<div style="display:flex; gap:0.45rem;">'
            + '<button type="button" onclick="confirmCandidateRegistration(' + registration.id + ')" class="btn-hover" style="padding:0.55rem 1rem;border:2px solid #800020;border-radius:10px;font-weight:700;background:#800020;color:#fff;cursor:pointer;">'
            + '<i class="fa-solid fa-check"></i> Confirm</button>'
            + '<button type="button" onclick="declineCandidateRegistration(' + registration.id + ')" class="btn-hover" style="padding:0.55rem 1rem;border:2px solid #b91c1c;border-radius:10px;font-weight:700;background:#fff;color:#b91c1c;cursor:pointer;">'
            + '<i class="fa-solid fa-xmark"></i> Decline</button>'
            + '</div>'
            + '</div>'
            + '<div style="margin-top:0.75rem;padding-top:0.75rem;border-top:1px dashed #e2e8f0;">'
            + '<div style="font-size:0.78rem;font-weight:700;color:#475569;margin-bottom:0.4rem;">All Election Positions (current candidate count):</div>'
            + '<div style="display:flex;flex-wrap:wrap;gap:0.4rem;">' + positionsHtml + '</div>'
            + '<div style="margin-top:0.65rem;font-size:0.82rem;color:#475569;"><strong>Advocacy:</strong> ' + (registration.description ? registration.description : '<span style="color:#94a3b8;">No advocacy submitted.</span>') + '</div>'
            + '</div>'
            + '</div>';
    }).join('');
}

function confirmCandidateRegistration(registrationId) {
    _swalConfirm(
        'Confirm Candidate Registration?',
        'This will mark the registration as confirmed. You will add the candidate later from Submit Registration.',
        'Confirm',
        function () {
            fetch('/department-head/candidate-registrations/' + registrationId + '/confirm', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    showToast(data.message, 'success');
                    loadCandidateRegistrations();
                    setTimeout(function () { reloadWithContext(); }, 800);
                } else {
                    _swalErr(data.message || 'Unable to confirm registration.');
                }
            })
            .catch(function (error) {
                console.error('Confirm registration error:', error);
                _swalErr('Unable to confirm registration right now.');
            });
        }
    );
}

function declineCandidateRegistration(registrationId) {
    Swal.fire({
        html: '<div style="text-align:center;padding:.25rem 0">' + _swalIcon('fa-circle-question')
            + '<h2 style="font-size:1.2rem;font-weight:800;color:#1f2937;margin-bottom:.4rem;">Decline Candidate Registration?</h2>'
            + '<p style="color:#6b7280;font-size:.9rem;">Please provide a reason to notify the student.</p></div>',
        input: 'textarea',
        inputLabel: 'Reason for decline',
        inputPlaceholder: 'Enter the reason this registration is being declined',
        inputAttributes: {
            'aria-label': 'Reason for decline'
        },
        showCancelButton: true,
        confirmButtonText: 'Decline',
        cancelButtonText: 'Cancel',
        focusCancel: true,
        reverseButtons: true,
        buttonsStyling: false,
        customClass: {
            confirmButton: 'swal-btn-solid',
            cancelButton: 'swal-btn-neutral',
            popup: 'swal-app-popup',
            actions: 'swal-app-actions'
        },
        inputValidator: function (value) {
            if (!value || !value.trim()) {
                return 'Please provide a reason for declining this registration.';
            }

            return null;
        }
    }).then(function (result) {
        if (!result.isConfirmed) {
            return;
        }

        var reason = (result.value || '').trim();

        fetch('/department-head/candidate-registrations/' + registrationId + '/decline', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                showToast(data.message, 'success');
                loadCandidateRegistrations();
            } else {
                showToast(data.message || 'Unable to decline registration.', 'error');
            }
        })
        .catch(function (error) {
            console.error('Decline registration error:', error);
            showToast('Unable to decline registration right now.', 'error');
        });
    });
}

// ── Delete candidate ─────────────────────────────────────
function deleteCandidate(id) {
    if (!hasCandidateManagePermission) {
        showToast('You do not have permission to remove candidates.', 'error');
        return;
    }

    if (isViewOnlyElection(activeElectionContext)) {
        showToast('This election is view-only. Candidate removal is disabled.', 'error');
        return;
    }

    var candidate = allCandidates.find(function (c) { return c.id === id; });
    if (!candidate) return;

    var candidateName = candidate.first_name;
    if (candidate.middle_name) candidateName += ' ' + candidate.middle_name;
    candidateName += ' ' + candidate.last_name;

    Swal.fire({
        title: 'Remove Candidate?',
        html: 'Are you sure you want to remove <strong>' + candidateName + '</strong>?<br><small style="color:#666;">This action cannot be undone.</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, remove',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then(function (result) {
        if (result.isConfirmed) {
            fetch('/department-head/candidates/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    showToast(data.message);
                    setTimeout(function () { reloadWithContext(); }, 1000);
                } else {
                    showToast(data.message || 'An error occurred', 'error');
                }
            })
            .catch(function (error) {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'error');
            });
        }
    });
}

// ── Folder navigation ────────────────────────────────────
function showElectionCandidates(electionId) {
    var election = setActiveElectionContext(electionId);
    if (!election) {
        showToast('Election context could not be loaded.', 'error');
        return;
    }

    loadStudentsData(electionId);

    document.getElementById('electionsView').style.display  = 'none';
    document.getElementById('candidatesView').style.display = 'block';
    document.getElementById('candidateContextActions').style.display = 'flex';

    syncCandidateContextFields();

    document.querySelectorAll('.election-candidates').forEach(function (div) {
        div.style.display = 'none';
    });
    var selected = document.querySelector('.election-candidates[data-election-id="' + electionId + '"]');
    if (selected) selected.style.display = 'block';

    var isViewOnly = selected ? selected.dataset.viewOnly === '1' : isViewOnlyElection(election);
    syncContextActionMode(isViewOnly);

    document.getElementById('pageTitle').textContent    = election.election_name + ' - Candidates';
    document.getElementById('pageSubtitle').textContent = !hasCandidateManagePermission
        ? 'View-only mode. You are not authorized to modify candidates.'
        : (isViewOnly
            ? 'This election is view-only. You can review candidates but cannot make changes.'
            : 'Election context active. New candidates will be added only to this election.');
}

function showElections() {
    activeElectionContext = null;
    clearPersistedElectionContext();
    document.getElementById('electionsView').style.display  = 'block';
    document.getElementById('candidatesView').style.display = 'none';
    document.getElementById('candidateContextActions').style.display = 'none';
    syncContextActionMode(false);
    document.getElementById('pageTitle').textContent        = 'Manage Candidates';
    document.getElementById('pageSubtitle').textContent     = 'Select an election to view candidates';
}

// ── Election folder filter ───────────────────────────────
function applyElecFilters() {
    var dateFrom = document.getElementById('elecFilterDateFrom').value;
    var dateTo   = document.getElementById('elecFilterDateTo').value;
    var sortBy   = document.getElementById('elecFilterSort').value;
    var status   = document.getElementById('elecFilterStatus').value;
    var search   = document.getElementById('elecFilterSearch').value.toLowerCase().trim();

    var grid = document.getElementById('electionsGrid');
    if (!grid) return;
    var cards = Array.from(grid.querySelectorAll('.nav-card'));

    cards.forEach(function (card) {
        var startDate  = card.dataset.start  || '';
        var cardStatus = card.dataset.status || '';
        var cardName   = card.dataset.name   || '';
        var visible = true;
        if (dateFrom && startDate < dateFrom) visible = false;
        if (dateTo   && startDate > dateTo)   visible = false;
        if (status   && cardStatus !== status) visible = false;
        if (search   && cardName.indexOf(search) === -1) visible = false;
        card.style.display = visible ? '' : 'none';
    });

    var visibleCards = cards.filter(function (c) { return c.style.display !== 'none'; });
    visibleCards.sort(function (a, b) {
        if (sortBy === 'most-candidates')   return (parseInt(b.dataset.candidates) || 0) - (parseInt(a.dataset.candidates) || 0);
        if (sortBy === 'fewest-candidates') return (parseInt(a.dataset.candidates) || 0) - (parseInt(b.dataset.candidates) || 0);
        if (sortBy === 'newest') return a.dataset.start < b.dataset.start ?  1 : -1;
        if (sortBy === 'oldest') return a.dataset.start > b.dataset.start ?  1 : -1;
        if (sortBy === 'az')     return (a.dataset.name || '').localeCompare(b.dataset.name || '');
        return 0;
    });
    visibleCards.forEach(function (card) { grid.appendChild(card); });

    var emptyEl = document.getElementById('elecEmptyFilter');
    if (visibleCards.length === 0) {
        if (!emptyEl) {
            emptyEl = document.createElement('div');
            emptyEl.id = 'elecEmptyFilter';
            emptyEl.style.cssText = 'grid-column:1/-1;text-align:center;padding:4rem;background:white;border-radius:16px;color:#888;';
            emptyEl.innerHTML = '<i class="fa-solid fa-filter" style="font-size:3rem;margin-bottom:1rem;opacity:0.3;"></i><h3 style="color:#6B7280;">No elections match your filters</h3><p style="color:#9ca3af;">Try adjusting or resetting your filters.</p>';
            grid.appendChild(emptyEl);
        }
    } else if (emptyEl) {
        emptyEl.remove();
    }
}

function resetElecFilters() {
    document.getElementById('elecFilterDateFrom').value = '';
    document.getElementById('elecFilterDateTo').value   = '';
    document.getElementById('elecFilterSort').value     = '';
    document.getElementById('elecFilterStatus').value   = '';
    document.getElementById('elecFilterSearch').value   = '';
    applyElecFilters();
}

// ── Student search ───────────────────────────────────────
var studentsData = [];

function loadStudentsData(campusElectionId) {
    var endpoint = '/department-head/students/list';
    if (campusElectionId) {
        endpoint += '?campus_election_id=' + encodeURIComponent(campusElectionId);
    }

    return fetch(endpoint)
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                studentsData = Array.isArray(data.students) ? data.students : [];
                return;
            }

            studentsData = [];
            console.warn('Unable to load students list:', data.message || 'Unknown error');
        })
        .catch(function (error) {
            studentsData = [];
            console.error('Error loading students:', error);
        });
}

loadStudentsData(null);

function searchStudents() {
    var searchTerm = document.getElementById('studentSearch').value.toLowerCase();
    var dropdown   = document.getElementById('studentDropdown');

    if (!activeElectionContext) {
        dropdown.style.display = 'none';
        return;
    }

    if (searchTerm.length < 2) { dropdown.style.display = 'none'; return; }

    var electionDept = String(activeElectionContext.department || '').toUpperCase();
    var filtered = studentsData.filter(function (s) {
        if (String(s.approval_status || '').toLowerCase() !== 'approved') {
            return false;
        }

        var matchesSearch = s.name.toLowerCase().indexOf(searchTerm) !== -1 ||
            (s.student_id && s.student_id.toLowerCase().indexOf(searchTerm) !== -1);

        if (!matchesSearch) {
            return false;
        }

        if (electionDept === 'CSG') {
            return true;
        }

        return sameDept(s.department || '', electionDept);
    });

    if (filtered.length === 0) {
        dropdown.innerHTML = '<div style="padding:10px;color:#888;">No students found</div>';
        dropdown.style.display = 'block';
        return;
    }

    dropdown.innerHTML = filtered.map(function (student) {
        return '<div onclick="selectStudent(' + student.id + ')" style="padding:10px;cursor:pointer;border-bottom:1px solid #f0f0f0;transition:background 0.2s;" onmouseover="this.style.background=\'#f8fafc\'" onmouseout="this.style.background=\'white\'">'
             + '<div style="font-weight:500;">' + student.name + '</div>'
             + '<div style="font-size:0.85rem;color:#666;">ID: ' + student.student_id + ' | Dept: ' + (student.department || 'N/A') + ' | Year: ' + (student.year_level || 'N/A') + '</div>'
             + '</div>';
    }).join('');
    dropdown.style.display = 'block';
}

function selectStudent(userId) {
    var student = studentsData.find(function (s) { return s.id === userId; });
    if (!student) return;

    if (!activeElectionContext) {
        showToast('Select an election first before picking a student.', 'error');
        return;
    }

    var electionDept = String(activeElectionContext.department || '').toUpperCase();
    if (electionDept !== 'CSG' && !sameDept(student.department || '', electionDept)) {
        showToast('Selected student is not eligible for the current election.', 'error');
        return;
    }

    document.getElementById('selectedUserId').value = student.id;
    document.getElementById('studentSearch').value  = student.name;
    document.getElementById('candFullName').value   = student.name;
    document.getElementById('candStudentId').value  = student.student_id;
    document.getElementById('candYearLevel').value  = student.year_level || 'N/A';

    var photoPreview = document.getElementById('candidatePhotoPreview');
    photoPreview.innerHTML = student.profile_picture
        ? '<img src="' + student.profile_picture + '" style="width:100%;height:100%;object-fit:cover;">'
        : '<i class="fa-solid fa-user" style="font-size:4rem;color:#6B7280;"></i>';

    document.getElementById('studentDropdown').style.display = 'none';
}

document.addEventListener('click', function (event) {
    var dropdown    = document.getElementById('studentDropdown');
    var searchInput = document.getElementById('studentSearch');
    if (dropdown && searchInput && !dropdown.contains(event.target) && event.target !== searchInput) {
        dropdown.style.display = 'none';
    }

    if (event.target && event.target.id === 'candidateModal') {
        closeModal();
    }

    if (event.target && event.target.id === 'registrationModal') {
        closeRegistrationModal();
    }
});

window.addEventListener('DOMContentLoaded', function () {
    var params = new URLSearchParams(window.location.search);
    var electionIdFromUrl = parseInt(params.get('election') || '', 10);
    var electionId = !isNaN(electionIdFromUrl) ? electionIdFromUrl : getPersistedElectionContextId();

    if (!electionId) {
        return;
    }

    var exists = electionsData.some(function (election) {
        return election.id === electionId;
    });

    if (!exists) {
        clearPersistedElectionContext();
        return;
    }

    showElectionCandidates(electionId);
});

// ── Auto-open Add modal from dashboard shortcut ──────────
if (new URLSearchParams(window.location.search).get('action') === 'add') {
    window.addEventListener('DOMContentLoaded', function () {
        showToast('Select an election first, then click Submit Registration.', 'error');
    });
}
