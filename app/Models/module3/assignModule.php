<?php

namespace App\Models\module3;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class assignModule extends Model
{
    /**
     * Get all agency types from the enum definition
     */
    public static function getAgencyTypes()
    {
        // Return the enum values as defined in the migration
        return ['Education', 'Police', 'Sports', 'Health'];
    }

    /**
     * Get agencies by type
     */
    public static function getAgenciesByType($agencyType)
    {
        try {
            $agencies = DB::select("
                SELECT agencyId, agency_name, agencyType
                FROM agency 
                WHERE agencyType = ?
                ORDER BY agency_name ASC
            ", [$agencyType]);

            return $agencies;
        } catch (\Exception $e) {
            Log::error('Error fetching agencies by type: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Assign inquiry to agency and update status
     */
    public static function assignInquiryToAgency($inquiryId, $agencyId, $mcmcComments)
    {
        DB::beginTransaction();
        try {
            $currentDate = date('Y-m-d');

            // Ensure we have an MCMC ID (create if doesn't exist)
            $mcmcRecord = DB::select("SELECT mcmcId FROM mcmc LIMIT 1");
            $mcmcId = 1; // Default MCMC ID

            if (empty($mcmcRecord)) {
                // Create a default MCMC record if none exists
                DB::insert("
                    INSERT INTO mcmc (mcmcUsername, mcmcPassword) 
                    VALUES ('mcmc_admin', ?)
                ", [password_hash('password123', PASSWORD_DEFAULT)]);
                $mcmcId = 1;
                Log::info("Created default MCMC record");
            } else {
                $mcmcId = $mcmcRecord[0]->mcmcId;
            }

            // Check if assignment already exists
            $existingAssignment = DB::select("
                SELECT assignmentId FROM inquiryassignment 
                WHERE inquiryId = ?
            ", [$inquiryId]);
            if (!empty($existingAssignment)) {
                // Update existing assignment
                DB::update("
                    UPDATE inquiryassignment 
                    SET agencyId = ?, mcmcComments = ?, assignDate = ?, isRejected = 0
                    WHERE inquiryId = ?
                ", [$agencyId, $mcmcComments, $currentDate, $inquiryId]);

                Log::info("Updated existing assignment for inquiry $inquiryId");
            } else {
                // Create new assignment
                DB::insert("
                    INSERT INTO inquiryassignment (inquiryId, agencyId, mcmcId, mcmcComments, assignDate, isRejected)
                    VALUES (?, ?, ?, ?, ?, 0)
                ", [$inquiryId, $agencyId, $mcmcId, $mcmcComments, $currentDate]);

                Log::info("Created new assignment for inquiry $inquiryId to agency $agencyId with MCMC ID $mcmcId");
            }

            // Update inquiry status to 'Under Investigation'
            DB::update("
                UPDATE inquiry 
                SET final_status = 'Under Investigation'
                WHERE inquiryId = ?
            ", [$inquiryId]);

            Log::info("Updated inquiry $inquiryId status to 'Under Investigation'");

            // Add entry to inquiry status history
            DB::insert("
                INSERT INTO inquirystatushistory (inquiryId, agencyId, status, status_comment)
                VALUES (?, ?, 'Under Investigation', ?)
            ", [$inquiryId, $agencyId, $mcmcComments]);

            Log::info("Added status history for inquiry $inquiryId");

            DB::commit();
            Log::info("Assignment transaction completed successfully for inquiry $inquiryId");
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning inquiry to agency: ' . $e->getMessage());
            return false;
        }
    }
}
