<?php

namespace App\Http\Controllers\module4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\module4\Inquiry;
use App\Models\SharedModels\InquiryStatusHistory;
use Illuminate\Support\Facades\Session;

class InquiryTrackingController extends Controller
{
    /**
     * Display the user's inquiry tracking dashboard
     */
    public function userInquiries(Request $request)
    {
        // Get user ID from session (you might need to adjust this based on your auth system)
        $userId = Session::get('user_id', 1); // Default to 1 for testing
        
        // Get filter parameters
        $status = $request->get('status');
        $search = $request->get('search');
        $sortBy = $request->get('sort', 'submission_date');
        $sortDirection = $request->get('direction', 'desc');

        // Build query
        $query = Inquiry::forUser($userId)
                       ->with(['statusHistory.agency', 'assignment.agency']);

        // Apply filters
        if ($status && $status !== 'all') {
            $query->byStatus($status);
        }

        if ($search) {
            $query->search($search);
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortDirection);

        // Get inquiries with pagination
        $inquiries = $query->paginate(10);

        // Get status counts for the filter tabs
        $statusCounts = [
            'all' => Inquiry::forUser($userId)->count(),
            'Under Investigation' => Inquiry::forUser($userId)->byStatus('Under Investigation')->count(),
            'True' => Inquiry::forUser($userId)->byStatus('True')->count(),
            'Fake' => Inquiry::forUser($userId)->byStatus('Fake')->count(),
            'Rejected' => Inquiry::forUser($userId)->byStatus('Rejected')->count(),
        ];

        return view('module4.user-inquiries', compact(
            'inquiries', 
            'statusCounts', 
            'status', 
            'search', 
            'sortBy', 
            'sortDirection'
        ));
    }

    /**
     * Show detailed view of a specific inquiry
     */
    public function showInquiry($inquiryId)
    {
        $userId = Session::get('user_id', 1); // Default to 1 for testing
          $inquiry = Inquiry::with([
            'statusHistory.agency',
            'assignment.agency'
        ])->where('inquiryId', $inquiryId)
          ->where('userId', $userId)
          ->firstOrFail();

        return view('module4.inquiry-detail', compact('inquiry'));
    }

    /**
     * Get real-time status updates via AJAX
     */
    public function getStatusUpdates($inquiryId)
    {
        $userId = Session::get('user_id', 1);
        
        $inquiry = Inquiry::with(['statusHistory.agency'])
                         ->where('inquiryId', $inquiryId)
                         ->where('userId', $userId)
                         ->firstOrFail();

        return response()->json([
            'status' => $inquiry->display_status,
            'status_color' => $inquiry->status_color,
            'latest_update' => $inquiry->latestStatus(),
            'history' => $inquiry->statusHistory->map(function($history) {
                return [
                    'status' => $history->status,
                    'comment' => $history->status_comment,
                    'date' => $history->formatted_date,
                    'agency' => $history->agency->name ?? 'Unknown Agency'
                ];
            })
        ]);
    }

    /**
     * Export inquiry data (for future implementation)
     */
    public function exportInquiries(Request $request)
    {
        // This can be implemented later for PDF/Excel export
        return response()->json(['message' => 'Export functionality coming soon']);
    }
}
