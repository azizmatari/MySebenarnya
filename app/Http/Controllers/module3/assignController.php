<?php

namespace App\Http\Controllers\module3;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\module2\Inquiry;
use App\Models\module2\InquiryAssignment;
use App\Models\module2\InquiryStatusHistory;
use App\Models\module1\MCMC;
use App\Models\module3\assignModule;

class assignController extends Controller
{
    /**
     * Show the assignment form for MCMC to assign inquiry to agency
     */
    public function showAssignmentForm($inquiryId)
    {
        try {
            // Get inquiry details
            $inquiry = DB::table('inquiry as i')
                ->leftJoin('publicuser as u', 'i.userId', '=', 'u.userId')
                ->leftJoin('inquiryassignment as ia', 'i.inquiryId', '=', 'ia.inquiryId')
                ->where('i.inquiryId', $inquiryId)
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
                ->first();

            if (!$inquiry) {
                return redirect()->route('mcmc.inquiries')->with('error', 'Inquiry not found.');
            }            // Get available agency types
            $agencyTypes = assignModule::getAgencyTypes();

            // Get assigned agency info if exists
            $assignedAgencyInfo = null;
            if ($inquiry->agencyId) {
                $assignedAgencyData = DB::select("
                    SELECT agencyId, agency_name, agencyType 
                    FROM agency 
                    WHERE agencyId = ?
                ", [$inquiry->agencyId]);

                if (!empty($assignedAgencyData)) {
                    $assignedAgencyInfo = $assignedAgencyData[0];
                }
            }

            // Process inquiry data for view
            $processedInquiry = (object)[
                'id' => $inquiry->id,
                'title' => $inquiry->title,
                'description' => $inquiry->description,
                'status' => $inquiry->final_status ?? 'Pending',
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
                'is_pending' => is_null($inquiry->final_status)
            ];
            return view('module3.mcmc_assign_user', [
                'inquiry' => $processedInquiry,
                'agencyTypes' => $agencyTypes,
                'assignedAgencyInfo' => $assignedAgencyInfo
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing assignment form: ' . $e->getMessage());
            return redirect()->route('mcmc.inquiries')->with('error', 'Unable to load assignment form. Please try again.');
        }
    }

    /**
     * Process evidence files from URL string
     */
    private function processEvidenceFiles($evidenceFileUrl)
    {
        if (empty($evidenceFileUrl)) {
            return [];
        }

        $files = explode(',', $evidenceFileUrl);
        $processedFiles = [];

        foreach ($files as $file) {
            $file = trim($file);
            if (!empty($file)) {
                $processedFiles[] = [
                    'name' => basename($file),
                    'url' => $file,
                    'type' => $this->getFileType($file)
                ];
            }
        }

        return $processedFiles;
    }

    /**
     * Get file type based on extension
     */
    private function getFileType($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $documentTypes = ['pdf', 'doc', 'docx', 'txt', 'rtf'];
        $videoTypes = ['mp4', 'avi', 'mov', 'wmv', 'flv'];

        if (in_array($extension, $imageTypes)) {
            return 'image';
        } elseif (in_array($extension, $documentTypes)) {
            return 'document';
        } elseif (in_array($extension, $videoTypes)) {
            return 'video';
        } else {
            return 'other';
        }
    }

    /**
     * Count total evidence items
     */
    private function countEvidence($evidenceFileUrl, $evidenceUrl)
    {
        $count = 0;

        if (!empty($evidenceFileUrl)) {
            $files = explode(',', $evidenceFileUrl);
            $count += count(array_filter($files, function ($file) {
                return !empty(trim($file));
            }));
        }

        if (!empty($evidenceUrl)) {
            $count += 1;
        }

        return $count;
    }
    /**
     * Process the assignment form submission
     */
    public function processAssignment(Request $request)
    {
        $request->validate([
            'inquiry_id' => 'required|integer',
            'agency_type' => 'required|string',
            'agency_id' => 'required|integer',
            'mcmc_comments' => 'required|string|max:1000'
        ]);
        try {
            $inquiryId = $request->inquiry_id;
            $agencyId = $request->agency_id;
            $mcmcComments = $request->mcmc_comments;

            Log::info("Processing assignment - Inquiry ID: $inquiryId, Agency ID: $agencyId");

            // Use the assignModule to handle the assignment
            $success = assignModule::assignInquiryToAgency($inquiryId, $agencyId, $mcmcComments);

            if ($success) {
                Log::info("Assignment successful for inquiry $inquiryId");
                return redirect()->route('mcmc.inquiries')
                    ->with('success', 'Inquiry assigned to agency successfully and status updated to "Under Investigation".');
            } else {
                Log::error("Assignment failed for inquiry $inquiryId");
                return redirect()->back()
                    ->with('error', 'Failed to assign inquiry. Please try again.')
                    ->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Error processing assignment: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while processing the assignment.')
                ->withInput();
        }
    }

    /**
     * AJAX endpoint to get agencies by type
     */
    public function getAgenciesByType(Request $request)
    {
        try {
            $agencyType = $request->input('agency_type');

            if (empty($agencyType)) {
                return response()->json(['error' => 'Agency type is required'], 400);
            }

            $agencies = assignModule::getAgenciesByType($agencyType);

            return response()->json([
                'success' => true,
                'agencies' => $agencies
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching agencies by type: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch agencies'], 500);
        }
    }
}