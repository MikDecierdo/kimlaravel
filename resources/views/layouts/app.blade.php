<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SPC Voting System')</title>
    <link rel="icon" href="{{ asset('images/spc-logo.png') }}" type="image/png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --primary: #800020;
            --primary-dark: #5c0015;
            --secondary: #FDB927;
            --bg-body: #f8f9fa;
            --bg-sidebar: #ffffff;
            --text-main: #2b2d42;
            --text-muted: #6B7280;
            --border: #e9ecef;
            --success: #6B7280;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Prevent layout shift when SweetAlert appears */
        body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) {
            padding-right: 0 !important;
        }

        .sidebar {
            width: 260px;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
            transition: var(--transition);
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 100;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-links {
            list-style: none;
            flex: 1;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            text-decoration: none;
            color: var(--text-muted);
            border-radius: 8px;
            transition: var(--transition);
            font-weight: 500;
            cursor: pointer;
        }

        .nav-link:hover, .nav-link.active {
            background-color: #eff3ff;
            color: var(--primary);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .main-content {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
            position: relative;
            margin-left: 260px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header-title h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        .header-title p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .bg-blue { background: #e0e7ff; color: var(--primary); }
        .bg-pink { background: #ffe5ec; color: var(--secondary); }
        .bg-green { background: #e6fffa; color: var(--success); }
        .bg-orange { background: #fff4e6; color: #f59e0b; }

        .toast-container {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 2000;
        }

        .toast {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideInRight 0.3s ease;
            border-left: 4px solid var(--success);
        }

        .toast.error {
            border-left-color: #dc3545;
        }

        .swal2-container {
            z-index: 20050 !important;
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            body { flex-direction: column; height: auto; overflow: auto; }
            .sidebar { 
                width: 100%; 
                flex-direction: row; 
                padding: 1rem; 
                overflow-x: auto; 
                align-items: center; 
                border-right: none; 
                border-bottom: 1px solid var(--border); 
            }
            .logo span { display: none; }
            .user-profile { display: none; }
            .nav-links { display: flex; gap: 10px; }
            .nav-item { margin: 0; }
            .nav-link { white-space: nowrap; padding: 8px 12px; }
            .main-content { padding: 1rem; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')
    
    <div class="toast-container" id="toastContainer"></div>

    <script>
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark';
            const color = type === 'success' ? 'var(--success)' : '#dc3545';
            
            toast.innerHTML = `<i class="fa-solid ${icon}" style="color:${color}"></i> ${message}`;
            
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // CSRF Token for AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    </script>

    <!-- ── Global SweetAlert Design System ───────────────────────────── -->
    <style>
        .swal-app-popup   { border-radius:18px!important; padding:1.75rem 1.5rem!important; box-shadow:0 20px 60px rgba(0,0,0,.16)!important; }
        .swal-app-actions { gap:.75rem!important; margin-top:1.25rem!important; }
        .swal-btn-solid   { background:#800020!important; color:#fff!important; border:2px solid #800020!important; border-radius:8px!important; padding:.6rem 1.75rem!important; font-weight:700!important; font-size:.9rem!important; cursor:pointer; transition:background .15s,border-color .15s; }
        .swal-btn-solid:hover   { background:#6d0018!important; border-color:#6d0018!important; }
        .swal-btn-outline { background:#fff!important; color:#800020!important; border:2px solid #800020!important; border-radius:8px!important; padding:.6rem 1.75rem!important; font-weight:700!important; font-size:.9rem!important; cursor:pointer; transition:background .15s; }
        .swal-btn-outline:hover { background:#fff5f7!important; }
        .swal-btn-neutral { background:#f3f4f6!important; color:#374151!important; border:2px solid #e5e7eb!important; border-radius:8px!important; padding:.6rem 1.75rem!important; font-weight:700!important; font-size:.9rem!important; cursor:pointer; }
        .swal-btn-neutral:hover { background:#e5e7eb!important; }
    </style>
    <script>
    // ── Shared SweetAlert icon builder ───────────────────────────────────
    function _swalIcon(faIcon) {
        return '<div style="width:66px;height:66px;background:#800020;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">'
             + '<i class="fa-solid ' + faIcon + '" style="font-size:1.6rem;color:#fff;"></i></div>';
    }
    // Success
    function _swalOK(title, msg, cb) {
        return Swal.fire({
            html: '<div style="text-align:center;padding:.25rem 0">' + _swalIcon('fa-check')
                + '<h2 style="font-size:1.2rem;font-weight:800;color:#1f2937;margin-bottom:.4rem;">' + title + '</h2>'
                + '<p style="color:#6b7280;font-size:.9rem;">' + (msg||'') + '</p></div>',
            confirmButtonText: 'OK',
            buttonsStyling: false,
            customClass: { confirmButton:'swal-btn-solid', popup:'swal-app-popup' }
        }).then(function(r){ if(typeof cb==='function') cb(r); return r; });
    }
    // Error
    function _swalErr(msg, title) {
        return Swal.fire({
            html: '<div style="text-align:center;padding:.25rem 0">' + _swalIcon('fa-xmark')
                + '<h2 style="font-size:1.2rem;font-weight:800;color:#800020;margin-bottom:.4rem;">' + (title||'Error') + '</h2>'
                + '<p style="color:#6b7280;font-size:.9rem;">' + (msg||'Something went wrong.') + '</p></div>',
            confirmButtonText: 'OK',
            buttonsStyling: false,
            customClass: { confirmButton:'swal-btn-solid', popup:'swal-app-popup' }
        });
    }
    // Warning / info
    function _swalWarn(title, msg, cb) {
        return Swal.fire({
            html: '<div style="text-align:center;padding:.25rem 0">' + _swalIcon('fa-exclamation')
                + '<h2 style="font-size:1.2rem;font-weight:800;color:#1f2937;margin-bottom:.4rem;">' + title + '</h2>'
                + '<p style="color:#6b7280;font-size:.9rem;">' + (msg||'') + '</p></div>',
            confirmButtonText: 'OK',
            buttonsStyling: false,
            customClass: { confirmButton:'swal-btn-solid', popup:'swal-app-popup' }
        }).then(function(r){ if(typeof cb==='function') cb(r); return r; });
    }
    // Confirm (solid maroon confirm + neutral cancel)
    function _swalConfirm(title, msg, confirmText, cb) {
        return Swal.fire({
            html: '<div style="text-align:center;padding:.25rem 0">' + _swalIcon('fa-circle-question')
                + '<h2 style="font-size:1.2rem;font-weight:800;color:#1f2937;margin-bottom:.4rem;">' + title + '</h2>'
                + '<p style="color:#6b7280;font-size:.9rem;">' + (msg||'') + '</p></div>',
            showCancelButton: true,
            confirmButtonText: confirmText || 'Confirm',
            cancelButtonText: 'Cancel',
            focusCancel: true,
            buttonsStyling: false,
            customClass: { confirmButton:'swal-btn-solid', cancelButton:'swal-btn-neutral', popup:'swal-app-popup', actions:'swal-app-actions' }
        }).then(function(r){ if(r.isConfirmed && typeof cb==='function') cb(); return r; });
    }
    // Delete (outline maroon "Yes, Delete" + solid maroon Cancel)
    function _swalDelete(itemName, cb) {
        return Swal.fire({
            html: '<div style="text-align:center;padding:.25rem 0">'
                + '<div style="width:66px;height:66px;background:#800020;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">'
                + '<i class="fa-solid fa-trash" style="font-size:1.4rem;color:#fff;"></i></div>'
                + '<h2 style="font-size:1.2rem;font-weight:800;color:#1f2937;margin-bottom:.4rem;">Delete?</h2>'
                + (itemName ? '<p style="color:#800020;font-weight:700;font-size:.95rem;word-break:break-word;">&ldquo;' + itemName + '&rdquo;</p>' : '')
                + '<p style="color:#9ca3af;font-size:.82rem;margin-top:.4rem;">This action cannot be undone.</p></div>',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-trash" style="margin-right:5px;"></i>Yes, Delete',
            cancelButtonText: 'Cancel',
            focusCancel: true,
            buttonsStyling: false,
            customClass: { confirmButton:'swal-btn-outline', cancelButton:'swal-btn-solid', popup:'swal-app-popup', actions:'swal-app-actions' }
        }).then(function(r){ if(r.isConfirmed && typeof cb==='function') cb(); return r; });
    }
    // Toast (bottom-right auto-dismiss)
    function _swalToast(type, msg) {
        var icon = type === 'success' ? 'fa-check' : (type === 'error' ? 'fa-xmark' : 'fa-exclamation');
        Swal.fire({
            html: '<div style="display:flex;align-items:center;gap:.75rem;padding:.1rem 0;">'
                + '<div style="width:36px;height:36px;min-width:36px;background:#800020;border-radius:50%;display:flex;align-items:center;justify-content:center;">'
                + '<i class="fa-solid ' + icon + '" style="font-size:.9rem;color:#fff;"></i></div>'
                + '<span style="color:#1f2937;font-size:.9rem;font-weight:600;">' + msg + '</span></div>',
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            buttonsStyling: false,
            customClass: { popup: 'swal-app-popup' }
        });
    }
    </script>

    @stack('scripts')
</body>
</html>
