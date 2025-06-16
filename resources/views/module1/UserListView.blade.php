{{-- File: resources/views/module1/UserListView.blade.php --}}

@include('layouts.sidebarMcmc')

<style>
  .main-content {
    margin-left: 250px;
    padding-top: 70px;
    background-color: #f4f6f9;
    min-height: 100vh;
  }
  .reports-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
  }
  /* Filter Section Styling */
  .filters {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    background-color: #fff;
    padding: 1.25rem;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    flex-wrap: wrap;
    gap: 15px;
  }
  
  /* User type filter buttons */
  .btn-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }
  
  .btn-custom {
    padding: 10px 16px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 120px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  }
  
  .btn-custom i {
    margin-right: 6px;
    font-size: 14px;
  }
  
  .btn-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }
  
  .btn-outline-primary {
    color: #4e73df;
    border: 1px solid #4e73df;
    background-color: white;
  }
  
  .btn-outline-primary:hover {
    background-color: #f8f9ff;
  }
  
  .btn-group .btn.active {
    background-color: #4e73df;
    color: white;
    border-color: #4e73df;
    box-shadow: 0 4px 8px rgba(78,115,223,0.25);
  }
  
  /* Search form styling */
  .input-group {
    width: 100%;
    max-width: 350px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.08);
    border-radius: 8px;
    overflow: hidden;
  }
  
  .input-group .form-control {
    border: 1px solid #e0e6f0;
    padding: 10px 15px;
    height: 44px;
    font-size: 14px;
    border-right: none;
    transition: all 0.3s ease;
  }
  
  .input-group .form-control:focus {
    box-shadow: none;
    border-color: #4e73df;
  }
  
  .input-group .btn-primary {
    background-color: #4e73df;
    border-color: #4e73df;
    color: white;
    font-weight: 500;
    padding: 10px 16px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    transition: all 0.3s ease;
  }
  
  .input-group .btn-primary:hover {
    background-color: #3a5ccc;
    border-color: #3a5ccc;
    box-shadow: 0 2px 6px rgba(78,115,223,0.25);
  }
  
  @media (max-width: 768px) {
    .filters {
      flex-direction: column;
      align-items: flex-start;
    }
    
    .btn-group, .input-group {
      width: 100%;
      max-width: 100%;
      margin-bottom: 10px;
    }
  }
  .chart-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    margin-bottom: 2rem;
    padding: 1.5rem;
    transition: all 0.3s ease;
  }
  .chart-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transform: translateY(-3px);
  }
  .chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 1rem;
    margin-bottom: 1rem;
  }
  .chart-title {
    font-size: 1.25rem;
    color: #333;
    font-weight: 600;
    margin: 0;
  }
  .export-buttons {
    display: flex;
    gap: 8px;
  }
  .export-btn {
    padding: 8px 16px;
    border: none;
    color: white;
    border-radius: 6px;
    transition: all 0.3s;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    font-weight: 500;
    cursor: pointer;
  }
  .btn-excel {
    background-color: #1F7244;
  }
  .btn-excel:hover {
    background-color: #2a9158;
    transform: translateY(-2px);
  }
  .btn-pdf {
    background-color: #D32F2F;
  }
  .btn-pdf:hover {
    background-color: #e53935;
    transform: translateY(-2px);
  }
  .btn-png {
    background-color: #1976D2;
  }
  .btn-png:hover {
    background-color: #1e88e5;
    transform: translateY(-2px);
  }
  .export-btn i {
    margin-right: 8px;
    font-size: 14px;
  }
  .chart-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 20px;
  }  .chart-box {
    flex: 1 1 calc(50% - 20px);
    min-width: 300px;
    height: 320px;
    background-color: #ffffff;
    border-radius: 8px;
    position: relative;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    z-index: 1;
    overflow: hidden;
  }
  
  .chart-box > div:first-child {
    width: 100%;
    height: 100%;
  }  .chart-loader {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 10;
    border-radius: 8px;
    transition: opacity 0.3s ease-out, visibility 0.3s ease-out;
  }
  
  .chart-loader.hidden {
    opacity: 0;
    visibility: hidden;
  }
  
  .chart-loader .spinner-border {
    color: #4e73df !important;
    width: 3rem;
    height: 3rem;
    margin-bottom: 1rem;
  }
  
  .chart-loader p {
    margin: 0;
    font-size: 14px;
    color: #555;
    font-weight: 500;
  }
  .table-container {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 1.5rem;
    margin-top: 1rem;
    overflow-x: auto;
  }
  .custom-table {
    width: 100%;
    border-collapse: collapse;
  }
  .custom-table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    color: #495057;
    font-weight: 600;
    padding: 12px 15px;
    text-align: left;
  }
  .custom-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
  }
  .custom-table tr:hover {
    background-color: #f6f9ff;
  }
  .user-badge {
    padding: 6px 12px;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 500;
    color: white;
    display: inline-block;
    text-align: center;
    min-width: 100px;
  }
  .badge-public {
    background-color: #ff6b88; /* Pink for Public Users */
  }
  .badge-agency {
    background-color: #36b96b; /* Green for Agency */
  }
  .badge-staff {
    background-color: #4e73df; /* Blue for MCMC Staff */
  }
  .pagination-container {
    margin-top: 20px;
    display: flex;
    justify-content: center;
  }

  /* Global search field and filter styles */
  input[type="text"][placeholder="Search users..."] {
    height: 38px;
    border: 1px solid #dde2e6;
    border-radius: 6px;
    padding: 8px 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    width: 240px;
    font-size: 14px;
    margin-right: 6px;
  }
  
  input[type="text"][placeholder="Search users..."]:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 3px rgba(78,115,223,0.15);
    outline: none;
  }
  
  button[type="submit"] {
    background-color: #4e73df;
    border: none;
    color: white;
    height: 38px;
    padding: 0 15px;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.2s;
  }
  
  button[type="submit"]:hover {
    background-color: #3a5ccc;
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
  }
  
  /* User type filter links */
  a[href*="user-reports"] {
    text-decoration: none;
    padding: 8px 16px;
    margin-right: 8px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 14px;
    color: #555;
    display: inline-flex;
    align-items: center;
    transition: all 0.2s;
  }
  
  a[href*="user-reports"]:hover {
    background-color: #f0f2fa;
    color: #4e73df;
  }
  
  a[href*="user-reports"] i {
    margin-right: 6px;
    font-size: 14px;
  }
  
  a[href*="user-reports"].active {
    background-color: #4e73df;
    color: white;
  }
  
  /* Top-level filter buttons with icons */
  a[href*="All Users"], 
  a[href*="Public Users"], 
  a[href*="Agencies"] {
    padding: 8px 16px;
    margin-right: 8px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    color: #534f77;
    background-color: #f8f9ff;
    border: 1px solid #dbe1fb;
    display: inline-flex;
    align-items: center;
    transition: all 0.2s;
  }
  
  a[href*="All Users"]:hover, 
  a[href*="Public Users"]:hover, 
  a[href*="Agencies"]:hover {
    background-color: #eef1ff;
    border-color: #c1c9f6;
  }
  
  a[href*="All Users"] i, 
  a[href*="Public Users"] i, 
  a[href*="Agencies"] i {
    margin-right: 6px;
    color: #6c63ff;
  }
  
  /* Agency type badge styling */
  .agency-type-badge {
    padding: 4px 10px;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 500;
    color: white;
    display: inline-block;
    text-align: center;
    min-width: 90px;
    background-color: #17a2b8;
  }
  
  /* Type-specific colors */
  .agency-type-badge[data-type="Education"] {
    background-color: #8e44ad; /* Purple for Education */
  }
  
  .agency-type-badge[data-type="Police"] {
    background-color: #2c3e50; /* Dark blue for Police */
  }
  
  .agency-type-badge[data-type="Sports"] {
    background-color: #27ae60; /* Green for Sports */
  }
  
  .agency-type-badge[data-type="Health"] {
    background-color: #e74c3c; /* Red for Health */
  }
    /* Agency button styling */
  .delete-agency-btn {
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
    background-color: #dc3545;
    border-color: #dc3545;
  }
  
  .delete-agency-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    background-color: #c82333;
    border-color: #bd2130;
  }
  
  .delete-agency-btn i {
    margin-right: 4px;
  }

    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
  }
  
