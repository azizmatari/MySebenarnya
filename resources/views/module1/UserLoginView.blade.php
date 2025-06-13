@include('layouts.header')

<style>
.login-container {
    max-width: 400px;
    margin: 60px auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    padding: 32px 28px;
    font-family: 'Segoe UI', Arial, sans-serif;
}
.login-container h2 {
    text-align: center;
    margin-bottom: 24px;
    color: #2d3a4b;
}
.login-container label {
    display: block;
    margin-bottom: 6px;
    color: #2d3a4b;
    font-weight: 500;
}
.login-container input[type="email"],
.login-container input[type="text"],
.login-container input[type="password"] {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 18px;
    border: 1px solid #d1d9e6;
    border-radius: 6px;
    font-size: 16px;
    background: #f9fafb;
    transition: border 0.2s;
}
.login-container input:focus {
    border-color: #4f8cff;
    outline: none;
}
.login-container button {
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
.login-container button:hover {
    background: #2563eb;
}
.form-footer {
    margin-top: 18px;
    text-align: center;
    font-size: 15px;
}
.form-footer a {
    color: #4f8cff;
    text-decoration: none;
    margin-left: 8px;
}
.form-footer a:hover {
    text-decoration: underline;
}
.role-select {
    width: 100%;
    margin-bottom: 18px;
    padding: 8px 10px;
    border-radius: 6px;
    border: 1px solid #d1d9e6;
    font-size: 16px;
    background: #f9fafb;
}
.error-message {
    color: #e53e3e;
    text-align: center;
    margin-top: 12px;
    font-weight: 500;
}
</style>

<div class="login-container">
    <h2>Login</h2>
    <form method="POST" action="{{ route('login.submit') }}">
    @csrf
    <label for="role">I am a:</label>
    <select id="role" name="role" class="role-select" onchange="toggleLoginInput()">
        <option value="public">Public User</option>
        <option value="mcmc">MCMC Staff</option>
        <option value="agency">Agency</option>
    </select>

    <div id="emailLoginInput">
        <label>Email</label>
        <input type="email" id="loginEmail" name="userEmail">
    </div>
    <div id="usernameLoginInput" style="display:none;">
        <label>Username</label>
        <input type="text" id="loginUsername" name="userUsername">
    </div>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Login</button>
    <div class="form-footer">
        <p>Don’t have an account? <a href="{{ route('register.view') }}">Register</a></p>
        <a href="{{ route('forgot.password') }}">Forgot your password?</a>
    </div>
</form>

    @if(session('error'))
        <div class="error-message">{{ session('error') }}</div>
    @endif
</div>

<script>
function toggleLoginInput() {
    var role = document.getElementById('role').value;
    if (role === 'public') {
        document.getElementById('emailLoginInput').style.display = '';
        document.getElementById('usernameLoginInput').style.display = 'none';
        document.querySelector('#emailLoginInput input').required = true;
        document.querySelector('#usernameLoginInput input').required = false;
    } else {
        document.getElementById('emailLoginInput').style.display = 'none';
        document.getElementById('usernameLoginInput').style.display = '';
        document.querySelector('#emailLoginInput input').required = false;
        document.querySelector('#usernameLoginInput input').required = true;
    }
}
document.addEventListener('DOMContentLoaded', toggleLoginInput);
</script>