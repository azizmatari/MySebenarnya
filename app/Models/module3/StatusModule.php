<?php

namespace App\Models\module3;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatusModule extends Model
{
    /**
     * Get all inquiries with "Under Investigation" or "Pending" status
     * Including agency and user information
     * 
     * Default Module 3 Behavior:
     * - New inquiries are inserted with NULL final_status in database
     * - This method converts NULL/empty status to "Pending" for display
     * - Only shows active inquiries (Pending, Under Investigation)
     * - Completed inquiries (True, Fake) are handled by other modules
     */
    public static function getActiveInquiries()
    {
        try {
            // First, check if there are any inquiries in the database
            $totalInquiries = DB::select("SELECT COUNT(*) as count FROM inquiry");
            $totalCount = $totalInquiries[0]->count ?? 0;

            Log::info('Total inquiries in database: ' . $totalCount);

            // If no inquiries exist, create some test data
            if ($totalCount == 0) {
                Log::info('No inquiries found, creating test data...');
                self::createTestInquiries();
            }

            $inquiries = DB::select("
                SELECT 
                    i.inquiryId,
                    i.title,
                    i.description,
                    CASE 
                        WHEN i.final_status IS NULL OR i.final_status = '' THEN 'Pending'
                        ELSE i.final_status
                    END as final_status,
                    i.submission_date,
                    COALESCE(a.agency_name, 'Not Assigned') as agency_name,
                    COALESCE(pu.userName, 'Anonymous User') as applicant_name,
                    COALESCE(i.evidenceUrl, NULL) as evidence_url,
                    COALESCE(i.evidenceFileUrl, NULL) as evidence_file_url,
                    CASE 
                        WHEN ia.assignDate IS NOT NULL THEN ia.assignDate
                        ELSE 'Not Assigned'
                    END as assignment_date
                FROM inquiry i
                LEFT JOIN publicuser pu ON i.userId = pu.userId
                LEFT JOIN inquiryassignment ia ON i.inquiryId = ia.inquiryId
                LEFT JOIN agency a ON ia.agencyId = a.agencyId
                WHERE (i.final_status = 'Under Investigation' 
                       OR i.final_status = 'Pending'
                       OR i.final_status IS NULL 
                       OR i.final_status = '')
                ORDER BY i.submission_date DESC
            ");

            // Log the count for debugging
            Log::info('Active inquiries found: ' . count($inquiries));
            Log::info('Query result: ', ['inquiries' => $inquiries]);

            return $inquiries;
        } catch (\Exception $e) {
            // Log error and return empty array
            Log::error('Error fetching active inquiries: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    //     /**
    //      * Create test inquiries for demonstration
    //      */
    //     private static function createTestInquiries()
    //     {
    //         try {
    //             // First, create a test user if it doesn't exist
    //             $testUser = DB::select("SELECT userId FROM publicuser WHERE userId = 1");
    //             if (empty($testUser)) {
    //                 DB::insert("
    //                     INSERT INTO publicuser (userId, userName, userEmail, userPassword, userContact_number)
    //                     VALUES (1, ?, ?, ?, ?)
    //                 ", [
    //                     'Test User',
    //                     'testuser@example.com',
    //                     password_hash('password', PASSWORD_DEFAULT),
    //                     '+60123456789'
    //                 ]);
    //                 Log::info('Test user created');
    //             }

    //             // Create test inquiries
    //             DB::insert("
    //                 INSERT INTO inquiry (title, description, userId, final_status, submission_date, evidenceUrl)
    //                 VALUES 
    //                 (?, ?, ?, ?, ?, ?),
    //                 (?, ?, ?, ?, ?, ?),
    //                 (?, ?, ?, ?, ?, ?),
    //                 (?, ?, ?, ?, ?, ?)
    //             ", [
    //                 'Breaking News Verification Request',
    //                 'Request to verify a viral news story about recent government policy changes that has been circulating on social media.',
    //                 1,
    //                 'Under Investigation',
    //                 date('Y-m-d', strtotime('-2 days')),
    //                 'https://example.com/news-article-1',

    //                 'Fact Check: Social Media Claim',
    //                 'Urgent verification needed for a claim about economic statistics that is spreading rapidly across multiple platforms.',
    //                 1,
    //                 'Under Investigation',
    //                 date('Y-m-d', strtotime('-1 day')),
    //                 'https://example.com/social-media-post',

    //                 'News Article Authenticity Check',
    //                 'Please verify the authenticity of a news article regarding recent scientific discoveries that seems questionable.',
    //                 1,
    //                 'Under Investigation',
    //                 date('Y-m-d'),
    //                 null,

    //                 'New Inquiry Awaiting Review',
    //                 'This is a newly submitted inquiry that has not been reviewed yet by any agency.',
    //                 1,
    //                 null,
    //                 date('Y-m-d'),
    //                 null
    //             ]);

    //             Log::info('Test inquiries created successfully');
    //         } catch (\Exception $e) {
    //             Log::error('Error creating test inquiries: ' . $e->getMessage());
    //         }
    //     }
}
