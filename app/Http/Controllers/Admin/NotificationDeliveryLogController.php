<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationDeliveryLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationDeliveryLogController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        $query = NotificationDeliveryLog::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->string('student_id'));
        }

        if ($request->filled('notification_type')) {
            $query->where('notification_type', $request->string('notification_type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->string('date_to'));
        }

        $logs = $query->paginate((int) $request->integer('per_page', 20));

        $summary = [
            'total' => NotificationDeliveryLog::count(),
            'sent' => NotificationDeliveryLog::where('status', 'sent')->count(),
            'failed' => NotificationDeliveryLog::where('status', 'failed')->count(),
            'skipped' => NotificationDeliveryLog::where('status', 'like', 'skipped%')->count(),
            'max_retries_exceeded' => NotificationDeliveryLog::where('status', 'max_retries_exceeded')->count(),
            'retried_today' => NotificationDeliveryLog::whereDate('last_retry_at', today())->count(),
            'retry_warning' => NotificationDeliveryLog::where('status', 'max_retries_exceeded')->count() >= 5,
        ];

        $retryThroughput = NotificationDeliveryLog::query()
            ->selectRaw('DATE(last_retry_at) as retry_date, COUNT(*) as retry_count')
            ->whereNotNull('last_retry_at')
            ->whereDate('last_retry_at', '>=', now()->subDays(6)->toDateString())
            ->groupBy('retry_date')
            ->orderBy('retry_date')
            ->get();

        if (! $request->expectsJson()) {
            return view('admin.notification-delivery-logs.index', [
                'logs' => $logs,
                'summary' => $summary,
                'retryThroughput' => $retryThroughput,
                'filters' => [
                    'status' => $request->string('status')->toString(),
                    'student_id' => $request->string('student_id')->toString(),
                    'notification_type' => $request->string('notification_type')->toString(),
                    'date_from' => $request->string('date_from')->toString(),
                    'date_to' => $request->string('date_to')->toString(),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'retry_throughput' => $retryThroughput,
            'data' => $logs,
        ]);
    }

    public function summary(): JsonResponse
    {
        $summary = [
            'total' => NotificationDeliveryLog::count(),
            'sent' => NotificationDeliveryLog::where('status', 'sent')->count(),
            'failed' => NotificationDeliveryLog::where('status', 'failed')->count(),
            'skipped' => NotificationDeliveryLog::where('status', 'like', 'skipped%')->count(),
            'max_retries_exceeded' => NotificationDeliveryLog::where('status', 'max_retries_exceeded')->count(),
            'retried_today' => NotificationDeliveryLog::whereDate('last_retry_at', today())->count(),
            'retry_warning' => NotificationDeliveryLog::where('status', 'max_retries_exceeded')->count() >= 5,
        ];

        $retryThroughput = NotificationDeliveryLog::query()
            ->selectRaw('DATE(last_retry_at) as retry_date, COUNT(*) as retry_count')
            ->whereNotNull('last_retry_at')
            ->whereDate('last_retry_at', '>=', now()->subDays(6)->toDateString())
            ->groupBy('retry_date')
            ->orderBy('retry_date')
            ->get();

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'retry_throughput' => $retryThroughput,
        ]);
    }
}
