<?php

namespace App\Http\Controllers\module2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\module2\Inquiry;
use App\Models\module2\InquiryAssignment;
use App\Models\module2\InquiryStatusHistory;
use App\Models\module1\MCMC;
use App\Models\module1\Agency;

class MCMCController extends Controller
{
    /**
     * Display list of new inquiries for MCMC staff review
     * Following MVC pattern: Controller handles business logic
     */
    public function viewNewInquiries()
    {
        try {
            // Get current MCMC staff ID from session (default to 1 for testing)
            $mcmcId = session('mcmc_id', 1);
            
            Log::info("MCMC viewing new inquiries - Staff ID: $mcmcId");            // Fetch new inquiries (null final_status or Under Investigation) that need MCMC review
            // Exclude rejected inquiries from the list
            $newInquiries = DB::table('inquiry as i')
                ->leftJoin('publicuser as u', 'i.userId', '=', 'u.userId')
                ->leftJoin('inquiryassignment as ia', 'i.inquiryId', '=', 'ia.inquiryId')
                ->where(function($query) {
                    $query->whereNull('i.final_status')
                          ->orWhere('i.final_status', 'Under Investigation');
                })
                ->where(function($query) {
                    $query->whereNull('ia.isRejected')
                          ->orWhere('ia.isRejected', false);
                })
                ->select(
                    'i.inquiryId as id',
                    'i.title',
                    'i.description',
                    'i.final_status',
                    'i.submission_date',
                    'i.evidenceFileUrl',
                    'i.evidenceUrl',
                    'u.userName as user_name',
                    'u.userEmail as user_email',
                    'u.userContact_number as user_contact',
                    'ia.assignmentId',
                    'ia.agencyId',
                    'ia.mcmcComments',
                    'ia.isRejected'
                )
                ->orderBy('i.submission_date', 'asc') // Oldest first for processing
                ->get();

            // Process inquiries data for view
            $processedInquiries = $newInquiries->map(function ($inquiry) {                return (object)[
                    'id' => $inquiry->id,
                    'title' => $inquiry->title,
                    'description' => $inquiry->description,
                    'status' => $inquiry->final_status ?? 'Pending', // Show as Pending if null
                    'submission_date' => $inquiry->submission_date,
                    'user_info' => (object)[
                        'name' => $inquiry->user_name ?? 'Anonymous',
                        'email' => $inquiry->user_email ?? 'N/A',
                        'contact' => $inquiry->user_contact ?? 'N/A'
                    ],
                    'evidence_files' => $this->processEvidenceFiles($inquiry->evidenceFileUrl),
                    'evidence_url' => $inquiry->evidenceUrl,
                    'evidence_count' => $this->countEvidence($inquiry->evidenceFileUrl, $inquiry->evidenceUrl),
                    'is_assigned' => !is_null($inquiry->assignmentId),
                    'assigned_agency' => $inquiry->agencyId,
                    'mcmc_comments' => $inquiry->mcmcComments,
                    'is_rejected' => $inquiry->isRejected ?? false,
                    'is_pending' => is_null($inquiry->final_status) // New field for pending status
                ];
            });            // Get available agencies for assignment
            $agencies = DB::table('agency')
                ->select('agencyId', 'agency_name')
                ->get();

            Log::info("MCMC found " . $processedInquiries->count() . " new inquiries");

            return view('module2.MCMC.MCMCInquiryList', [
                'inquiries' => $processedInquiries,
                'agencies' => $agencies
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching new inquiries for MCMC: ' . $e->getMessage());
            
            return view('module2.MCMC.MCMCInquiryList', [
                'inquiries' => collect([]),
                'agencies' => collect([]),
                'error' => 'Unable to fetch inquiries. Please try again.'
            ]);
        }
    }

    /**
     * Validate and filter inquiry (approve for agency assignment)
     * Following MVC pattern: Controller handles business logic
     */
    public function validateInquiry(Request $request)
    {
        $request->validate([
            'inquiry_id' => 'required|integer|exists:inquiry,inquiryId',
            'agency_id' => 'required|integer|exists:agency,agencyId',
            'mcmc_comments' => 'required|string|max:500'
        ]);

        try {
            $inquiryId = $request->inquiry_id;
            $agencyId = $request->agency_id;
            $mcmcComments = $request->mcmc_comments;
            $mcmcId = session('mcmc_id', 1);

            DB::beginTransaction();

            // Check if assignment already exists
            $existingAssignment = DB::table('inquiryassignment')
                ->where('inquiryId', $inquiryId)
                ->first();

            if ($existingAssignment) {
                // Update existing assignment
                DB::table('inquiryassignment')
                    ->where('inquiryId', $inquiryId)
                    ->update([
                        'agencyId' => $agencyId,
                        'mcmcComments' => $mcmcComments,
                        'isRejected' => false,
                        'mcmcId' => $mcmcId,
                        'assignDate' => now()->format('Y-m-d')
                    ]);
            } else {
                // Create new assignment
                DB::table('inquiryassignment')->insert([
                    'inquiryId' => $inquiryId,
                    'agencyId' => $agencyId,
                    'mcmcComments' => $mcmcComments,
                    'isRejected' => false,
                    'mcmcId' => $mcmcId,
                    'assignDate' => now()->format('Y-m-d')
                ]);
            }

            // Add status history entry
            DB::table('inquirystatushistory')->insert([
                'inquiryId' => $inquiryId,
                'agencyId' => $agencyId,
                'status' => 'Assigned to Agency',
                'status_comment' => "MCMC has validated and assigned this inquiry to agency for investigation. Comments: $mcmcComments"
            ]);

            // Update inquiry status to show it's been processed by MCMC
            DB::table('inquiry')
                ->where('inquiryId', $inquiryId)
                ->update([
                    'final_status' => 'Under Investigation' // Keep as Under Investigation but now assigned
                ]);

            DB::commit();

            Log::info("MCMC validated inquiry $inquiryId and assigned to agency $agencyId");

            return redirect()->back()->with('success', 'Inquiry validated and assigned to agency successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error validating inquiry: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to validate inquiry. Please try again.');
        }
    }

    /**
     * Reject inquiry as non-serious or invalid
     * Following MVC pattern: Controller handles business logic
     */
    public function rejectInquiry(Request $request)
    {
        $request->validate([
            'inquiry_id' => 'required|integer|exists:inquiry,inquiryId',
            'rejection_reason' => 'required|string|max:500'
        ]);

        try {
            $inquiryId = $request->inquiry_id;
            $rejectionReason = $request->rejection_reason;
            $mcmcId = session('mcmc_id', 1);

            DB::beginTransaction();

            // Update inquiry status to rejected
            DB::table('inquiry')
                ->where('inquiryId', $inquiryId)
                ->update([
                    'final_status' => 'Rejected'
                ]);

            // Check if assignment exists, update or create
            $existingAssignment = DB::table('inquiryassignment')
                ->where('inquiryId', $inquiryId)
                ->first();

            if ($existingAssignment) {
                DB::table('inquiryassignment')
                    ->where('inquiryId', $inquiryId)
                    ->update([
                        'mcmcComments' => $rejectionReason,
                        'isRejected' => true,
                        'mcmcId' => $mcmcId,
                        'assignDate' => now()->format('Y-m-d')
                    ]);
            } else {
                DB::table('inquiryassignment')->insert([
                    'inquiryId' => $inquiryId,
                    'agencyId' => 1, // Default agency ID
                    'mcmcComments' => $rejectionReason,
                    'isRejected' => true,
                    'mcmcId' => $mcmcId,
                    'assignDate' => now()->format('Y-m-d')
                ]);
            }

            // Add status history entry
            DB::table('inquirystatushistory')->insert([
                'inquiryId' => $inquiryId,
                'agencyId' => 1, // Default agency
                'status' => 'Rejected',
                'status_comment' => "MCMC has rejected this inquiry as non-serious or invalid. Reason: $rejectionReason"
            ]);

            DB::commit();

            Log::info("MCMC rejected inquiry $inquiryId - Reason: $rejectionReason");

            return redirect()->back()->with('success', 'Inquiry rejected successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting inquiry: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to reject inquiry. Please try again.');
        }
    }

    /**
     * View inquiry details for MCMC review
     */
    public function viewInquiryDetails($inquiryId)
    {
        try {
            // Fetch detailed inquiry information
            $inquiry = DB::table('inquiry as i')
                ->leftJoin('publicuser as u', 'i.userId', '=', 'u.userId')
                ->leftJoin('inquiryassignment as ia', 'i.inquiryId', '=', 'ia.inquiryId')
                ->leftJoin('agency as a', 'ia.agencyId', '=', 'a.agencyId')
                ->where('i.inquiryId', $inquiryId)                ->select(
                    'i.*',
                    'u.userName as user_name',
                    'u.userEmail as user_email',
                    'u.userContact_number as user_contact',
                    'ia.mcmcComments',
                    'ia.isRejected',
                    'ia.assignDate',
                    'a.agency_name'
                )
                ->first();

            if (!$inquiry) {
                return redirect()->back()->with('error', 'Inquiry not found.');
            }

            // Get status history
            $statusHistory = DB::table('inquirystatushistory as ish')
                ->leftJoin('agency as a', 'ish.agencyId', '=', 'a.agencyId')
                ->where('ish.inquiryId', $inquiryId)                ->select('ish.*', 'a.agency_name')
                ->orderBy('ish.status_id', 'desc')
                ->get();

            // Get available agencies
            $agencies = DB::table('agency')
                ->select('agencyId', 'agency_name')
                ->get();

            return view('module2.MCMC.InquiryDetails', [
                'inquiry' => $inquiry,
                'statusHistory' => $statusHistory,
                'agencies' => $agencies,
                'evidence_files' => $this->processEvidenceFiles($inquiry->evidenceFileUrl)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching inquiry details: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to fetch inquiry details.');
        }
    }

    /**
     * Helper method to process evidence files
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
                    
                    $evidence_files[] = [
                        'name' => $fileName,
                        'type' => $this->getFileType($extension),
                        'path' => $filePath,
                        'url' => asset('storage/' . $filePath)
                    ];
                }
            }
        }
        
        return $evidence_files;
    }

    /**
     * Helper method to count total evidence
     */
    private function countEvidence($evidenceFileUrl, $evidenceUrl)
    {
        $count = 0;
        
        if ($evidenceFileUrl) {
            $count += count(array_filter(explode(',', $evidenceFileUrl)));
        }
        
        if ($evidenceUrl) {
            $count += 1;
        }
        
        return $count;
    }

    /**
     * Helper method to get file type from extension
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
     * Create test data for MCMC testing
     */
    public function createMCMCTestData()
    {
        try {
            // Create test user if doesn't exist
            $testUser = DB::table('publicuser')->where('userEmail', 'testuser@example.com')->first();
            if (!$testUser) {
                $testUserId = DB::table('publicuser')->insertGetId([
                    'userName' => 'Test User',
                    'userEmail' => 'testuser@example.com',
                    'userPassword' => bcrypt('password'),
                    'userContact_number' => '1234567890'
                ]);
            } else {
                $testUserId = $testUser->userId;
            }

            // Create MCMC staff if doesn't exist
            $mcmcStaff = DB::table('mcmc')->where('mcmcUsername', 'mcmc_staff')->first();
            if (!$mcmcStaff) {
                DB::table('mcmc')->insert([
                    'mcmcName' => 'MCMC Staff',
                    'mcmcEmail' => 'mcmc@example.com',
                    'mcmcUsername' => 'mcmc_staff',
                    'mcmcPassword' => bcrypt('password')
                ]);
            }            // Create test agency if doesn't exist
            $testAgency = DB::table('agency')->where('agency_name', 'Test Agency')->first();
            if (!$testAgency) {
                DB::table('agency')->insert([
                    'agency_name' => 'Test Agency',
                    'agencyPassword' => bcrypt('password'),
                    'agencyUsername' => 'test_agency',
                    'agencyType' => 'Education'
                ]);
            }            // Create test inquiries for MCMC review - some with null status (pending)
            $inquiry1 = DB::table('inquiry')->insertGetId([
                'title' => 'Fake News About Health Policy',
                'description' => 'There are claims circulating on social media about new health policies that appear to be false. Need verification and fact-checking.',
                'userId' => $testUserId,
                'final_status' => null, // Pending MCMC review
                'submission_date' => now()->subDays(2)->format('Y-m-d'),
                'evidenceUrl' => 'https://example.com/fake-news-article',
                'evidenceFileUrl' => null
            ]);

            $inquiry2 = DB::table('inquiry')->insertGetId([
                'title' => 'Misinformation About Economic Data',
                'description' => 'Suspicious claims about economic statistics that need professional review and validation by relevant authorities.',
                'userId' => $testUserId,
                'final_status' => null, // Pending MCMC review
                'submission_date' => now()->subDays(1)->format('Y-m-d'),
                'evidenceUrl' => null,
                'evidenceFileUrl' => 'evidence/economic_data.pdf'
            ]);

            $inquiry3 = DB::table('inquiry')->insertGetId([
                'title' => 'Spam Content Verification',
                'description' => 'This appears to be spam content that should be rejected.',
                'userId' => $testUserId,
                'final_status' => null, // Pending MCMC review
                'submission_date' => now()->format('Y-m-d'),
                'evidenceUrl' => 'https://spam-site.com',
                'evidenceFileUrl' => null
            ]);

            $inquiry4 = DB::table('inquiry')->insertGetId([
                'title' => 'Climate Change Claims',
                'description' => 'Need verification of recent climate change statistics being shared on social media.',
                'userId' => $testUserId,
                'final_status' => 'Under Investigation', // Already in process
                'submission_date' => now()->subDays(3)->format('Y-m-d'),
                'evidenceUrl' => 'https://example.com/climate-data',
                'evidenceFileUrl' => null            ]);

            // Create assignment record for the inquiry that's already under investigation
            DB::table('inquiryassignment')->insert([
                'inquiryId' => $inquiry4,
                'agencyId' => 1,
                'mcmcComments' => 'Already assigned for investigation by our verification team.',
                'isRejected' => false,
                'mcmcId' => 1,
                'assignDate' => now()->subDays(2)->format('Y-m-d')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'MCMC test data created successfully',
                'inquiries_created' => 4,
                'pending_inquiries' => 3,
                'assigned_inquiries' => 1,
                'test_user_id' => $testUserId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}