</style>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" 
      integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" 
      crossorigin="anonymous" referrerpolicy="no-referrer" />

<div class="main-content">
  <div class="reports-wrapper">
    <!-- Filters Section -->    <div class="filters">
      <div class="btn-group">
        <a href="{{ route('user.reports.index',['type'=>'all','q'=>request('q')]) }}" 
           class="btn btn-outline-primary btn-custom {{ request('type', 'all') === 'all' ? 'active' : '' }}">
          <i class="fas fa-users"></i> All Users
        </a>
        <a href="{{ route('user.reports.index',['type'=>'public','q'=>request('q')]) }}" 
           class="btn btn-outline-primary btn-custom {{ request('type') === 'public' ? 'active' : '' }}">
          <i class="fas fa-user"></i> Public Users
        </a>
        <a href="{{ route('user.reports.index',['type'=>'agency','q'=>request('q')]) }}" 
           class="btn btn-outline-primary btn-custom {{ request('type') === 'agency' ? 'active' : '' }}">
          <i class="fas fa-building"></i> Agencies
        </a>
      </div>
      <form method="GET" action="{{ route('user.reports.index') }}" class="d-flex">
        <input type="hidden" name="type" value="{{ request('type','all') }}">
        <div class="input-group">
          <input type="text" name="q" placeholder="Search users..." value="{{ request('q') }}" class="form-control" />
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Go
          </button>
        </div>
      </form>
    </div>

    <!-- Percentage Chart Card -->
    <div class="chart-card">
      <div class="chart-header">
        <h5 class="chart-title">Users by Percentage</h5>
        <div class="export-buttons">
          <button onclick="exportAsImage('percent-pie', 'png')" class="export-btn btn-png">
            <i class="fas fa-file-image"></i> PNG
          </button>
          <button onclick="exportAsImage('percent-pie', 'pdf')" class="export-btn btn-pdf">
            <i class="fas fa-file-pdf"></i> PDF
          </button>
          <a href="{{ route('user.reports.export.excel', ['mode' => 'percent']) }}" class="export-btn btn-excel">
            <i class="fas fa-file-excel"></i> Excel
          </a>
        </div>
      </div>      <div class="chart-container">
        <div class="chart-box">
          <div id="percent-pie-container"></div>
          <div class="chart-loader" id="percent-pie-loader">
            <div class="spinner-border text-primary"></div>
            <p>Loading chart...</p>
          </div>
        </div>
        <div class="chart-box">
          <div id="percent-bar-container"></div>
          <div class="chart-loader" id="percent-bar-loader">
            <div class="spinner-border text-primary"></div>
            <p>Loading chart...</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Count Chart Card -->
    <div class="chart-card">
      <div class="chart-header">
        <h5 class="chart-title">Users by Count</h5>
        <div class="export-buttons">
          <button onclick="exportAsImage('count-pie', 'png')" class="export-btn btn-png">
            <i class="fas fa-file-image"></i> PNG
          </button>
          <button onclick="exportAsImage('count-pie', 'pdf')" class="export-btn btn-pdf">
            <i class="fas fa-file-pdf"></i> PDF
          </button>
          <a href="{{ route('user.reports.export.excel', ['mode' => 'count']) }}" class="export-btn btn-excel">
            <i class="fas fa-file-excel"></i> Excel
          </a>
        </div>
      </div>      <div class="chart-container">
        <div class="chart-box">
          <div id="count-pie-container"></div>
          <div class="chart-loader" id="count-pie-loader">
            <div class="spinner-border text-primary"></div>
            <p>Loading chart...</p>
          </div>
        </div>
        <div class="chart-box">
          <div id="count-bar-container"></div>
          <div class="chart-loader" id="count-bar-loader">
            <div class="spinner-border text-primary"></div>
            <p>Loading chart...</p>
          </div>
        </div>
      </div>
    </div>    <!-- Users Table -->
    <div class="table-container">
      @if(session('success'))
        <div class="alert alert-success mb-3" style="padding: 0.75rem 1.25rem; margin-bottom: 1rem; border: 1px solid #d4edda; border-radius: 0.25rem; color: #155724; background-color: #d4edda;">
          {{ session('success') }}
        </div>
      @endif
      
      @if(session('error'))
        <div class="alert alert-danger mb-3" style="padding: 0.75rem 1.25rem; margin-bottom: 1rem; border: 1px solid #f8d7da; border-radius: 0.25rem; color: #721c24; background-color: #f8d7da;">
          {{ session('error') }}
        </div>
      @endif
      
      <table class="custom-table"><thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th style="text-align: center;">Role</th>
            @if(request('type') === 'agency')
            <th style="text-align: center;">Type</th>
            <th style="text-align: center;">Actions</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @foreach($users as $user)
            <tr>
              <td>{{ $user->name }}</td>
              <td>{{ $user->email }}</td>
              <td style="text-align: center;">
                <span class="user-badge 
                  {{ $user->role === 'Public User' ? 'badge-public' : 
                    ($user->role === 'Agency' ? 'badge-agency' : 'badge-staff') }}">
                  {{ $user->role }}
                </span>
              </td>              @if(request('type') === 'agency')
              <td style="text-align: center;">
                <span class="agency-type-badge">{{ $user->agencyType }}</span>
              </td>              <td style="text-align: center;">
                <form action="{{ url('/user-reports/agency/' . $user->agencyId . '/delete') }}" method="POST" style="display: inline;">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this agency?');">
                    <i class="fas fa-trash"></i> Delete
                  </button>
                </form>
              </td>
              @endif
            </tr>
          @endforeach
        </tbody>      </table>
      <div class="pagination-container">
        {{ $users->links() }}
      </div>
    </div>
  </div>
