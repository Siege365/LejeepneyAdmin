<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;

class SettingsController extends Controller
{
    /**
     * Get all public settings for mobile app
     */
    public function index()
    {
        $settings = AppSetting::getPublicSettings();

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }
}
