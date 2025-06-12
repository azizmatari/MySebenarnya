<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
    }

    /* Sidebar styles - matching MyPetakom design */
    .sidebar {
        width: 250px;
        background-color: #2c2c54;
        color: #a2a3b7;
        height: 100vh;
        overflow-y: auto;
        transition: all 0.3s ease;
        position: fixed;
        left: 0;
        top: 0;
        z-index: 1000;
    }

    .sidebar-header {
        padding: 20px;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-header h1 {
        color: #fff;
        font-size: 22px;
        font-weight: 600;
        margin: 0;
    }

    .sidebar-nav {
        padding: 0;
        margin-top: 20px;
    }

    .nav-item {
        padding: 15px 20px;
        display: flex;
        align-items: center;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
        color: #a2a3b7;
    }

    .nav-item:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    .nav-item.active {
        background-color: rgba(255, 255, 255, 0.1);
        color: #fff;
        border-right: 3px solid #007bff;
    }

    .nav-item i {
        margin-right: 15px;
        font-size: 18px;
        width: 24px;
        text-align: center;
    }

    .nav-item a {
        text-decoration: none;
        color: inherit;
        font-size: 14px;
        font-weight: 500;
        flex: 1;
    }

    .nav-divider {
        padding: 15px 20px 10px;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 600;
        color: #6c757d;
        letter-spacing: 1px;
    }

    /* Main content area */
    .main-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        margin-left: 250px;
        padding-top: 70px;
    }

    /* Top bar styles */
    .top-bar {
        position: fixed;
        top: 0;
        left: 250px;
        right: 0;
        height: 70px;
        background-color: #fff;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 0 30px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        z-index: 999;
        border-bottom: 1px solid #ebedf2;
    }

    .profile-dropdown {
        position: relative;
        display: flex;
        align-items: center;
    }

    .user-type {
        margin-right: 15px;
        font-weight: 600;
        font-size: 12px;
        color: #6c757d;
        background-color: #f8f9fa;
        padding: 6px 12px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .profile-btn {
        display: flex;
        align-items: center;
        cursor: pointer;
        border: none;
        background: none;
        padding: 0;
    }

    .profile-img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        cursor: pointer;
        border: 2px solid #e9ecef;
    }

    .dropdown-content {
        position: absolute;
        right: 0;
        top: 50px;
        background-color: white;
        min-width: 200px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        border-radius: 8px;
        z-index: 1001;
        display: none;
        overflow: hidden;
    }

    .dropdown-content a {
        color: #495057;
        padding: 15px 20px;
        text-decoration: none;
        display: flex;
        align-items: center;
        font-size: 14px;
        transition: background-color 0.2s;
    }

    .dropdown-content a i {
        margin-right: 10px;
        font-size: 16px;
    }

    .dropdown-content a:hover {
        background-color: #f8f9fa;
    }

    /* Show dropdown when active class is added */
    .show {
        display: block;
    }

    /* Content area styling */
    .content {
        padding: 30px;
        flex: 1;
    }

    .card {
        background: white;
        border-radius: 8px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
        border: 1px solid #e9ecef;
    }
</style>

<!-- This is now a partial template - only the sidebar content -->
<div class="sidebar">
    <div class="sidebar-header">
        <h1>MySebenarnya</h1>
    </div>
    <div class="sidebar-nav">
        <div class="nav-item active">
            <i class="material-icons">dashboard</i>
            <a href="{{ route('module3.status') }}">Dashboard</a>
        </div>
        <div class="nav-divider">INQUIRY MANAGEMENT</div>
        <!-- Active inquiry tracking -->
        
        <!-- Submit new inquiry -->
        <div class="nav-item">
            <i class="material-icons">add_circle</i>
            <a href="{{ route('module2.inquiry.User.UserCreateInquiry') }}">Submit New Inquiry</a>
        </div>
        <!-- My inquiries -->
        <!-- <div class="nav-item">
            <i class="material-icons">list_alt</i>
            <a href="{{ route('module2.inquiry.my-inquiries') }}">My Inquiries</a>
        </div> -->
        <!-- Inquiry guidelines -->
        <div class="nav-item">
            <i class="material-icons">help</i>
            <a href="#" style="color: #6c757d;">Guidelines</a>
        </div>
    </div>
</div>

<!-- Top Bar -->
<div class="top-bar">
    <div class="profile-dropdown">
        <div class="user-type">PUBLIC USER</div>
        <button class="profile-btn" onclick="toggleDropdown(event)">
            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=PublicUser" alt="Profile" class="profile-img" />
        </button>
        <div class="dropdown-content" id="profileDropdown">
            <a href="{{ route('user.profile') }}">
                <i class="material-icons">person</i> My Profile
            </a>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="material-icons">exit_to_app</i> Logout
            </a>
        </div>
    </div>
</div>

<!-- Hidden logout form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<!-- JS -->
<script>
    function toggleDropdown(event) {
        event.stopPropagation();
        document.getElementById("profileDropdown").classList.toggle("show");
    }

    window.onclick = function(event) {
        if (!event.target.matches('.profile-btn') && !event.target.matches('.profile-img')) {
            const dropdown = document.getElementById("profileDropdown");
            if (dropdown.classList.contains("show")) {
                dropdown.classList.remove("show");
            }
        }
    };
</script>