/* ============================================================
   Department Head — Campus Elections JS
   PHP data bridge (in blade): const electionsData = @json($elections)
   ============================================================ */

// ── Toast bridge ────────────────────────────────────────
function showToast(message, type) {
    type = type || 'success';
    _swalToast(type, message);
}

var addedPositions = [];
var addedEditPositions = [];
var addedPartylistTeams = [];
var addedEditPartylistTeams = [];
var lastAddedPositionSelectionKey = '';

function parseIsoDate(isoDate) {
    if (!isoDate) return null;
    var parts = isoDate.split('-').map(function (part) { return parseInt(part, 10); });
    if (parts.length !== 3 || !parts[0] || !parts[1] || !parts[2]) return null;
    return new Date(parts[0], parts[1] - 1, parts[2]);
}

function validateAddElectionDateRules() {
    var regStartValue = document.getElementById('electionRegStartDate').value;
    var regEndValue = document.getElementById('electionRegEndDate').value;
    var electionStartValue = document.getElementById('electionStartDate').value;
    var electionEndValue = document.getElementById('electionEndDate').value;

    if (!regStartValue || !regEndValue || !electionStartValue || !electionEndValue) {
        return { ok: false, message: 'Please compl ete all required date fields.' };
    }

    var regStart = parseIsoDate(regStartValue);
    var regEnd = parseIsoDate(regEndValue);
    var electionStart = parseIsoDate(electionStartValue);
    var electionEnd = parseIsoDate(electionEndValue);

    if (!regStart || !regEnd || !electionStart || !electionEnd) {
        return { ok: false, message: 'Please enter valid calendar dates.' };
    }
    if (regStart > regEnd) {
        return { ok: false, message: 'Registration start date must be on or before registration end date.' };
    }
    if (electionStart > electionEnd) {
        return { ok: false, message: 'Election start date must be on or before election end date.' };
    }
    if (regEnd >= electionStart) {
        return { ok: false, message: 'Registration period must end before the election starts.' };
    }

    return { ok: true };
}

function getSelectionKey(values) {
    return (values || [])
        .map(function (value) { return String(value || '').trim(); })
        .filter(function (value) { return value !== ''; })
        .sort()
        .join('||');
}

function setAddPositionButtonState(isAdded) {
    var button = document.getElementById('addPositionBtn');
    if (!button) return;

    if (isAdded) {
        button.innerHTML = '<i class="fa-solid fa-check"></i> Already Added';
        button.style.background = 'linear-gradient(135deg, #15803d 0%, #22c55e 100%)';
    } else {
        button.innerHTML = '<i class="fa-solid fa-plus"></i> Add Position';
        button.style.background = 'linear-gradient(135deg, #800020 0%, #A0153E 100%)';
    }
}

function syncAddPositionButtonStateWithSelection() {
    var selected = getCheckedValues('positionChoices[]');
    var selectedKey = getSelectionKey(selected);
    setAddPositionButtonState(selectedKey !== '' && selectedKey === lastAddedPositionSelectionKey);
}

function registerAddPositionSelectionListeners() {
    document.querySelectorAll('input[name="positionChoices[]"]').forEach(function (checkbox) {
        if (checkbox.dataset.addPositionBound === '1') return;
        checkbox.addEventListener('change', syncAddPositionButtonStateWithSelection);
        checkbox.dataset.addPositionBound = '1';
    });
}

function resetCustomPositions() {
    var container = document.getElementById('positionInput');
    if (!container) {
        return;
    }
    Array.from(container.querySelectorAll('label[data-custom-position="1"]')).forEach(function (label) {
        label.remove();
    });
}

function addCustomPosition() {
    var input = document.getElementById('customPositionName');
    if (!input) {
        return;
    }

    var position = input.value.trim();
    if (!position) {
        showToast('Please enter a position name.', 'error');
        return;
    }

    var existing = Array.from(document.querySelectorAll('input[name="positionChoices[]"]')).map(function (checkbox) {
        return checkbox.value.trim().toLowerCase();
    });

    if (existing.indexOf(position.toLowerCase()) !== -1) {
        showToast('This position already exists.', 'error');
        return;
    }

    var container = document.getElementById('positionInput');
    if (!container) {
        return;
    }

    var label = document.createElement('label');
    label.dataset.customPosition = '1';
    label.style.display = 'flex';
    label.style.alignItems = 'center';
    label.style.gap = '0.45rem';
    label.style.fontSize = '0.88rem';

    var checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.name = 'positionChoices[]';
    checkbox.value = position;
    checkbox.checked = true;
    checkbox.style.margin = '0';

    label.appendChild(checkbox);
    label.appendChild(document.createTextNode(' ' + position));
    container.appendChild(label);

    input.value = '';
    registerAddPositionSelectionListeners();
    syncAddPositionButtonStateWithSelection();
    showToast('Added custom position: ' + position);
}

