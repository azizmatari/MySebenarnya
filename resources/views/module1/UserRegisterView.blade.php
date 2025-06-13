@include('layouts.header')

<style>
.register-container {
    max-width: 400px;
    margin: 60px auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    padding: 32px 28px;
    font-family: 'Segoe UI', Arial, sans-serif;
}
.register-container h2 {
    text-align: center;
    margin-bottom: 24px;
    color: #2d3a4b;
}
.register-container label {
    display: block;
    margin-bottom: 6px;
    color: #2d3a4b;
    font-weight: 500;
}
.register-container input[type="text"],
.register-container input[type="email"],
.register-container input[type="password"] {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 18px;
    border: 1px solid #d1d9e6;
    border-radius: 6px;
    font-size: 16px;
    background: #f9fafb;
    transition: border 0.2s;
}
.register-container input:focus {
    border-color: #4f8cff;
    outline: none;
}
.register-container button {
    width: 100%;
    padding: 10px 0;
    background: #4f8cff;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 17px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}
.register-container button:hover {
    background: #2563eb;
}
.login-link {
    text-align: center;
    margin-top: 16px;
}
.login-link a {
    color: #4f8cff;
    text-decoration: none;
}
.login-link a:hover {
    text-decoration: underline;
}
.error-list {
    color: #e53e3e;
    margin-top: 10px;
    font-size: 15px;
}
</style>

<div class="register-container">
    <h2>Register</h2>
    <form method="POST" action="{{ route('register.submit') }}">
        @csrf

        <label>Name</label>
        <input type="text" name="userName" required>

        <label>Email</label>
        <input type="email" name="userEmail" required>

        <label>Password</label>
        <input type="password" name="userPassword" required>

        <label>Confirm Password</label>
        <input type="password" name="userPassword_confirmation" required>

        <button type="submit">Register</button>

        <p class="login-link">Already have an account? <a href="{{ route('login') }}">Login</a></p>
    </form>

    @if ($errors->any())
        <ul class="error-list">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif
</div>