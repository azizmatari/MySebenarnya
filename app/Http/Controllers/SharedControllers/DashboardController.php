<?php

namespace App\Http\Controllers\SharedControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function mcmcDashboard() {
        return view('Dashboard.MCMCDashboard');
    }

    public function userDashboard() {
        return view('Dashboard.UserDashboard');
    }

    public function agencyDashboard() {
        return view('Dashboard.AgencyDashboard');
    }
}

