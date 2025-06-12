<?php

namespace App\Http\Controllers\module2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
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
     */    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'news_title' => 'required|string|max:30',
            'detailed_info' => 'required|string|max:250',
            'evidence_files.*' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,txt,mp4,mp3',
            'evidence_links' => 'nullable|string|max:500',
            'terms' => 'required|accepted',
        ]);        try {
            // Create the inquiry record
            $inquiryId = DB::table('inquiry')->insertGetId([
                'title' => $validated['news_title'],
                'description' => $validated['detailed_info'],
                'evidenceUrl' => $validated['evidence_links'] ?? null,
                'userId' => session('user_id', 1), // Default to 1 for testing
                'final_status' => null,
                'submission_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Handle file uploads
            if ($request->hasFile('evidence_files')) {
                foreach ($request->file('evidence_files') as $file) {
                    if ($file->isValid()) {
                        // Generate unique filename
                        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                        
                        // Store file in public/evidence directory
                        $path = $file->storeAs('evidence', $filename, 'public');
                        
                        // Update inquiry with first file path
                        if (!DB::table('inquiry')->where('inquiryId', $inquiryId)->value('evidenceFileUrl')) {
                            DB::table('inquiry')
                                ->where('inquiryId', $inquiryId)
                                ->update(['evidenceFileUrl' => $path]);
                        }
                    }
                }
            }

            // Automatically create assignment (for testing purposes)
            DB::table('inquiry_assignment')->insert([
                'inquiry_id' => $inquiryId,
                'assigned_to' => 1, // Default agency staff ID
                'assigned_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create status history entry
            DB::table('inquiry_status_history')->insert([
                'inquiry_id' => $inquiryId,
                'status' => 'pending',
                'changed_by' => session('user_id', 1),
                'changed_date' => now(),
                'comments' => 'Inquiry submitted by user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Redirect to success page
            return redirect()->route('inquiry.success')->with([
                'inquiry_id' => $inquiryId,
                'title' => $validated['news_title']
            ]);

        } catch (\Exception $e) {
            // Handle errors
            return back()->withErrors(['error' => 'Failed to submit inquiry. Please try again.'])
                        ->withInput();
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
    }    /**
     * Display user's inquiry history with sample data
     */
    public function index()
    {
        $userId = session('user_id', 1); // Default to 1 for testing
          // For demonstration, create fake sample data with only completed and rejected statuses
        $inquiries = collect([
            (object)[
                'id' => 1,
                'title' => 'COVID-19 Vaccine Side Effects Claim',
                'description' => 'Saw a post claiming vaccines cause magnetism. Need verification.',
                'status' => 'completed',
                'submission_date' => '2024-12-01 10:30:00',
                'created_at' => '2024-12-01 10:30:00',
                'result' => 'false',
                'admin_response' => 'This claim has been thoroughly debunked by multiple health organizations. Vaccines do not cause magnetism.'
            ],
            (object)[
                'id' => 2, 
                'title' => 'Local Election Results Manipulation',
                'description' => 'WhatsApp message claiming vote counting was rigged in my district.',
                'status' => 'completed',
                'submission_date' => '2024-12-05 14:15:00',
                'created_at' => '2024-12-05 14:15:00',
                'result' => 'true',
                'admin_response' => 'Investigation confirmed irregularities in vote counting process. Matter has been reported to authorities.'
            ],
            (object)[
                'id' => 3,
                'title' => 'Miracle Weight Loss Drug Discovery',
                'description' => 'Facebook ad promoting new pill that melts fat overnight.',
                'status' => 'rejected',
                'submission_date' => '2024-12-08 09:45:00',
                'created_at' => '2024-12-08 09:45:00',
                'result' => null,
                'admin_response' => 'Insufficient evidence provided. Please submit screenshots of the actual advertisement and source links.'
            ],
            (object)[
                'id' => 4,
                'title' => 'Celebrity Death Hoax on Social Media',
                'description' => 'Multiple posts claiming famous actor died in car accident.',
                'status' => 'completed',
                'submission_date' => '2024-12-10 16:20:00',
                'created_at' => '2024-12-10 16:20:00',
                'result' => 'false',
                'admin_response' => 'This is a death hoax. The celebrity is alive and well, confirmed by official representatives.'
            ],
            (object)[
                'id' => 5,
                'title' => 'Government Policy Change Rumor',
                'description' => 'Telegram message about new tax increase starting next month.',
                'status' => 'completed',
                'submission_date' => '2024-12-12 11:10:00',
                'created_at' => '2024-12-12 11:10:00',
                'result' => 'false',
                'admin_response' => 'No official announcement has been made regarding tax increases. This appears to be misinformation.'
            ],
            (object)[
                'id' => 6,
                'title' => 'Natural Disaster Prediction Post',
                'description' => 'TikTok video predicting earthquake in KL this week.',
                'status' => 'rejected',
                'submission_date' => '2024-12-13 08:30:00',
                'created_at' => '2024-12-13 08:30:00',
                'result' => null,
                'admin_response' => 'Unable to verify due to lack of credible evidence sources. Please provide links to the original content.'
            ]
        ]);

        return view('module2.inquiry.UserInquiryList', compact('inquiries'));
    }

    /**
     * Display public inquiries with anonymized user data
     */
    public function publicInquiries()
    {
        // For demonstration, create fake public inquiry data with anonymized information
        // In real implementation, this would query the database with proper anonymization
        $publicInquiries = collect([
            (object)[
                'id' => 101,
                'title' => 'Vaccine Misinformation on Social Media',
                'description' => 'Claims about COVID-19 vaccines causing severe side effects spreading on Facebook.',
                'status' => 'completed',
                'submission_date' => '2024-12-01 10:30:00',
                'completion_date' => '2024-12-03 14:20:00',
                'result' => 'false',
                'category' => 'Health',
                'source_platform' => 'Facebook',
                'admin_response' => 'This claim has been thoroughly investigated and found to be misleading. Current scientific evidence shows vaccines are safe and effective.',
                'anonymized_user' => 'User***A1',
                'evidence_count' => 3
            ],
            (object)[
                'id' => 102,
                'title' => 'Election Results Manipulation Claims',
                'description' => 'Allegations of vote tampering in recent local elections circulating via WhatsApp.',
                'status' => 'completed',
                'submission_date' => '2024-12-05 14:15:00',
                'completion_date' => '2024-12-07 09:45:00',
                'result' => 'true',
                'category' => 'Politics',
                'source_platform' => 'WhatsApp',
                'admin_response' => 'Investigation confirmed irregularities in the counting process. Authorities have been notified and corrective measures implemented.',
                'anonymized_user' => 'User***B2',
                'evidence_count' => 5
            ],
            (object)[
                'id' => 103,
                'title' => 'Celebrity Death Hoax Spreading Online',
                'description' => 'False reports of a famous actor dying in a car accident being shared widely.',
                'status' => 'completed',
                'submission_date' => '2024-12-10 16:20:00',
                'completion_date' => '2024-12-11 11:30:00',
                'result' => 'false',
                'category' => 'Entertainment',
                'source_platform' => 'Twitter',
                'admin_response' => 'This is confirmed to be a death hoax. The celebrity is alive and well, as confirmed by official representatives.',
                'anonymized_user' => 'User***C3',
                'evidence_count' => 2
            ],
            (object)[
                'id' => 104,
                'title' => 'Fake Investment Opportunity Advertisement',
                'description' => 'Suspicious investment scheme promising unrealistic returns being promoted online.',
                'status' => 'completed',
                'submission_date' => '2024-12-08 09:45:00',
                'completion_date' => '2024-12-09 16:15:00',
                'result' => 'false',
                'category' => 'Finance',
                'source_platform' => 'Instagram',
                'admin_response' => 'This is a fraudulent investment scheme. The company is not registered and the promised returns are unrealistic.',
                'anonymized_user' => 'User***D4',
                'evidence_count' => 4
            ],
            (object)[
                'id' => 105,
                'title' => 'Natural Disaster Warning Message',
                'description' => 'Unverified earthquake prediction warning circulating on messaging apps.',
                'status' => 'completed',
                'submission_date' => '2024-12-12 11:10:00',
                'completion_date' => '2024-12-13 13:25:00',
                'result' => 'false',
                'category' => 'Safety',
                'source_platform' => 'Telegram',
                'admin_response' => 'No credible scientific basis for this prediction. Official meteorological agencies have not issued any such warnings.',
                'anonymized_user' => 'User***E5',
                'evidence_count' => 1
            ],
            (object)[
                'id' => 106,
                'title' => 'Government Policy Misinformation',
                'description' => 'False claims about new government regulations affecting small businesses.',
                'status' => 'completed',
                'submission_date' => '2024-12-06 08:30:00',
                'completion_date' => '2024-12-08 15:20:00',
                'result' => 'false',
                'category' => 'Government',
                'source_platform' => 'Facebook',
                'admin_response' => 'These claims are inaccurate. No such regulations have been proposed or implemented by the government.',
                'anonymized_user' => 'User***F6',
                'evidence_count' => 3
            ],
            (object)[
                'id' => 107,
                'title' => 'Product Safety Alert Verification',
                'description' => 'Warning about contaminated food products needs verification.',
                'status' => 'completed',
                'submission_date' => '2024-12-04 13:45:00',
                'completion_date' => '2024-12-05 10:30:00',
                'result' => 'true',
                'category' => 'Health',
                'source_platform' => 'WhatsApp',
                'admin_response' => 'Confirmed by health authorities. Product recall has been issued and consumers advised to check batch numbers.',
                'anonymized_user' => 'User***G7',
                'evidence_count' => 6
            ],
            (object)[
                'id' => 108,
                'title' => 'Technology Security Threat Warning',
                'description' => 'Claims about new malware targeting banking apps.',
                'status' => 'completed',
                'submission_date' => '2024-12-09 17:15:00',
                'completion_date' => '2024-12-11 14:50:00',
                'result' => 'true',
                'category' => 'Technology',
                'source_platform' => 'LinkedIn',
                'admin_response' => 'Cybersecurity experts have confirmed this threat. Users are advised to update their banking apps and enable two-factor authentication.',
                'anonymized_user' => 'User***H8',
                'evidence_count' => 7
            ]
        ]);
        
        return view('module2.inquiry.PublicInquiriesList', compact('publicInquiries'));
    }
}