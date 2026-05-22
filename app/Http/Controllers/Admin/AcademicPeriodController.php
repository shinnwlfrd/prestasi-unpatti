<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Services\Admin\ConfigAuditService;
use Illuminate\Http\Request;

class AcademicPeriodController extends Controller
{
    protected $auditService;

    public function __construct(ConfigAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

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
            'year' => 'required|string|max:4',
            'semester' => 'required|string|in:Ganjil,Genap',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'submission_deadline' => 'nullable|date|after_or_equal:start_date',
            'validation_deadline' => 'nullable|date|after_or_equal:submission_deadline',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $existing = AcademicPeriod::withTrashed()->where('code', $request->code)->first();

        if ($existing) {
            if ($existing->trashed()) {
                $oldValues = $existing->getOriginal();
                $existing->restore();
                $existing->update($validated);
                $period = $existing;
                $this->auditService->logChange($period, 'restored', $oldValues, $period->getAttributes());
            } else {
                return back()->withErrors(['code' => 'Kode periode sudah digunakan.'])->withInput();
            }
        } else {
            $period = AcademicPeriod::create($validated);
            $this->auditService->logChange($period, 'created', null, $period->getAttributes());
        }

        // If set as active, deactivate others
        if ($request->is_active) {
            $oldValues = $period->getOriginal();
            $period->activate();
            $this->auditService->logChange($period, 'activated', $oldValues, $period->getAttributes());
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
            'code' => 'required|string|max:50|unique:academic_periods,code,'.$period->id.',id,deleted_at,NULL',
            'year' => 'required|string|max:4',
            'semester' => 'required|string|in:Ganjil,Genap',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'submission_deadline' => 'nullable|date|after_or_equal:start_date',
            'validation_deadline' => 'nullable|date|after_or_equal:submission_deadline',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $oldValues = $period->getOriginal();
        $period->update($validated);

        $newValues = $period->getAttributes();
        $this->auditService->logChange($period, 'updated', $oldValues, $newValues);

        // If set as active, deactivate others
        if ($request->is_active && ! $oldValues['is_active']) {
            $oldValuesActive = $period->getOriginal();
            $period->activate();
            $this->auditService->logChange($period, 'activated', $oldValuesActive, $period->getAttributes());
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

        $oldValues = $period->getOriginal();
        $period->delete();

        $this->auditService->logChange($period, 'deleted', $oldValues, null);

        return redirect()
            ->route('admin.periods.index')
            ->with('success', 'Periode akademik berhasil dihapus.');
    }

    public function activate(AcademicPeriod $period)
    {
        $oldValues = $period->getOriginal();
        $period->activate();

        $this->auditService->logChange($period, 'activated', $oldValues, $period->getAttributes());

        return back()->with('success', 'Periode akademik berhasil diaktifkan.');
    }
}
