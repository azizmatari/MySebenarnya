<?php

namespace App\Models\module3;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StatusModule extends Model
{
    /**
     * Get all inquiries with "Under Investigation" status
     * Including agency and user information
     */
    public static function getActiveInquiries()
    {
        try {
            $inquiries = DB::select("
                SELECT 
                    i.inquiryId,
                    i.title,
                    i.description,
                    i.final_status,
                    i.submission_date,
                    a.agency_name,
                    pu.userName as applicant_name
                FROM inquiry i
                INNER JOIN agency a ON i.agencyId = a.agencyId
                INNER JOIN publicuser pu ON i.userId = pu.userId
                WHERE i.final_status = 'Under Investigation'
                ORDER BY i.submission_date DESC
            ");

            return $inquiries;
        } catch (\Exception $e) {
            // Log error and return empty array
            \Log::error('Error fetching active inquiries: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get inquiry count by status
     */
    public static function getInquiryCountByStatus($status = 'Under Investigation')
    {
        try {
            $count = DB::select("
                SELECT COUNT(*) as count 
                FROM inquiry 
                WHERE final_status = ?
            ", [$status]);

            return $count[0]->count ?? 0;
        } catch (\Exception $e) {
            \Log::error('Error counting inquiries: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get inquiry statistics
     */
    public static function getInquiryStatistics()
    {
        try {
            // Get total active inquiries
            $activeCount = self::getInquiryCountByStatus('Under Investigation');

            // Get agencies involved count
            $agenciesInvolved = DB::select("
                SELECT COUNT(DISTINCT a.agencyId) as count
                FROM inquiry i
                INNER JOIN agency a ON i.agencyId = a.agencyId
                WHERE i.final_status = 'Under Investigation'
            ");

            // Get this week's inquiries count
            $thisWeekCount = DB::select("
                SELECT COUNT(*) as count
                FROM inquiry 
                WHERE final_status = 'Under Investigation' 
                AND submission_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            ");

            return [
                'active_inquiries' => $activeCount,
                'agencies_involved' => $agenciesInvolved[0]->count ?? 0,
                'this_week' => $thisWeekCount[0]->count ?? 0
            ];
        } catch (\Exception $e) {
            \Log::error('Error fetching inquiry statistics: ' . $e->getMessage());
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
                    a.agency_name,
                    a.agencyId,
                    pu.userName as applicant_name,
                    pu.userEmail as applicant_email,
                    pu.userContact_number as applicant_contact
                FROM inquiry i
                INNER JOIN agency a ON i.agencyId = a.agencyId
                INNER JOIN publicuser pu ON i.userId = pu.userId
                WHERE i.inquiryId = ?
            ", [$inquiryId]);

            return $inquiry[0] ?? null;
        } catch (\Exception $e) {
            \Log::error('Error fetching inquiry details: ' . $e->getMessage());
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
                    pu.userName as applicant_name
                FROM inquiry i
                INNER JOIN publicuser pu ON i.userId = pu.userId
                WHERE i.agencyId = ? AND i.final_status = 'Under Investigation'
                ORDER BY i.submission_date DESC
            ", [$agencyId]);

            return $inquiries;
        } catch (\Exception $e) {
            \Log::error('Error fetching inquiries by agency: ' . $e->getMessage());
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
                WHERE inquiryId = ? AND final_status = 'Under Investigation'
            ", [$inquiryId]);

            return ($count[0]->count ?? 0) > 0;
        } catch (\Exception $e) {
            \Log::error('Error checking inquiry status: ' . $e->getMessage());
            return false;
        }
    }
}