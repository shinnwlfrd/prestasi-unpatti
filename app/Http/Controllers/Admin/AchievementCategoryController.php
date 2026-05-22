<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AchievementCategory;
use App\Services\Admin\ConfigAuditService;
use Illuminate\Http\Request;

class AchievementCategoryController extends Controller
{
    protected $auditService;

    public function __construct(ConfigAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index()
    {
        $categories = AchievementCategory::withCount('achievements')
            ->ordered()
            ->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $existing = AchievementCategory::withTrashed()->where('name', $request->name)->first();

        if ($existing) {
            if ($existing->trashed()) {
                $oldValues = $existing->getOriginal();
                $existing->restore();
                $existing->update($request->all());

                $this->auditService->logChange($existing, 'restored', $oldValues, $existing->getAttributes());

                return redirect()->route('admin.categories.index')
                    ->with('success', 'Kategori yang sebelumnya dihapus telah dipulihkan dan diperbarui.');
            }

            return back()->withErrors(['name' => 'Nama kategori sudah digunakan.'])->withInput();
        }

        $category = AchievementCategory::create($request->all());
        $this->auditService->logChange($category, 'created', null, $category->getAttributes());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(AchievementCategory $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, AchievementCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:achievement_categories,name,'.$category->id.',id,deleted_at,NULL',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $oldValues = $category->getOriginal();
        $category->update($request->all());

        $this->auditService->logChange($category, 'updated', $oldValues, $category->getAttributes());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(AchievementCategory $category)
    {
        $oldValues = $category->getOriginal();
        $category->delete();

        $this->auditService->logChange($category, 'deleted', $oldValues, null);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
