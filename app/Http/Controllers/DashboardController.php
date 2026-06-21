<?php

namespace App\Http\Controllers;

use App\Services\DashboardSummaryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardSummaryService $dashboardSummary): View
    {
        return view('dashboard', $dashboardSummary->forUser($request->user()));
    }
}