function getCheckedValues(inputName) {
    return Array.from(document.querySelectorAll('input[name="' + inputName + '"]:checked'))
        .map(function (checkbox) { return checkbox.value.trim(); })
        .filter(function (value) { return value !== ''; });
}

function setCheckedValues(inputName, values) {
    var valueSet = new Set((values || []).map(function (value) {
        return String(value || '').trim();
    }).filter(function (value) {
        return value !== '';
    }));

    document.querySelectorAll('input[name="' + inputName + '"]').forEach(function (checkbox) {
        checkbox.checked = valueSet.has(checkbox.value.trim());
    });
}

function mergeUnique(existing, incoming) {
    var merged = Array.isArray(existing) ? existing.slice() : [];
    (incoming || []).forEach(function (value) {
        if (merged.indexOf(value) === -1) {
            merged.push(value);
        }
    });

    return merged;
}

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function renderPartylistChips(containerId, teams, removeHandlerName) {
    var container = document.getElementById(containerId);
    if (!container) {
        return;
    }

    if (!teams.length) {
        container.innerHTML = '<span style="font-size:0.82rem;color:#64748b;">No partylist teams added yet.</span>';
        return;
    }

    container.innerHTML = teams.map(function (team, index) {
        return '<span style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.45rem 0.7rem;border-radius:999px;background:#f8fafc;border:1px solid #e2e8f0;color:#334155;font-size:0.82rem;font-weight:600;">'
            + '<span><strong>' + escapeHtml(team.name) + '</strong>' + (team.tagline ? ' <span style="font-weight:500;color:#64748b;">' + escapeHtml(team.tagline) + '</span>' : '') + '</span>'
            + '<button type="button" onclick="' + removeHandlerName + '(' + index + ')" style="border:none;background:transparent;color:#b91c1c;cursor:pointer;font-size:0.9rem;">&times;</button>'
            + '</span>';
    }).join('');
}

function syncPartylistSupport() {
    renderPartylistChips('partylistChipList', addedPartylistTeams, 'removePartylistTeam');
    renderPartylistChips('editPartylistChipList', addedEditPartylistTeams, 'removeEditPartylistTeam');
}

function addPartylistTeam() {
    var nameInput = document.getElementById('partylistName');
    var taglineInput = document.getElementById('partylistTagline');
    var name = nameInput ? nameInput.value.trim() : '';
    var tagline = taglineInput ? taglineInput.value.trim() : '';

    if (!name) {
        showToast('Please enter a partylist team name.', 'error');
        return;
    }

    if (addedPartylistTeams.some(function (team) { return team.name.toLowerCase() === name.toLowerCase(); })) {
        showToast('That partylist team already exists.', 'error');
        return;
    }

    addedPartylistTeams.push({ name: name, tagline: tagline });
    if (nameInput) nameInput.value = '';
    if (taglineInput) taglineInput.value = '';
    syncPartylistSupport();
}

function removePartylistTeam(index) {
    addedPartylistTeams.splice(index, 1);
    syncPartylistSupport();
}

function addEditPartylistTeam() {
    var nameInput = document.getElementById('editPartylistName');
    var taglineInput = document.getElementById('editPartylistTagline');
    var name = nameInput ? nameInput.value.trim() : '';
    var tagline = taglineInput ? taglineInput.value.trim() : '';

    if (!name) {
        showToast('Please enter a partylist team name.', 'error');
        return;
    }

    if (addedEditPartylistTeams.some(function (team) { return team.name.toLowerCase() === name.toLowerCase(); })) {
        showToast('That partylist team already exists.', 'error');
        return;
    }

    addedEditPartylistTeams.push({ name: name, tagline: tagline });
    if (nameInput) nameInput.value = '';
    if (taglineInput) taglineInput.value = '';
    syncPartylistSupport();
}

