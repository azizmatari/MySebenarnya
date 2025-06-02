<div class="header">
    {{-- Logo Section --}}
    <div class="logo-section">
        <img src="{{ asset('images/mcmc-logo.png') }}" alt="MCMC Logo">
        <h2>
            <span class="logo-bold">MYSE</span><span class="logo-red">BENARNYA</span>
        </h2>
    </div>

    {{-- User Info + Logout --}}
    @if(session()->has('user_id'))
        <div class="user-info-wrapper">
            <div class="user-info-text">
                <p class="welcome-text">Welcome, {{ session('username') }}</p>
                <p class="role-label">Role: {{ ucfirst(session('role')) }}</p>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="btn-logout">Logout</button>
            </form>
        </div>
    @endif
</div>
