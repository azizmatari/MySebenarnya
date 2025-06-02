<div class="sidebar" style="width: 220px; background-color: #2c3e50; color: #ecf0f1; height: 100vh; padding-top: 20px; position: fixed;">
    <ul style="list-style: none; padding: 0; margin: 0;">
        <li style="padding: 12px 20px;">
            <a href="{{ route('mcmc.dashboard') }}">Dashboard</a>
        </li>
        <li style="padding: 12px 20px;">
            <a href="{{ route('mcmc.profile') }}" >Profile</a>
        </li>
        <li style="padding: 12px 20px;">
            <a href="{{ route('mcmc.inquiries') }}" >Manage Inquiries</a>
        </li>
        <li style="padding: 12px 20px;">
            <a href="{{ route('mcmc.checkInquiries') }}">Check Inquiry</a>
        </li>
        <li style="padding: 12px 20px;">
            <a href="{{ route('mcmc.registerAgency') }}" >Register Agency</a>
        </li>
        <li style="padding: 12px 20px;">
            <a href="{{ route('mcmc.reports') }}" >Reports</a>
        </li>
        <li style="padding: 12px 20px;">
            <a href="{{ route('logout') }}" >Logout</a>
        </li>
    </ul>
</div>