function removeEditPartylistTeam(index) {
    addedEditPartylistTeams.splice(index, 1);
    syncPartylistSupport();
}

function setPartylistTeamsToFormData(formData, teams) {
    (teams || []).forEach(function (team, index) {
        formData.append('partylist_teams[' + index + '][name]', team.name || '');
        formData.append('partylist_teams[' + index + '][tagline]', team.tagline || '');
    });
}

function parseJsonResponse(res) {
    return res.text().then(function (text) {
        var payload = {};

        if (text) {
            try {
                payload = JSON.parse(text);
            } catch (error) {
                payload = {
                    success: false,
                    message: text.indexOf('<') !== -1
                        ? 'The server returned an HTML response instead of JSON. Please check your session and try again.'
                        : 'Unexpected server response. Please try again.'
                };
            }
        }

        if (!res.ok && !payload.message) {
            payload.message = 'Request failed. Please try again.';
        }

        return payload;
    });
}

// ── Positions chip (Add modal) ───────────────────────────
function addPositionChip() {
    var selected = getCheckedValues('positionChoices[]');
    if (selected.length === 0) { showToast('Please choose at least one position', 'error'); return; }

    var selectedKey = getSelectionKey(selected);

    var beforeCount = addedPositions.length;
    addedPositions = mergeUnique(addedPositions, selected);
    var addedNow = addedPositions.length - beforeCount;

    if (addedNow <= 0) {
        showToast('Selected positions are already added', 'error');
    } else {
        showToast(addedNow + ' position(s) added');
    }

    lastAddedPositionSelectionKey = selectedKey;
    setAddPositionButtonState(true);
}

// ── Positions chip (Edit modal) ──────────────────────────
function addEditPositionChip() {
    var selected = getCheckedValues('editPositionChoices[]');
    if (selected.length === 0) { showToast('Please choose at least one position', 'error'); return; }

    var beforeCount = addedEditPositions.length;
    addedEditPositions = mergeUnique(addedEditPositions, selected);
    var addedNow = addedEditPositions.length - beforeCount;

    if (addedNow <= 0) {
        showToast('Selected positions are already added', 'error');
    } else {
        showToast(addedNow + ' position(s) added');
    }

    setCheckedValues('editPositionChoices[]', []);
}

