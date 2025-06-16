<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\module1\PublicUser;
use App\Models\module1\Agency;
use App\Models\module1\MCMC;

class ReportExport implements FromCollection
{
    protected $mode;

    public function __construct($mode = 'count')
    {
        $this->mode = $mode;
    }

    /**
     * Return the collection of rows for Excel export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Use the centralized method from PublicUser model
        $stats = PublicUser::getUserStats();
        $publicCount = $stats['counts']['publicCount'];
        $agencyCount = $stats['counts']['agencyCount'];
        $staffCount = $stats['counts']['staffCount'];
        $total = $stats['counts']['total'];

        // Get date and time for report header
        $currentDate = now()->format('Y-m-d');
        $currentTime = now()->format('H:i:s');

        if ($this->mode === 'percent') {
            return new Collection([
                ['MySebenarnya System - User Percentage Report'],
                ['Generated on:', $currentDate, 'at', $currentTime],
                [''],
                ['User Type', 'Count', 'Percentage'],
                ['Public Users', $publicCount, round($publicCount / $total * 100, 1) . '%'],
                ['Agency Users', $agencyCount, round($agencyCount / $total * 100, 1) . '%'],
                ['MCMC Staff', $staffCount, round($staffCount / $total * 100, 1) . '%'],
                ['Total Users', $total, '100.0%'],
                [''],
                ['Note: This report shows the distribution of different user types in the system.']
            ]);
        } else {
            // Count mode or default
            return new Collection([
                ['MySebenarnya System - User Count Report'],
                ['Generated on:', $currentDate, 'at', $currentTime],
                [''],
                ['User Type', 'Count'],
                ['Public Users', $publicCount],
                ['Agency Users', $agencyCount],
                ['MCMC Staff', $staffCount],
                ['Total Users', $total],
                [''],
                ['Note: This report shows the count of different user types in the system.']
            ]);
        }
    }
}
