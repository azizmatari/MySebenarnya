<?php

namespace App\Http\Controllers\module3;

use App\Http\Controllers\Controller;
use App\Models\module3\ReportModule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Display the main reports page
     */
    public function index()
    {
        try {
            // Get initial data for page load
            $availableYears = ReportModule::getAvailableYears();
            $agencyTypes = ReportModule::getAgencyTypes();
            
            // Get default data (no filters)
            $overallStats = ReportModule::getOverallStats();
            $agencyStats = ReportModule::getAgencyStats();
            $statusChart = ReportModule::getStatusChartData();
            $agencyChart = ReportModule::getAgencyChartData();

            return view('module3.reportMCMC', compact(
                'availableYears',
                'agencyTypes',
                'overallStats',
                'agencyStats',
                'statusChart',
                'agencyChart'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading reports page: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load reports page.');
        }
    }

    /**
     * Generate filtered report via AJAX
     */
    public function generateReport(Request $request): JsonResponse
    {
        try {
            $filters = [];
            
            // Get filters from request
            if ($request->filled('year')) {
                $filters['year'] = $request->year;
            }
            if ($request->filled('month')) {
                $filters['month'] = $request->month;
            }
            if ($request->filled('agency_type')) {
                $filters['agency_type'] = $request->agency_type;
            }

            // Get filtered data
            $overallStats = ReportModule::getOverallStats($filters);
            $agencyStats = ReportModule::getAgencyStats($filters);
            $statusChart = ReportModule::getStatusChartData($filters);
            $agencyChart = ReportModule::getAgencyChartData($filters);

            return response()->json([
                'success' => true,
                'data' => [
                    'overall_stats' => $overallStats,
                    'agency_stats' => $agencyStats,
                    'status_chart' => $statusChart,
                    'agency_chart' => $agencyChart,
                    'filters' => $filters
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download report as PDF/Excel
     */
    public function downloadReport(Request $request)
    {
        try {
            $filters = [];
            
            // Get filters from request
            if ($request->filled('year')) {
                $filters['year'] = $request->year;
            }
            if ($request->filled('month')) {
                $filters['month'] = $request->month;
            }
            if ($request->filled('agency_type')) {
                $filters['agency_type'] = $request->agency_type;
            }

            $format = $request->get('format', 'pdf'); // pdf or excel
            $reportData = ReportModule::generateReportData($filters);

            if ($format === 'excel') {
                return $this->downloadExcel($reportData);
            } else {
                return $this->downloadPDF($reportData);
            }
        } catch (\Exception $e) {
            Log::error('Error downloading report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error downloading report.');
        }
    }

    /**
     * Download report as PDF
     */
    private function downloadPDF($reportData)
    {
        // Simple HTML to PDF conversion (you can use libraries like TCPDF or DOMPDF)
        $html = view('module3.reports.pdf_template', $reportData)->render();
        
        $filename = 'inquiry_report_' . date('Y-m-d_H-i-s') . '.html';
        
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Download report as Excel
     */
    private function downloadExcel($reportData)
    {
        // Simple CSV generation (you can use libraries like PhpSpreadsheet for better Excel support)
        $csv = $this->generateCSV($reportData);
        
        $filename = 'inquiry_report_' . date('Y-m-d_H-i-s') . '.csv';
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Generate CSV content
     */
    private function generateCSV($reportData)
    {
        $csv = "Inquiry Report - Generated on " . $reportData['generated_at'] . "\n\n";
        
        // Overall Statistics
        $csv .= "OVERALL STATISTICS\n";
        $csv .= "Total Inquiries," . $reportData['overall_stats']->total_inquiries . "\n";
        $csv .= "Under Investigation," . $reportData['overall_stats']->under_investigation . "\n";
        $csv .= "Verified True," . $reportData['overall_stats']->verified_true . "\n";
        $csv .= "Verified Fake," . $reportData['overall_stats']->verified_fake . "\n";
        $csv .= "Rejected," . $reportData['overall_stats']->rejected . "\n\n";
        
        // Agency Statistics
        $csv .= "AGENCY STATISTICS\n";
        $csv .= "Agency Name,Agency Type,Total Assignments,Under Investigation,Verified True,Verified Fake,Rejected\n";
        
        foreach ($reportData['agency_stats'] as $agency) {
            $csv .= "{$agency->agency_name},{$agency->agencyType},{$agency->total_assignments},{$agency->under_investigation},{$agency->verified_true},{$agency->verified_fake},{$agency->rejected}\n";
        }
        
        return $csv;    }
}
                    $title = 'Filtered Inquiries Report';
                    break;
                case 'agency_performance':
                    $data = ReportModule::getAgencyPerformanceSummary();
                    $title = 'Agency Performance Report';
                    break;
            }

            // Generate PDF content (simple HTML format for download)
            $html = $this->generatePDFContent($data, $title, $reportType);
            
            return Response::make($html, 200, [
                'Content-Type' => 'text/html',
                'Content-Disposition' => 'attachment; filename="' . strtolower(str_replace(' ', '_', $title)) . '_' . date('Y-m-d') . '.html"'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error exporting to PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to export report.');
        }
    }

    /**
     * Export report to Excel (CSV format)
     */
    public function exportToExcel(Request $request)
    {
        try {
            $reportType = $request->input('report_type', 'agency_assignments');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $agencyId = $request->input('agency_id');

            // Get data based on report type
            $data = [];
            $filename = '';
            
            switch ($reportType) {
                case 'agency_assignments':
                    $data = ReportModule::getInquiriesAssignedByAgency();
                    $filename = 'agency_assignment_report';
                    break;
                case 'filtered_inquiries':
                    $data = ReportModule::getInquiriesByDateAndAgency($startDate, $endDate, $agencyId);
                    $filename = 'filtered_inquiries_report';
                    break;
                case 'agency_performance':
                    $data = ReportModule::getAgencyPerformanceSummary();
                    $filename = 'agency_performance_report';
                    break;
            }

            // Generate CSV content
            $csv = $this->generateCSVContent($data, $reportType);
            
            return Response::make($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error exporting to Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to export report.');
        }
    }

    /**
     * Generate PDF content (HTML format)
     */
    private function generatePDFContent($data, $title, $reportType)
    {
        $html = "<!DOCTYPE html><html><head><title>$title</title><style>";
        $html .= "body { font-family: Arial, sans-serif; margin: 20px; }";
        $html .= "table { width: 100%; border-collapse: collapse; margin-top: 20px; }";
        $html .= "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }";
        $html .= "th { background-color: #f2f2f2; }";
        $html .= "h1 { color: #333; }";
        $html .= "</style></head><body>";
        $html .= "<h1>$title</h1>";
        $html .= "<p>Generated on: " . date('Y-m-d H:i:s') . "</p>";
        
        if (!empty($data)) {
            $html .= "<table>";
            
            // Headers based on report type
            if ($reportType === 'agency_assignments') {
                $html .= "<tr><th>Agency Name</th><th>Agency Type</th><th>Total Assignments</th><th>Active Inquiries</th><th>Completed Inquiries</th></tr>";
                foreach ($data as $row) {
                    $html .= "<tr>";
                    $html .= "<td>" . htmlspecialchars($row->agency_name) . "</td>";
                    $html .= "<td>" . htmlspecialchars($row->agencyType) . "</td>";
                    $html .= "<td>" . $row->total_assignments . "</td>";
                    $html .= "<td>" . $row->active_inquiries . "</td>";
                    $html .= "<td>" . $row->completed_inquiries . "</td>";
                    $html .= "</tr>";
                }
            } elseif ($reportType === 'agency_performance') {
                $html .= "<tr><th>Agency Name</th><th>Type</th><th>Total Assigned</th><th>Completed</th><th>Pending</th><th>Completion Rate (%)</th><th>Avg Days</th></tr>";
                foreach ($data as $row) {
                    $html .= "<tr>";
                    $html .= "<td>" . htmlspecialchars($row->agency_name) . "</td>";
                    $html .= "<td>" . htmlspecialchars($row->agencyType) . "</td>";
                    $html .= "<td>" . $row->total_assigned . "</td>";
                    $html .= "<td>" . $row->completed . "</td>";
                    $html .= "<td>" . $row->pending . "</td>";
                    $html .= "<td>" . ($row->completion_rate ?? 0) . "%</td>";
                    $html .= "<td>" . ($row->avg_completion_days ? round($row->avg_completion_days, 1) : 'N/A') . "</td>";
                    $html .= "</tr>";
                }
            }
            
            $html .= "</table>";
        } else {
            $html .= "<p>No data available for this report.</p>";
        }
        
        $html .= "</body></html>";
        return $html;
    }

    /**
     * Generate CSV content
     */
    private function generateCSVContent($data, $reportType)
    {
        $csv = '';
        
        if (!empty($data)) {
            // Headers based on report type
            if ($reportType === 'agency_assignments') {
                $csv .= "Agency Name,Agency Type,Total Assignments,Active Inquiries,Completed Inquiries\n";
                foreach ($data as $row) {
                    $csv .= '"' . $row->agency_name . '","' . $row->agencyType . '",' . $row->total_assignments . ',' . $row->active_inquiries . ',' . $row->completed_inquiries . "\n";
                }
            } elseif ($reportType === 'agency_performance') {
                $csv .= "Agency Name,Type,Total Assigned,Completed,Pending,Completion Rate (%),Avg Days\n";
                foreach ($data as $row) {
                    $csv .= '"' . $row->agency_name . '","' . $row->agencyType . '",' . $row->total_assigned . ',' . $row->completed . ',' . $row->pending . ',' . ($row->completion_rate ?? 0) . ',' . ($row->avg_completion_days ? round($row->avg_completion_days, 1) : 'N/A') . "\n";
                }
            }
        }
        
        return $csv;
    }
}