// ── Add modal ────────────────────────────────────────────
function openModal() {
    document.getElementById('electionModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    resetCustomPositions();
    var customInput = document.getElementById('customPositionName');
    if (customInput) {
        customInput.value = '';
    }
    addedPositions = [];
    addedPartylistTeams = [];
    lastAddedPositionSelectionKey = '';
    setCheckedValues('positionChoices[]', []);
    registerAddPositionSelectionListeners();
    setAddPositionButtonState(false);
    syncPartylistSupport();
}
function closeModal() {
    document.getElementById('electionModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    resetCustomPositions();
    var customInput = document.getElementById('customPositionName');
    if (customInput) {
        customInput.value = '';
    }
    addedPositions = [];
    addedPartylistTeams = [];
    lastAddedPositionSelectionKey = '';
    setCheckedValues('positionChoices[]', []);
    setAddPositionButtonState(false);
    syncPartylistSupport();
}

function handleAddElection(e) {
    e.preventDefault();
    var dateValidation = validateAddElectionDateRules();
    if (!dateValidation.ok) { showToast(dateValidation.message, 'error'); return; }

    var positions     = addedPositions.slice();
    if (positions.length === 0) { showToast('Please add at least one position', 'error'); return; }

    var formData = new FormData();
    formData.append('department',    document.getElementById('electionDept').value);
    formData.append('election_name', document.getElementById('electionName').value);
    formData.append('description',   document.getElementById('electionDesc').value || '');
    formData.append('start_date',    document.getElementById('electionStartDate').value);
    formData.append('end_date',      document.getElementById('electionEndDate').value);
    formData.append('registration_start_date', document.getElementById('electionRegStartDate').value);
    formData.append('registration_end_date', document.getElementById('electionRegEndDate').value);
    formData.append('voting_start_time', document.getElementById('electionVotingStartTime').value);
    formData.append('voting_end_time', document.getElementById('electionVotingEndTime').value);
    formData.append('is_active',     document.getElementById('electionActive').checked);
    positions.forEach(function (pos, idx) { formData.append('positions[' + idx + ']', pos); });
    setPartylistTeamsToFormData(formData, addedPartylistTeams);
    var photo = document.getElementById('electionPhoto').files[0];
    if (photo) formData.append('banner_image', photo);

    fetch('/department-head/campus-elections', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: formData
    })
    .then(function (res) {
        return parseJsonResponse(res).then(function (data) {
            if (!res.ok) {
                throw data;
            }

            return data;
        });
    })
    .then(function (data) {
        if (data.success) {
            showToast(data.message);
            closeModal();
            document.getElementById('addElectionForm').reset();
            addedPartylistTeams = [];
            syncPartylistSupport();
            setTimeout(function () { location.reload(); }, 1000);
        }
    })
    .catch(function (error) {
        console.error('Error:', error);
        if (error.errors)        showToast(Object.values(error.errors)[0][0], 'error');
        else if (error.message)  showToast(error.message, 'error');
        else                     showToast('An error occurred. Please try again.', 'error');
    });
}

// ── Edit modal ───────────────────────────────────────────
function openEditModal(id) {
    var election = electionsData.find(function (e) { return e.id === id; });
    if (!election) return;

    document.getElementById('editElectionId').value         = election.id;
    document.getElementById('editElectionName').value       = election.election_name;
    document.getElementById('editElectionDesc').value       = election.description || '';
    document.getElementById('editElectionStartDate').value  = election.start_date.split('T')[0];
    document.getElementById('editElectionEndDate').value    = election.end_date.split('T')[0];
    document.getElementById('editElectionRegStartDate').value = (election.registration_start_date || election.start_date).split('T')[0];
    document.getElementById('editElectionRegEndDate').value = (election.registration_end_date || election.end_date).split('T')[0];
    document.getElementById('editElectionVotingStartTime').value = formatTimeForInput(election.voting_start_time, '08:00');
    document.getElementById('editElectionVotingEndTime').value = formatTimeForInput(election.voting_end_time, '17:00');
    document.getElementById('editElectionActive').checked   = election.is_active;

    addedEditPositions = Array.isArray(election.positions)
        ? election.positions.map(function (pos) { return String(pos || '').trim(); }).filter(function (pos) { return pos !== ''; })
        : [];
    setCheckedValues('editPositionChoices[]', addedEditPositions);

    addedEditPartylistTeams = Array.isArray(election.partylist_teams)
        ? election.partylist_teams.map(function (team) {
            return {
                name: String(team.name || '').trim(),
                tagline: String(team.tagline || '').trim()
            };
        }).filter(function (team) { return team.name !== ''; })
        : [];
    syncPartylistSupport();

    var bannerDiv = document.getElementById('currentBanner');
    bannerDiv.innerHTML = election.banner_image
        ? '<small style="color:#888;">Current banner: <img src="' + election.banner_image + '" style="max-width:100px;border-radius:4px;vertical-align:middle;"></small>'
        : '';

    document.getElementById('editElectionModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeEditModal() {
    document.getElementById('editElectionModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    addedEditPositions = [];
    addedEditPartylistTeams = [];
    setCheckedValues('editPositionChoices[]', []);
    syncPartylistSupport();
}

function handleUpdateElection(e) {
    e.preventDefault();
    var id            = document.getElementById('editElectionId').value;
    var positions     = addedEditPositions.slice();
    if (positions.length === 0) { _swalErr('Please add at least one position.'); return; }

    _swalConfirm('Update Election?', 'Are you sure you want to update this election?', 'Yes, Update', function () {
        var formData = new FormData();
        formData.append('_method',       'PUT');
        formData.append('department',    document.getElementById('editElectionDept').value);
        formData.append('election_name', document.getElementById('editElectionName').value);
        formData.append('description',   document.getElementById('editElectionDesc').value || '');
        formData.append('start_date',    document.getElementById('editElectionStartDate').value);
        formData.append('end_date',      document.getElementById('editElectionEndDate').value);
        formData.append('registration_start_date', document.getElementById('editElectionRegStartDate').value);
        formData.append('registration_end_date', document.getElementById('editElectionRegEndDate').value);
        formData.append('voting_start_time', document.getElementById('editElectionVotingStartTime').value);
        formData.append('voting_end_time', document.getElementById('editElectionVotingEndTime').value);
        formData.append('is_active',     document.getElementById('editElectionActive').checked);
        positions.forEach(function (pos, idx) { formData.append('positions[' + idx + ']', pos); });
        setPartylistTeamsToFormData(formData, addedEditPartylistTeams);
        var photo = document.getElementById('editElectionPhoto').files[0];
        if (photo) formData.append('banner_image', photo);

        fetch('/department-head/campus-elections/' + id, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData
        })
        .then(function (res) {
            return parseJsonResponse(res).then(function (data) {
                if (!res.ok) {
                    throw data;
                }

                return data;
            });
        })
        .then(function (data) {
            if (data.success) {
                _swalOK('Updated!', data.message, function () { closeEditModal(); location.reload(); });
            }
        })
        .catch(function (error) {
            console.error('Error:', error);
            var msg = 'An error occurred. Please try again.';
            if (error.errors)       msg = Object.values(error.errors)[0][0];
            else if (error.message) msg = error.message;
            _swalErr(msg);
        });
    });
}

function formatTimeForInput(value, fallback) {
    var raw = String(value || fallback || '').trim();
    if (!raw) {
        return '';
    }

    return raw.length >= 5 ? raw.slice(0, 5) : raw;
}

// ── Toggle active / inactive ─────────────────────────────
function toggleStatus(id, newStatus) {
    var action      = newStatus ? 'enable' : 'disable';
    var actionLabel = action.charAt(0).toUpperCase() + action.slice(1);
    _swalConfirm(actionLabel + ' Election?', 'Are you sure you want to ' + action + ' this election?', 'Yes, ' + actionLabel, function () {
        fetch('/department-head/campus-elections/' + id + '/toggle', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ is_active: newStatus })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                _swalOK('Done!', data.message, function () { location.reload(); });
            }
        })
        .catch(function (error) {
            console.error('Error:', error);
            _swalErr('An error occurred. Please try again.');
        });
    });
}

