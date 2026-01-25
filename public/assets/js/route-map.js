/**
 * Route Map JavaScript
 * Handles map functionality for creating and editing routes
 * Uses OSRM for road-snapped routing
 * Lejeepney Admin Panel
 */

(function() {
    'use strict';

    // Configuration from server
    const config = window.routeFormConfig || {
        mode: 'create',
        initialPath: [],
        initialWaypoints: [],
        initialColor: '#EBAF3E',
        davaoCenter: [7.0731, 125.6128]
    };

    // OSRM API endpoint (free, no API key required)
    const OSRM_API = 'https://router.project-osrm.org/route/v1/driving';

    // State
    let map = null;
    let waypoints = [];      // User-clicked points (for editing)
    let fullPath = [];       // Full road-snapped path from OSRM
    let markers = [];
    let pathPolyline = null;
    let isLoading = false;
    let isRouteSet = false;   // Track if route is finalized/set

    // DOM Elements
    const elements = {
        map: document.getElementById('map'),
        pathInput: document.getElementById('path'),
        waypointsInput: document.getElementById('waypoints'),
        colorInput: document.getElementById('color'),
        colorValue: document.getElementById('colorValue'),
        undoBtn: document.getElementById('undoLast'),
        clearBtn: document.getElementById('clearPath'),
        setRouteBtn: document.getElementById('setRoute'),
        pathInfo: document.getElementById('pathInfo'),
        form: document.getElementById('routeForm'),
        submitBtn: document.getElementById('submitBtn')
    };

    /**
     * Initialize the map
     */
    function initMap() {
        if (!elements.map) return;

        // Create map centered on Davao City
        map = L.map('map').setView(config.davaoCenter, 14);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        // Load existing path for edit mode
        if (config.initialWaypoints && config.initialWaypoints.length > 0) {
            loadExistingWaypoints(config.initialWaypoints);
        } else if (config.initialPath && config.initialPath.length > 0) {
            // Fallback to path if no waypoints
            loadExistingPath(config.initialPath);
        }

        // Map click handler
        map.on('click', handleMapClick);

        // Initialize controls
        initControls();
    }

    /**
     * Handle map click to add waypoint
     */
    async function handleMapClick(e) {
        if (isLoading || isRouteSet) return; // Don't allow adding points if route is set

        const point = {
            lat: parseFloat(e.latlng.lat.toFixed(6)),
            lng: parseFloat(e.latlng.lng.toFixed(6))
        };

        waypoints.push(point);
        addMarker(point, waypoints.length);
        
        // Fetch road-snapped route
        await updatePathWithRouting();
        updatePathInfo();
        saveToInput();
    }

    /**
     * Add a marker to the map
     */
    function addMarker(point, number) {
        const marker = L.marker([point.lat, point.lng], {
            draggable: true,
            icon: L.divIcon({
                className: 'custom-marker',
                html: `<div class="marker-icon">${number}</div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            })
        }).addTo(map);

        // Marker drag end handler (fetch new route after drag)
        marker.on('dragend', async function(e) {
            const index = markers.indexOf(marker);
            if (index !== -1) {
                waypoints[index] = {
                    lat: parseFloat(e.target.getLatLng().lat.toFixed(6)),
                    lng: parseFloat(e.target.getLatLng().lng.toFixed(6))
                };
                await updatePathWithRouting();
                updatePathInfo();
                saveToInput();
            }
        });

        // Marker popup
        marker.bindPopup(`
            <strong>Point ${number}</strong><br>
            Lat: ${point.lat.toFixed(6)}<br>
            Lng: ${point.lng.toFixed(6)}
        `);

        markers.push(marker);
    }

    /**
     * Fetch route from OSRM and update the path
     */
    async function updatePathWithRouting() {
        if (waypoints.length < 2) {
            // Clear path if less than 2 points
            if (pathPolyline) {
                map.removeLayer(pathPolyline);
                pathPolyline = null;
            }
            fullPath = [];
            updateMarkerClasses();
            return;
        }

        isLoading = true;
        showLoadingState(true);

        try {
            // Build coordinates for OSRM
            const coords = waypoints.map(p => `${p.lng},${p.lat}`).join(';');
            const url = `${OSRM_API}/${coords}?overview=full&geometries=geojson`;

            const response = await fetch(url);
            const data = await response.json();

            if (data.code === 'Ok' && data.routes && data.routes[0]) {
                // Extract the full path from OSRM response
                const route = data.routes[0];
                fullPath = route.geometry.coordinates.map(coord => ({
                    lat: coord[1],
                    lng: coord[0]
                }));

                // Draw the road-snapped path
                drawPath(fullPath);
            } else {
                // Fallback to straight lines if OSRM fails
                console.warn('OSRM routing failed, using straight lines');
                fullPath = [...waypoints];
                drawPath(fullPath);
            }
        } catch (error) {
            console.error('OSRM routing error:', error);
            // Fallback to straight lines
            fullPath = [...waypoints];
            drawPath(fullPath);
        } finally {
            isLoading = false;
            showLoadingState(false);
        }
    }

    /**
     * Draw the path polyline on the map with direction arrows
     */
    function drawPath(points) {
        // Remove existing polyline and arrows
        if (pathPolyline) {
            // Remove arrow markers if they exist
            if (pathPolyline.arrowMarkers) {
                pathPolyline.arrowMarkers.forEach(marker => map.removeLayer(marker));
                pathPolyline.arrowMarkers = [];
            }
            // Remove decorator if it exists
            if (pathPolyline.decorator) {
                map.removeLayer(pathPolyline.decorator);
                pathPolyline.decorator = null;
            }
            map.removeLayer(pathPolyline);
            pathPolyline = null;
        }

        if (points.length >= 2) {
            const color = elements.colorInput ? elements.colorInput.value : config.initialColor;
            
            // Create polyline with arrow decorator
            pathPolyline = L.polyline(
                points.map(p => [p.lat, p.lng]),
                {
                    color: color,
                    weight: 5,
                    opacity: 0.8,
                    lineCap: 'round',
                    lineJoin: 'round'
                }
            ).addTo(map);

            // Add direction arrows using Leaflet polylineDecorator if available
            // If not available, add arrows manually
            addDirectionArrows(pathPolyline, color);
        }

        updateMarkerClasses();
    }

    /**
     * Add direction arrows to the polyline
     */
    function addDirectionArrows(polyline, color) {
        // Check if Leaflet.PolylineDecorator is available
        if (typeof L.polylineDecorator !== 'undefined') {
            // Use polyline decorator plugin
            const decorator = L.polylineDecorator(polyline, {
                patterns: [
                    {
                        offset: '10%',
                        repeat: 80,
                        symbol: L.Symbol.arrowHead({
                            pixelSize: 12,
                            pathOptions: {
                                fillOpacity: 1,
                                weight: 0,
                                color: color
                            }
                        })
                    }
                ]
            }).addTo(map);
            
            // Store reference to remove later
            if (!pathPolyline.decorator) {
                pathPolyline.decorator = decorator;
            }
        } else {
            // Fallback: Add arrow markers manually
            addManualArrows(polyline, color);
        }
    }

    /**
     * Add manual arrow markers along the path (fallback)
     */
    function addManualArrows(polyline, color) {
        const latlngs = polyline.getLatLngs();
        const arrowSpacing = 500; // meters (500m intervals)
        
        // Remove old arrows if they exist
        if (pathPolyline.arrowMarkers) {
            pathPolyline.arrowMarkers.forEach(marker => map.removeLayer(marker));
        }
        pathPolyline.arrowMarkers = [];
        
        // Get contrasting color for arrows
        const arrowColor = getContrastColor(color);

        // Calculate total path length
        let pathLength = 0;
        for (let i = 0; i < latlngs.length - 1; i++) {
            pathLength += map.distance(latlngs[i], latlngs[i + 1]);
        }

        // Place arrows every 500m
        if (pathLength < arrowSpacing) return; // Don't show arrows if path is too short

        let currentDistance = arrowSpacing; // Start at 500m, not 0

        let accumulatedDistance = 0;
        for (let i = 0; i < latlngs.length - 1; i++) {
            const segmentDistance = map.distance(latlngs[i], latlngs[i + 1]);
            
            while (accumulatedDistance + segmentDistance >= currentDistance) {
                const segmentRatio = (currentDistance - accumulatedDistance) / segmentDistance;
                const lat = latlngs[i].lat + (latlngs[i + 1].lat - latlngs[i].lat) * segmentRatio;
                const lng = latlngs[i].lng + (latlngs[i + 1].lng - latlngs[i].lng) * segmentRatio;
                
                // Calculate bearing angle for arrow direction
                const deltaLat = latlngs[i + 1].lat - latlngs[i].lat;
                const deltaLng = latlngs[i + 1].lng - latlngs[i].lng;
                // Subtract 90 degrees because the arrow symbol ➤ points right by default
                const bearing = (Math.atan2(deltaLng, deltaLat) * (180 / Math.PI)) - 90;

                // Create arrow marker pointing in the direction of travel
                const arrowIcon = L.divIcon({
                    className: 'arrow-marker',
                    html: `<div class="arrow-icon" style="transform: rotate(${bearing}deg); color: ${arrowColor};">➤</div>`,
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });

                const marker = L.marker([lat, lng], {
                    icon: arrowIcon,
                    interactive: false
                }).addTo(map);

                pathPolyline.arrowMarkers.push(marker);
                currentDistance += arrowSpacing;
            }

            accumulatedDistance += segmentDistance;
        }
    }

    /**
     * Show/hide loading state
     */
    function showLoadingState(show) {
        if (elements.pathInfo) {
            if (show) {
                elements.pathInfo.innerHTML = `
                    <span><i class="fa-solid fa-spinner fa-spin"></i> Calculating route...</span>
                `;
            }
        }
    }

    /**
     * Update marker classes for start/end indication
     */
    function updateMarkerClasses() {
        markers.forEach((marker, index) => {
            const isFirst = index === 0;
            const isLast = index === markers.length - 1 && markers.length > 1;
            
            let markerClass = '';
            let label = index + 1;
            
            if (isFirst) {
                markerClass = 'start';
                label = '<i class="fa-solid fa-play" style="font-size: 0.65rem;"></i>';
            } else if (isLast) {
                markerClass = 'end';
                label = '<i class="fa-solid fa-stop" style="font-size: 0.6rem;"></i>';
            }
            
            const icon = L.divIcon({
                className: 'custom-marker',
                html: `<div class="marker-icon ${markerClass}">${label}</div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });
            
            marker.setIcon(icon);
            
            // Update popup
            let popupContent = '';
            if (isFirst) {
                popupContent = `
                    <strong><i class="fa-solid fa-play"></i> Starting Point</strong><br>
                    Lat: ${waypoints[index].lat.toFixed(6)}<br>
                    Lng: ${waypoints[index].lng.toFixed(6)}
                `;
            } else if (isLast) {
                popupContent = `
                    <strong><i class="fa-solid fa-stop"></i> End Point</strong><br>
                    Lat: ${waypoints[index].lat.toFixed(6)}<br>
                    Lng: ${waypoints[index].lng.toFixed(6)}
                `;
            } else {
                popupContent = `
                    <strong>Waypoint ${index + 1}</strong><br>
                    Lat: ${waypoints[index].lat.toFixed(6)}<br>
                    Lng: ${waypoints[index].lng.toFixed(6)}
                `;
            }
            
            marker.bindPopup(popupContent);
        });
    }

    /**
     * Update path info display
     */
    function updatePathInfo() {
        if (!elements.pathInfo) return;

        const distance = calculateTotalDistance();
        
        if (isRouteSet) {
            // Show route is finalized
            elements.pathInfo.innerHTML = `
                <span style="color: #10B981;"><i class="fa-solid fa-check-circle"></i> <strong>Route Set</strong></span>
                <span><i class="fa-solid fa-ruler"></i> Distance: <strong>${distance.toFixed(2)} km</strong></span>
            `;
        } else {
            // Show waypoint editing mode
            elements.pathInfo.innerHTML = `
                <span><i class="fa-solid fa-map-pin"></i> Waypoints: <strong>${waypoints.length}</strong></span>
                <span><i class="fa-solid fa-ruler"></i> Distance: <strong>${distance.toFixed(2)} km</strong></span>
            `;
        }
        
        // Update button states
        updateButtonStates();
    }

    /**
     * Calculate total distance from the full path
     */
    function calculateTotalDistance() {
        if (fullPath.length < 2) return 0;

        let total = 0;
        for (let i = 0; i < fullPath.length - 1; i++) {
            total += haversineDistance(
                fullPath[i].lat,
                fullPath[i].lng,
                fullPath[i + 1].lat,
                fullPath[i + 1].lng
            );
        }
        return total;
    }

    /**
     * Haversine distance formula
     */
    function haversineDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Earth's radius in km
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    /**
     * Convert degrees to radians
     */
    function toRad(deg) {
        return deg * (Math.PI / 180);
    }

    /**
     * Save path and waypoints to hidden inputs
     */
    function saveToInput() {
        // Save the full road-snapped path
        if (elements.pathInput) {
            elements.pathInput.value = JSON.stringify(fullPath);
        }
        // Save waypoints (user-clicked points) for editing later
        if (elements.waypointsInput) {
            elements.waypointsInput.value = JSON.stringify(waypoints);
        }
    }

    /**
     * Load existing waypoints for editing
     */
    async function loadExistingWaypoints(points) {
        if (!Array.isArray(points)) return;

        waypoints = points.map(p => ({
            lat: parseFloat(p.lat),
            lng: parseFloat(p.lng)
        }));

        // Add markers
        waypoints.forEach((point, index) => {
            addMarker(point, index + 1);
        });

        // Fetch route and draw path
        await updatePathWithRouting();
        updatePathInfo();

        // Fit map to waypoint bounds
        if (waypoints.length > 0) {
            const bounds = L.latLngBounds(waypoints.map(p => [p.lat, p.lng]));
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    /**
     * Load existing path (fallback if no waypoints)
     */
    function loadExistingPath(path) {
        if (!Array.isArray(path)) return;

        // Use path points as waypoints
        waypoints = path.map(p => ({
            lat: parseFloat(p.lat),
            lng: parseFloat(p.lng)
        }));

        // Add markers for all points
        waypoints.forEach((point, index) => {
            addMarker(point, index + 1);
        });

        // Set fullPath and draw
        fullPath = [...waypoints];
        drawPath(fullPath);
        updatePathInfo();

        // Fit map to bounds
        if (waypoints.length > 0) {
            const bounds = L.latLngBounds(waypoints.map(p => [p.lat, p.lng]));
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    /**
     * Initialize control buttons
     */
    function initControls() {
        // Undo button
        if (elements.undoBtn) {
            elements.undoBtn.addEventListener('click', undoLastPoint);
        }

        // Clear button
        if (elements.clearBtn) {
            elements.clearBtn.addEventListener('click', clearAllPoints);
        }

        // Set route button
        if (elements.setRouteBtn) {
            elements.setRouteBtn.addEventListener('click', toggleRouteSet);
        }

        // Color picker
        if (elements.colorInput) {
            elements.colorInput.addEventListener('input', handleColorChange);
        }

        // Form submit validation
        if (elements.form) {
            elements.form.addEventListener('submit', handleFormSubmit);
        }
    }

    /**
     * Undo last point
     */
    async function undoLastPoint() {
        if (waypoints.length === 0 || isLoading || isRouteSet) return;

        waypoints.pop();
        const lastMarker = markers.pop();
        if (lastMarker) {
            map.removeLayer(lastMarker);
        }

        await updatePathWithRouting();
        updatePathInfo();
        saveToInput();
    }

    /**
     * Clear all points
     */
    function clearAllPoints() {
        if (waypoints.length === 0) return;

        if (!confirm('Are you sure you want to clear the entire route?')) {
            return;
        }

        // Remove all markers
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];

        // Remove polyline and arrows
        if (pathPolyline) {
            // Remove arrow markers if they exist
            if (pathPolyline.arrowMarkers) {
                pathPolyline.arrowMarkers.forEach(marker => map.removeLayer(marker));
                pathPolyline.arrowMarkers = [];
            }
            // Remove decorator if it exists
            if (pathPolyline.decorator) {
                map.removeLayer(pathPolyline.decorator);
                pathPolyline.decorator = null;
            }
            map.removeLayer(pathPolyline);
            pathPolyline = null;
        }

        // Clear points and route state
        waypoints = [];
        fullPath = [];
        isRouteSet = false;

        updatePathInfo();
        saveToInput();
    }

    /**
     * Toggle route set - keeps start/end visible, hides middle waypoints
     */
    function toggleRouteSet() {
        if (waypoints.length < 2 || isLoading) return;

        isRouteSet = !isRouteSet;
        
        if (isRouteSet) {
            // Keep start and end markers visible, hide middle waypoints
            markers.forEach((marker, index) => {
                const isFirst = index === 0;
                const isLast = index === markers.length - 1;
                
                if (isFirst || isLast) {
                    // Keep start/end visible but not draggable
                    marker.setOpacity(1);
                    marker.dragging.disable();
                } else {
                    // Hide middle waypoints
                    marker.setOpacity(0);
                    marker.dragging.disable();
                }
            });
        } else {
            // Show all waypoint markers and make draggable
            markers.forEach(marker => {
                marker.setOpacity(1);
                marker.dragging.enable();
            });
        }
        
        updatePathInfo();
    }

    /**
     * Update button states based on route status
     */
    function updateButtonStates() {
        // Update set route button
        if (elements.setRouteBtn) {
            if (isRouteSet) {
                elements.setRouteBtn.innerHTML = '<i class="fa-solid fa-edit"></i> Edit Route';
                elements.setRouteBtn.classList.add('btn-warning');
                elements.setRouteBtn.classList.remove('btn-primary');
            } else {
                elements.setRouteBtn.innerHTML = '<i class="fa-solid fa-check"></i> Set Route';
                elements.setRouteBtn.classList.remove('btn-warning');
                elements.setRouteBtn.classList.add('btn-primary');
            }
        }
        
        // Disable/enable other buttons
        if (elements.undoBtn) {
            elements.undoBtn.disabled = isRouteSet;
        }
        if (elements.clearBtn) {
            elements.clearBtn.disabled = isRouteSet;
        }
    }

    /**
     * Handle color change
     */
    function handleColorChange(e) {
        const color = e.target.value;

        // Update color value display
        if (elements.colorValue) {
            elements.colorValue.textContent = color.toUpperCase();
        }

        // Update polyline color and redraw arrows with new color
        if (pathPolyline) {
            pathPolyline.setStyle({ color: color });
            
            // Redraw arrows with new color
            if (pathPolyline.arrowMarkers) {
                pathPolyline.arrowMarkers.forEach(marker => {
                    const icon = marker.getElement();
                    if (icon) {
                        const arrow = icon.querySelector('.arrow-icon');
                        if (arrow) {
                            arrow.style.color = color;
                        }
                    }
                });
            }
            
            // Update decorator color if using plugin
            if (pathPolyline.decorator) {
                map.removeLayer(pathPolyline.decorator);
                addDirectionArrows(pathPolyline, color);
            }
        }
    }

    /**
     * Handle form submission
     */
    function handleFormSubmit(e) {
        // Validate minimum points
        if (waypoints.length < 2) {
            e.preventDefault();
            alert('Please add at least 2 waypoints to create a valid route path.');
            return false;
        }

        // Show loading state
        if (elements.submitBtn) {
            elements.submitBtn.disabled = true;
            elements.submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        }

        return true;
    }

    /**
     * Get contrasting color (black or white) based on background color
     */
    function getContrastColor(hexColor) {
        // Remove # if present
        const hex = hexColor.replace('#', '');
        
        // Convert to RGB
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);
        
        // Calculate luminance
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        
        // Return black for light colors, white for dark colors
        return luminance > 0.5 ? '#000000' : '#FFFFFF';
    }

    /**
     * Initialize when DOM is ready
     */
    function init() {
        initMap();
        
        // Initialize color value display
        if (elements.colorInput && elements.colorValue) {
            elements.colorValue.textContent = elements.colorInput.value.toUpperCase();
        }
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
