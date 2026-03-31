<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use Illuminate\Http\Request;

class AcademicPeriodController extends Controller
{
    public function index()
    {
        $periods = AcademicPeriod::ordered()->paginate(15);

        return view('admin.periods.index', compact('periods'));
    }

    public function create()
    {
        return view('admin.periods.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $existing = AcademicPeriod::withTrashed()->where('code', $request->code)->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
                $existing->update($validated);
                $period = $existing;
            } else {
                return back()->withErrors(['code' => 'Kode periode sudah digunakan.'])->withInput();
            }
        } else {
            $period = AcademicPeriod::create($validated);
        }

        // If set as active, deactivate others
        if ($request->is_active) {
            $period->activate();
        }

        return redirect()
            ->route('admin.periods.index')
            ->with('success', 'Periode akademik berhasil disimpan.');
    }

    public function edit(AcademicPeriod $period)
    {
        return view('admin.periods.edit', compact('period'));
    }

    public function update(Request $request, AcademicPeriod $period)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:academic_periods,code,' . $period->id . ',id,deleted_at,NULL',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $period->update($validated);

        // If set as active, deactivate others
        if ($request->is_active) {
            $period->activate();
        }

        return redirect()
            ->route('admin.periods.index')
            ->with('success', 'Periode akademik berhasil diperbarui.');
    }

    public function destroy(AcademicPeriod $period)
    {
        // Prevent deleting active period
        if ($period->is_active) {
            return back()->with('error', 'Tidak dapat menghapus periode yang sedang aktif.');
        }

        $period->delete();

        return redirect()
            ->route('admin.periods.index')
            ->with('success', 'Periode akademik berhasil dihapus.');
    }

    public function activate(AcademicPeriod $period)
    {
        $period->activate();

        return back()->with('success', 'Periode akademik berhasil diaktifkan.');
    }
}
