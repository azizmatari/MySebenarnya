<?php

namespace App\Http\Controllers\module2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InquiryController extends Controller
{
    /**
     * Display the inquiry creation form
     */
    public function create()
    {
        return view('module2.inquiry.UserCreateInquiry');
    }
    /**
     * Store a new inquiry with evidence
     */
    public function store(Request $request)
    {        // Validate the request
        $validated = $request->validate([
            'news_title' => 'required|string|max:30',
            'detailed_info' => 'required|string|max:250',
            'evidence_files.*' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,txt,mp4,mp3',
            'evidence_links' => 'nullable|url|max:500',
            'terms' => 'required|accepted',
        ]);

        try {            // Create the inquiry record using your actual database table
            $inquiryId = DB::table('inquiry')->insertGetId([
                'title' => $validated['news_title'],
                'description' => $validated['detailed_info'],
                'userId' => session('user_id', 1), // Using userId to match your DB
                'final_status' => null, // New inquiries start as null (displayed as "Pending" in module 3)
                'submission_date' => now()->toDateString(), // date format for your DB
                'evidenceUrl' => $validated['evidence_links'] ?? null, // Store evidence links
                // Removed created_at and updated_at - they don't exist in the migration
            ]);

            // Handle file uploads - store file paths in evidenceFileUrl
            if ($request->hasFile('evidence_files')) {
                $filePaths = [];
                foreach ($request->file('evidence_files') as $file) {
                    if ($file->isValid()) {
                        // Generate unique filename
                        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

                        // Store file in public/evidence directory
                        $path = $file->storeAs('evidence', $filename, 'public');
                        $filePaths[] = $path;
                    }
                }

                // Update inquiry with evidence file paths (comma-separated)
                DB::table('inquiry')
                    ->where('inquiryId', $inquiryId)
                    ->update([
                        'evidenceFileUrl' => implode(',', $filePaths)
                    ]);
            }

            // NOTE: Do NOT create assignment or status history records here!
            // New inquiries should remain unassigned (null status = "Pending" in module 3)
            // Assignment and status updates should only happen when MCMC processes the inquiry

            // Redirect to success page
            return redirect()->route('inquiry.success')->with([
                'inquiry_id' => $inquiryId,
                'title' => $validated['news_title']
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error storing inquiry: ' . $e->getMessage());

            // Return with error message instead of fake success
            return redirect()->back()
                ->withInput()
                ->withErrors(['database' => 'Unable to submit inquiry. Please try again later.']);
        }
    }

    /**
     * Display success page after inquiry submission
     */
    public function success()
    {
        if (!session()->has('inquiry_id')) {
            return redirect()->route('module2.inquiry.UserCreateInquiry');
        }

        return view('module2.inquiry.UserInquirySuccess');
    }
    /**
     * Display user's inquiry history from database
     */    public function index()
    {
        $userId = session('user_id', 1); // Default to 1 for testing

        // Debug: Let's see what userId we're using
        \Log::info('UserInquiryList - Current userId: ' . $userId);

        // TEMP DEBUG: Check if we have any inquiries at all in the database
        try {
            $totalInquiries = DB::table('inquiry')->count();
            \Log::info('Total inquiries in database: ' . $totalInquiries);

            $userInquiries = DB::table('inquiry')->where('userId', $userId)->count();
            \Log::info('Inquiries for user ' . $userId . ': ' . $userInquiries);

            // If no inquiries for this user, show all inquiries for testing
            if ($userInquiries == 0) {
                \Log::info('No inquiries for user ' . $userId . ', showing all inquiries for testing');
                $testInquiries = DB::table('inquiry')->limit(5)->get();
                \Log::info('Sample inquiries: ' . json_encode($testInquiries));
            }
        } catch (\Exception $e) {
            \Log::error('Error checking database: ' . $e->getMessage());
        }

        try { // Fetch user's inquiries from database with assignments and status history
            $inquiries = DB::table('inquiry as i')
                ->leftJoin('inquiryassignment as ia', 'i.inquiryId', '=', 'ia.inquiryId')
                ->where('i.userId', $userId) // Filter by user ID
                ->select(
                    'i.inquiryId as id',
                    'i.title',
                    'i.description',
                    'i.final_status',
                    'i.submission_date',
                    'i.evidenceFileUrl',
                    'i.evidenceUrl',
                    'ia.mcmcComments as admin_response', // Use correct field from migration
                    'ia.isRejected'
                    // Removed 'created_at' and 'rejectedReason' - don't exist in migration
                )
                ->orderBy('i.submission_date', 'desc') // Use submission_date instead of created_at
                ->get()
                ->map(function ($inquiry) {
                    // Map database status to display status
                    $status = 'under_investigation';
                    $result = null;

                    if ($inquiry->final_status) {
                        switch ($inquiry->final_status) {
                            case 'True':
                                $status = 'completed';
                                $result = 'true';
                                break;
                            case 'Fake':
                                $status = 'completed';
                                $result = 'false';
                                break;
                            case 'Rejected':
                                $status = 'rejected';
                                $result = null;
                                break;
                            default:
                                $status = 'under_investigation';
                                break;
                        }
                    }

                    // Process evidence files
                    $evidence_files = [];
                    if ($inquiry->evidenceFileUrl) {
                        $filePaths = explode(',', $inquiry->evidenceFileUrl);
                        foreach ($filePaths as $filePath) {
                            if (trim($filePath)) {
                                $fileName = basename(trim($filePath));
                                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                                $fileSize = 'Unknown';

                                // Try to get actual file size
                                $fullPath = storage_path('app/public/' . trim($filePath));
                                if (file_exists($fullPath)) {
                                    $sizeBytes = filesize($fullPath);
                                    $fileSize = $this->formatFileSize($sizeBytes);
                                }

                                $evidence_files[] = [
                                    'name' => $fileName,
                                    'type' => $this->getFileType($extension),
                                    'size' => $fileSize
                                ];
                            }
                        }
                    }

                    // Get status history for this inquiry
                    $status_history = [];
                    try {
                        $status_history = DB::table('inquirystatushistory')
                            ->where('inquiryId', $inquiry->id)
                            ->select('status', 'status_comment') // Remove created_at since it doesn't exist
                            ->get()
                            ->map(function ($history) {
                                return [
                                    'status' => strtolower(str_replace(' ', '_', $history->status)),
                                    'date' => now(), // Use current date since no timestamps
                                    'description' => $history->status_comment ?? $this->getStatusDescription($history->status)
                                ];
                            })
                            ->toArray();
                    } catch (\Exception $e) {
                        // If status history table doesn't exist, create default entry
                        $status_history = [
                            [
                                'status' => 'under_investigation',
                                'date' => $inquiry->submission_date, // Use submission_date
                                'description' => 'Your news verification request was received and is under investigation'
                            ]
                        ];
                    }

                    // If no status history exists, create default entry
                    if (empty($status_history)) {
                        $status_history = [
                            [
                                'status' => 'under_investigation',
                                'date' => $inquiry->submission_date, // Use submission_date
                                'description' => 'Your news verification request was received and is under investigation'
                            ]
                        ];
                    }
                    return (object)[
                        'id' => $inquiry->id,
                        'title' => $inquiry->title,
                        'description' => $inquiry->description,
                        'status' => $status,
                        'submission_date' => $inquiry->submission_date,
                        'created_at' => $inquiry->submission_date, // Use submission_date since created_at doesn't exist
                        'result' => $result,
                        'admin_response' => $inquiry->admin_response ?? ($inquiry->isRejected ? 'Request was rejected' : null),
                        'evidence_files' => $evidence_files,
                        'evidence_url' => $inquiry->evidenceUrl,
                        'status_history' => $status_history
                    ];
                });
        } catch (\Exception $e) {
            // Log the exception for debugging
            \Log::error('Error fetching inquiries: ' . $e->getMessage());

            // Return empty collection instead of sample data
            $inquiries = collect([]);
        }

        // Debug: Log how many inquiries we found
        \Log::info('UserInquiryList - Found ' . $inquiries->count() . ' inquiries');

        // Convert to array for proper JSON serialization in view
        $inquiriesForJs = $inquiries->values()->toArray();
        \Log::info('UserInquiryList - Converted to array with ' . count($inquiriesForJs) . ' items');

        // Pass the collection for @if checks in view, but also pass array for JS
        return view('module2.inquiry.UserInquiryList', [
            'inquiries' => $inquiries,
            'inquiriesForJs' => $inquiriesForJs
        ]);
    }

    /**
     * Helper function to format file size
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }
        return $bytes;
    }

    /**
     * Helper function to get file type from extension
     */
    private function getFileType($extension)
    {
        $extension = strtolower($extension);
        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $documentTypes = ['pdf', 'doc', 'docx', 'txt'];
        $videoTypes = ['mp4', 'avi', 'mov', 'wmv'];
        $audioTypes = ['mp3', 'wav', 'aac'];

        if (in_array($extension, $imageTypes)) {
            return 'image';
        } elseif (in_array($extension, $documentTypes)) {
            return 'document';
        } elseif (in_array($extension, $videoTypes)) {
            return 'video';
        } elseif (in_array($extension, $audioTypes)) {
            return 'audio';
        } else {
            return 'file';
        }
    }

    /**
     * Helper function to get status description
     */
    private function getStatusDescription($status)
    {
        switch ($status) {
            case 'Under Investigation':
                return 'Your news verification request is currently under investigation';
            case 'True':
                return 'The information has been verified as accurate';
            case 'Fake':
                return 'The information has been identified as false or misleading';
            case 'Rejected':
                return 'Unable to verify due to insufficient evidence or unclear information';
            default:
                return 'Status updated';
        }
    }
    /**
     * Display public inquiries with anonymized user data from database
     * Following MVC pattern: Controller handles business logic, View handles presentation
     */
    public function publicInquiries()
    {
        // Debug: Log method entry
        \Log::info('PublicInquiries method called');

        try {
            // Debug: Test database connection and check for data
            $totalInquiries = DB::table('inquiry')->count();
            $completedInquiries = DB::table('inquiry')->whereIn('final_status', ['True', 'Fake'])->count();

            \Log::info("Database check - Total inquiries: $totalInquiries, Completed: $completedInquiries");

            // If no completed inquiries, log sample data
            if ($completedInquiries === 0) {
                $sampleInquiries = DB::table('inquiry')->take(3)->get();
                \Log::info('No completed inquiries found. Sample data: ' . json_encode($sampleInquiries));
            }

            // Fetch only completed inquiries (True/Fake status) from database
            $rawInquiries = DB::table('inquiry as i')
                ->leftJoin('inquiryassignment as ia', 'i.inquiryId', '=', 'ia.inquiryId')
                ->leftJoin('publicuser as u', 'i.userId', '=', 'u.userId')
                ->whereIn('i.final_status', ['True', 'Fake']) // Only show completed investigations
                ->select(
                    'i.inquiryId as id',
                    'i.title',
                    'i.description',
                    'i.final_status',
                    'i.submission_date',
                    'i.evidenceFileUrl',
                    'i.evidenceUrl',
                    'ia.mcmcComments as admin_response', // Use correct field name from migration
                    'u.userName as username' // Fixed: use userName not userUsername
                )
                ->orderBy('i.submission_date', 'desc') // Use submission_date since updated_at doesn't exist
                ->get();

            \Log::info('Raw inquiries count: ' . $rawInquiries->count());
            \Log::info('Raw inquiries sample: ' . json_encode($rawInquiries->take(1)));

            // Process the raw data into a proper format for the view
            $publicInquiries = $rawInquiries->map(function ($inquiry) {
                // Process evidence files
                $evidence_files = $this->processEvidenceFiles($inquiry->evidenceFileUrl);

                // Anonymize username for public display
                $anonymized_user = $this->anonymizeUsername($inquiry->username);

                // Prepare clean data object for view
                return (object)[
                    'id' => $inquiry->id,
                    'title' => $inquiry->title,
                    'description' => $inquiry->description,
                    'status' => 'completed', // All public inquiries are completed
                    'submission_date' => $inquiry->submission_date,
                    'completion_date' => $inquiry->submission_date, // Use submission_date since updated_at doesn't exist
                    'result' => $this->mapFinalStatusToResult($inquiry->final_status),
                    'admin_response' => $inquiry->admin_response ?? $this->getDefaultResponse($inquiry->final_status),
                    'anonymized_user' => $anonymized_user,
                    'evidence_count' => count($evidence_files),
                    'evidence_files' => $evidence_files,
                    'evidence_url' => $inquiry->evidenceUrl // Include evidence links
                ];
            });

            // Log for debugging
            \Log::info('Public Inquiries - Processed ' . $publicInquiries->count() . ' completed inquiries');
        } catch (\Exception $e) {
            // Log the exception for debugging
            \Log::error('Error fetching public inquiries: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            // Return empty collection - no sample data
            $publicInquiries = collect([]);
        }

        // Debug: Log before view return
        \Log::info('Returning view with ' . $publicInquiries->count() . ' inquiries');

        // Return view with data (MVC pattern)
        return view('module2.inquiry.PublicInquiriesList', compact('publicInquiries'));
    }

    /**
     * Process evidence files from database string
     * Helper method following MVC separation of concerns
     */
    private function processEvidenceFiles($evidenceFileUrl)
    {
        $evidence_files = [];

        if ($evidenceFileUrl) {
            $filePaths = explode(',', $evidenceFileUrl);
            foreach ($filePaths as $filePath) {
                $filePath = trim($filePath);
                if (!empty($filePath)) {
                    $fileName = basename($filePath);
                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);

                    // Get real file size
                    $fileSize = 'Unknown';
                    $fullPath = storage_path('app/public/' . $filePath);
                    if (file_exists($fullPath)) {
                        $sizeBytes = filesize($fullPath);
                        $fileSize = $this->formatFileSize($sizeBytes);
                    }

                    $evidence_files[] = [
                        'name' => $fileName,
                        'type' => $this->getFileType($extension),
                        'size' => $fileSize,
                        'url' => asset('storage/' . $filePath),
                        'exists' => file_exists($fullPath)
                    ];
                }
            }
        }

        return $evidence_files;
    }

    /**
     * Anonymize username for public display
     * Following privacy best practices
     */
    private function anonymizeUsername($username)
    {
        if (!$username) {
            return 'Anonymous User';
        }

        // Create consistent anonymized identifier
        $hash = substr(md5($username), 0, 4);
        return 'User***' . strtoupper($hash);
    }

    /**
     * Map database final_status to view result format
     */
    private function mapFinalStatusToResult($finalStatus)
    {
        return $finalStatus === 'True' ? 'true' : 'false';
    }

    /**
     * Helper function to get default admin response based on status
     */
    private function getDefaultResponse($status)
    {
        switch ($status) {
            case 'True':
                return 'This information has been verified and confirmed as accurate.';
            case 'Fake':
                return 'This information has been investigated and found to be false or misleading.';
            default:
                return 'Investigation completed.';
        }
    }

    /**
     * Test method to verify controller accessibility
     */
    public function testPublicInquiries()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'InquiryController is accessible',
            'timestamp' => now()
        ]);
    }

    /**
     * Download or view evidence file
     */
    public function getEvidenceFile($inquiryId, $filename)
    {
        try {
            // Get the inquiry and verify user ownership
            $userId = session('user_id', 1);
            $inquiry = DB::table('inquiry')
                ->where('inquiryId', $inquiryId)
                ->where('userId', $userId) // Ensure user can only access their own files
                ->first();

            if (!$inquiry) {
                abort(404, 'Inquiry not found or access denied');
            }

            // Check if the file exists in the inquiry's evidence files
            $evidenceFiles = explode(',', $inquiry->evidenceFileUrl);
            $requestedFile = null;

            foreach ($evidenceFiles as $filePath) {
                $filePath = trim($filePath);
                if (basename($filePath) === $filename) {
                    $requestedFile = $filePath;
                    break;
                }
            }

            if (!$requestedFile) {
                abort(404, 'Evidence file not found');
            }

            // Build the full file path
            $fullPath = storage_path('app/public/' . $requestedFile);

            if (!file_exists($fullPath)) {
                abort(404, 'Evidence file does not exist');
            }

            // Return the file
            return response()->file($fullPath);
        } catch (\Exception $e) {
            \Log::error('Error accessing evidence file: ' . $e->getMessage());
            abort(500, 'Unable to access evidence file');
        }
    }

    /**
     * Serve evidence file for user's own inquiry
     */
    public function viewEvidenceFile($inquiryId, $filename)
    {
        try {
            $userId = session('user_id', 1);

            // Get the inquiry and verify user ownership
            $inquiry = DB::table('inquiry')
                ->where('inquiryId', $inquiryId)
                ->where('userId', $userId)
                ->first();

            if (!$inquiry || !$inquiry->evidenceFileUrl) {
                abort(404, 'Evidence file not found');
            }

            // Check if the requested file exists in the inquiry's evidence files
            $evidenceFiles = explode(',', $inquiry->evidenceFileUrl);
            $targetFile = null;

            foreach ($evidenceFiles as $filePath) {
                $filePath = trim($filePath);
                if (basename($filePath) === $filename) {
                    $targetFile = $filePath;
                    break;
                }
            }

            if (!$targetFile) {
                abort(404, 'File not found in inquiry evidence');
            }

            $fullPath = storage_path('app/public/' . $targetFile);

            if (!file_exists($fullPath)) {
                abort(404, 'Evidence file does not exist on server');
            }

            // Return the file
            return response()->file($fullPath);
        } catch (\Exception $e) {
            \Log::error('Error viewing evidence file: ' . $e->getMessage());
            abort(500, 'Unable to access evidence file');
        }
    }

    /**
     * Debug method to test data retrieval
     */
    public function debugPublicInquiries()
    {
        try {
            // Test basic database connection
            $inquiryCount = DB::table('inquiry')->count();
            $completedCount = DB::table('inquiry')->whereIn('final_status', ['True', 'Fake'])->count();

            // Test query
            $rawData = DB::table('inquiry as i')
                ->leftJoin('inquiryassignment as ia', 'i.inquiryId', '=', 'ia.inquiryId')
                ->leftJoin('publicuser as u', 'i.userId', '=', 'u.userId')
                ->whereIn('i.final_status', ['True', 'Fake'])
                ->select(
                    'i.inquiryId as id',
                    'i.title',
                    'i.description',
                    'i.final_status',
                    'i.submission_date',
                    'i.evidenceFileUrl',
                    'i.evidenceUrl',
                    'ia.mcmcComments as admin_response',
                    'u.userName as username'
                )
                ->get();

            return response()->json([
                'total_inquiries' => $inquiryCount,
                'completed_inquiries' => $completedCount,
                'query_result_count' => $rawData->count(),
                'sample_data' => $rawData->take(2),
                'all_inquiries_sample' => DB::table('inquiry')->take(3)->get()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
