<style>
body {
    margin: 0;
    font-family: Inter, sans-serif;
    background: #f8fafc;
    color: #1e293b;
}

.auth-page {
    min-height: 100vh;
    display: flex;
}

.left-side {
    width: 50%;
    background: #2563eb;
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 80px;
}

.left-side h1 {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 20px;
}

.left-side p {
    font-size: 18px;
    line-height: 30px;
    opacity: .9;
    max-width: 420px;
}

.right-side {
    width: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #fff;
}

.form-wrapper {
    width: 420px;
}

.logo {
    font-size: 28px;
    font-weight: 700;
    color: #2563eb;
    margin-bottom: 40px;
}

.title {
    font-size: 34px;
    font-weight: 700;
    margin-bottom: 10px;
}

.subtitle {
    color: #64748b;
    margin-bottom: 35px;
}

.email {
    background: #eff6ff;
    color: #2563eb;
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 30px;
    font-weight: 600;
}

.form-group {
    margin-bottom: 22px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
}

.form-control {
    width: 100%;
    height: 50px;
    border: 1px solid #dbe2ea;
    border-radius: 10px;
    padding: 0 16px;
    font-size: 15px;
    transition: .3s;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
}

.btn-submit {
    width: 100%;
    height: 50px;
    background: #2563eb;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: .3s;
}

.btn-submit:hover {
    background: #1d4ed8;
}

.footer {
    margin-top: 30px;
    text-align: center;
    color: #94a3b8;
    font-size: 14px;
}

@media(max-width:992px) {

    .left-side {
        display: none;
    }

    .right-side {
        width: 100%;
    }

    .form-wrapper {
        width: 90%;
        max-width: 420px;
    }

}
</style>

@extends('layouts.app')

@section('content')

<div class="auth-wrapper">

    <div class="auth-card">

        <div class="logo">
            SWT
        </div>

        <h2 class="auth-title">
            Set Password
        </h2>

        <p class="auth-subtitle">
            Create your password to activate your account.
        </p>

        <div class="email-box">
            {{ $invitation->email }}
        </div>

        <form action="{{ route('invitation.store', $invitation->token) }}" method="POST">

            @csrf

            <div class="mb-3">

                <label class="form-label">
                    Password
                </label>

                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                    placeholder="Enter your password">

                @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror

            </div>

            <div class="mb-4">

                <label class="form-label">
                    Confirm Password
                </label>

                <input type="password" name="password_confirmation" class="form-control"
                    placeholder="Confirm your password">

            </div>



            <button class="btn btn-primary w-100">

                Activate Account

            </button>

        </form>

        <div class="footer-text">

            © {{ date('Y') }} SyncWithTeam

        </div>

    </div>

</div>
@endsection