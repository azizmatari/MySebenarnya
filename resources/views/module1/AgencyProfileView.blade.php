{{-- filepath: resources/views/module1/AgencyProfileView.blade.php --}}
{{-- For agency --}}
@include('layouts.sidebarAgency')
<style>
    .main-content {
        margin-left: 220px; /* width of sidebar */
        padding: 40px 20px;
    }
    .card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        padding: 32px;
        max-width: 700px;
        margin: 40px auto;
    }
    h2 {
        color: #333;
        margin-bottom: 24px;
    }
    label {
        display: block;
        margin-top: 18px;
        margin-bottom: 6px;
        font-weight: 500;
    }
    input[type="text"], input[type="password"], input[type="file"] {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin-bottom: 10px;
    }
    button[type="submit"] {
        background: #7367f0;
        color: #fff;
        border: none;
        padding: 10px 24px;
        border-radius: 4px;
        font-size: 16px;
        margin-top: 18px;
        cursor: pointer;
        transition: background 0.2s;
    }
    button[type="submit"]:hover {
        background: #5b50c7;
    }
    .success-message {
        color: #28a745;
        margin-top: 16px;
    }
    .error-message {
        color: #dc3545;
        margin-top: 16px;
    }
</style>
<div class="main-content">
    <div class="card" style="max-width: 500px; margin: 40px auto;">
        <h2>Edit Agency Profile</h2>
        <form method="POST" action="{{ route('agency.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')

            <label for="agency_name">Agency Name</label>
            <input type="text" id="agency_name" name="agency_name" value="{{ old('agency_name', $agency->agency_name) }}" required>

            <label for="agencyContact">Contact Details</label>
            <input type="text" id="agencyContact" name="agencyContact" value="{{ old('agencyContact', $agency->agencyContact) }}">

            <label for="profile_picture">Profile Picture</label>
            <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
            @if($agency->profile_picture)
                <div style="margin: 10px 0;">
                    <img src="{{ asset('storage/' . $agency->profile_picture) }}" alt="Profile Picture" style="max-width: 100px;">
                </div>
            @endif

            <hr style="margin: 24px 0;">

            <h4>Change Password</h4>
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password">

            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password">

            <label for="new_password_confirmation">Confirm New Password</label>
            <input type="password" id="new_password_confirmation" name="new_password_confirmation">

            <button type="submit" style="margin-top: 18px;">Update Profile</button>
        </form>

        @if(session('success'))
            <div class="success-message">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="error-message">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>

