<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AchievementLevel;
use App\Services\Admin\ConfigAuditService;
use Illuminate\Http\Request;

class AchievementLevelController extends Controller
{
    protected $auditService;

    public function __construct(ConfigAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index()
    {
        $levels = AchievementLevel::latest()->paginate(15);

        return view('admin.levels.index', compact('levels'));
    }

    public function create()
    {
        return view('admin.levels.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $existing = AchievementLevel::withTrashed()->where('name', $request->name)->first();

        if ($existing) {
            if ($existing->trashed()) {
                $oldValues = $existing->getOriginal();
                $existing->restore();
                $existing->update($request->all());

                $this->auditService->logChange($existing, 'restored', $oldValues, $existing->getAttributes());

                return redirect()->route('admin.levels.index')
                    ->with('success', 'Level yang sebelumnya dihapus telah dipulihkan dan diperbarui.');
            }

            return back()->withErrors(['name' => 'Nama level sudah digunakan.'])->withInput();
        }

        $level = AchievementLevel::create($request->all());
        $this->auditService->logChange($level, 'created', null, $level->getAttributes());

        return redirect()->route('admin.levels.index')
            ->with('success', 'Level berhasil ditambahkan.');
    }

    public function edit(AchievementLevel $level)
    {
        return view('admin.levels.edit', compact('level'));
    }

    public function update(Request $request, AchievementLevel $level)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:achievement_levels,name,'.$level->id.',id,deleted_at,NULL',
            'description' => 'nullable|string',
            'points' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $oldValues = $level->getOriginal();
        $level->update($request->all());

        $this->auditService->logChange($level, 'updated', $oldValues, $level->getAttributes());

        return redirect()->route('admin.levels.index')
            ->with('success', 'Level berhasil diperbarui.');
    }

    public function destroy(AchievementLevel $level)
    {
        $oldValues = $level->getOriginal();
        $level->delete();

        $this->auditService->logChange($level, 'deleted', $oldValues, null);

        return redirect()->route('admin.levels.index')
            ->with('success', 'Level berhasil dihapus.');
    }
}
