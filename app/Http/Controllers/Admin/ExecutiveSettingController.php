<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExecutiveSetting;
use Illuminate\Http\Request;

class ExecutiveSettingController extends Controller
{
    /**
     * Display a listing of the executive settings.
     */
    public function index()
    {
        $settings = ExecutiveSetting::where('category', 'executive_panel')
            ->orderBy('id')
            ->get();
            
        return view('admin.settings.executive', compact('settings'));
    }

    /**
     * Update the specified settings in storage.
     */
    public function update(Request $request)
    {
        $settingsData = $request->input('settings', []);
        
        foreach ($settingsData as $key => $value) {
            ExecutiveSetting::where('key', $key)->update(['value' => $value]);
        }
        
        // Clear potential cache if implemented
        
        return redirect()->back()->with('success', 'Pengaturan Panel Kendali Eksekutif berhasil diperbarui.');
    }
}
