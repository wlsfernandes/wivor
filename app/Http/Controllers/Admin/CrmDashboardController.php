<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmContact;
use App\Models\CrmEventContact;
use App\Models\CrmImport;
use Illuminate\View\View;

/**
 * Show administrators an overview of the CRM contact directory.
 */
class CrmDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.crm.dashboard', [
            'summary' => [
                'contacts' => CrmContact::count(),
                'linkedEvents' => CrmEventContact::query()->distinct()->count('event_id'),
                'imports' => CrmImport::count(),
            ],
        ]);
    }
}
