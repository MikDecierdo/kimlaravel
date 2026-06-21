@extends('layouts.department-head')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/shared.css') }}">
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/profile.css') }}">
@endpush

@section('dept-head-content')

<header>
    <div class="header-title">
        <h1>Edit Profile</h1>
        <p>{{ auth()->user()->department }} Department</p>
    </div>
</header>

<div class="profile-page">

    @if(session('success'))
        <div class="alert-success-bar">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert-error-bar">
            <strong><i class="fa-solid fa-triangle-exclamation"></i> Please fix the following:</strong>
            <ul style="margin: 0.4rem 0 0 1.25rem; padding: 0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($pending ?? false)
        {{-- Verification Code Form --}}
        <div class="form-card" style="border: 2px solid #2b6cb0;">
            <div class="form-card-title">
                <div class="title-icon"><i class="fa-solid fa-shield-halved"></i></div>
                Verify Password Change
            </div>
            <p style="margin: 0 0 1rem 0; color: #4a5568;">A 6-digit verification code was sent to your email. Please enter it below.</p>
            <form action="{{ route('department-head.profile.verify-password') }}" method="POST" style="display:flex; gap:0.75rem; align-items:flex-end; flex-wrap:wrap;">
                @csrf
                <div class="form-group" style="flex:1; min-width:200px;">
                    <label>Verification Code</label>
                    <input type="text" name="verification_code" class="form-control" placeholder="000000" maxlength="6" required pattern="[0-9]{6}" inputmode="numeric" autocomplete="off">
                </div>
                <button type="submit" class="btn-save" style="margin-bottom:2px;">
                    <i class="fa-solid fa-check"></i> Verify Code
                </button>
            </form>
            <form action="{{ route('department-head.profile.resend-code') }}" method="POST" style="margin-top:0.75rem;">
                @csrf
                <button type="submit" style="background:none; border:none; color:#2b6cb0; cursor:pointer; font-size:0.875rem; padding:0; text-decoration:underline;">
                    Resend verification code
                </button>
            </form>
        </div>
    @endif

    <form action="{{ route('department-head.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Hidden file input for avatar --}}
        <input type="file" id="avatarInput" name="profile_picture" accept="image/*" style="display:none;">

        <!-- Profile Card (Banner + Avatar) -->
        <div class="profile-card">
            <div class="profile-banner">
                <div class="profile-avatar-wrap">
                    <div class="profile-avatar" id="avatarBtn" onclick="document.getElementById('avatarInput').click()">
                        @if($staff->profile_picture)
                            <img id="avatarPreview" src="{{ $staff->profile_picture }}" alt="{{ $staff->name }}"
                                 style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <span id="avatarInitial">{{ strtoupper(substr($staff->name, 0, 1)) }}</span>
                            <img id="avatarPreview" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:none;">
                        @endif
                        <div class="avatar-overlay"><i class="fa-solid fa-camera"></i></div>
                    </div>
                </div>
            </div>
            <div class="profile-header-info">
                <h2>{{ $staff->name }} {{ $staff->last_name }}</h2>
                <p>{{ $staff->email }}</p>
                <span class="profile-badge">
                    <i class="fa-solid fa-building"></i>
                    {{ $staff->department }} Department Head
                </span>
            </div>
        </div>

        <!-- Personal Info -->
        <div class="form-card">
            <div class="form-card-title">
                <div class="title-icon"><i class="fa-solid fa-user"></i></div>
                Personal Information
            </div>
            <div class="profile-grid">
                <div class="form-group">
                    <label>First Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $staff->name) }}" required>
                </div>
                <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $staff->middle_name) }}">
                </div>
                <div class="form-group">
                    <label>Last Name <span class="required">*</span></label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $staff->last_name) }}" required>
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" class="form-control" value="{{ $staff->department }}" readonly>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $staff->phone_number) }}" placeholder="e.g. 09XXXXXXXXX">
                </div>
                <div class="form-group">
                    <label>Office Location</label>
                    <input type="text" name="office_location" class="form-control" value="{{ old('office_location', $staff->office_location) }}" placeholder="e.g. Room 201, Main Building">
                </div>
                <div class="form-group">
                    <label>Email Address <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $staff->email) }}" required>
                </div>
                <div class="form-group">
                    <label>Employee ID</label>
                    <input type="text" class="form-control" value="{{ $staff->employee_id }}" readonly>
                </div>
            </div>
            <button type="submit" class="btn-save">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
        </div>

        <!-- Change Password -->
        <div class="form-card">
            <div class="form-card-title">
                <div class="title-icon"><i class="fa-solid fa-lock"></i></div>
                Change Password
                <span style="font-size:0.75rem;font-weight:500;color:#9ca3af;margin-left:auto;">Leave blank to keep current password</span>
            </div>
            <div class="profile-grid">
                <div class="form-group full">
                    <label>Current Password</label>
                    <div class="pw-wrapper">
                        <input type="password" name="current_password" id="currentPw" class="form-control" placeholder="Enter current password" autocomplete="current-password">
                        <button type="button" class="pw-toggle" onclick="togglePw('currentPw','iconCurrent')">
                            <i id="iconCurrent" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <div class="pw-wrapper">
                        <input type="password" name="password" id="newPw" class="form-control" placeholder="Min. 8 characters" autocomplete="new-password">
                        <button type="button" class="pw-toggle" onclick="togglePw('newPw','iconNew')">
                            <i id="iconNew" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <div class="pw-wrapper">
                        <input type="password" name="password_confirmation" id="confirmPw" class="form-control" placeholder="Repeat new password" autocomplete="new-password">
                        <button type="button" class="pw-toggle" onclick="togglePw('confirmPw','iconConfirm')">
                            <i id="iconConfirm" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div id="pwMatchMsg" class="pw-match" style="display:none;"></div>
                </div>
            </div>
            <button type="submit" class="btn-save">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script src="{{ asset('assets/dept-head/js/profile.js') }}" defer></script>
@endpush

@endsection
