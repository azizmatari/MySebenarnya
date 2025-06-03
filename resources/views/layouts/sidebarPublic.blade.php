<div class="sidebar">
    <nav class="sidebar-nav">
        <a href="{{ route('user.dashboard') }}">Dashboard</a>
        <a href="#" class="disabled" onclick="return false;">Submit Inquiry (Coming Soon)</a>
        <a href="#" class="disabled" onclick="return false;">My Inquiries (Coming Soon)</a>
        <form action="{{ route('logout') }}" method="POST" style="margin-top: 10px;">
            @csrf
            <button type="submit" class="btn btn-danger" style="width: 100%;">Logout</button>
        </form>
    </nav>
</div>
