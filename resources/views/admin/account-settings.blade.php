@extends('layouts.admin')

@section('title', 'Account Settings')
@section('page-title', 'Account Settings')

@push('styles')
@vite('resources/css/pages/account-settings.css')
@endpush

@section('content')
<!-- Page Header -->
<div class="card cs-page-header">
    <div class="card-header">
        <div>
            <h2 class="cs-page-title">Account Settings</h2>
            <p class="cs-page-subtitle">Manage your profile information and security settings.</p>
        </div>
    </div>
</div>

<div class="settings-grid">
    <!-- Profile Information -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-user"></i> Profile Information</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.account.update-profile') }}">
                @csrf
                @method('PUT')

                <div class="settings-avatar-section">
                    <div class="settings-avatar">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div>
                        <h4>{{ $user->name }}</h4>
                        <p class="text-muted">Administrator</p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-input" required>
                    @error('name')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-input" required>
                    @error('email')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input" placeholder="Optional">
                    @error('phone')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-lock"></i> Change Password</h3>
        </div>
        <div class="card-body">
            <form id="passwordChangeForm" method="POST" action="{{ route('admin.account.update-password') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="current_password" name="current_password" class="form-input" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    @error('current_password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">New Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="password" name="password" class="form-input" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                    <p class="form-hint">Minimum 8 characters</p>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-primary" onclick="showPasswordChangeModal()">
                        <i class="fa-solid fa-key"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Account Info -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-circle-info"></i> Account Information</h3>
        </div>
        <div class="card-body">
            <div class="account-info-list">
                <div class="account-info-item">
                    <label>Role</label>
                    <span class="badge badge-primary">Administrator</span>
                </div>
                <div class="account-info-item">
                    <label>Account Created</label>
                    <span>{{ $user->created_at->format('F d, Y') }}</span>
                </div>
                <div class="account-info-item">
                    <label>Last Updated</label>
                    <span>{{ $user->updated_at->format('F d, Y \a\t h:i A') }}</span>
                </div>
                <div class="account-info-item">
                    <label>User ID</label>
                    <span>#{{ $user->id }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="card danger-zone-card">
        <div class="card-header">
            <h3><i class="fa-solid fa-exclamation-triangle"></i> Danger Zone</h3>
        </div>
        <div class="card-body">
            <div class="danger-zone-content">
                <div>
                    <h4 class="danger-zone-title">Delete Account</h4>
                    <p class="danger-zone-text">Permanently delete your administrator account. This action cannot be undone.</p>
                </div>
                <button type="button" class="btn btn-danger" onclick="showDeleteAccountModal()">
                    <i class="fa-solid fa-trash-can"></i> Delete Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Password Change Modal -->
<div class="modal-backdrop" id="passwordChangeModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-lock modal-icon-blue"></i> Confirm Password Change</h3>
            <button class="modal-close-btn" onclick="closePasswordChangeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to change your password?</p>
            <p class="modal-body-hint">You will need to use your new password for future logins.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closePasswordChangeModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="showPasswordChangeConfirm()">Continue</button>
        </div>
    </div>
</div>

<!-- Password Change Confirm Modal -->
<div class="modal-backdrop" id="passwordChangeConfirmModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title modal-title-warning"><i class="fa-solid fa-triangle-exclamation"></i> Final Confirmation</h3>
            <button class="modal-close-btn" onclick="closePasswordChangeConfirm()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p class="modal-body-center-bold">Your password will be changed immediately.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closePasswordChangeConfirm()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="confirmPasswordChange()">Confirm Change</button>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal-backdrop" id="deleteAccountModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-exclamation-triangle modal-icon-danger"></i> Delete Account</h3>
            <button class="modal-close-btn" onclick="closeDeleteAccountModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p class="modal-body-bold">Are you sure you want to delete your account?</p>
            <p class="modal-body-danger">This action cannot be undone. All your data will be permanently deleted.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeDeleteAccountModal()">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="showDeleteAccountConfirm()"><i class="fa-solid fa-trash"></i> Yes, Delete</button>
        </div>
    </div>
</div>

<!-- Delete Account Confirm Modal -->
<div class="modal-backdrop" id="deleteAccountConfirmModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title modal-title-danger"><i class="fa-solid fa-triangle-exclamation"></i> Final Warning</h3>
            <button class="modal-close-btn" onclick="closeDeleteAccountConfirm()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p class="modal-body-center-bold modal-body-danger-lg">This is permanent!</p>
            <p class="modal-body-center">Enter your password to confirm account deletion:</p>
            <form id="deleteAccountForm" method="POST" action="{{ route('admin.account.delete') }}" class="modal-body-form">
                @csrf
                @method('DELETE')
                <div class="password-input-wrapper">
                    <input type="password" id="confirmation_password" name="confirmation_password" class="form-input" placeholder="Enter your password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('confirmation_password')">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                @error('confirmation_password')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeDeleteAccountConfirm()">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="confirmDeleteAccount()">
                <i class="fa-solid fa-trash-can"></i> Permanently Delete Account
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@vite('resources/js/pages/account-settings.js')
@endpush
