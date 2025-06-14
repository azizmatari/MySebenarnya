<?php

namespace App\Http\Controllers\module3;

use App\Http\Controllers\Controller;
use App\Models\module3\StatusModule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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
            Log::error('Error loading inquiry status page: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load inquiry status page.');
        }
    }

    /**
     * Get active inquiries via AJAX
     * This method returns JSON data for the frontend
     * 
     * Default behavior for Module 3:
     * - New inquiries are inserted with NULL final_status in database
     * - System automatically displays NULL status as "Pending"
     * - Only shows inquiries with status: NULL, empty, "Pending", or "Under Investigation"
     */
    public function getInquiries(): JsonResponse
    {
        try {
            // Get active inquiries from the model
            $inquiries = StatusModule::getActiveInquiries();

            // Convert stdClass objects to arrays for proper JSON serialization
            $inquiriesArray = [];
            foreach ($inquiries as $inquiry) {
                $inquiriesArray[] = (array) $inquiry;
            }

            Log::info('Sending inquiries to frontend: ' . count($inquiriesArray) . ' inquiries');

            // Return JSON response with proper structure
            return response()->json($inquiriesArray, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching inquiries: ' . $e->getMessage());
            return response()->json([
                'error' => 'Unable to fetch inquiries',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    //     /**
    //      * Test method to check StatusModule directly
    //      */
    //     public function testStatusModule()
    //     {
    //         try {
    //             $inquiries = StatusModule::getActiveInquiries();

    //             return response()->json([
    //                 'success' => true,
    //                 'count' => count($inquiries),
    //                 'inquiries' => $inquiries
    //             ]);
    //         } catch (\Exception $e) {
    //             Log::error('Error testing StatusModule: ' . $e->getMessage());
    //             return response()->json([
    //                 'error' => $e->getMessage(),
    //                 'trace' => $e->getTraceAsString()
    //             ], 500);
    //         }
    //     }
}
