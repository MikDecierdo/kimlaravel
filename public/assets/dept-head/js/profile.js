/* ============================================================
   Department Head — Edit Profile Page Scripts
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

    // ── Avatar preview on file selection ───────────────────
    var avatarInput = document.getElementById('avatarInput');
    if (avatarInput) {
        avatarInput.addEventListener('change', function (e) {
            var file = e.target.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (ev) {
                var preview = document.getElementById('avatarPreview');
                var initial = document.getElementById('avatarInitial');
                preview.src = ev.target.result;
                preview.style.display = 'block';
                if (initial) initial.style.display = 'none';
            };
            reader.readAsDataURL(file);
        });
    }

    // ── Password match indicator ────────────────────────────
    var newPwInput     = document.getElementById('newPw');
    var confirmPwInput = document.getElementById('confirmPw');
    if (newPwInput && confirmPwInput) {
        newPwInput.addEventListener('input', checkPwMatch);
        confirmPwInput.addEventListener('input', checkPwMatch);
    }

});

// ── Password visibility toggle ──────────────────────────────
function togglePw(fieldId, iconId) {
    var input = document.getElementById(fieldId);
    var icon  = document.getElementById(iconId);
    if (!input || !icon) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// ── Password match indicator ────────────────────────────────
function checkPwMatch() {
    var pw      = document.getElementById('newPw').value;
    var confirm = document.getElementById('confirmPw').value;
    var msg     = document.getElementById('pwMatchMsg');
    if (!msg) return;
    if (!confirm) { msg.style.display = 'none'; return; }
    msg.style.display = 'block';
    if (pw === confirm) {
        msg.textContent = '\u2713 Passwords match';
        msg.className   = 'pw-match ok';
    } else {
        msg.textContent = '\u2717 Passwords do not match';
        msg.className   = 'pw-match fail';
    }
}
