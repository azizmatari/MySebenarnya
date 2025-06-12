<?php

namespace App\Http\Controllers\module3;

use App\Http\Controllers\Controller;
use App\Models\module3\StatusModule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class StatusController extends Controller
{
    /**
     * Display the inquiry status page
     * 
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        try {
            // Log page access for analytics
            Log::info('Public user accessed inquiry status page', [
                'timestamp' => now(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Return the Blade view (pure HTML/CSS/JS)
            return view('module3.PublicUser_inquiry_status');
        } catch (\Exception $e) {
            Log::error('Error loading inquiry status page: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Unable to load inquiry status page.');
        }
    }

    /**
     * Get active inquiries via AJAX
     * 
     * This method handles all the business logic for fetching inquiries
     * and returns properly formatted JSON data for the frontend
     *
     * @return JsonResponse
     */
    public function getInquiries(): JsonResponse
    {
        try {            // Temporarily disable caching to get fresh data for debugging
            $inquiries = StatusModule::getActiveInquiries();

            // Log raw data for debugging
            Log::info('Raw inquiries from database:', [
                'count' => count($inquiries),
                'data' => $inquiries
            ]);

            // Validate and format the data
            $formattedInquiries = $this->formatInquiriesForView($inquiries);

            // Log successful retrieval
            Log::info('Successfully retrieved active inquiries', [
                'count' => count($formattedInquiries),
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'inquiries' => $formattedInquiries,
                'count' => count($formattedInquiries),
                'timestamp' => now()->toISOString()
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching inquiries: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Unable to fetch inquiries',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Get inquiry statistics via AJAX
     *
     * @return JsonResponse
     */
    public function getStatistics(): JsonResponse
    {
        try {
            // Check cache first (cache for 10 minutes)
            $cacheKey = 'inquiry_statistics';
            $statistics = Cache::remember($cacheKey, 600, function () {
                return StatusModule::getInquiryStatistics();
            });

            // Add additional calculated statistics
            $enhancedStats = $this->enhanceStatistics($statistics);

            Log::info('Successfully retrieved inquiry statistics', [
                'stats' => $enhancedStats,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'statistics' => $enhancedStats,
                'timestamp' => now()->toISOString()
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching statistics: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Unable to fetch statistics',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Get specific inquiry details
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getInquiryDetails($id): JsonResponse
    {
        try {
            // Validate inquiry ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid inquiry ID'
                ], 400);
            }

            // Get inquiry details from model
            $inquiry = StatusModule::getInquiryById($id);

            if (!$inquiry) {
                return response()->json([
                    'success' => false,
                    'error' => 'Inquiry not found'
                ], 404);
            }

            // Format inquiry details
            $formattedInquiry = $this->formatInquiryDetails($inquiry);

            return response()->json([
                'success' => true,
                'inquiry' => $formattedInquiry,
                'timestamp' => now()->toISOString()
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching inquiry details: ' . $e->getMessage(), [
                'inquiry_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Unable to fetch inquiry details',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Clear cache and refresh data
     *
     * @return JsonResponse
     */
    public function refreshData(): JsonResponse
    {
        try {
            // Clear relevant caches
            Cache::forget('active_inquiries_list');
            Cache::forget('inquiry_statistics');

            Log::info('Inquiry data cache cleared by user request');

            return response()->json([
                'success' => true,
                'message' => 'Data refreshed successfully',
                'timestamp' => now()->toISOString()
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error refreshing data: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Unable to refresh data',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Format inquiries data for the view
     *
     * @param array $inquiries
     * @return array
     */    private function formatInquiriesForView(array $inquiries): array
    {
        return array_map(function ($inquiry) {
            return [
                'inquiryId' => (int) $inquiry->inquiryId,
                'title' => $this->sanitizeString($inquiry->title),
                'description' => $this->sanitizeString($inquiry->description),
                'final_status' => $this->sanitizeString($inquiry->final_status),
                'submission_date' => $inquiry->submission_date,
                'agency_name' => $this->sanitizeString($inquiry->agency_name),
                'applicant_name' => $this->sanitizeString($inquiry->applicant_name),
                'evidence_url' => $inquiry->evidence_url ?? null,
                // Add formatted date for display
                'formatted_date' => \Carbon\Carbon::parse($inquiry->submission_date)->format('M d, Y'),
                // Add time ago format
                'time_ago' => \Carbon\Carbon::parse($inquiry->submission_date)->diffForHumans(),
            ];
        }, $inquiries);
    }

    /**
     * Format inquiry details for detailed view
     *
     * @param object $inquiry
     * @return array
     */
    private function formatInquiryDetails($inquiry): array
    {
        return [
            'inquiryId' => (int) $inquiry->inquiryId,
            'title' => $this->sanitizeString($inquiry->title),
            'description' => $this->sanitizeString($inquiry->description),
            'final_status' => $this->sanitizeString($inquiry->final_status),
            'submission_date' => $inquiry->submission_date,
            'agency_name' => $this->sanitizeString($inquiry->agency_name),
            'agencyId' => (int) $inquiry->agencyId,
            'applicant_name' => $this->sanitizeString($inquiry->applicant_name),
            'applicant_email' => $this->sanitizeString($inquiry->applicant_email ?? ''),
            'applicant_contact' => $this->sanitizeString($inquiry->applicant_contact ?? ''),
            'formatted_date' => \Carbon\Carbon::parse($inquiry->submission_date)->format('M d, Y'),
            'time_ago' => \Carbon\Carbon::parse($inquiry->submission_date)->diffForHumans(),
        ];
    }

    /**
     * Enhance statistics with additional calculations
     *
     * @param array $statistics
     * @return array
     */
    private function enhanceStatistics(array $statistics): array
    {
        return [
            'active_inquiries' => (int) ($statistics['active_inquiries'] ?? 0),
            'agencies_involved' => (int) ($statistics['agencies_involved'] ?? 0),
            'this_week' => (int) ($statistics['this_week'] ?? 0),
            // Add percentage of weekly inquiries
            'weekly_percentage' => $this->calculateWeeklyPercentage($statistics),
            // Add average per agency
            'avg_per_agency' => $this->calculateAveragePerAgency($statistics),
            'last_updated' => now()->toISOString(),
        ];
    }

    /**
     * Calculate weekly percentage
     *
     * @param array $statistics
     * @return float
     */
    private function calculateWeeklyPercentage(array $statistics): float
    {
        $total = (int) ($statistics['active_inquiries'] ?? 0);
        $weekly = (int) ($statistics['this_week'] ?? 0);

        return $total > 0 ? round(($weekly / $total) * 100, 1) : 0.0;
    }

    /**
     * Calculate average inquiries per agency
     *
     * @param array $statistics
     * @return float
     */
    private function calculateAveragePerAgency(array $statistics): float
    {
        $total = (int) ($statistics['active_inquiries'] ?? 0);
        $agencies = (int) ($statistics['agencies_involved'] ?? 0);

        return $agencies > 0 ? round($total / $agencies, 1) : 0.0;
    }

    /**
     * Sanitize string for safe output
     *
     * @param string $string
     * @return string
     */
    private function sanitizeString(?string $string): string
    {
        if (is_null($string)) {
            return '';
        }

        // Remove any malicious content and trim
        return trim(htmlspecialchars($string, ENT_QUOTES, 'UTF-8'));
    }
}
