<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AchievementLevel;
use Illuminate\Http\Request;

class AchievementLevelController extends Controller
{
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
            'name' => 'required|string|max:255|unique:achievement_levels',
            'description' => 'nullable|string',
            'points' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        AchievementLevel::create($request->all());

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
            'name' => 'required|string|max:255|unique:achievement_levels,name,'.$level->id,
            'description' => 'nullable|string',
            'points' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $level->update($request->all());

        return redirect()->route('admin.levels.index')
            ->with('success', 'Level berhasil diperbarui.');
    }

    public function destroy(AchievementLevel $level)
    {
        $level->delete();

        return redirect()->route('admin.levels.index')
            ->with('success', 'Level berhasil dihapus.');
    }
}
