@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Settings')

@push('styles')
    @vite(['resources/css/pages/settings.css'])
@endpush

@section('content')
<div class="settings-container">
    <div class="settings-header">
        <div>
            <h1 class="settings-title">Settings</h1>
            <p class="settings-subtitle">Configure application settings and preferences</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" class="settings-form">
        @csrf
        @method('PUT')

        <!-- Fare Settings Section -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon">
                    <i class="fas fa-peso-sign"></i>
                </div>
                <div>
                    <h2 class="settings-card-title">Fare Settings</h2>
                    <p class="settings-card-description">Configure base fare and pricing</p>
                </div>
            </div>

            <div class="settings-card-body">
                <div class="form-group">
                    <label for="base_fare" class="form-label">
                        Base Fare
                        <span class="form-label-required">*</span>
                    </label>
                    <div class="input-with-prefix">
                        <span class="input-prefix">₱</span>
                        <input 
                            type="number" 
                            id="base_fare" 
                            name="base_fare" 
                            class="form-input @error('base_fare') is-invalid @enderror" 
                            value="{{ old('base_fare', $settings['base_fare']->value ?? 13.00) }}" 
                            step="0.01" 
                            min="0" 
                            max="999999"
                            required
                        >
                    </div>
                    @error('base_fare')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                    <p class="form-hint">The minimum fare charged for jeepney rides</p>
                </div>

                <div class="form-group" style="margin-top: 1.5rem;">
                    <label for="fare_per_km" class="form-label">
                        Fare Per Kilometer
                        <span class="form-label-required">*</span>
                    </label>
                    <div class="input-with-prefix">
                        <span class="input-prefix">₱</span>
                        <input 
                            type="number" 
                            id="fare_per_km" 
                            name="fare_per_km" 
                            class="form-input @error('fare_per_km') is-invalid @enderror" 
                            value="{{ old('fare_per_km', $settings['fare_per_km']->value ?? 1.50) }}" 
                            step="0.01" 
                            min="0" 
                            max="999999"
                            required
                        >
                    </div>
                    @error('fare_per_km')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                    <p class="form-hint">Additional fare charged per kilometer traveled</p>
                </div>

                <!-- Save Button -->
                <div class="settings-card-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </div>
        </div>

        <!-- Future Settings Sections Can Be Added Here -->
    </form>
</div>

<!-- Confirmation Modal -->
<div id="confirmSettingsModal" class="modal-backdrop">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-exclamation-triangle" style="color: #F59E0B;"></i>
                Confirm Changes
            </h3>
            <button type="button" class="modal-close-btn" onclick="closeConfirmationModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 1.25rem; color: #64748B; line-height: 1.6;">
                You are about to update the fare settings. Please confirm the changes:
            </p>
            <div class="settings-summary">
                <div class="summary-item">
                    <span class="summary-label">Base Fare:</span>
                    <span class="summary-value" id="confirmBaseFare">₱13.00</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Fare Per Kilometer:</span>
                    <span class="summary-value" id="confirmFarePerKm">₱1.50</span>
                </div>
            </div>
            <p style="margin-top: 1rem; font-size: 0.875rem; color: #64748B;">
                <i class="fas fa-info-circle"></i>
                These changes will affect all fare calculations immediately.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeConfirmationModal()">
                <i class="fas fa-times"></i>
                Cancel
            </button>
            <button type="button" class="btn btn-primary" onclick="confirmSaveSettings()">
                <i class="fas fa-check"></i>
                Confirm & Save
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/pages/settings.js'])
    
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('{{ session('success') }}', 'success');
        });
    </script>
    @endif
    
    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('{{ session('error') }}', 'error');
        });
    </script>
    @endif
@endpush
