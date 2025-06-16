<!-- filepath: resources/views/module1/RegisterAgencyView.blade.php -->
@include('layouts.sidebarMcmc')
<!-- Add this style block at the top of your RegisterAgencyView.blade.php -->
<style>
.main-content {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f7fa;
}
.container {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.10);
    padding: 20px 18px 18px 18px; /* slightly less vertical padding */
    width: 100%;
    max-width: 340px; /* reduced width */
}
h2 {
    text-align: center;
    margin-bottom: 15px;
    color: #2d3a4b;
    font-size: 1.4rem;
    font-weight: 700;
    letter-spacing: 1px;
}

form label {
    display: block;
    margin-bottom: 4px;
    color: #2d3a4b;
    font-weight: 500;
    font-size: 0.97rem;
}
form input[type="text"],
form input[type="password"],
form input[type="number"] {
    width: 100%;
    padding: 7px 10px;
    margin-bottom: 10px; /* less margin between fields */
    border: 1px solid #d1d9e6;
    border-radius: 6px;
    font-size: 0.97rem;
    background: #f9fafb;
    transition: border 0.2s;
}
form input:focus {
    border-color: #4f8cff;
    outline: none;
}
form button[type="submit"] {
    width: 100%;
    padding: 9px 0;
    background: #4f8cff;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 4px;
}
form button[type="submit"]:hover {
    background: #2563eb;
}
.success-message {
    color: #22bb33;
    text-align: center;
    margin-bottom: 10px;
    font-weight: 600;
}
.error-message {
    color: #e53e3e;
    text-align: center;
    margin-bottom: 10px;
    font-weight: 600;
}
@media (max-width: 600px) {
    .container {
        padding: 10px 2px;
    }
}
</style>

<div class="main-content">
    <div class="container" style="max-width: 500px; margin: 40px auto;">
        <h2>Register Agency</h2>
        @if(session('success'))
            <div style="color: green;">{{ session('success') }}</div>
        @endif
        <form method="POST" action="{{ route('register.agency.submit') }}">
            @csrf
            <div>
                <label>Agency Name</label>
                <input type="text" name="agency_name" value="{{ old('agency_name') }}" required maxlength="50">
            </div>
            <div>
                <label>Username</label>
                <input type="text" name="agencyUsername" value="{{ old('agencyUsername') }}" required maxlength="20">
            </div>
            <div>
                <label>Password</label>
                <input type="password" name="agencyPassword" required>
            </div>            <div>
                <label>Confirm Password</label>
                <input type="password" name="agencyPassword_confirmation" required>
            </div>            <div>
                <label>Agency Type</label>
                <select name="agencyType" required style="width: 100%; padding: 7px 10px; margin-bottom: 10px; border: 1px solid #d1d9e6; border-radius: 6px; font-size: 0.97rem; background: #f9fafb; transition: border 0.2s;">
                    <option value="" disabled selected>-- Select Agency Type --</option>
                    @foreach($agencyTypes as $type)
                        <option value="{{ $type }}" {{ old('agencyType') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
        
            <button type="submit">Register Agency</button>
        </form>
        @if($errors->any())
            <div style="color: red;">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif    </div>    
    {{-- 
    COMMENTED OUT - Agency Type Manager Section
    For future use with dynamic agency types
    
    HTML REMOVED - When re-enabling dynamic agency types, restore this section with proper route definitions:
    - A form to add new agency types (action to "add.agency.type" route)
    - Display of current agency types
    - Reset types button (action to "reset.agency.types" route)
    
    The form and buttons would look like:
    <div class="container" style="max-width: 500px; margin: 40px auto;">
        <h2>Manage Agency Types</h2>
        <!-- Add type form -->
        <!-- Current types display -->
        <!-- Reset types button -->
    </div>
    --}}
</div>
