<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigAuditLog;
use Illuminate\Http\Request;

class ConfigAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ConfigAuditLog::with(['user', 'auditable']);

        if ($request->has('action') && ! empty($request->action)) {
            $query->where('action', $request->action);
        }

        if ($request->has('type') && ! empty($request->type)) {
            $type = $request->type;
            if ($type === 'period') {
                $query->where('auditable_type', 'App\Models\AcademicPeriod');
            } elseif ($type === 'level') {
                $query->where('auditable_type', 'App\Models\AchievementLevel');
            } elseif ($type === 'category') {
                $query->where('auditable_type', 'App\Models\AchievementCategory');
            }
        }

        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('admin.audit.index', compact('logs'));
    }
}
