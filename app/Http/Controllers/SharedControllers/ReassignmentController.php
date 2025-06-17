<?php

namespace App\Http\Controllers\SharedControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\module4\Inquiry;
use App\Models\SharedModels\InquiryAssignment;
use App\Models\SharedModels\InquiryStatusHistory;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReassignmentController extends Controller
{
    /**
     * Display the agency assignment management page
     */    public function index()
    {
        $agencyId = Session::get('user_id');
        
        Log::info('Reassignment index accessed. Session data:', [
            'user_id' => Session::get('user_id'),
            'username' => Session::get('username'),
            'role' => Session::get('role')
        ]);
        
        if (!$agencyId) {
            Log::info('No user_id in session for reassignment controller, redirecting to login');
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Get pending assignments for this agency (not yet accepted or rejected)
        $pendingAssignments = InquiryAssignment::with(['inquiry', 'mcmcStaff'])
            ->where('agencyId', $agencyId)
            ->where('isRejected', false)
            ->whereDoesntHave('inquiry.statusHistory', function($query) use ($agencyId) {
                $query->where('agencyId', $agencyId);
            })
            ->orderBy('assignDate', 'desc')
            ->get();

        // Get accepted assignments (have status history from this agency)
        $acceptedAssignments = InquiryAssignment::with(['inquiry', 'mcmcStaff'])
            ->where('agencyId', $agencyId)
            ->where('isRejected', false)
            ->whereHas('inquiry.statusHistory', function($query) use ($agencyId) {
                $query->where('agencyId', $agencyId);
            })
            ->orderBy('assignDate', 'desc')
            ->take(10)
            ->get();

        return view('SharedViews.agency-assignment-management', compact('pendingAssignments', 'acceptedAssignments'));
    }

    /**
     * Accept an assignment
     */    public function accept(Request $request, $assignmentId)    {
        $agencyId = Session::get('user_id');
        
        if (!$agencyId) {
            Log::info('No user_id in session for accept assignment');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // Add debugging
            Log::info('Accept assignment request', [
                'assignment_id' => $assignmentId,
                'agency_id' => $agencyId
            ]);

            $assignment = InquiryAssignment::where('assignmentId', $assignmentId)
                ->where('agencyId', $agencyId)
                ->where('isRejected', false)
                ->first();

            if (!$assignment) {
                Log::warning('Assignment not found', [
                    'assignment_id' => $assignmentId,
                    'agency_id' => $agencyId
                ]);
                return response()->json(['error' => 'Assignment not found or already processed'], 404);
            }

            // Check if already accepted by checking status history
            $existingAcceptance = InquiryStatusHistory::where('inquiryId', $assignment->inquiryId)
                ->where('agencyId', $agencyId)
                ->first();

            if ($existingAcceptance) {
                return response()->json(['error' => 'Assignment already accepted'], 409);
            }            // Create status history entry to mark as accepted
            $acceptanceData = [
                'inquiryId' => $assignment->inquiryId,
                'agencyId' => $agencyId,
                'status' => 'Under Investigation',
                'status_comment' => 'Agency accepted the inquiry assignment and started investigation',
                'officer_name' => Session::get('username') ?: 'Agency Officer',
                'updated_by_agent_id' => $agencyId,
                'supporting_document' => null // No document for acceptance
            ];
            
            $statusHistory = InquiryStatusHistory::create($acceptanceData);
            
            if ($statusHistory) {
                Log::info('Acceptance status history entry created', [
                    'status_id' => $statusHistory->status_id,
                    'inquiry_id' => $assignment->inquiryId
                ]);
            } else {
                Log::error('Failed to create acceptance status history entry', [
                    'assignment_id' => $assignmentId,
                    'data' => $acceptanceData
                ]);
            }

            Log::info('Agency accepted assignment', [
                'agency_id' => $agencyId,
                'assignment_id' => $assignmentId,
                'inquiry_id' => $assignment->inquiryId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assignment accepted successfully. The inquiry now appears in your dashboard.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error accepting assignment', [
                'agency_id' => $agencyId,
                'assignment_id' => $assignmentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Failed to accept assignment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Request reassignment with reason
     */
    public function requestReassignment(Request $request, $assignmentId)
    {
        $request->validate([
            'reassignment_reason' => 'required|string|max:1000'
        ]);        $agencyId = Session::get('user_id');
        
        if (!$agencyId) {
            Log::info('No user_id in session for requestReassignment');
            return response()->json(['error' => 'Unauthorized'], 401);
        }try {
            DB::beginTransaction();
            
            $assignment = InquiryAssignment::where('assignmentId', $assignmentId)
                ->where('agencyId', $agencyId)
                ->where('isRejected', false)
                ->first();

            if (!$assignment) {
                DB::rollback();
                return response()->json(['error' => 'Assignment not found or already processed'], 404);
            }            // Step 1: Set isRejected = true
            $assignment->update(['isRejected' => true]);

            // Step 2: Add row to InquiryStatusHistory// Add status history entry - simple version
            $statusHistoryData = [
                'inquiryId' => $assignment->inquiryId,
                'agencyId' => $agencyId,
                'status' => 'Rejected',
                'status_comment' => $request->reassignment_reason,
                'officer_name' => Session::get('username') ?: 'Agency',
                'updated_by_agent_id' => $agencyId
            ];
            
            $statusHistory = InquiryStatusHistory::create($statusHistoryData);            
            if (!$statusHistory) {
                DB::rollback();
                return response()->json(['error' => 'Failed to create status history'], 500);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reassignment request submitted successfully.'
            ]);        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to submit reassignment request'], 500);
        }}

    /**
     * Show specific reassignment request details
     */    public function show($assignmentId)
    {
        $agencyId = Session::get('user_id');
        
        Log::info('Show reassignment request details', [
            'assignment_id' => $assignmentId, 
            'agency_id' => $agencyId
        ]);
        
        if (!$agencyId) {
            Log::info('No user_id in session for reassignment show');
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $assignment = InquiryAssignment::with(['inquiry', 'mcmcStaff'])
            ->where('assignmentId', $assignmentId)
            ->where('agencyId', $agencyId)
            ->first();

        if (!$assignment) {
            return redirect()->route('agency.assignment.management')->with('error', 'Assignment not found.');
        }

        return response()->json([
            'assignment' => $assignment,
            'inquiry' => $assignment->inquiry
        ]);
    }

    /**
     * MCMC staff view for managing reassignment requests
     */
    public function mcmcIndex()
    {
        try {            // Get all reassignment requests (rejected assignments)
            $reassignmentRequests = InquiryAssignment::with(['inquiry', 'agency'])
                ->where('isRejected', true)
                ->orderBy('updated_at', 'desc')
                ->get();

            return view('mcmc.reassignment-requests', compact('reassignmentRequests'));
        } catch (\Exception $e) {
            Log::error('Error loading MCMC reassignment requests', [
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Failed to load reassignment requests');
        }
    }

    /**
     * MCMC approve reassignment request
     */
    public function approve(Request $request, $assignmentId)
    {
        $request->validate([
            'new_agency_id' => 'required|exists:agency,agencyId',
            'mcmc_comments' => 'nullable|string|max:1000'
        ]);

        try {
            $assignment = InquiryAssignment::where('assignmentId', $assignmentId)
                ->where('isRejected', true)
                ->first();

            if (!$assignment) {
                return response()->json(['error' => 'Reassignment request not found'], 404);
            }

            // Create new assignment for new agency
            $newAssignment = InquiryAssignment::create([
                'inquiryId' => $assignment->inquiryId,
                'agencyId' => $request->new_agency_id,
                'mcmcStaffId' => Session::get('user_id'), // MCMC staff ID
                'assignDate' => now(),
                'isRejected' => false,
                'mcmcComments' => $request->mcmc_comments ?: null
            ]);

            // Create status history entry for reassignment approval
            $statusHistoryData = [
                'inquiryId' => $assignment->inquiryId,
                'agencyId' => $request->new_agency_id,
                'status' => 'Reassigned',
                'status_comment' => 'MCMC approved reassignment request. ' . ($request->mcmc_comments ?: 'No additional comments.'),
                'officer_name' => Session::get('username') ?: 'MCMC Staff',
                'updated_by_agent_id' => Session::get('user_id'),
                'supporting_document' => null
            ];

            $statusHistory = InquiryStatusHistory::create($statusHistoryData);

            // Reset inquiry final_status to null so new agency can work on it
            $inquiry = Inquiry::find($assignment->inquiryId);
            if ($inquiry) {
                $inquiry->update(['final_status' => null]);
            }

            Log::info('MCMC approved reassignment request', [
                'old_assignment_id' => $assignmentId,
                'new_assignment_id' => $newAssignment->assignmentId,
                'new_agency_id' => $request->new_agency_id,
                'status_history_id' => $statusHistory->status_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reassignment approved successfully. Inquiry has been assigned to new agency.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error approving reassignment request', [
                'assignment_id' => $assignmentId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to approve reassignment'], 500);
        }
    }

    /**
     * MCMC reject reassignment request
     */
    public function reject(Request $request, $assignmentId)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        try {
            $assignment = InquiryAssignment::where('assignmentId', $assignmentId)
                ->where('isRejected', true)
                ->first();

            if (!$assignment) {
                return response()->json(['error' => 'Reassignment request not found'], 404);
            }

            // Create status history entry for reassignment rejection
            $statusHistoryData = [
                'inquiryId' => $assignment->inquiryId,
                'agencyId' => $assignment->agencyId,
                'status' => 'Reassignment Rejected',
                'status_comment' => 'MCMC rejected reassignment request: ' . $request->rejection_reason,
                'officer_name' => Session::get('username') ?: 'MCMC Staff',
                'updated_by_agent_id' => Session::get('user_id'),
                'supporting_document' => null
            ];

            $statusHistory = InquiryStatusHistory::create($statusHistoryData);

            // Reset assignment status - agency needs to continue working on it
            $assignment->update([
                'isRejected' => false,
                'mcmcComments' => 'Reassignment request rejected: ' . $request->rejection_reason
            ]);

            Log::info('MCMC rejected reassignment request', [
                'assignment_id' => $assignmentId,
                'agency_id' => $assignment->agencyId,
                'status_history_id' => $statusHistory->status_id,
                'reason' => $request->rejection_reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reassignment request rejected. Agency has been notified to continue working on the inquiry.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error rejecting reassignment request', [
                'assignment_id' => $assignmentId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to reject reassignment'], 500);
        }
    }
}
