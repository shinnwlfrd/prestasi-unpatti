<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AchievementCategory;
use Illuminate\Http\Request;

class AchievementCategoryController extends Controller
{
    public function index()
    {
        $categories = AchievementCategory::latest()->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:achievement_categories',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        AchievementCategory::create($request->all());

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
            'name' => 'required|string|max:255|unique:achievement_categories,name,'.$category->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $category->update($request->all());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(AchievementCategory $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
