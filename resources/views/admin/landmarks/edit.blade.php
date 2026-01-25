@extends('layouts.admin')

@section('title', 'Edit Landmark')
@section('page-title', 'Edit Landmark')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="{{ asset('assets/css/route-form.css') }}">
@endpush

@section('content')
<div class="card route-form-card">
    <div class="card-header">
        <h3>
            <i class="fa-solid fa-edit"></i>
            Edit: {{ $landmark->name }}
        </h3>
        <a href="{{ route('admin.landmarks.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Landmarks
        </a>
    </div>
    
    <form action="{{ route('admin.landmarks.update', $landmark) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fa-solid fa-exclamation-triangle me-2"></i>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Basic Information Section -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="fa-solid fa-info-circle"></i>
                    Basic Information
                </h4>
                <div class="form-grid">
                    <!-- Landmark Name -->
                    <div class="form-group">
                        <label for="name">Landmark Name <span class="required">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $landmark->name) }}" 
                               placeholder="e.g., SM Lanang Premier" required>
                        @error('name')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div class="form-group">
                        <label for="category">Category <span class="required">*</span></label>
                        <select class="form-control @error('category') is-invalid @enderror" 
                                id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="city_center" {{ old('category', $landmark->category) == 'city_center' ? 'selected' : '' }}>🏙 City Center</option>
                            <option value="mall" {{ old('category', $landmark->category) == 'mall' ? 'selected' : '' }}>🛍 Mall</option>
                            <option value="school" {{ old('category', $landmark->category) == 'school' ? 'selected' : '' }}>🏫 School</option>
                            <option value="hospital" {{ old('category', $landmark->category) == 'hospital' ? 'selected' : '' }}>🏥 Hospital</option>
                            <option value="transport" {{ old('category', $landmark->category) == 'transport' ? 'selected' : '' }}>🚌 Transport</option>
                            <option value="other" {{ old('category', $landmark->category) == 'other' ? 'selected' : '' }}>📍 Other</option>
                        </select>
                        @error('category')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="form-group form-group-full">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="2" 
                                  placeholder="Enter a brief description of this landmark">{{ old('description', $landmark->description) }}</textarea>
                        @error('description')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Featured Checkbox -->
                    <div class="form-group form-group-full">
                        <label class="form-check" style="display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input class="form-check-input" type="checkbox" id="is_featured" 
                                   name="is_featured" value="1" {{ old('is_featured', $landmark->is_featured) ? 'checked' : '' }}
                                   style="width: 18px; height: 18px; margin: 0;">
                            <span style="font-weight: 500;">
                                <i class="fa-solid fa-star" style="color: #F59E0B;"></i> Featured Landmark
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Images Section -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="fa-solid fa-images"></i>
                    Images
                </h4>
                <div class="form-grid">
                    <!-- Icon Image -->
                    <div class="form-group">
                        <label for="icon_image">Icon Image</label>
                        
                        @if($landmark->icon_image)
                            <div class="current-icon mb-2" style="padding: 0.75rem; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px;">
                                <img src="{{ Storage::url($landmark->icon_image) }}" 
                                     alt="{{ $landmark->name }}" 
                                     class="img-thumbnail"
                                     style="max-width: 100px; border: 1px solid #E2E8F0;">
                                <label class="form-check mt-2" style="display: flex; align-items: center; gap: 0.375rem; cursor: pointer; margin: 0.5rem 0 0 0;">
                                    <input class="form-check-input" type="checkbox" 
                                           name="remove_icon" value="1" style="width: 14px; height: 14px; margin: 0;">
                                    <span class="remove-btn"><i class="fa-solid fa-trash"></i> Remove Icon</span>
                                </label>
                            </div>
                        @endif

                        <div class="file-input-wrapper">
                            <input type="file" class="form-control file-input @error('icon_image') is-invalid @enderror" 
                                   id="icon_image" name="icon_image" 
                                   accept="image/jpeg,image/png,image/jpg,image/webp">
                            <label for="icon_image" class="file-input-label">
                                <i class="fa-solid fa-cloud-upload-alt"></i>
                                <span>Choose Icon Image</span>
                            </label>
                        </div>
                        <div id="iconPreview" class="mt-2" style="display: none;">
                            <img id="iconPreviewImg" src="" alt="Icon Preview" 
                                 class="img-thumbnail" style="max-width: 100px;">
                        </div>
                        @error('icon_image')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Gallery Images -->
                    <div class="form-group">
                        <label for="gallery_images">Gallery Images</label>
                        
                        @if($landmark->gallery_images && count($landmark->gallery_images) > 0)
                            <div class="current-gallery mb-2">
                                <div class="gallery-preview-grid">
                                    @foreach($landmark->gallery_images as $index => $image)
                                        <div class="gallery-item" style="position: relative; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 0.5rem;">
                                            <img src="{{ Storage::url($image) }}" 
                                                 alt="Gallery {{ $index + 1 }}" 
                                                 class="img-thumbnail"
                                                 style="width: 100%; height: 100px; object-fit: cover; border: 1px solid #E2E8F0;">
                                            <label class="form-check mt-1" style="display: flex; align-items: center; gap: 0.25rem; cursor: pointer; margin: 0;">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="remove_gallery[]" value="{{ $index }}" style="width: 12px; height: 12px; margin: 0;">
                                                <span class="remove-btn" style="font-size: 0.7rem;"><i class="fa-solid fa-trash"></i> Remove</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="file-input-wrapper">
                            <input type="file" class="form-control file-input @error('gallery_images.*') is-invalid @enderror" 
                                   id="gallery_images" name="gallery_images[]" 
                                   accept="image/jpeg,image/png,image/jpg,image/webp" multiple>
                            <label for="gallery_images" class="file-input-label">
                                <i class="fa-solid fa-images"></i>
                                <span>Add Gallery Images</span>
                            </label>
                        </div>
                        <small class="form-text text-muted">You can select multiple images</small>
                        <div id="galleryPreview" class="mt-2" style="display: none;">
                            <div id="galleryPreviewContainer" class="gallery-preview-grid"></div>
                        </div>
                        @error('gallery_images.*')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Location Section -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="fa-solid fa-map-location-dot"></i>
                    Location
                </h4>
                <div class="form-grid">
                    <!-- Coordinates -->
                    <div class="form-group">
                        <label for="latitude">Latitude <span class="required">*</span></label>
                        <input type="text" class="form-control @error('latitude') is-invalid @enderror" 
                               id="latitude" name="latitude" value="{{ old('latitude', $landmark->latitude) }}" 
                               required readonly>
                        @error('latitude')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="longitude">Longitude <span class="required">*</span></label>
                        <input type="text" class="form-control @error('longitude') is-invalid @enderror" 
                               id="longitude" name="longitude" value="{{ old('longitude', $landmark->longitude) }}" 
                               required readonly>
                        @error('longitude')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Map -->
                    <div class="form-group form-group-full">
                        <label>Update Location on Map <span class="required">*</span></label>
                        <div id="map" style="height: 300px; border-radius: 6px; border: 1px solid #CBD5E1;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="card-footer">
            <div class="form-actions">
                <a href="{{ route('admin.landmarks.index') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-times"></i>
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                    Update Landmark
                </button>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Pass landmark data to JS
    window.landmarkData = {
        latitude: {{ $landmark->latitude }},
        longitude: {{ $landmark->longitude }}
    };
</script>
<script src="{{ asset('assets/js/landmark-form.js') }}"></script>
@endpush
