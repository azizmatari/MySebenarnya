{{-- File: resources/views/reports/McmcReports.blade.php --}}

<!-- Sidebar + Top Bar -->
@include('layouts.sidebarMcmc')

{{-- Top Bar --}}

{{-- Page-specific CSS for report cards --}}
<style>
    .main-content {
        margin-left: 250px;    /* sidebar width */
        padding-top: 70px;     /* top bar height */
        background-color: #f8f9fa;
        min-height: 100vh;
    }
    .reports-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }
    .report-card {
        flex: 1 1 calc(25% - 20px);
        max-width: calc(25% - 20px);
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 20px;
        text-align: center;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .report-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 14px rgba(0,0,0,0.15);
    }
    .report-card i.material-icons {
        font-size: 36px;
        color: #007bff;
        margin-bottom: 12px;
    }
    .report-card h5 {
        font-size: 1rem;
        color: #212529;
        margin-bottom: 8px;
        font-weight: 600;
    }
    .report-card p {
        font-size: 0.875rem;
        color: #6c757d;
        flex-grow: 1;
        margin-bottom: 12px;
    }
    .report-card .btn,
    .report-card button {
        margin-top: auto;
        font-size: 0.875rem;
        padding: 8px 16px;
    }
</style>

{{-- Main content wrapper --}}
<div class="main-content">
    <div class="reports-container">

        {{-- 1️⃣ System Users Report --}}
        <div class="report-card">
            <i class="material-icons">people</i>
            <h5>System Users Report</h5>
            <p>View & export users data</p>
            <a href="{{ route('user.reports.index') }}" class="btn btn-primary">Go</a>

        </div>

        {{-- 2️⃣ Inquiry Report --}}
        <div class="report-card">
            <i class="material-icons">description</i>
            <h5>Inquiry Report</h5>
            <p>View & export inquiry status</p>
            <a href="#" class="btn btn-primary">Go</a>
        </div>

        {{-- 3️⃣ Agency Report (Coming Soon) --}}
        <div class="report-card">
            <i class="material-icons">business</i>
            <h5>Agency Report</h5>
            <p><em>Coming Soon</em></p>
            <button class="btn btn-outline-secondary" disabled>Go</button>
        </div>

        {{-- 4️⃣ Custom Report (Coming Soon) --}}
        <div class="report-card">
            <i class="material-icons">insert_chart</i>
            <h5>Custom Report</h5>
            <p><em>Coming Soon</em></p>
            <button class="btn btn-outline-secondary" disabled>Go</button>
        </div>

    </div>
</div>