// ── Description overlay ──────────────────────────────────
function openDescOverlay(id) {
    document.getElementById('election-card-' + id).classList.add('overlay-open');
}
function closeDescOverlay(id) {
    document.getElementById('election-card-' + id).classList.remove('overlay-open');
}

// ── Campus Elections Filter ──────────────────────────────
function applyCampusFilters() {
    var dateFrom = document.getElementById('campusFilterDateFrom').value;
    var dateTo   = document.getElementById('campusFilterDateTo').value;
    var sortBy   = document.getElementById('campusFilterSort').value;
    var status   = document.getElementById('campusFilterStatus').value;
    var search   = document.getElementById('campusFilterSearch').value.toLowerCase().trim();

    var grid = document.getElementById('campusElectionsGrid');
    if (!grid) return;
    var cards = Array.from(grid.querySelectorAll('.election-card'));

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

    var emptyEl = document.getElementById('campusEmptyFilter');
    if (visibleCards.length === 0) {
        if (!emptyEl) {
            emptyEl = document.createElement('div');
            emptyEl.id = 'campusEmptyFilter';
            emptyEl.style.cssText = 'grid-column:1/-1;text-align:center;padding:4rem;background:white;border-radius:16px;color:#888;';
            emptyEl.innerHTML = '<i class="fa-solid fa-filter" style="font-size:3rem;margin-bottom:1rem;opacity:0.3;"></i><h3 style="color:#6B7280;">No elections match your filters</h3><p style="color:#9ca3af;">Try adjusting or resetting your filters.</p>';
            grid.appendChild(emptyEl);
        }
    } else if (emptyEl) {
        emptyEl.remove();
    }
}

function resetCampusFilters() {
    document.getElementById('campusFilterDateFrom').value = '';
    document.getElementById('campusFilterDateTo').value   = '';
    document.getElementById('campusFilterSort').value     = '';
    document.getElementById('campusFilterStatus').value   = '';
    document.getElementById('campusFilterSearch').value   = '';
    applyCampusFilters();
}

// ── Auto-open Add modal from dashboard shortcut ──────────
if (new URLSearchParams(window.location.search).get('action') === 'add') {
    window.addEventListener('DOMContentLoaded', function () { openModal(); });
}

// ── Close overlay modals on outside click ────────────────
window.onclick = function (event) {
    // campus-elections uses custom overlay-trigger; nothing to close here
};
