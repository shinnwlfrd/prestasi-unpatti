<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AchievementExport;
use App\Services\Exports\AchievementExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(
        protected AchievementExportService $exportService
    ) {}

    public function exportAchievements(Request $request): JsonResponse|RedirectResponse
    {
        $export = $this->exportService->queueAdminExport($request->user(), $request);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Export dimasukkan ke antrean.',
                'export' => $this->exportService->serialize($export, 'admin.export.download', 'admin.export.show'),
            ], 202);
        }

        return redirect()->back()->with('status', 'Export sedang diproses di background. Unduh file dari panel status export.');
    }

    public function recent(Request $request): JsonResponse
    {
        $exports = $this->exportService
            ->getRecentForUser($request->user())
            ->map(fn (AchievementExport $export) => $this->exportService->serialize($export, 'admin.export.download', 'admin.export.show'));

        return response()->json(['data' => $exports]);
    }

    public function show(Request $request, AchievementExport $achievementExport): JsonResponse
    {
        abort_unless($achievementExport->user_id === $request->user()->id, 403);

        return response()->json([
            'data' => $this->exportService->serialize($achievementExport, 'admin.export.download', 'admin.export.show'),
        ]);
    }

    public function download(Request $request, AchievementExport $achievementExport): StreamedResponse
    {
        abort_unless($this->exportService->authorizeDownload($achievementExport, $request->user()), 403);

        return Storage::disk($achievementExport->disk)->download(
            $achievementExport->file_path,
            $achievementExport->file_name
        );
    }
}
