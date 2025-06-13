@include('layouts.header')

<style>
.forgot-container {
    max-width: 400px;
    margin: 60px auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    padding: 32px 28px;
    font-family: 'Segoe UI', Arial, sans-serif;
}
.forgot-container h2 {
    text-align: center;
    margin-bottom: 24px;
    color: #2d3a4b;
}
.forgot-container label {
    display: block;
    margin-bottom: 6px;
    color: #2d3a4b;
    font-weight: 500;
}
.forgot-container input[type="email"],
.forgot-container input[type="text"] {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 18px;
    border: 1px solid #d1d9e6;
    border-radius: 6px;
    font-size: 16px;
    background: #f9fafb;
    transition: border 0.2s;
}
.forgot-container input:focus {
    border-color: #4f8cff;
    outline: none;
}
.forgot-container button {
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
.forgot-container button:hover {
    background: #2563eb;
}
.back-link {
    text-align: center;
    margin-top: 16px;
}
.back-link a {
    color: #4f8cff;
    text-decoration: none;
}
.back-link a:hover {
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
</style>

<div class="forgot-container">
    <h2>Forgot Password</h2>
    <form method="POST" action="#">
        @csrf
        <label for="role">I am a:</label>
        <select id="role" name="role" class="role-select" onchange="toggleInput()">
            <option value="public">Public User</option>
            <option value="mcmc">MCMC Staff</option>
            <option value="agency">Agency</option>
        </select>

        <div id="emailInput">
            <label>Enter your email address</label>
            <input type="email" name="userEmail">
        </div>
        <div id="usernameInput" style="display:none;">
            <label>Enter your username</label>
            <input type="text" name="username">
        </div>

        <button type="submit">Send Reset Link</button>
        <div class="back-link">
            <a href="{{ route('login') }}">Back to Login</a>
        </div>
    </form>
</div>

<script>
function toggleInput() {
    var role = document.getElementById('role').value;
    if (role === 'public') {
        document.getElementById('emailInput').style.display = '';
        document.getElementById('usernameInput').style.display = 'none';
        document.querySelector('input[name="userEmail"]').required = true;
        document.querySelector('input[name="username"]').required = false;
    } else {
        document.getElementById('emailInput').style.display = 'none';
        document.getElementById('usernameInput').style.display = '';
        document.querySelector('input[name="userEmail"]').required = false;
        document.querySelector('input[name="username"]').required = true;
    }
}
document.addEventListener('DOMContentLoaded', toggleInput);
</script>