<?php

namespace App\Http\Controllers\module1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\module1\PublicUser;
use App\Models\module1\Agency;
use App\Models\module1\MCMC;
use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;

class UserReportController extends Controller
{
    // عرض صفحة التقارير الأساسية
    public function dashboardReports()
    {
        return view('reports.McmcReports');
    }

    // عرض قائمة المستخدمين مع إمكانية البحث والفلترة
    public function index(Request $request)
    {
        $type = $request->get('type', 'all');
        $q = $request->get('q');

        if ($type === 'public') {
            $query = PublicUser::select(
                'userName as name',
                'userEmail as email',
                DB::raw("'Public User' as role")
            );
            // For public users, we need to use the actual column names in WHERE clause
            if ($q) {
                $query->where(function ($query) use ($q) {
                    $query->where('userName', 'like', "%{$q}%")
                        ->orWhere('userEmail', 'like', "%{$q}%");
                });
            }
        } elseif ($type === 'agency') {
            $query = Agency::select(
                'agency_name as name',
                'agencyUsername as email',
                DB::raw("'Agency' as role"),
                'agencyType',
                'agencyId'
            );
            // For agency users, we need to use the actual column names in WHERE clause
            if ($q) {
                $query->where(function ($query) use ($q) {
                    $query->where('agency_name', 'like', "%{$q}%")
                        ->orWhere('agencyUsername', 'like', "%{$q}%")
                        ->orWhere('agencyType', 'like', "%{$q}%");
                });
            }
        } else {
            // For "all" type, we need separate queries with proper WHERE clauses for each table
            if ($q) {
                $public = PublicUser::select(
                    'userName as name',
                    'userEmail as email',
                    DB::raw("'Public User' as role"),
                    DB::raw("NULL as agencyType"),
                    DB::raw("NULL as agencyId")
                )
                    ->where(function ($query) use ($q) {
                        $query->where('userName', 'like', "%{$q}%")
                            ->orWhere('userEmail', 'like', "%{$q}%");
                    });

                $agency = Agency::select(
                    'agency_name as name',
                    'agencyUsername as email',
                    DB::raw("'Agency' as role"),
                    'agencyType',
                    'agencyId'
                )
                    ->where(function ($query) use ($q) {
                        $query->where('agency_name', 'like', "%{$q}%")
                            ->orWhere('agencyUsername', 'like', "%{$q}%")
                            ->orWhere('agencyType', 'like', "%{$q}%");
                    });

                $mcmc = MCMC::select(
                    'mcmcName as name',
                    'mcmcEmail as email',
                    DB::raw("'MCMC Staff' as role"),
                    DB::raw("NULL as agencyType"),
                    DB::raw("NULL as agencyId")
                )
                    ->where(function ($query) use ($q) {
                        $query->where('mcmcName', 'like', "%{$q}%")
                            ->orWhere('mcmcEmail', 'like', "%{$q}%");
                    });
            } else {
                $public = PublicUser::select(
                    'userName as name',
                    'userEmail as email',
                    DB::raw("'Public User' as role"),
                    DB::raw("NULL as agencyType"),
                    DB::raw("NULL as agencyId")
                );

                $agency = Agency::select(
                    'agency_name as name',
                    'agencyUsername as email',
                    DB::raw("'Agency' as role"),
                    'agencyType',
                    'agencyId'
                );

                $mcmc = MCMC::select(
                    'mcmcName as name',
                    'mcmcEmail as email',
                    DB::raw("'MCMC Staff' as role"),
                    DB::raw("NULL as agencyType"),
                    DB::raw("NULL as agencyId")
                );
            }

            $query = $public->union($agency)->union($mcmc);
        }

        // Get user stats using the centralized model method
        $userStats = PublicUser::getUserStats();
        $users = $query->paginate(20);

        return view('module1.UserListView', compact('users', 'userStats'));
    }

    // احصائيات المستخدمين لعرضها في charts
    public function charts()
    {        // Use the centralized method from the model
        $stats = PublicUser::getUserStats();

        // Default data in case of empty results
        if ($stats['counts']['total'] === 0) {
            $stats = [
                'counts' => [
                    'publicCount' => 1,
                    'agencyCount' => 1,
                    'staffCount' => 1,
                    'total' => 3
                ],
                'percentages' => [
                    'publicPercent' => 33.3,
                    'agencyPercent' => 33.3,
                    'staffPercent' => 33.3
                ]
            ];
        }

        return response()->json([
            'publicCount' => $stats['counts']['publicCount'],
            'agencyCount' => $stats['counts']['agencyCount'],
            'staffCount' => $stats['counts']['staffCount'],
            'total' => $stats['counts']['total'],
            'percentages' => $stats['percentages']
        ]);
    }

    // تصدير ملف اكسل بالتقارير
    public function exportExcel(string $mode)
    {
        $fileName = "user_report_{$mode}_" . now()->format('Ymd_His') . ".xlsx";
        return Excel::download(new ReportExport($mode), $fileName);
    }
    /**
     * Update agency type
     */    public function updateAgencyType(Request $request, $id)
    {
        $request->validate([
            'agencyType' => 'required|in:Education,Police,Sports,Health',
        ]);

        $agency = Agency::findOrFail($id);
        $agency->agencyType = $request->agencyType;
        $agency->save();

        return redirect()->back()->with('success', 'Agency type updated successfully');
    }

    /**
     * Delete an agency
     */
    public function deleteAgency($id)
    {
        try {
            $agency = Agency::findOrFail($id);
            $agencyName = $agency->agency_name;

            // Delete the agency
            $agency->delete();

            return redirect()->route('user.reports.index', ['type' => 'agency'])
                ->with('success', "Agency '{$agencyName}' has been successfully deleted.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete the agency. ' . $e->getMessage());
        }
    }
}
