<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        // Per-user last activity for each tool
        $toolLast = DB::table('user_tool_activities')
            ->where('user_id', Auth::id())
            ->pluck('last_at', 'tool');  // ['calculator' => '2025-10-15 11:22:00', 'investment' => '...']

        return view('admin.dashboard', compact('toolLast'));
    }
}
