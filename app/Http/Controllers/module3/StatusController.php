<?php

namespace App\Http\Controllers\module3;

use App\Http\Controllers\Controller;
use App\Models\module3\StatusModule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StatusController extends Controller
{
    /**
     * Display the inquiry status page
     */
    public function index()
    {
        try {
            // Return the view (this will be accessed via route)
            return view('module3.PublicUser_inquiry_status');
        } catch (\Exception $e) {
            \Log::error('Error loading inquiry status page: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load inquiry status page.');
        }
    }

    /**
     * Get active inquiries via AJAX
     * This method returns JSON data for the frontend
     */
    public function getInquiries(): JsonResponse
    {
        try {
            // Get active inquiries from the model
            $inquiries = StatusModule::getActiveInquiries();

            // Return JSON response
            return response()->json($inquiries, 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching inquiries: ' . $e->getMessage());
            return response()->json([
                'error' => 'Unable to fetch inquiries',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get inquiry statistics via AJAX
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $statistics = StatusModule::getInquiryStatistics();

            return response()->json($statistics, 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching statistics: ' . $e->getMessage());
            return response()->json([
                'error' => 'Unable to fetch statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get inquiry details by ID
     */
    public function getInquiryDetails(Request $request, $inquiryId): JsonResponse
    {
        try {
            // Validate inquiry ID
            if (!is_numeric($inquiryId) || $inquiryId <= 0) {
                return response()->json([
                    'error' => 'Invalid inquiry ID'
                ], 400);
            }

            // Get inquiry details
            $inquiry = StatusModule::getInquiryById($inquiryId);

            if (!$inquiry) {
                return response()->json([
                    'error' => 'Inquiry not found'
                ], 404);
            }

            return response()->json($inquiry, 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching inquiry details: ' . $e->getMessage());
            return response()->json([
                'error' => 'Unable to fetch inquiry details',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get inquiries by agency
     */
    public function getInquiriesByAgency(Request $request, $agencyId): JsonResponse
    {
        try {
            // Validate agency ID
            if (!is_numeric($agencyId) || $agencyId <= 0) {
                return response()->json([
                    'error' => 'Invalid agency ID'
                ], 400);
            }

            // Get inquiries by agency
            $inquiries = StatusModule::getInquiriesByAgency($agencyId);

            return response()->json($inquiries, 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching inquiries by agency: ' . $e->getMessage());
            return response()->json([
                'error' => 'Unable to fetch inquiries by agency',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if inquiry is still active
     */
    public function checkInquiryStatus(Request $request, $inquiryId): JsonResponse
    {
        try {
            $isActive = StatusModule::isInquiryActive($inquiryId);

            return response()->json([
                'inquiry_id' => $inquiryId,
                'is_active' => $isActive,
                'status' => $isActive ? 'Under Investigation' : 'Processed'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error checking inquiry status: ' . $e->getMessage());
            return response()->json([
                'error' => 'Unable to check inquiry status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get inquiry count
     */
    public function getInquiryCount(): JsonResponse
    {
        try {
            $count = StatusModule::getInquiryCountByStatus('Under Investigation');

            return response()->json([
                'count' => $count,
                'status' => 'Under Investigation'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching inquiry count: ' . $e->getMessage());
            return response()->json([
                'error' => 'Unable to fetch inquiry count',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh inquiries (force refresh)
     */
    public function refreshInquiries(): JsonResponse
    {
        try {
            // Clear any cache if you're using it
            // Cache::forget('active_inquiries');

            // Get fresh data
            $inquiries = StatusModule::getActiveInquiries();
            $statistics = StatusModule::getInquiryStatistics();

            return response()->json([
                'inquiries' => $inquiries,
                'statistics' => $statistics,
                'timestamp' => now()->toDateTimeString()
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error refreshing inquiries: ' . $e->getMessage());
            return response()->json([
                'error' => 'Unable to refresh inquiries',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
