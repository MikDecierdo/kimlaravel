<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Account Request - SPC Voting System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary-maroon:       #800000;
            --hover-maroon:         #5c0000;
            --accent-yellow:        #EAB308;
            --accent-yellow-light:  #fef3c7;
            --bg-color:             #ffffff;
            --text-main:            #2d3748;
            --text-light:           #718096;
            --input-border:         #e2e8f0;
            --focus-ring:           rgba(234,179,8,.3);
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Inter', sans-serif;
            height: 100vh;
            width: 100%;
            overflow: hidden;
            background-color: var(--bg-color);
        }

        /* â”€â”€ Split layout â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .split-screen { display:flex; height:100%; width:100%; }

        /* Left pane */
        .left-pane {
            flex: 1;
            background-image: url('{{ asset('images/campus-courtyard.jpg') }}');
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .left-pane::before {
            content:'';
            position:absolute; inset:0;
            background: linear-gradient(to bottom, rgba(128,0,0,.15), rgba(128,0,0,.85));
        }
        .image-overlay-text {
            position:absolute; bottom:4rem; left:4rem;
            color:white; z-index:2; text-shadow:0 2px 4px rgba(0,0,0,.3);
        }
        .image-overlay-text h2 { font-size:2.5rem; font-weight:700; margin-bottom:.5rem; }
        .image-overlay-text p  { font-size:1.1rem; opacity:.9; color:var(--accent-yellow); }

        /* Right pane â€” wider to accommodate bigger form */
        .right-pane {
            flex: 1.35;
            display:flex; flex-direction:column;
            justify-content:flex-start; align-items:center;
            padding:2rem 2.5rem;
            background:#fff; position:relative;
            overflow-y: auto;
        }

        .register-container {
            width:100%;
            max-width:620px;
            padding: 1.5rem 0;
        }

        /* â”€â”€ Brand â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .brand-logo {
            display:flex; flex-direction:column;
            align-items:center; margin-bottom:1.25rem; text-align:center;
        }
        .brand-logo img  { height:72px; width:auto; margin-bottom:.7rem; object-fit:contain; }
        .brand-logo h1   { font-size:1.15rem; font-weight:700; color:var(--primary-maroon); line-height:1.4; }
        .brand-logo span { font-size:.88rem; color:var(--text-light); font-weight:500; }

        .welcome-text { margin-bottom:1.25rem; text-align:center; }
        .welcome-text h2 { font-size:1.35rem; color:var(--text-main); margin-bottom:.3rem; }
        .welcome-text p  { color:var(--text-light); font-size:.88rem; }

        /* Pending info banner */
        .pending-info {
            background:#fff7ed; border:1px solid #fed7aa; border-left:4px solid #f97316;
            border-radius:8px; padding:.7rem 1rem; font-size:.81rem;
            color:#92400e; margin-bottom:1.25rem;
            display:flex; gap:.6rem; align-items:flex-start;
        }

        /* â”€â”€ Form elements â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .form-group { margin-bottom:1.1rem; position:relative; }
        .form-label  { display:block; margin-bottom:.4rem; font-size:.86rem; font-weight:600; color:var(--text-main); }

        /* Grid helpers */
        .form-row-2 { display:grid; grid-template-columns:1fr 1fr;     gap:.9rem; }
        .form-row-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:.9rem; }

        /* Input wrapper with left icon */
        .input-wrapper { position:relative; }
        .input-wrapper .left-icon {
            position:absolute; left:.95rem; top:50%; transform:translateY(-50%);
            color:#a0aec0; font-size:.95rem; transition:color .3s; pointer-events:none;
            z-index:1;
        }
        .form-input {
            width:100%; padding:.78rem 1rem .78rem 2.65rem;
            border:2px solid var(--input-border); border-radius:8px;
            font-size:.9rem; font-family:inherit; color:var(--text-main);
            transition:all .3s; outline:none;
            background:#fff;
        }
        .form-input:focus {
            border-color:var(--accent-yellow);
            box-shadow:0 0 0 4px var(--focus-ring);
        }
        .form-input:focus ~ .left-icon { color:var(--accent-yellow); }

        select.form-input { appearance:none; }

        /* Email input group — username + fixed domain suffix */
        .email-input-group {
            display:flex;
            border:2px solid var(--input-border);
            border-radius:8px;
            overflow:hidden;
            align-items:stretch;
            transition:border-color .3s, box-shadow .3s;
            background:#fff;
        }
        .email-input-group:focus-within {
            border-color:var(--accent-yellow);
            box-shadow:0 0 0 4px var(--focus-ring);
        }
        .email-input-group:focus-within .left-icon { color:var(--accent-yellow); }
        .email-username-wrapper { position:relative; flex:1; display:flex; align-items:center; }
        .email-username-wrapper .left-icon {
            position:absolute; left:.95rem;
            color:#a0aec0; font-size:.95rem; pointer-events:none; z-index:1;
        }
        .email-username-input {
            width:100%; border:none; outline:none;
            padding:.78rem 1rem .78rem 2.65rem;
            font-size:.9rem; font-family:inherit; color:var(--text-main);
            background:transparent;
        }
        .email-suffix-badge {
            display:flex; align-items:center;
            background:rgba(128,0,0,.06);
            color:var(--primary-maroon); font-weight:700;
            font-size:.85rem; padding:.78rem .9rem;
            border-left:1px solid var(--input-border);
            white-space:nowrap; user-select:none;
            transition:background .25s;
        }
        .email-suffix-badge.no-dept {
            color:#a0aec0; background:#f8fafc; font-weight:500;
        }

        /* Password wrapper â€” with show/hide button on right */
        .pw-wrapper { position:relative; }
        .pw-wrapper .left-icon {
            position:absolute; left:.95rem; top:50%; transform:translateY(-50%);
            color:#a0aec0; font-size:.95rem; pointer-events:none;
            transition:color .3s; z-index:1;
        }
        .pw-wrapper .form-input {
            padding-right: 3rem;   /* room for toggle btn */
        }
        .pw-wrapper .form-input:focus ~ .left-icon { color:var(--accent-yellow); }
        .pw-toggle {
            position:absolute; right:.75rem; top:50%; transform:translateY(-50%);
            background:none; border:none; cursor:pointer;
            color:#a0aec0; font-size:.95rem; padding:.25rem;
            transition:color .2s; z-index:2;
            display:flex; align-items:center; justify-content:center;
        }
        .pw-toggle:hover { color:var(--primary-maroon); }

        /* Submit button */
        .btn-submit {
            width:100%; padding:.88rem;
            background-color:var(--primary-maroon);
            color:white;
            border:2px solid var(--primary-maroon);
            border-radius:8px; font-size:1rem; font-weight:600;
            cursor:pointer; transition:all .3s;
            display:flex; justify-content:center; align-items:center; gap:8px;
            margin-top:.5rem;
        }
        .btn-submit:hover  { background-color:var(--hover-maroon); border-color:var(--hover-maroon); }
        .btn-submit:active { transform:scale(.98); }

        .register-footer {
            margin-top:1.5rem; text-align:center;
            font-size:.85rem; color:var(--text-light);
        }
        .register-footer a { color:var(--primary-maroon); text-decoration:none; font-weight:600; }
        .register-footer a:hover { text-decoration:underline; }

        /* Error */
        .error-msg {
            background:#fff5f5; color:#c53030;
            padding:.75rem 1rem; border-radius:6px;
            font-size:.86rem; margin-bottom:1.1rem;
            border-left:4px solid #c53030;
            display:flex; align-items:center; gap:10px;
        }

        /* Section divider label */
        .form-section-label {
            font-size:.72rem; font-weight:800; text-transform:uppercase;
            letter-spacing:.07em; color:var(--primary-maroon);
            margin-bottom:.75rem; margin-top:.25rem;
            display:flex; align-items:center; gap:.5rem;
        }
        .form-section-label::after {
            content:''; flex:1; height:1px; background:#e2e8f0;
        }

        @media (max-width:960px) {
            .left-pane { display:none; }
            .split-screen { justify-content:center; }
            .right-pane { width:100%; flex:none; }
            body { overflow:auto; }
        }
        @media (max-width:520px) {
            .form-row-2, .form-row-3 { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>
<div class="split-screen">

    <!-- Left: campus image -->
    <div class="left-pane">
        <div class="image-overlay-text">
            <h2>Join the SPC Community</h2>
            <p>Submit your account request today</p>
        </div>
    </div>

    <!-- Right: registration form -->
    <div class="right-pane">
        <div class="register-container">

            <!-- Brand -->
            <div class="brand-logo">
                <img src="{{ asset('images/spc-logo.png') }}" alt="SPC Logo">
                <h1>Southern de Oro Philippines College</h1>
                <span>Student Account Request</span>
            </div>

            <div class="welcome-text">
                <h2>Submit Account Request</h2>
                <p>Fill in your details. Your Department Head will review and approve your account.</p>
            </div>

            <!-- Pending notice -->
            <div class="pending-info">
                <i class="fas fa-info-circle" style="flex-shrink:0;margin-top:1px;"></i>
                <span>After submitting, your account will be <strong>pending approval</strong>. You will be able to sign in once your Department Head approves your request.</span>
            </div>

            <!-- Errors -->
            @if($errors->any())
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- â‘  Department first â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
                <div class="form-section-label"><i class="fas fa-building"></i> Department &amp; Year Level</div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="department" class="form-label">Department <span style="color:#ef4444">*</span></label>
                        <div class="input-wrapper">
                            <select id="department" name="department" class="form-input" required
                                    onchange="onDeptChange(this.value)">
                                <option value="">— Choose department —</option>
                                <option value="BSIT" {{ old('department')=='BSIT' ? 'selected':'' }}>BSIT — Information Technology</option>
                                <option value="CBAE" {{ old('department')=='CBAE' ? 'selected':'' }}>CBAE — Business &amp; Accountancy</option>
                                <option value="CRIM" {{ old('department')=='CRIM' ? 'selected':'' }}>CRIM — Criminology</option>
                                <option value="CHTM" {{ old('department')=='CHTM' ? 'selected':'' }}>CHTM — Hospitality &amp; Tourism</option>
                                <option value="CTE"  {{ old('department')=='CTE'  ? 'selected':'' }}>CTE — Teacher Education</option>
                                <option value="SHS"  {{ old('department')=='SHS'  ? 'selected':'' }}>SHS — Senior High School</option>
                            </select>
                            <i class="fas fa-building left-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="year_level" class="form-label">Year Level <span style="color:#ef4444">*</span></label>
                        <div class="input-wrapper">
                            <select id="year_level" name="year_level" class="form-input" required>
                                <option value="">— Choose year level —</option>
                                <option value="1st Year" data-group="college" {{ old('year_level')=='1st Year' ? 'selected':'' }}>1st Year</option>
                                <option value="2nd Year" data-group="college" {{ old('year_level')=='2nd Year' ? 'selected':'' }}>2nd Year</option>
                                <option value="3rd Year" data-group="college" {{ old('year_level')=='3rd Year' ? 'selected':'' }}>3rd Year</option>
                                <option value="4th Year" data-group="college" {{ old('year_level')=='4th Year' ? 'selected':'' }}>4th Year</option>
                                <option value="Grade 11" data-group="shs" {{ old('year_level')=='Grade 11' ? 'selected':'' }}>Grade 11</option>
                                <option value="Grade 12" data-group="shs" {{ old('year_level')=='Grade 12' ? 'selected':'' }}>Grade 12</option>
                            </select>
                            <i class="fas fa-layer-group left-icon"></i>
                        </div>
                    </div>
                </div>

                <!-- â‘¡ Student ID â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
                <div class="form-group">
                    <label for="student_id" class="form-label">Student ID</label>
                    <div class="input-wrapper">
                        <input type="text" id="student_id" name="student_id" class="form-input"
                               placeholder="e.g. 2024-00001"
                               value="{{ old('student_id') }}">
                        <i class="fas fa-id-card left-icon"></i>
                    </div>
                </div>

                <!-- â‘¢ Name â€” 3 columns â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
                <div class="form-section-label"><i class="fas fa-user"></i> Full Name</div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name <span style="color:#ef4444">*</span></label>
                        <div class="input-wrapper">
                            <input type="text" id="first_name" name="first_name" class="form-input"
                                   placeholder="Juan"
                                   value="{{ old('first_name') }}" required autofocus>
                            <i class="fas fa-user left-icon"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="middle_name" class="form-label">Middle Name <span style="color:#9ca3af;font-weight:400;font-size:0.8rem;">(Optional)</span></label>
                        <div class="input-wrapper">
                            <input type="text" id="middle_name" name="middle_name" class="form-input"
                                   placeholder="Santos"
                                   value="{{ old('middle_name') }}">
                            <i class="fas fa-user left-icon"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name <span style="color:#ef4444">*</span></label>
                        <div class="input-wrapper">
                            <input type="text" id="last_name" name="last_name" class="form-input"
                                   placeholder="dela Cruz"
                                   value="{{ old('last_name') }}" required>
                            <i class="fas fa-user left-icon"></i>
                        </div>
                    </div>
                </div>

                <!-- â‘£ Email â€” dynamic placeholder â”€â”€â”€â”€â”€â”€â”€â”€ -->
                <div class="form-section-label"><i class="fas fa-envelope"></i> Contact</div>

                <div class="form-group">
                    <label for="emailUsername" class="form-label">Email Address <span style="color:#ef4444">*</span></label>
                    <div class="email-input-group" id="emailInputGroup">
                        <div class="email-username-wrapper">
                            <input type="text" id="emailUsername" class="email-username-input"
                                   placeholder="yourname"
                                   value="{{ old('email') ? explode('@', old('email'))[0] : '' }}"
                                   autocomplete="off" spellcheck="false" required>
                            <i class="fas fa-envelope left-icon"></i>
                        </div>
                        <span class="email-suffix-badge no-dept" id="emailSuffixBadge">select dept first</span>
                    </div>
                    {{-- hidden field carries the full assembled email --}}
                    <input type="hidden" id="email" name="email"
                           value="{{ old('email') }}">
                </div>

                <!-- â‘¤ Password â€” show/hide â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
                <div class="form-section-label"><i class="fas fa-lock"></i> Security</div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="password" class="form-label">Password <span style="color:#ef4444">*</span></label>
                        <div class="pw-wrapper">
                            <input type="password" id="password" name="password" class="form-input"
                                   placeholder="Min. 8 characters" required>
                            <i class="fas fa-lock left-icon"></i>
                            <button type="button" class="pw-toggle" onclick="togglePw('password','eyeIcon1')" tabindex="-1">
                                <i class="fas fa-eye" id="eyeIcon1"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirm Password <span style="color:#ef4444">*</span></label>
                        <div class="pw-wrapper">
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="form-input" placeholder="Re-enter password" required>
                            <i class="fas fa-lock left-icon"></i>
                            <button type="button" class="pw-toggle" onclick="togglePw('password_confirmation','eyeIcon2')" tabindex="-1">
                                <i class="fas fa-eye" id="eyeIcon2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i>
                    <span>Submit Request</span>
                </button>
            </form>

            <div class="register-footer">
                Already have an approved account? <a href="{{ route('login') }}">Sign In</a>
                <br><br>
                &copy; {{ date('Y') }} SPC Voting System. All rights reserved.
            </div>
        </div>
    </div>
</div>

<script>
    // -- Department -> email domain -----------------------------------
    const domainMap = {
        'BSIT': '@bsitstudents.com',
        'CBAE': '@cbaestudents.com',
        'CRIM': '@crimstudents.com',
        'CHTM': '@chtmstudents.com',
        'CTE':  '@ctestudents.com',
        'SHS':  '@shsstudents.com',
    };
    let currentDomain = '';

    function updateYearLevel(dept) {
        const ylSelect = document.getElementById('year_level');
        const isSHS    = dept === 'SHS';
        const isNone   = dept === '';
        let   firstVisible = null;

        Array.from(ylSelect.options).forEach(function(opt) {
            if (!opt.value) return; // keep placeholder
            const group = opt.getAttribute('data-group');
            if (isNone) {
                opt.hidden   = false;
                opt.disabled = false;
            } else if (isSHS) {
                opt.hidden   = group !== 'shs';
                opt.disabled = group !== 'shs';
            } else {
                opt.hidden   = group !== 'college';
                opt.disabled = group !== 'college';
            }
            if (!opt.hidden && !firstVisible) firstVisible = opt;
        });

        // Reset selection if current value is now hidden
        const cur = ylSelect.options[ylSelect.selectedIndex];
        if (cur && cur.value && cur.hidden) {
            ylSelect.value = '';
        }
    }

    function onDeptChange(dept) {
        updateEmailHint(dept);
        updateYearLevel(dept);
    }

    function updateEmailHint(dept) {
        const badge = document.getElementById('emailSuffixBadge');
        if (domainMap[dept]) {
            currentDomain = domainMap[dept];
            badge.textContent = domainMap[dept];
            badge.classList.remove('no-dept');
        } else {
            currentDomain = '';
            badge.textContent = 'select dept first';
            badge.classList.add('no-dept');
        }
        assembleEmail();
    }

    function assembleEmail() {
        const username = document.getElementById('emailUsername').value.trim();
        document.getElementById('email').value = currentDomain ? (username + currentDomain) : username;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const usernameEl = document.getElementById('emailUsername');

        // Block @ and spaces from being typed
        usernameEl.addEventListener('keydown', function (e) {
            if (e.key === '@' || e.key === ' ') e.preventDefault();
        });
        // Strip @ and spaces if pasted
        usernameEl.addEventListener('input', function () {
            const cleaned = this.value.replace(/[@\s]/g, '');
            if (cleaned !== this.value) this.value = cleaned;
            assembleEmail();
        });

        // Restore state on validation error (old() values)
        const dept = document.getElementById('department').value;
        if (dept) onDeptChange(dept);
    });

    // Guard before submit
    document.querySelector('form').addEventListener('submit', function (e) {
        assembleEmail();
        if (!currentDomain) {
            e.preventDefault();
            document.getElementById('department').focus();
            alert('Please select your department so your email domain can be assigned.');
        }
    });

    // -- Show / hide password -----------------------------------------
    function togglePw(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
            icon.closest('.pw-toggle').style.color = 'var(--primary-maroon)';
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
            icon.closest('.pw-toggle').style.color = '';
        }
    }
</script>
</body>
</html>