</div>

<!-- Delete modals removed - using direct form submission instead -->

<!-- Kept commented out for future reference -->
<!-- 
<div class="modal fade" id="editAgencyModal" tabindex="-1" aria-labelledby="editAgencyModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editAgencyModalLabel">Edit Agency Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editAgencyForm" method="POST">
        @csrf      
        <div class="modal-body">
          <div class="mb-3">
            <label for="agencyName" class="form-label">Agency Name</label>
            <input type="text" class="form-control" id="agencyName" readonly>
          </div>          
          <div class="mb-3">
            <label for="agencyType" class="form-label">Agency Type</label>
            <select class="form-select" id="agencyType" name="agencyType" required>
              @foreach(App\Models\module1\Agency::getAgencyTypes() as $type)
                <option value="{{ $type }}">{{ $type }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
-->



<!-- Add Bootstrap JS for modal functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Add required scripts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" integrity="sha512-qZvrmS2ekKPF2mSznTQsxqPgnpkI4DNTlrdUmTzrDgektczlKNRRhy5X5AAOnx5S09ydFYWWNSfcEqDTTHgtNA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>  // Store charts globally for export functionality
  let charts = {};
    // Fetch chart data from API
  function fetchChartData() {
    // Show loading status
    console.log('Fetching chart data...');
    
    // Fetch data from API - controller now handles fallback data
    fetch('{{ route("user.reports.charts") }}')
      .then(response => {
        if (!response.ok) {
          console.error('API response not OK:', response.status);
          throw new Error('Failed to fetch chart data');
        }
        return response.json();
      })
      .then(data => {
        console.log('Chart data received:', data);
        if (!data || typeof data !== 'object') {
          console.error('Invalid data format received');
          throw new Error('Invalid data format');
        }
        renderCharts(data);
      })      .catch(error => {
        console.error('Error loading chart data:', error);
        // Make sure all loading spinners are removed even on error
        document.querySelectorAll('.chart-loader').forEach(loader => {
          loader.style.display = 'none';
        });
        // Alert the user about the error
        alert('Error loading chart data. Please try refreshing the page.');
        // The controller now handles fallback data, but for client-side failures:
        const fallbackData = {
          publicCount: 1,
          agencyCount: 1,
          staffCount: 1,
          total: 3,
          percentages: {
            publicPercent: 33.3,
            agencyPercent: 33.3,
            staffPercent: 33.3
          }
        };
        renderCharts(fallbackData);
      });
  }
    // Hide all chart loaders
  function hideAllChartLoaders() {
    document.querySelectorAll('.chart-loader').forEach(loader => {
      loader.classList.add('hidden');
      // After animation completes, set display to none
      setTimeout(() => {
        loader.style.display = 'none';
      }, 300);
    });
  }
    // Render all charts
  function renderCharts(data) {
    const { publicCount, agencyCount, staffCount, total, percentages } = data;
    
    // Use pre-calculated percentages from the controller
    const percentData = [
      percentages.publicPercent,
      percentages.agencyPercent,
      percentages.staffPercent
    ];
    
    // Count data
    const countData = [publicCount, agencyCount, staffCount];
    
    // Chart colors and labels
    const colors = ['#ff6b88', '#36b96b', '#4e73df']; // Pink, Green, Blue
    const labels = ['Public Users', 'Agency Users', 'MCMC Staff'];
    
    // Create pie charts
    createPieChart('percent-pie-container', labels, percentData, colors, '%');
    createPieChart('count-pie-container', labels, countData, colors);
    
    // Create bar charts
    createBarChart('percent-bar-container', labels, percentData, colors, '%');
    createBarChart('count-bar-container', labels, countData, colors);
    
    // Ensure all loaders are hidden after charts are rendered
    setTimeout(hideAllChartLoaders, 500);
  }
    // Create a pie chart
  function createPieChart(containerId, labels, data, colors, suffix = '') {
    // Fix the ID of the loading spinner based on the chart type
    let loaderIdSuffix = containerId.replace('-container', '-loader');
    const loadingSpinner = document.getElementById(loaderIdSuffix);
    if (loadingSpinner) {
      loadingSpinner.style.display = 'none';
    }
    
    const options = {
      series: data,
      labels: labels,
      chart: {
        type: 'pie',
        height: '100%',
        fontFamily: 'Segoe UI, Arial, sans-serif',
        animations: {
          enabled: true,
          easing: 'easeinout',
          speed: 800
        },
        toolbar: {
          show: false
        },
        background: '#ffffff',
        foreColor: '#333333',
      },
      colors: colors,
      legend: {
        position: 'bottom',
        fontWeight: 600,
        fontSize: '14px',
        markers: {
          width: 12,
          height: 12,
          radius: 6
        }
      },
      stroke: {
        width: 2,
        colors: ['#ffffff']
      },
      tooltip: {
        y: {
          formatter: function(value) {
            return value + suffix;
          }
        },
        style: {
          fontSize: '14px'
        }
      },      dataLabels: {
        enabled: true,
        formatter: function(val, opts) {
          return opts.w.config.series[opts.seriesIndex] + suffix;
        },
        textAnchor: 'middle',
        style: {
          fontSize: '16px',
          fontWeight: 'bold',
          colors: ["#fff"],
          textOutline: '3px solid rgba(0,0,0,0.8)'
        },
        dropShadow: {
          enabled: true,
          top: 1,
          left: 1,
          blur: 3,
          color: '#000',
          opacity: 0.6
        }
      },
      fill: {
        opacity: 1,
        type: 'solid'
      },
      responsive: [{
        breakpoint: 480,
        options: {
          legend: {
            position: 'bottom'
          }
        }
      }]
    };
    
    const chart = new ApexCharts(document.getElementById(containerId), options);
    chart.render();
    
    // Store chart for export
    charts[containerId] = chart;
  }
    // Create a bar chart
  function createBarChart(containerId, labels, data, colors, suffix = '') {
    // Fix the ID of the loading spinner based on the chart type
    let loaderIdSuffix = containerId.replace('-container', '-loader');
    const loadingSpinner = document.getElementById(loaderIdSuffix);
    if (loadingSpinner) {
      loadingSpinner.style.display = 'none';
    }
    
    const options = {
      series: [{
        name: suffix === '%' ? 'Percentage' : 'Count',
        data: data
      }],
      chart: {
        type: 'bar',
        height: '100%',
        fontFamily: 'Segoe UI, Arial, sans-serif',
        animations: {
          enabled: true,
          easing: 'easeinout',
          speed: 800
        },
        toolbar: {
          show: false
        },
        background: '#ffffff',
        foreColor: '#333333'
      },
      colors: colors,
      plotOptions: {
        bar: {
          distributed: true,
          borderRadius: 4,
          dataLabels: {
            position: 'top'
          },
          columnWidth: '70%'
        }
      },
      dataLabels: {
        enabled: true,
        formatter: function(val) {
          return val + suffix;
        },
        offsetY: -20,
        style: {
          fontSize: '14px',
          fontWeight: 'bold',
          colors: ["#304758"]
        }
      },
      grid: {
        borderColor: '#e0e0e0',
        strokeDashArray: 5
      },
      stroke: {
        width: 0
      },
      xaxis: {
        categories: labels,
        position: 'bottom',
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        },
        labels: {
          style: {
            fontSize: '13px'
          }
        }
      },
      yaxis: {
        labels: {
          formatter: function(val) {
            return val + suffix;
          },
          style: {
            fontSize: '13px'
          }
        },
        min: 0
      },
      tooltip: {
        y: {
          formatter: function(value) {
            return value + suffix;
          }
        },
        style: {
          fontSize: '14px'
        }
      },
      legend: {
        show: false
      }
    };
    
    const chart = new ApexCharts(document.getElementById(containerId), options);
    chart.render();
    
    // Store chart for export
    charts[containerId] = chart;
  }
  
  // Export chart as PNG or PDF
  function exportAsImage(chartType, format) {
    // For example, chartType could be 'percent-pie' or 'count-bar'
    const containerId = chartType + '-container';
    const chart = charts[containerId];
    
    if (!chart) {
      console.error(`Chart ${containerId} not found`);
      alert('Chart is not available for export. Please try again.');
      return;
    }
    
    // Show exporting message
    const loadingElement = document.createElement('div');
    loadingElement.className = 'position-fixed w-100 h-100 d-flex justify-content-center align-items-center';
    loadingElement.style.cssText = 'top: 0; left: 0; background-color: rgba(255,255,255,0.8); z-index: 9999;';
    loadingElement.innerHTML = `
      <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); text-align: center;">
        <div class="spinner-border text-primary mb-3" role="status"></div>
        <p class="mb-0">Preparing ${format.toUpperCase()} file...</p>
      </div>
    `;
    document.body.appendChild(loadingElement);
    
    setTimeout(() => {
      try {
        if (format === 'png') {
          // Export as PNG
          chart.dataURI().then(({ imgURI }) => {
            const downloadLink = document.createElement('a');
            downloadLink.href = imgURI;
            downloadLink.download = `user-${chartType}-${new Date().toISOString().slice(0,10)}.png`;
            downloadLink.click();
            document.body.removeChild(loadingElement);
          });
        } else if (format === 'pdf') {
          // Export as PDF using jsPDF
          chart.dataURI().then(({ imgURI }) => {
            if (typeof window.jspdf === 'undefined') {
              throw new Error('jsPDF library is not loaded');
            }
            
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('landscape');
            
            // Add title
            pdf.setFontSize(18);
            pdf.setTextColor(33, 37, 41);
            pdf.text(`User ${chartType.replace('-', ' ').replace('pie', '').replace('bar', '')} Report`, 15, 15);
            
            // Add timestamp and info
            pdf.setFontSize(10);
            pdf.setTextColor(108, 117, 125);
            pdf.text(`Generated: ${new Date().toLocaleString()}`, 15, 25);
            
            // Add system info
            pdf.text('MySebenarnya System Report', pdf.internal.pageSize.width - 60, 10);
            
            // Add the chart image - strip off the header
            const imgData = imgURI;
            pdf.addImage(imgData, 'PNG', 15, 40, 180, 100);
            
            // Add footer
            pdf.setFontSize(8);
            pdf.text('© 2025 MySebenarnya - Confidential Report', pdf.internal.pageSize.width / 2, pdf.internal.pageSize.height - 10, { align: 'center' });
            
            // Save the PDF
            pdf.save(`user-${chartType}-${new Date().toISOString().slice(0,10)}.pdf`);
            
            // Remove loading element
            document.body.removeChild(loadingElement);
          });
        }
      } catch (error) {
        console.error('Error exporting chart:', error);
        alert('Failed to export chart. Please try again later.');
        document.body.removeChild(loadingElement);
      }
    }, 500); // Small delay for UI responsiveness
  }
    document.addEventListener('DOMContentLoaded', function() {
    // Apply type color to badges
    document.querySelectorAll('.agency-type-badge').forEach(badge => {
      badge.setAttribute('data-type', badge.textContent.trim());
    });    // Event handlers for modal deletion removed - using direct form submission
    
    // Initialize charts
    fetchChartData();
  });
</script>


