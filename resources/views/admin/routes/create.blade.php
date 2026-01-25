@extends('layouts.admin')

@section('title', 'Add New Route')
@section('page-title', 'Add New Route')

@section('content')
<div class="card route-form-card">
    <div class="card-header">
        <h3>
            <i class="fa-solid fa-route"></i>
            Route Information
        </h3>
        <a href="{{ route('admin.routes.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Routes
        </a>
    </div>
    
    <form action="{{ route('admin.routes.store') }}" method="POST" id="routeForm">
        @csrf
        <div class="card-body">
            <!-- Form Fields -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="fa-solid fa-info-circle"></i>
                    Basic Information
                </h4>
                <div class="form-grid">
                    <!-- Route Name -->
                    <div class="form-group">
                        <label for="name">Route Name <span class="required">*</span></label>
                        <input 
                            type="text" 
                            class="form-control @error('name') is-invalid @enderror" 
                            id="name" 
                            name="name" 
                            placeholder="e.g., Maa - Agdao"
                            value="{{ old('name') }}"
                            required
                        >
                        @error('name')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Terminal/Origin -->
                    <div class="form-group">
                        <label for="terminal">Terminal / Origin <span class="required">*</span></label>
                        <input 
                            type="text" 
                            class="form-control @error('terminal') is-invalid @enderror" 
                            id="terminal" 
                            name="terminal" 
                            placeholder="e.g., Maa Centro"
                            value="{{ old('terminal') }}"
                            required
                        >
                        @error('terminal')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label for="status">Status <span class="required">*</span></label>
                        <select 
                            class="form-control @error('status') is-invalid @enderror" 
                            id="status" 
                            name="status"
                            required
                        >
                            <option value="available" {{ old('status', 'available') === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="unavailable" {{ old('status') === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                        </select>
                        @error('status')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Color -->
                    <div class="form-group">
                        <label for="color">Route Color</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                class="form-control color-input" 
                                id="color" 
                                name="color" 
                                value="{{ old('color', '#EBAF3E') }}"
                            >
                            <span class="color-value" id="colorValue">#EBAF3E</span>
                        </div>
                    @error('color')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                    </div>

                    <!-- Description -->
                    <div class="form-group form-group-full">
                        <label for="description">Description</label>
                        <textarea 
                            class="form-control" 
                            id="description" 
                            name="description" 
                            rows="2"
                            placeholder="Key landmarks along the route, operating hours, etc..."
                        >{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Map Section -->
            <div class="form-section map-section">
                <h4 class="section-title">
                    <i class="fa-solid fa-map-marked-alt"></i>
                    Route Path
                </h4>
                <p class="section-description">
                    Click on the map to add waypoints. The path will automatically follow the actual roads.
                    <strong>Green marker</strong> indicates the starting point, <strong>red marker</strong> indicates the end point.
                    Direction arrows will guide users along the route path.
                </p>
                
                <div id="map" class="route-map"></div>
                
                <input type="hidden" id="path" name="path" value="{{ old('path', '[]') }}">
                <input type="hidden" id="waypoints" name="waypoints" value="{{ old('waypoints', '[]') }}">
                
                @error('path')
                    <span class="error-text">{{ $message }}</span>
                @enderror
                
                <div class="map-controls">
                    <div class="map-buttons">
                        <button type="button" class="btn btn-outline btn-sm" id="undoLast">
                            <i class="fa-solid fa-undo"></i> Undo
                        </button>
                        <button type="button" class="btn btn-outline btn-sm" id="clearPath">
                            <i class="fa-solid fa-eraser"></i> Clear All
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" id="setRoute">
                            <i class="fa-solid fa-check"></i> Set Route
                        </button>
                    </div>
                    <div class="map-info" id="pathInfo">
                        <span><i class="fa-solid fa-map-pin"></i> Waypoints: <strong>0</strong></span>
                        <span><i class="fa-solid fa-ruler"></i> Distance: <strong>0.00 km</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('admin.routes.index') }}" class="btn btn-secondary">
                <i class="fa-solid fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="fa-solid fa-save"></i> Create Route
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="{{ asset('assets/css/route-form.css') }}">
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Pass initial data to JavaScript
    window.routeFormConfig = {
        mode: 'create',
        initialPath: {!! old('path', '[]') !!},
        initialWaypoints: {!! old('waypoints', '[]') !!},
        initialColor: '{{ old('color', '#EBAF3E') }}',
        davaoCenter: [7.0731, 125.6128]
    };
    
      document.addEventListener('DOMContentLoaded', function() {
          const colorInput = document.getElementById('color');
          const colorValue = document.getElementById('colorValue');
          const randomColor = '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0').toUpperCase();
          colorInput.value = randomColor;
          colorValue.textContent = randomColor;
                        });
                    
</script>
<script src="{{ asset('assets/js/route-map.js') }}"></script>
@endpush
