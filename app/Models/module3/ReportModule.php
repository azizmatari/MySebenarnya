<?php

namespace App\Models\module3;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportModule extends Model
{
    /**
     * Get overall inquiry statistics for the top summary section
     */
    public static function getOverallStats($filters = [])
    {
        try {
            $query = "
                SELECT 
                    COUNT(DISTINCT i.inquiryId) as total_inquiries,
                    SUM(CASE WHEN (i.final_status = 'Under Investigation' OR i.final_status IS NULL OR i.final_status = '') THEN 1 ELSE 0 END) as under_investigation,
                    SUM(CASE WHEN i.final_status = 'True' THEN 1 ELSE 0 END) as verified_true,
                    SUM(CASE WHEN i.final_status = 'Fake' THEN 1 ELSE 0 END) as verified_fake,
                    SUM(CASE WHEN ia.isRejected = 1 THEN 1 ELSE 0 END) as rejected
                FROM inquiry i
                LEFT JOIN inquiryassignment ia ON i.inquiryId = ia.inquiryId
                LEFT JOIN agency a ON ia.agencyId = a.agencyId
            ";

            $params = [];
            $conditions = [];

            // Add date filters
            if (!empty($filters['year'])) {
                $conditions[] = "YEAR(i.submission_date) = ?";
                $params[] = $filters['year'];
            }
            if (!empty($filters['month']) && !empty($filters['year'])) {
                $conditions[] = "MONTH(i.submission_date) = ?";
                $params[] = $filters['month'];
            }

            // Add agency type filter
            if (!empty($filters['agency_type'])) {
                $conditions[] = "a.agencyType = ?";
                $params[] = $filters['agency_type'];
            }

            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }

            $result = DB::select($query, $params);
            return $result[0] ?? (object)[
                'total_inquiries' => 0,
                'under_investigation' => 0,
                'verified_true' => 0,
                'verified_fake' => 0,
                'rejected' => 0
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching overall stats: ' . $e->getMessage());
            return (object)[
                'total_inquiries' => 0,
                'under_investigation' => 0,
                'verified_true' => 0,
                'verified_fake' => 0,
                'rejected' => 0
            ];
        }
    }

    /**
     * Get inquiry assignment statistics for each agency
     */
    public static function getAgencyStats($filters = [])
    {
        try {
            $query = "
                SELECT 
                    a.agency_name,
                    a.agencyType,
                    COUNT(ia.inquiryId) as total_assignments,
                    SUM(CASE WHEN (i.final_status = 'Under Investigation' OR i.final_status IS NULL OR i.final_status = '') THEN 1 ELSE 0 END) as under_investigation,
                    SUM(CASE WHEN i.final_status = 'True' THEN 1 ELSE 0 END) as verified_true,
                    SUM(CASE WHEN i.final_status = 'Fake' THEN 1 ELSE 0 END) as verified_fake,
                    SUM(CASE WHEN ia.isRejected = 1 THEN 1 ELSE 0 END) as rejected
                FROM agency a
                LEFT JOIN inquiryassignment ia ON a.agencyId = ia.agencyId
                LEFT JOIN inquiry i ON ia.inquiryId = i.inquiryId
            ";

            $params = [];
            $conditions = [];

            // Add date filters
            if (!empty($filters['year'])) {
                $conditions[] = "YEAR(COALESCE(i.submission_date, ia.assignDate)) = ?";
                $params[] = $filters['year'];
            }
            if (!empty($filters['month']) && !empty($filters['year'])) {
                $conditions[] = "MONTH(COALESCE(i.submission_date, ia.assignDate)) = ?";
                $params[] = $filters['month'];
            }

            // Add agency type filter
            if (!empty($filters['agency_type'])) {
                $conditions[] = "a.agencyType = ?";
                $params[] = $filters['agency_type'];
            }

            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }

            $query .= " GROUP BY a.agencyId, a.agency_name, a.agencyType 
                       HAVING total_assignments > 0
                       ORDER BY total_assignments DESC";

            return DB::select($query, $params);
        } catch (\Exception $e) {
            Log::error('Error fetching agency stats: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get chart data for status distribution (pie chart)
     */
    public static function getStatusChartData($filters = [])
    {
        $stats = self::getOverallStats($filters);
        
        return [
            'labels' => ['Under Investigation', 'Verified True', 'Verified Fake', 'Rejected'],
            'data' => [
                (int)$stats->under_investigation,
                (int)$stats->verified_true,
                (int)$stats->verified_fake,
                (int)$stats->rejected
            ],
            'colors' => ['#ffc107', '#28a745', '#dc3545', '#6c757d']
        ];
    }

    /**
     * Get chart data for agency distribution (bar chart)
     */
    public static function getAgencyChartData($filters = [])
    {
        $agencyStats = self::getAgencyStats($filters);
        
        $labels = [];
        $data = [];
        $colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6610f2', '#e83e8c', '#fd7e14'];
        
        foreach ($agencyStats as $index => $agency) {
            $labels[] = $agency->agency_name;
            $data[] = (int)$agency->total_assignments;
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => array_slice($colors, 0, count($labels))
        ];
    }

    /**
     * Get available years from inquiry data
     */
    public static function getAvailableYears()
    {
        try {
            return DB::select("
                SELECT DISTINCT YEAR(submission_date) as year 
                FROM inquiry 
                WHERE submission_date IS NOT NULL 
                ORDER BY year DESC
            ");
        } catch (\Exception $e) {
            Log::error('Error fetching available years: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get agency types
     */
    public static function getAgencyTypes()
    {
        return ['Education', 'Police', 'Sports', 'Health'];
    }

    /**
     * Generate complete report data for export
     */
    public static function generateReportData($filters = [])
    {
        return [
            'overall_stats' => self::getOverallStats($filters),
            'agency_stats' => self::getAgencyStats($filters),
            'status_chart' => self::getStatusChartData($filters),
            'agency_chart' => self::getAgencyChartData($filters),
            'filters' => $filters,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
}
