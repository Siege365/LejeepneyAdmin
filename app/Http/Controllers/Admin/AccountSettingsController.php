<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AccountSettingsController extends Controller
{
    /**
     * Show the account settings page
     */
    public function index()
    {
        $user = Auth::user();
        return view('admin.account-settings', compact('user'));
    }

    /**
     * Update profile information (name & email)
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $oldName = $user->name;
        $oldEmail = $user->email;

        $user->update($validated);

        ActivityLog::log(
            'profile_updated',
            'User',
            $user->id,
            $user->name,
            "Admin '{$oldName}' updated their profile"
        );

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        ActivityLog::log(
            'password_changed',
            'User',
            $user->id,
            $user->name,
            "Admin '{$user->name}' changed their password"
        );

        return back()->with('success', 'Password changed successfully!');
    }

    /**
     * Delete user account (only if not the last admin)
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'confirmation_password' => 'required',
        ]);

        $user = Auth::user();

        // Verify password
        if (!Hash::check($request->confirmation_password, $user->password)) {
            return back()->withErrors(['confirmation_password' => 'Password is incorrect.']);
        }

        // Check if this is the last admin
        $adminCount = \App\Models\User::where('role', 'admin')->count();
        if ($adminCount <= 1) {
            return back()->withErrors(['confirmation_password' => 'Cannot delete the last administrator account.']);
        }

        ActivityLog::log(
            'account_deleted',
            'User',
            $user->id,
            $user->name,
            "Admin '{$user->name}' deleted their account"
        );

        // Logout and delete
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        $user->delete();

        return redirect()->route('login')->with('success', 'Your account has been deleted successfully.');
    }
}
