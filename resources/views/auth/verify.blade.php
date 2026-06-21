@extends('layouts.app')

@section('title', 'Verify Email - SPC Voting System')

@section('content')
<div class="verify-container">
    <div class="verify-card">
        <div class="verify-icon">
            <i class="fa-solid fa-envelope-circle-check"></i>
        </div>
        <h2>Verify Your Email Address</h2>

        @if (session('resent'))
            <div class="alert alert-success">
                <i class="fa-solid fa-check-circle"></i>
                A fresh verification link has been sent to your email address.
            </div>
        @endif

        <p>Before proceeding, please check your email for a verification link.</p>
        <p>If you did not receive the email, click the button below to request another.</p>

        <form method="POST" action="{{ route('verification.resend') }}">
            @csrf
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-paper-plane"></i> Resend Verification Link
            </button>
        </form>
    </div>
</div>

<style>
    .verify-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
    }
    .verify-card {
        background: white;
        padding: 3rem;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        text-align: center;
        max-width: 480px;
        width: 100%;
    }
    .verify-icon {
        font-size: 3rem;
        color: var(--primary);
        margin-bottom: 1rem;
    }
    .verify-card h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 1rem;
    }
    .verify-card p {
        color: var(--text-muted);
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
    }
    .verify-card .btn-primary {
        margin-top: 1rem;
    }
    .alert-success {
        background: #e6fffa;
        color: #065f46;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>
@endsection
