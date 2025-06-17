<?php

namespace App\Http\Controllers\SharedControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\module4\Inquiry;
use App\Models\SharedModels\InquiryStatusHistory;
use App\Models\SharedModels\InquiryAssignment;
use App\Models\module1\Agency;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function mcmcDashboard() {
        // Get MCMC staff ID from session
        $mcmcId = Session::get('user_id', 1);
        
        // === MONITORING AGENCY INVESTIGATION PROGRESS ===
        
        // Get all inquiries with their assignments and latest status
        $allInquiries = Inquiry::with([
            'statusHistory' => function($query) {
                $query->orderBy('updated_at', 'desc');
            },
            'assignment' => function($query) {
                $query->latest('assignmentId');
            },
            'assignment.agency',
            'statusHistory.agency'
        ])->orderBy('submission_date', 'desc')->get();

        // Add current assignment to each inquiry for easier access in the view
        $allInquiries->each(function($inquiry) {
            $inquiry->currentAssignment = $inquiry->currentAssignment();
        });
        
        // Get agency performance statistics
        $agencyStats = $this->getAgencyPerformanceStats();
        
        // Get status distribution
        $statusDistribution = $this->getStatusDistribution();
        
        // Get recent activities (last 10 status updates)
        $recentActivities = InquiryStatusHistory::with(['inquiry', 'agency'])
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();
        
        // Get pending inquiries (Under Investigation)
        $pendingInquiries = $allInquiries->filter(function($inquiry) {
            return $inquiry->getCurrentStatus() === 'Under Investigation';
        });
        
        // Get resolved inquiries (True/Fake/Rejected)
        $resolvedInquiries = $allInquiries->filter(function($inquiry) {
            return in_array($inquiry->getCurrentStatus(), ['True', 'Fake', 'Rejected']);
        });
        
        // Calculate overall statistics
        $totalInquiries = $allInquiries->count();
        $totalPending = $pendingInquiries->count();
        $totalResolved = $resolvedInquiries->count();
        $totalAgencies = Agency::count();
        
        // Get inquiry trends (last 7 days)
        $inquiryTrends = $this->getInquiryTrends();
        
        // Get agency performance metrics
        $agencyPerformance = $this->getDetailedAgencyPerformance();
        
        // === MCMC COMPLETION NOTIFICATIONS ===
        
        // Get recently completed inquiries (status changed to True, Fake, or Rejected)
        $completedNotifications = InquiryStatusHistory::with(['inquiry', 'agency'])
            ->whereIn('status', ['True', 'Fake', 'Rejected'])
            ->whereNotNull('agencyId') // Only show completions by agencies
            ->orderBy('status_id', 'desc')
            ->take(15)
            ->get();
        
        // Mark recent completions as "new" notifications (top 8 considered new)
        $completedNotifications = $completedNotifications->map(function($completion, $index) {
            $completion->is_new = $index < 8;
            $completion->completion_type = $completion->status === 'True' ? 'verified' : 
                                         ($completion->status === 'Fake' ? 'identified_fake' : 'rejected');
            return $completion;
        });
        
        // Count new completion notifications
        $newCompletionCount = $completedNotifications->where('is_new', true)->count();
        
        // Get completion statistics for the notification bar
        $completionStats = [
            'today_completed' => $completedNotifications->where('is_new', true)->count(),
            'total_completed' => $completedNotifications->count(),
            'agencies_active' => $completedNotifications->pluck('agency.agencyName')->unique()->filter()->count(),
            'verified_count' => $completedNotifications->where('status', 'True')->count(),
            'fake_count' => $completedNotifications->where('status', 'Fake')->count(),
            'rejected_count' => $completedNotifications->where('status', 'Rejected')->count()
        ];
        
        return view('Dashboard.MCMCDashboard', compact(
            'allInquiries',
            'agencyStats',
            'statusDistribution', 
            'recentActivities',
            'pendingInquiries',
            'resolvedInquiries',
            'totalInquiries',
            'totalPending',
            'totalResolved',
            'totalAgencies',
            'inquiryTrends',
            'agencyPerformance',
            'completedNotifications',
            'newCompletionCount',
            'completionStats'
        ));
    }
    
    /**
     * Get agency performance statistics for overview cards
     */
    private function getAgencyPerformanceStats()
    {
        return Agency::select('agency_name', 'agencyId')
            ->withCount([
                'assignments as total_assigned',
                'statusHistory as total_updates'
            ])
            ->get()
            ->map(function($agency) {
                // Calculate resolved count
                $resolved = InquiryStatusHistory::where('agencyId', $agency->agencyId)
                    ->whereIn('status', ['True', 'Fake', 'Rejected'])
                    ->distinct('inquiryId')
                    ->count();
                    
                $agency->resolved_count = $resolved;
                $agency->resolution_rate = $agency->total_assigned > 0 
                    ? round(($resolved / $agency->total_assigned) * 100, 1) 
                    : 0;
                    
                return $agency;
            });
    }
    
    /**
     * Get status distribution for charts
     */
    private function getStatusDistribution()
    {
        return InquiryStatusHistory::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }
    
    /**
     * Get inquiry trends for the last 7 days
     */
    private function getInquiryTrends()
    {
        $trends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Inquiry::whereDate('submission_date', $date)->count();
            $trends[] = [
                'date' => $date->format('M j'),
                'count' => $count
            ];
        }
        return $trends;
    }
    
    /**
     * Get detailed agency performance for reports
     */
    private function getDetailedAgencyPerformance()
    {
        return Agency::with([
            'assignments.inquiry',
            'statusHistory.inquiry'
        ])
        ->get()
        ->map(function($agency) {
            $assignments = $agency->assignments;
            $totalAssigned = $assignments->count();
            
            // Calculate resolved inquiries
            $resolvedInquiries = $assignments->filter(function($assignment) {
                $inquiry = $assignment->inquiry;
                return $inquiry && in_array($inquiry->getCurrentStatus(), ['True', 'Fake', 'Rejected']);
            });
            
            $totalResolved = $resolvedInquiries->count();
            
            // Calculate average resolution time (simplified - using days between assignment and resolution)
            $avgResolutionTime = 0;
            if ($totalResolved > 0) {
                $totalDays = 0;
                foreach ($resolvedInquiries as $assignment) {
                    $assignDate = Carbon::parse($assignment->assignDate);
                    $resolveDate = Carbon::now(); // Simplified - in real system, get actual resolution date
                    $totalDays += $assignDate->diffInDays($resolveDate);
                }
                $avgResolutionTime = round($totalDays / $totalResolved, 1);
            }
            
            return [
                'agency_name' => $agency->agency_name,
                'agency_type' => $agency->agencyType,
                'total_assigned' => $totalAssigned,
                'total_resolved' => $totalResolved,
                'pending_count' => $totalAssigned - $totalResolved,
                'resolution_rate' => $totalAssigned > 0 ? round(($totalResolved / $totalAssigned) * 100, 1) : 0,
                'avg_resolution_days' => $avgResolutionTime,
                'last_activity' => $agency->statusHistory->max('updated_at') ?? 'No activity'
            ];
        });
    }

    public function userDashboard() {
        // Get user ID from session (adjust this based on your auth system)
        $userId = Session::get('user_id', 1); // Default to 1 for testing
        
        // Get all inquiries for the user
        $inquiries = Inquiry::with([
            'statusHistory.agency',
            'assignment.agency'
        ])->where('userId', $userId)
          ->orderBy('submission_date', 'desc')
          ->get();

        // Calculate statistics
        $totalInquiries = $inquiries->count();
        $pendingInquiries = $inquiries->filter(function($inquiry) {
            return $inquiry->getCurrentStatus() === 'Under Investigation';
        })->count();
        
        $resolvedInquiries = $inquiries->filter(function($inquiry) {
            return in_array($inquiry->getCurrentStatus(), ['True', 'Fake', 'Rejected']);
        })->count();

        // Get recent status updates (last 7 days) - Since we don't have timestamps in inquirystatushistory
        // We'll count based on recent inquiries instead
        $recentUpdates = InquiryStatusHistory::whereHas('inquiry', function($query) use ($userId) {
            $query->where('userId', $userId);
        })->count();

        // Get recent inquiries (last 5)
        $recentInquiries = $inquiries->take(5);

        // Get recent status updates for notifications (latest 10 to show more recent activity)
        $recentStatusUpdates = InquiryStatusHistory::with(['inquiry', 'agency'])
            ->whereHas('inquiry', function($query) use ($userId) {
                $query->where('userId', $userId);
            })
            ->orderBy('status_id', 'desc')
            ->take(10)
            ->get();

        // Add notification flags to recent updates (mark recent ones as "new")
        $recentStatusUpdates = $recentStatusUpdates->map(function($update, $index) {
            // Consider top 5 as "new" notifications (since we don't have timestamps)
            $update->is_new = $index < 5;
            return $update;
        });

        // Count of updates that would be considered "new notifications"
        $newNotificationCount = $recentStatusUpdates->where('is_new', true)->count();

        return view('Dashboard.UserDash', compact(
            'inquiries',
            'totalInquiries',
            'pendingInquiries',
            'resolvedInquiries',
            'recentUpdates',
            'recentInquiries',
            'recentStatusUpdates',
            'newNotificationCount'
        ));
    }

    public function agencyDashboard() {
        // Get agency ID from session
        $agencyId = Session::get('agency_id');
        
        if (!$agencyId) {
            return redirect()->route('login')->with('error', 'Please login to access dashboard');
        }
        
        // Get agency details
        $agency = Agency::find($agencyId);
        if (!$agency) {
            return redirect()->route('login')->with('error', 'Agency not found');
        }
        
        // Get all inquiries assigned to this agency
        $assignedInquiries = Inquiry::with([
            'statusHistory' => function($query) {
                $query->orderBy('updated_at', 'desc');
            },
            'assignment' => function($query) use ($agencyId) {
                $query->where('agencyId', $agencyId)->latest('assignmentId');
            },
            'assignment.agency',
            'statusHistory.agency'
        ])
        ->whereHas('assignment', function($query) use ($agencyId) {
            $query->where('agencyId', $agencyId)
                  ->where('isRejected', false);
        })
        ->orderBy('submission_date', 'desc')
        ->get();

        // Add current assignment and status to each inquiry
        $assignedInquiries->each(function($inquiry) use ($agencyId) {
            $inquiry->currentAssignment = $inquiry->assignment()
                ->where('agencyId', $agencyId)
                ->where('isRejected', false)
                ->latest('assignmentId')
                ->first();
        });
        
        // Calculate statistics
        $totalAssigned = $assignedInquiries->count();
        $underInvestigation = $assignedInquiries->filter(function($inquiry) {
            return $inquiry->getCurrentStatus() === 'Under Investigation';
        })->count();
        $resolved = $assignedInquiries->filter(function($inquiry) {
            return in_array($inquiry->getCurrentStatus(), ['True', 'Fake', 'Rejected']);
        })->count();
        $verified = $assignedInquiries->filter(function($inquiry) {
            return $inquiry->getCurrentStatus() === 'True';
        })->count();
        $fake = $assignedInquiries->filter(function($inquiry) {
            return $inquiry->getCurrentStatus() === 'Fake';
        })->count();
        $rejected = $assignedInquiries->filter(function($inquiry) {
            return $inquiry->getCurrentStatus() === 'Rejected';
        })->count();
        
        // Get recent status updates by this agency
        $recentUpdates = InquiryStatusHistory::with(['inquiry'])
            ->where('agencyId', $agencyId)
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();
        
        // Get pending inquiries that need attention
        $pendingInquiries = $assignedInquiries->filter(function($inquiry) {
            return $inquiry->getCurrentStatus() === 'Under Investigation';
        });
        
        // Get status distribution for this agency
        $statusDistribution = [
            'Under Investigation' => $underInvestigation,
            'True' => $verified,
            'Fake' => $fake,
            'Rejected' => $rejected
        ];
        
        return view('Dashboard.AgencyDashboard', compact(
            'agency',
            'assignedInquiries',
            'totalAssigned',
            'underInvestigation',
            'resolved',
            'verified',
            'fake',
            'rejected',
            'recentUpdates',
            'pendingInquiries',
            'statusDistribution'
        ));
    }

    /**
     * Get real-time status updates for a specific inquiry (AJAX endpoint)
     */
    public function getInquiryStatus($inquiryId)
    {
        $userId = Session::get('user_id', 1);
        
        $inquiry = Inquiry::with(['statusHistory.agency'])
                         ->where('inquiryId', $inquiryId)
                         ->where('userId', $userId)
                         ->first();

        if (!$inquiry) {
            return response()->json(['error' => 'Inquiry not found'], 404);
        }

        $status = $inquiry->getCurrentStatus();
        $statusColor = match($status) {
            'Under Investigation' => 'warning',
            'True' => 'success',
            'Fake' => 'danger',
            'Rejected' => 'secondary',
            'Reassigned' => 'info',
            default => 'info'
        };

        return response()->json([
            'status' => $status,
            'status_color' => $statusColor,
            'latest_update' => $inquiry->statusHistory->first(),
            'history' => $inquiry->statusHistory->map(function($history) {
                return [
                    'status' => $history->status,
                    'comment' => $history->status_comment ?? 'No comments provided',
                    'date' => $history->getFormattedDateAttribute(),
                    'agency' => $history->agency->agency_name ?? 'Unassigned'
                ];
            })
        ]);
    }
    
    /**
     * Generate agency performance reports with filters
     */
    /**
     * Generate agency performance reports with filters
     */
    public function generateAgencyReport(Request $request)
    {
        try {
            $startDate = $request->get('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
            $agencyId = $request->get('agency_id');
            $inquiryCategory = $request->get('inquiry_category');
            $format = $request->get('format', 'json');
            
            // Build query with filters
            $query = InquiryAssignment::with(['inquiry', 'agency'])
                ->whereBetween('assignDate', [$startDate, $endDate]);
                
            if ($agencyId) {
                $query->where('agencyId', $agencyId);
            }
            
            $assignments = $query->get();
            
            // Process data for report
            $reportData = $this->processReportData($assignments, $inquiryCategory);
            
            switch ($format) {
                case 'pdf':
                    // For PDF, return JSON data for client-side PDF generation with charts
                    return response()->json([
                        'success' => true,
                        'message' => 'PDF data prepared for client-side generation',
                        'report_data' => $reportData,
                        'charts_data' => $this->getChartsDataForPDF($reportData),
                        'summary' => [
                            'total_agencies' => count($reportData),
                            'date_range' => "$startDate to $endDate",
                            'format' => $format,
                            'generated_at' => date('Y-m-d H:i:s')
                        ]
                    ]);
                case 'excel':
                    return $this->generateExcelResponse($reportData, $startDate, $endDate);
                default:
                    return response()->json([
                        'success' => true,
                        'message' => 'Report generated successfully',
                        'report_data' => $reportData,
                        'summary' => [
                            'total_agencies' => count($reportData),
                            'date_range' => "$startDate to $endDate",
                            'format' => $format
                        ]
                    ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Report generation failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate PDF response for direct download
     */
    private function generatePDFResponse($data, $startDate, $endDate)
    {
        $content = "AGENCY PERFORMANCE REPORT\n";
        $content .= "Date Range: $startDate to $endDate\n";
        $content .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $content .= str_repeat("=", 60) . "\n\n";
        
        if (empty($data)) {
            $content .= "No agency data available for the selected period.\n";
        } else {
            foreach ($data as $agency) {
                $content .= "AGENCY: " . ($agency['agency_name'] ?? 'Unknown') . "\n";
                $content .= "Type: " . ($agency['agency_type'] ?? 'N/A') . "\n";
                $content .= "Total Assigned: " . ($agency['total_assigned'] ?? 0) . "\n";
                $content .= "Total Resolved: " . ($agency['total_resolved'] ?? 0) . "\n";
                $content .= "Resolution Rate: " . ($agency['resolution_rate'] ?? 0) . "%\n";
                $content .= "Avg Resolution Time: " . ($agency['avg_resolution_time'] ?? 0) . " days\n";
                $content .= "Pending: " . ($agency['pending_count'] ?? 0) . "\n";
                $content .= str_repeat("-", 40) . "\n\n";
            }
        }
        
        $filename = 'agency-performance-' . date('Y-m-d') . '.txt';
        
        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    /**
     * Generate Excel response for direct download
     */
    private function generateExcelResponse($data, $startDate, $endDate)
    {
        $csv = "Agency Performance Report\n";
        $csv .= "Date Range: $startDate to $endDate\n";
        $csv .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Headers
        $csv .= "Agency Name,Agency Type,Total Assigned,Total Resolved,Resolution Rate (%),Avg Resolution Time (days),Pending Count\n";
        
        if (empty($data)) {
            $csv .= "No data available,,,,,\n";
        } else {
            foreach ($data as $agency) {
                $csv .= '"' . ($agency['agency_name'] ?? 'Unknown') . '",';
                $csv .= '"' . ($agency['agency_type'] ?? 'N/A') . '",';
                $csv .= ($agency['total_assigned'] ?? 0) . ',';
                $csv .= ($agency['total_resolved'] ?? 0) . ',';
                $csv .= ($agency['resolution_rate'] ?? 0) . ',';
                $csv .= ($agency['avg_resolution_time'] ?? 0) . ',';
                $csv .= ($agency['pending_count'] ?? 0) . "\n";
            }
        }
        
        $filename = 'agency-performance-' . date('Y-m-d') . '.csv';
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    /**
     * Process assignment data for reporting
     */
    private function processReportData($assignments, $inquiryCategory = null)
    {
        $agencies = [];
        
        foreach ($assignments as $assignment) {
            $inquiry = $assignment->inquiry;
            $agencyId = $assignment->agencyId;
            $agencyName = $assignment->agency->agency_name ?? 'Unknown Agency';
            
            // Filter by inquiry category if specified
            if ($inquiryCategory && !str_contains(strtolower($inquiry->description ?? ''), strtolower($inquiryCategory))) {
                continue;
            }
            
            if (!isset($agencies[$agencyId])) {
                $agencies[$agencyId] = [
                    'agency_name' => $agencyName,
                    'agency_type' => $assignment->agency->agencyType ?? 'Unknown',
                    'total_assigned' => 0,
                    'resolved' => 0,
                    'under_investigation' => 0,
                    'rejected' => 0,
                    'fake' => 0,
                    'true' => 0,
                    'resolution_times' => [],
                    'inquiries' => []
                ];
            }
            
            $agencies[$agencyId]['total_assigned']++;
            
            $currentStatus = $inquiry->getCurrentStatus();
            switch ($currentStatus) {
                case 'Under Investigation':
                    $agencies[$agencyId]['under_investigation']++;
                    break;
                case 'Rejected':
                    $agencies[$agencyId]['rejected']++;
                    $agencies[$agencyId]['resolved']++;
                    break;
                case 'Fake':
                    $agencies[$agencyId]['fake']++;
                    $agencies[$agencyId]['resolved']++;
                    break;
                case 'True':
                    $agencies[$agencyId]['true']++;
                    $agencies[$agencyId]['resolved']++;
                    break;
            }
            
            // Calculate resolution time (simplified)
            if (in_array($currentStatus, ['True', 'Fake', 'Rejected'])) {
                $assignDate = Carbon::parse($assignment->assignDate);
                $resolutionTime = $assignDate->diffInDays(Carbon::now());
                $agencies[$agencyId]['resolution_times'][] = $resolutionTime;
            }
            
            // Add inquiry details
            $agencies[$agencyId]['inquiries'][] = [
                'inquiry_id' => $inquiry->inquiryId,
                'title' => $inquiry->title,
                'status' => $currentStatus,
                'submission_date' => $inquiry->submission_date,
                'assign_date' => $assignment->assignDate,
                'mcmc_comments' => $assignment->mcmcComments
            ];
        }
        
        // Calculate averages and percentages
        foreach ($agencies as &$agency) {
            $agency['resolution_rate'] = $agency['total_assigned'] > 0 
                ? round(($agency['resolved'] / $agency['total_assigned']) * 100, 1) 
                : 0;
                
            $agency['avg_resolution_time'] = !empty($agency['resolution_times']) 
                ? round(array_sum($agency['resolution_times']) / count($agency['resolution_times']), 1)
                : 0;
                
            $agency['pending_count'] = $agency['total_assigned'] - $agency['resolved'];
        }
        
        return array_values($agencies);
    }
    
    /**
     * Get real-time inquiry updates for MCMC monitoring
     */
    public function getInquiryUpdates(Request $request)
    {
        $since = $request->get('since', Carbon::now()->subHour());
        
        $updates = InquiryStatusHistory::with(['inquiry', 'agency'])
            ->where('updated_at', '>=', $since)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function($update) {
                return [
                    'inquiry_id' => $update->inquiryId,
                    'inquiry_title' => $update->inquiry->title ?? 'Unknown',
                    'agency_name' => $update->agency->agency_name ?? 'Unknown Agency',
                    'old_status' => 'Previous Status', // Would need additional logic to track
                    'new_status' => $update->status,
                    'status_comment' => $update->status_comment,
                    'updated_at' => $update->updated_at->format('Y-m-d H:i:s'),
                    'updated_by' => $update->updated_by_agent_id ?? 'System'
                ];
            });
            
        return response()->json($updates);
    }
    
    /**
     * Generate PDF report - Creates text-based PDF for download
     */
    private function generatePDFReport($data, $startDate, $endDate)
    {
        try {
            // Simple logging without facade (in case of import issues)
            error_log('PDF Generation: Starting with ' . count($data) . ' agencies');
            
            // Use .txt extension since we're creating text content, not real PDF
            $filename = 'agency-performance-' . date('Y-m-d-H-i-s') . '.txt';
            
            // Create text-based content for PDF
            $pdfContent = $this->createSimplePDFContent($data, $startDate, $endDate);
            
            // Store the file temporarily
            $filePath = storage_path('app/reports/' . $filename);
            
            // Create directory if it doesn't exist
            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }
            
            // Write content to file
            $result = file_put_contents($filePath, $pdfContent);
            
            if ($result === false) {
                error_log('PDF Generation: Failed to write file');
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to write PDF file'
                ], 500);
            }
            
            error_log('PDF Generation: File created successfully - ' . $filename);
            
            return response()->json([
                'success' => true,
                'message' => 'PDF report generated successfully',
                'report_type' => 'Agency Performance Report (Text Format)',
                'date_range' => "$startDate to $endDate",
                'total_agencies' => count($data),
                'download_url' => url('/reports/download/pdf/' . $filename),
                'filename' => $filename
            ]);
            
        } catch (\Exception $e) {
            error_log('PDF Generation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'PDF generation failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate Excel report - Now creates actual downloadable content
     */
    private function generateExcelReport($data, $startDate, $endDate)
    {
        $filename = 'agency-performance-' . date('Y-m-d-H-i-s') . '.csv';
        
        // Create CSV content (Excel-compatible)
        $csvContent = $this->createCSVContent($data, $startDate, $endDate);
        
        // Store the file temporarily
        $filePath = storage_path('app/reports/' . $filename);
        
        // Create directory if it doesn't exist
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }
        
        file_put_contents($filePath, $csvContent);
        
        return response()->json([
            'success' => true,
            'message' => 'Excel report generated successfully',
            'report_type' => 'Agency Performance Report',
            'date_range' => "$startDate to $endDate",
            'total_agencies' => count($data),
            'download_url' => url('/reports/download/excel/' . $filename),
            'filename' => $filename
        ]);
    }
    
    /**
     * Create simple PDF-like content (text-based)
     */
    private function createSimplePDFContent($data, $startDate, $endDate)
    {
        $content = "AGENCY PERFORMANCE REPORT\n";
        $content .= "Date Range: $startDate to $endDate\n";
        $content .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $content .= str_repeat("=", 50) . "\n\n";
        
        foreach ($data as $agency) {
            $content .= "AGENCY: " . $agency['agency_name'] . "\n";
            $content .= "Type: " . $agency['agency_type'] . "\n";
            $content .= "Total Assigned: " . $agency['total_assigned'] . "\n";
            $content .= "Total Resolved: " . $agency['total_resolved'] . "\n";
            $content .= "Resolution Rate: " . $agency['resolution_rate'] . "%\n";
            $content .= "Avg Resolution Time: " . $agency['avg_resolution_time'] . " days\n";
            $content .= "Pending: " . $agency['pending_count'] . "\n";
            $content .= str_repeat("-", 30) . "\n\n";
        }
        
        return $content;
    }
    
    /**
     * Create CSV content for Excel export
     */
    private function createCSVContent($data, $startDate, $endDate)
    {
        $csv = "Agency Performance Report - $startDate to $endDate\n";
        $csv .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Headers
        $csv .= "Agency Name,Agency Type,Total Assigned,Total Resolved,Resolution Rate (%),Avg Resolution Time (days),Pending Count\n";
        
        // Data rows
        foreach ($data as $agency) {
            $csv .= '"' . $agency['agency_name'] . '",';
            $csv .= '"' . $agency['agency_type'] . '",';
            $csv .= $agency['total_assigned'] . ',';
            $csv .= $agency['total_resolved'] . ',';
            $csv .= $agency['resolution_rate'] . ',';
            $csv .= $agency['avg_resolution_time'] . ',';
            $csv .= $agency['pending_count'] . "\n";
        }
        
        return $csv;
    }
    
    /**
     * Generate HTML content for reports
     */
    private function generateReportHTML($data, $startDate, $endDate, $type)
    {
        $html = "<h1>Agency Performance Report ($type)</h1>";
        $html .= "<p>Date Range: $startDate to $endDate</p>";
        $html .= "<p>Generated: " . date('Y-m-d H:i:s') . "</p>";
        
        $html .= "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        $html .= "<tr>";
        $html .= "<th>Agency Name</th>";
        $html .= "<th>Type</th>";
        $html .= "<th>Assigned</th>";
        $html .= "<th>Resolved</th>";
        $html .= "<th>Rate (%)</th>";
        $html .= "<th>Avg Time (days)</th>";
        $html .= "<th>Pending</th>";
        $html .= "</tr>";
        
        foreach ($data as $agency) {
            $html .= "<tr>";
            $html .= "<td>" . htmlspecialchars($agency['agency_name']) . "</td>";
            $html .= "<td>" . htmlspecialchars($agency['agency_type']) . "</td>";
            $html .= "<td>" . $agency['total_assigned'] . "</td>";
            $html .= "<td>" . $agency['total_resolved'] . "</td>";
            $html .= "<td>" . $agency['resolution_rate'] . "%</td>";
            $html .= "<td>" . $agency['avg_resolution_time'] . "</td>";
            $html .= "<td>" . $agency['pending_count'] . "</td>";
            $html .= "</tr>";
        }
        
        $html .= "</table>";
        return $html;
    }
    
    /**
     * Download PDF report file (text format)
     */
    public function downloadPDFReport($filename)
    {
        $filePath = storage_path('app/reports/' . $filename);
        
        \Log::info('Download PDF request', ['filename' => $filename, 'path' => $filePath]);
        
        if (!file_exists($filePath)) {
            \Log::error('PDF file not found', ['path' => $filePath]);
            abort(404, 'Report file not found');
        }
        
        // Determine content type based on file extension
        $contentType = 'text/plain';
        if (str_ends_with($filename, '.pdf')) {
            $contentType = 'application/pdf';
        } elseif (str_ends_with($filename, '.txt')) {
            $contentType = 'text/plain';
        }
        
        \Log::info('Serving PDF download', [
            'file' => $filename,
            'size' => filesize($filePath),
            'content_type' => $contentType
        ]);
        
        return response()->download($filePath, $filename, [
            'Content-Type' => $contentType,
        ])->deleteFileAfterSend();
    }
    
    /**
     * Download Excel report file
     */
    public function downloadExcelReport($filename)
    {
        $filePath = storage_path('app/reports/' . $filename);
        
        if (!file_exists($filePath)) {
            abort(404, 'Report file not found');
        }
        
        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ])->deleteFileAfterSend();
    }
    
    /**
     * Get filtered inquiries for MCMC dashboard
     */
    public function getFilteredInquiries(Request $request)
    {
        try {
            \Log::info('Filter request received', $request->all());
            
            $status = $request->get('status');
            $agencyId = $request->get('agency_id');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            
            $query = Inquiry::with([
                'statusHistory.agency', 
                'assignment' => function($query) {
                    $query->latest('assignmentId');
                },
                'assignment.agency'
            ]);
            
            if ($dateFrom && $dateTo) {
                $query->whereBetween('submission_date', [$dateFrom, $dateTo]);
            }
            
            $inquiries = $query->get();
            \Log::info('Initial inquiries found', ['count' => $inquiries->count()]);
            
            // Add current assignment to each inquiry
            $inquiries->each(function($inquiry) {
                $inquiry->currentAssignment = $inquiry->currentAssignment();
            });
            
            // Filter by status if specified
            if ($status) {
                $inquiries = $inquiries->filter(function($inquiry) use ($status) {
                    return $inquiry->getCurrentStatus() === $status;
                });
                \Log::info('After status filter', ['count' => $inquiries->count(), 'status' => $status]);
            }
            
            // Filter by agency if specified
            if ($agencyId) {
                $inquiries = $inquiries->filter(function($inquiry) use ($agencyId) {
                    return $inquiry->currentAssignment && $inquiry->currentAssignment->agencyId == $agencyId;
                });
                \Log::info('After agency filter', ['count' => $inquiries->count(), 'agency_id' => $agencyId]);
            }
            
            // Format the response data
            $formattedInquiries = $inquiries->map(function($inquiry) {
                return [
                    'inquiryId' => $inquiry->inquiryId,
                    'title' => $inquiry->title,
                    'current_status' => $inquiry->getCurrentStatus(),
                    'submission_date' => $inquiry->submission_date,
                    'assign_date' => $inquiry->currentAssignment ? $inquiry->currentAssignment->assignDate : null,
                    'agency' => $inquiry->currentAssignment && $inquiry->currentAssignment->agency ? [
                        'agency_name' => $inquiry->currentAssignment->agency->agency_name,
                        'agencyType' => $inquiry->currentAssignment->agency->agencyType ?? 'Unknown'
                    ] : null
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $formattedInquiries->values(),
                'total' => $formattedInquiries->count()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Filter request failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get charts data for PDF generation
     */
    private function getChartsDataForPDF($reportData)
    {
        $agencyNames = [];
        $totalAssigned = [];
        $totalResolved = [];
        $resolutionRates = [];
        $pendingCounts = [];
        
        foreach ($reportData as $agency) {
            $agencyNames[] = $agency['agency_name'] ?? 'Unknown';
            $totalAssigned[] = $agency['total_assigned'] ?? 0;
            $totalResolved[] = $agency['total_resolved'] ?? 0;
            $resolutionRates[] = $agency['resolution_rate'] ?? 0;
            $pendingCounts[] = $agency['pending_count'] ?? 0;
        }
        
        return [
            'agency_performance' => [
                'labels' => $agencyNames,
                'assigned' => $totalAssigned,
                'resolved' => $totalResolved,
                'rates' => $resolutionRates,
                'pending' => $pendingCounts
            ],
            'summary_stats' => [
                'total_agencies' => count($reportData),
                'total_assigned' => array_sum($totalAssigned),
                'total_resolved' => array_sum($totalResolved),
                'total_pending' => array_sum($pendingCounts),
                'avg_resolution_rate' => !empty($resolutionRates) ? round(array_sum($resolutionRates) / count($resolutionRates), 1) : 0
            ]
        ];
    }

    /**
     * Update inquiry status by agency
     */
    public function updateInquiryStatus(Request $request, $inquiryId)
    {
        // Custom validation rules based on status
        $rules = [
            'status' => 'required|in:Under Investigation,True,Fake,Rejected',
            'reviewing_officer' => 'required|string|max:255',
            'supporting_document' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif,txt,xlsx,xls'
        ];
        
        // Make status_comment required for rejection
        if ($request->status === 'Rejected') {
            $rules['status_comment'] = 'required|string|max:1000';
        } else {
            $rules['status_comment'] = 'nullable|string|max:1000';
        }
        
        $request->validate($rules);

        $agencyId = Session::get('agency_id');
        if (!$agencyId) {
            return response()->json(['error' => 'Unauthorized: No agency session found'], 401);
        }

        // Verify this inquiry is assigned to this agency
        $inquiry = Inquiry::with('assignment')
            ->whereHas('assignment', function($query) use ($agencyId) {
                $query->where('agencyId', $agencyId)
                      ->where('isRejected', false);
            })
            ->where('inquiryId', $inquiryId)
            ->first();

        if (!$inquiry) {
            return response()->json(['error' => 'Inquiry not found or not assigned to your agency'], 404);
        }

        try {
            DB::beginTransaction();

            // Handle file upload if present
            $supportingDocumentPath = null;
            if ($request->hasFile('supporting_document')) {
                $file = $request->file('supporting_document');
                $filename = time() . '_' . $inquiryId . '_' . $file->getClientOriginalName();
                
                Log::info('File upload started', [
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'generated_filename' => $filename
                ]);
                
                // Ensure directories exist
                $storageDir = storage_path('app/public/supporting_documents');
                if (!file_exists($storageDir)) {
                    mkdir($storageDir, 0755, true);
                }
                
                $publicDir = public_path('storage/supporting_documents');
                if (!file_exists($publicDir)) {
                    mkdir($publicDir, 0755, true);
                }
                
                // Store in Laravel's storage system
                $supportingDocumentPath = $file->storeAs('supporting_documents', $filename, 'public');
                
                // Verify file was stored
                $storagePath = storage_path('app/public/' . $supportingDocumentPath);
                if (file_exists($storagePath) && filesize($storagePath) > 0) {
                    // Copy to public directory for direct access
                    $publicPath = public_path('storage/supporting_documents/' . $filename);
                    copy($storagePath, $publicPath);
                    
                    Log::info('File upload successful', [
                        'storage_path' => $storagePath,
                        'public_path' => $publicPath,
                        'file_size' => filesize($storagePath),
                        'db_path' => $supportingDocumentPath
                    ]);
                } else {
                    Log::error('File upload failed - file not found or empty', [
                        'expected_path' => $storagePath,
                        'file_exists' => file_exists($storagePath),
                        'file_size' => file_exists($storagePath) ? filesize($storagePath) : 'N/A'
                    ]);
                    $supportingDocumentPath = null;
                }
            }

            // Check if status is "Rejected" - treat as rebounce/reassignment
            if ($request->status === 'Rejected') {
                // Mark current assignment as rejected (only change isRejected flag)
                $assignment = InquiryAssignment::where('inquiryId', $inquiryId)
                    ->where('agencyId', $agencyId)
                    ->where('isRejected', false)
                    ->first();
                
                if ($assignment) {
                    // For agency rejection, only update isRejected flag
                    $assignment->update([
                        'isRejected' => true
                    ]);
                }

                // Create status history entry for rejection (put comment directly in status_comment)
                $statusHistoryData = [
                    'inquiryId' => $inquiryId,
                    'agencyId' => $agencyId,
                    'status' => $request->status, // 'Rejected'
                    'status_comment' => $request->status_comment ?: 'No reason provided',
                    'officer_name' => $request->reviewing_officer,
                    'updated_by_agent_id' => $agencyId,
                    'supporting_document' => $supportingDocumentPath
                ];
                
                Log::info('Creating status history entry for rejection', [
                    'data' => $statusHistoryData,
                    'inquiry_id' => $inquiryId,
                    'agency_id' => $agencyId
                ]);
                
                $statusHistory = new InquiryStatusHistory($statusHistoryData);
                $saved = $statusHistory->save();
                
                if ($saved) {
                    Log::info('Status history entry created successfully', [
                        'status_id' => $statusHistory->status_id,
                        'inquiry_id' => $inquiryId
                    ]);
                } else {
                    Log::error('Failed to save status history entry', [
                        'inquiry_id' => $inquiryId,
                        'data' => $statusHistoryData
                    ]);
                }

                // Set inquiry final_status to null (rebounce to MCMC - like reassignment)
                $inquiry->final_status = null;
                $inquiry->save();

                Log::info('Agency rejected inquiry - bounced back to MCMC', [
                    'inquiry_id' => $inquiryId,
                    'agency_id' => $agencyId,
                    'reason' => $request->status_comment,
                    'reviewing_officer' => $request->reviewing_officer
                ]);

                $responseMessage = 'Case rejected and returned to MCMC for reassignment.';
            } else {
                // Normal status update (True, Fake, Under Investigation)
                // Create status history entry using the new officer_name field
                $statusHistory = new InquiryStatusHistory([
                    'inquiryId' => $inquiryId,
                    'agencyId' => $agencyId,
                    'status' => $request->status,
                    'status_comment' => $request->status_comment,
                    'officer_name' => $request->reviewing_officer,
                    'updated_by_agent_id' => $agencyId,
                    'supporting_document' => $supportingDocumentPath
                ]);
                $statusHistory->save();

                // Update inquiry final status normally
                $inquiry->final_status = $request->status;
                $inquiry->save();

                Log::info('Agency updated inquiry status', [
                    'inquiry_id' => $inquiryId,
                    'agency_id' => $agencyId,
                    'new_status' => $request->status,
                    'reviewing_officer' => $request->reviewing_officer
                ]);

                $responseMessage = 'Status updated successfully';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $responseMessage,
                'new_status' => $request->status,
                'updated_at' => $statusHistory->updated_at->format('Y-m-d H:i:s'),
                'is_rejection' => $request->status === 'Rejected'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating inquiry status', [
                'inquiry_id' => $inquiryId,
                'agency_id' => $agencyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug version of update inquiry status - with detailed logging
     */
    public function debugUpdateInquiryStatus(Request $request, $inquiryId)
    {
        try {
            // Log all input data
            Log::info('=== DEBUG STATUS UPDATE ===', [
                'inquiry_id' => $inquiryId,
                'request_data' => $request->all(),
                'session_data' => session()->all(),
                'has_file' => $request->hasFile('supporting_document'),
                'file_info' => $request->hasFile('supporting_document') ? [
                    'name' => $request->file('supporting_document')->getClientOriginalName(),
                    'size' => $request->file('supporting_document')->getSize(),
                    'mime' => $request->file('supporting_document')->getMimeType()
                ] : null
            ]);

            // Check session
            $agencyId = Session::get('agency_id');
            if (!$agencyId) {
                return response()->json([
                    'error' => 'No agency session found',
                    'session_data' => session()->all()
                ], 401);
            }

            // Validate input
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'status' => 'required|in:Under Investigation,True,Fake,Rejected',
                'status_comment' => 'nullable|string|max:1000',
                'reviewing_officer' => 'required|string|max:255',
                'supporting_document' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif,txt,xlsx,xls'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if inquiry exists and is assigned to agency
            $inquiry = Inquiry::with('assignment')
                ->whereHas('assignment', function($query) use ($agencyId) {
                    $query->where('agencyId', $agencyId)
                          ->where('isRejected', false);
                })
                ->where('inquiryId', $inquiryId)
                ->first();

            if (!$inquiry) {
                return response()->json([
                    'error' => 'Inquiry not found or not assigned to your agency',
                    'agency_id' => $agencyId,
                    'inquiry_id' => $inquiryId
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'All checks passed! Now testing file upload...',
                'data' => [
                    'agency_id' => $agencyId,
                    'inquiry_id' => $inquiryId,
                    'status' => $request->status,
                    'officer' => $request->reviewing_officer,
                    'has_file' => $request->hasFile('supporting_document')
                ]
            ]);

            // Test actual file upload
            $supportingDocumentPath = null;
            if ($request->hasFile('supporting_document')) {
                $file = $request->file('supporting_document');
                $filename = time() . '_' . $inquiryId . '_' . $file->getClientOriginalName();
                
                Log::info('Testing file upload', [
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'generated_filename' => $filename,
                    'temp_path' => $file->getPathname()
                ]);
                
                // Ensure directories exist
                $storageDir = storage_path('app/public/supporting_documents');
                $publicDir = public_path('storage/supporting_documents');
                
                if (!file_exists($storageDir)) {
                    mkdir($storageDir, 0755, true);
                    Log::info('Created storage directory: ' . $storageDir);
                }
                
                if (!file_exists($publicDir)) {
                    mkdir($publicDir, 0755, true);
                    Log::info('Created public directory: ' . $publicDir);
                }
                
                // Store in Laravel's storage system
                try {
                    $supportingDocumentPath = $file->storeAs('supporting_documents', $filename, 'public');
                    Log::info('File stored successfully', ['path' => $supportingDocumentPath]);
                    
                    // Verify file was stored
                    $storagePath = storage_path('app/public/' . $supportingDocumentPath);
                    $fileExists = file_exists($storagePath);
                    $fileSize = $fileExists ? filesize($storagePath) : 0;
                    
                    Log::info('File verification', [
                        'storage_path' => $storagePath,
                        'exists' => $fileExists,
                        'size' => $fileSize
                    ]);
                    
                    if ($fileExists && $fileSize > 0) {
                        // Copy to public directory
                        $publicPath = public_path('storage/supporting_documents/' . $filename);
                        $copySuccess = copy($storagePath, $publicPath);
                        Log::info('File copy result', [
                            'public_path' => $publicPath,
                            'copy_success' => $copySuccess,
                            'public_exists' => file_exists($publicPath),
                            'public_size' => file_exists($publicPath) ? filesize($publicPath) : 0
                        ]);
                        
                        return response()->json([
                            'success' => true,
                            'message' => 'File upload test successful!',
                            'file_info' => [
                                'filename' => $filename,
                                'original_name' => $file->getClientOriginalName(),
                                'storage_path' => $storagePath,
                                'public_path' => $publicPath,
                                'storage_exists' => $fileExists,
                                'storage_size' => $fileSize,
                                'public_exists' => file_exists($publicPath),
                                'public_size' => file_exists($publicPath) ? filesize($publicPath) : 0,
                                'db_path' => $supportingDocumentPath
                            ]
                        ]);
                    } else {
                        return response()->json([
                            'error' => 'File upload failed - file not found or empty after storage',
                            'debug_info' => [
                                'storage_path' => $storagePath,
                                'exists' => $fileExists,
                                'size' => $fileSize
                            ]
                        ], 500);
                    }
                } catch (\Exception $e) {
                    Log::error('File storage exception', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    return response()->json([
                        'error' => 'File storage exception: ' . $e->getMessage()
                    ], 500);
                }
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'No file uploaded, but all other checks passed!'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Debug status update error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Exception: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed inquiry information for agency
     */
    public function getAgencyInquiryDetails($inquiryId)
    {
        $agencyId = Session::get('agency_id');
        if (!$agencyId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $inquiry = Inquiry::with([
            'statusHistory' => function($query) {
                $query->orderBy('updated_at', 'desc');
            },
            'assignment' => function($query) use ($agencyId) {
                $query->where('agencyId', $agencyId);
            },
            'assignment.agency',
            'statusHistory.agency'
        ])
        ->whereHas('assignment', function($query) use ($agencyId) {
            $query->where('agencyId', $agencyId)
                  ->where('isRejected', false);
        })
        ->where('inquiryId', $inquiryId)
        ->first();

        if (!$inquiry) {
            return response()->json(['error' => 'Inquiry not found'], 404);
        }

        // Format the response
        $response = [
            'inquiry' => [
                'inquiryId' => $inquiry->inquiryId,
                'title' => $inquiry->title,
                'description' => $inquiry->description,
                'submission_date' => $inquiry->submission_date,
                'status' => $inquiry->getCurrentStatus(),
                'currentAssignment' => $inquiry->currentAssignment(),
                'evidence_type' => $inquiry->evidence_type,
                'evidence_details' => $inquiry->evidence_details
            ],
            'statusHistory' => $inquiry->statusHistory->map(function($history) {
                return [
                    'status' => $history->status,
                    'status_comment' => $history->status_comment,
                    'reviewing_officer' => $history->officer_name,
                    'formatted_comment' => $history->formatted_comment,
                    'updated_at' => $history->updated_at,
                    'agency_name' => $history->agency ? $history->agency->agency_name : 'N/A',
                    'supporting_document' => $history->supporting_document,
                    'document_url' => $history->supporting_document ? asset('storage/' . $history->supporting_document) : null,
                    'document_name' => $history->supporting_document ? basename($history->supporting_document) : null
                ];
            })
        ];

        return response()->json($response);
    }

    /**
     * Get agency dashboard statistics (AJAX endpoint)
     */
    public function getAgencyStats()
    {
        $agencyId = Session::get('agency_id');
        if (!$agencyId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get basic statistics
        $assignedInquiries = Inquiry::whereHas('assignment', function($query) use ($agencyId) {
            $query->where('agencyId', $agencyId)
                  ->where('isRejected', false);
        })->get();

        $stats = [
            'total_assigned' => $assignedInquiries->count(),
            'under_investigation' => $assignedInquiries->filter(function($inquiry) {
                return $inquiry->getCurrentStatus() === 'Under Investigation';
            })->count(),
            'verified_true' => $assignedInquiries->filter(function($inquiry) {
                return $inquiry->getCurrentStatus() === 'True';
            })->count(),
            'identified_fake' => $assignedInquiries->filter(function($inquiry) {
                return $inquiry->getCurrentStatus() === 'Fake';
            })->count(),
            'rejected' => $assignedInquiries->filter(function($inquiry) {
                return $inquiry->getCurrentStatus() === 'Rejected';
            })->count()
        ];

        $stats['resolved'] = $stats['verified_true'] + $stats['identified_fake'] + $stats['rejected'];
        $stats['resolution_rate'] = $stats['total_assigned'] > 0 
            ? round(($stats['resolved'] / $stats['total_assigned']) * 100, 1) 
            : 0;

        return response()->json($stats);
    }

    /**
     * Get full status history for an inquiry (for user dashboard)
     */
    public function getFullInquiryHistory($inquiryId)
    {
        $userId = Session::get('user_id');
        
        // Verify this inquiry belongs to the logged-in user
        $inquiry = Inquiry::where('inquiryId', $inquiryId)
                          ->where('userId', $userId)
                          ->first();
        
        if (!$inquiry) {
            return response()->json(['error' => 'Inquiry not found or access denied'], 404);
        }

        // Get full status history with agency information
        $statusHistory = InquiryStatusHistory::with('agency')
            ->where('inquiryId', $inquiryId)
            ->orderBy('updated_at', 'desc')
            ->get();

        // Format the response
        $formattedHistory = $statusHistory->map(function($history) {
            return [
                'status' => $history->status,
                'status_comment' => $history->status_comment,
                'officer_name' => $history->officer_name,
                'supporting_document' => $history->supporting_document,
                'document_url' => $history->supporting_document ? asset('storage/' . $history->supporting_document) : null,
                'document_name' => $history->supporting_document ? basename($history->supporting_document) : null,
                'agency_name' => $history->agency ? $history->agency->agency_name : 'Unknown Agency',
                'updated_by' => $history->updated_by_agent_id ? 'Agent ID: ' . $history->updated_by_agent_id : 'System',
                'formatted_date' => $history->updated_at->format('M d, Y'),
                'formatted_time' => $history->updated_at->format('h:i A'),
                'updated_at' => $history->updated_at
            ];
        });

        return response()->json($formattedHistory);
    }

    /**
     * Download supporting document securely
     */
    public function downloadSupportingDocument($filename)
    {
        // Sanitize filename to prevent directory traversal
        $filename = basename($filename);
        
        // Try multiple possible file locations
        $possiblePaths = [
            storage_path('app/public/supporting_documents/' . $filename),
            storage_path('app/public/' . $filename),
            public_path('storage/supporting_documents/' . $filename),
            public_path('storage/' . $filename)
        ];
        
        $filePath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path) && filesize($path) > 0) {
                $filePath = $path;
                break;
            }
        }
        
        // If file not found in standard locations, check database for exact path
        if (!$filePath) {
            $historyRecord = InquiryStatusHistory::where('supporting_document', 'LIKE', '%' . $filename)
                                                ->first();
            if ($historyRecord && $historyRecord->supporting_document) {
                $dbPath = storage_path('app/public/' . $historyRecord->supporting_document);
                if (file_exists($dbPath) && filesize($dbPath) > 0) {
                    $filePath = $dbPath;
                }
            }
        }
        
        if (!$filePath) {
            Log::error('Supporting document not found', [
                'filename' => $filename,
                'tried_paths' => $possiblePaths
            ]);
            abort(404, 'File not found or file is empty');
        }
        
        // Log successful file access
        Log::info('Serving supporting document', [
            'filename' => $filename,
            'path' => $filePath,
            'size' => filesize($filePath)
        ]);
        
        // Get file info
        $mimeType = mime_content_type($filePath);
        $originalName = basename($filePath);
        
        // Return file download response
        return response()->download($filePath, $originalName, [
            'Content-Type' => $mimeType,
        ]);
    }
}

