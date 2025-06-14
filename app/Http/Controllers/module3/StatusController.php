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
     */
    public function getInquiries(): JsonResponse
    {
        try {
            // Get active inquiries from the model
            $inquiries = StatusModule::getActiveInquiries();

            // Return JSON response
            return response()->json($inquiries, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching inquiries: ' . $e->getMessage());
            return response()->json([
                'error' => 'Unable to fetch inquiries',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
