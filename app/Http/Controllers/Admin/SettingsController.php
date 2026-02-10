<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        $settings = AppSetting::all()->keyBy('key');
        
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'base_fare' => 'required|numeric|min:0|max:999999',
            'fare_per_km' => 'required|numeric|min:0|max:999999',
        ]);

        $changes = [];
        $descriptions = [];

        // Update base fare
        $oldBaseFare = AppSetting::get('base_fare', 0);
        $newBaseFare = $request->base_fare;
        
        if ($oldBaseFare != $newBaseFare) {
            AppSetting::set(
                'base_fare',
                $newBaseFare,
                'number',
                'Base fare for jeepney rides in PHP'
            );
            
            $changes['base_fare'] = [
                'old' => $oldBaseFare,
                'new' => $newBaseFare,
            ];
            $descriptions[] = 'Base Fare: ₱' . number_format($oldBaseFare, 2) . ' → ₱' . number_format($newBaseFare, 2);
        }

        // Update fare per km
        $oldFarePerKm = AppSetting::get('fare_per_km', 0);
        $newFarePerKm = $request->fare_per_km;
        
        if ($oldFarePerKm != $newFarePerKm) {
            AppSetting::set(
                'fare_per_km',
                $newFarePerKm,
                'number',
                'Fare per kilometer in PHP'
            );
            
            $changes['fare_per_km'] = [
                'old' => $oldFarePerKm,
                'new' => $newFarePerKm,
            ];
            $descriptions[] = 'Fare Per KM: ₱' . number_format($oldFarePerKm, 2) . ' → ₱' . number_format($newFarePerKm, 2);
        }

        // Log the activity if there were changes
        if (!empty($changes)) {
            ActivityLog::log(
                'updated',
                'AppSetting',
                null,
                'Fare Settings',
                'Updated fare settings: ' . implode(', ', $descriptions),
                $changes
            );
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings updated successfully!');
    }
}
