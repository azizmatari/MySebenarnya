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
     */    public static function getActiveInquiries()
    {        try {
            $inquiries = DB::select("
                SELECT 
                    i.inquiryId,
                    i.title,
                    i.description,
                    COALESCE(i.final_status, 'Pending') as final_status,
                    i.submission_date,
                    '--' as agency_name,
                    COALESCE(pu.userName, 'Unknown User') as applicant_name,
                    COALESCE(i.evidenceUrl, NULL) as evidence_url,
                    COALESCE(i.evidenceFileUrl, NULL) as evidence_file_url
                FROM inquiry i
                LEFT JOIN publicuser pu ON i.userId = pu.userId
                WHERE (i.final_status IN ('Under Investigation', 'Pending') OR i.final_status IS NULL)
                ORDER BY i.submission_date DESC
            ");// Log the count for debugging
            Log::info('Active inquiries found: ' . count($inquiries));

            return $inquiries;
        } catch (\Exception $e) {            // Log error and return empty array
            Log::error('Error fetching active inquiries: ' . $e->getMessage());
            return [];
        }
    }
    /**
     * Get inquiry count by status
     */    public static function getInquiryCountByStatus($status = null)
    {
        try {
            if ($status) {
                if ($status === 'Pending') {
                    // Count both explicit 'Pending' and NULL status
                    $count = DB::select("
                        SELECT COUNT(*) as count 
                        FROM inquiry 
                        WHERE final_status = 'Pending' OR final_status IS NULL
                    ");
                } else {
                    // Count specific status
                    $count = DB::select("
                        SELECT COUNT(*) as count 
                        FROM inquiry 
                        WHERE final_status = ?
                    ", [$status]);
                }
            } else {
                // Count both Under Investigation, Pending, and NULL
                $count = DB::select("
                    SELECT COUNT(*) as count 
                    FROM inquiry 
                    WHERE (final_status IN ('Under Investigation', 'Pending') OR final_status IS NULL)
                ");
            }

            return $count[0]->count ?? 0;
        } catch (\Exception $e) {
            Log::error('Error counting inquiries: ' . $e->getMessage());
            return 0;
        }
    }
    /**
     * Get inquiry statistics
     */
    public static function getInquiryStatistics()
    {
        try {
            // Get total active inquiries (Under Investigation + Pending)
            $activeCount = self::getInquiryCountByStatus();

            // Get agencies involved count
            $agenciesInvolved = DB::select("
                SELECT COUNT(DISTINCT a.agencyId) as count
                FROM inquiry i
                LEFT JOIN agency a ON i.agencyId = a.agencyId
                WHERE i.final_status IN ('Under Investigation', 'Pending')
            ");

            // Get this week's inquiries count
            $thisWeekCount = DB::select("
                SELECT COUNT(*) as count
                FROM inquiry 
                WHERE final_status IN ('Under Investigation', 'Pending')
                AND submission_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            ");

            return [
                'active_inquiries' => $activeCount,
                'agencies_involved' => $agenciesInvolved[0]->count ?? 0,
                'this_week' => $thisWeekCount[0]->count ?? 0
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching inquiry statistics: ' . $e->getMessage());
            return [
                'active_inquiries' => 0,
                'agencies_involved' => 0,
                'this_week' => 0
            ];
        }
    }
    /**
     * Get inquiry details by ID
     */
    public static function getInquiryById($inquiryId)
    {
        try {
            $inquiry = DB::select("
                SELECT 
                    i.inquiryId,
                    i.title,
                    i.description,
                    i.final_status,
                    i.submission_date,
                    COALESCE(a.agency_name, 'Unknown Agency') as agency_name,
                    COALESCE(a.agencyId, 0) as agencyId,
                    COALESCE(pu.userName, 'Unknown User') as applicant_name,
                    COALESCE(pu.userEmail, '') as applicant_email,
                    COALESCE(pu.userContact_number, '') as applicant_contact
                FROM inquiry i
                LEFT JOIN agency a ON i.agencyId = a.agencyId
                LEFT JOIN publicuser pu ON i.userId = pu.userId
                WHERE i.inquiryId = ?
            ", [$inquiryId]);

            return $inquiry[0] ?? null;
        } catch (\Exception $e) {
            Log::error('Error fetching inquiry details: ' . $e->getMessage());
            return null;
        }
    }
    /**
     * Get inquiries by agency
     */
    public static function getInquiriesByAgency($agencyId)
    {
        try {
            $inquiries = DB::select("
                SELECT 
                    i.inquiryId,
                    i.title,
                    i.description,
                    i.final_status,
                    i.submission_date,
                    COALESCE(pu.userName, 'Unknown User') as applicant_name
                FROM inquiry i
                LEFT JOIN publicuser pu ON i.userId = pu.userId
                WHERE i.agencyId = ? AND i.final_status IN ('Under Investigation', 'Pending')
                ORDER BY i.submission_date DESC
            ", [$agencyId]);

            return $inquiries;
        } catch (\Exception $e) {
            Log::error('Error fetching inquiries by agency: ' . $e->getMessage());
            return [];
        }
    }
    /**
     * Check if inquiry exists and is active
     */
    public static function isInquiryActive($inquiryId)
    {
        try {
            $count = DB::select("
                SELECT COUNT(*) as count
                FROM inquiry 
                WHERE inquiryId = ? AND final_status IN ('Under Investigation', 'Pending')
            ", [$inquiryId]);

            return ($count[0]->count ?? 0) > 0;
        } catch (\Exception $e) {
            Log::error('Error checking inquiry status: ' . $e->getMessage());
            return false;
        }
    }
}
