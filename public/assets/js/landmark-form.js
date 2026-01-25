// Landmark Form JavaScript

// Initialize map
const map = L.map('map').setView([7.0731, 125.6128], 13); // Davao City center

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

// Marker for landmark location
let marker = null;

// Set initial marker if editing
if (window.landmarkData) {
    marker = L.marker([window.landmarkData.latitude, window.landmarkData.longitude], {
        draggable: true
    }).addTo(map);
    
    marker.on('dragend', function(e) {
        const position = e.target.getLatLng();
        updateCoordinates(position.lat, position.lng);
    });
} else {
    // Get initial coordinates from form
    const lat = parseFloat(document.getElementById('latitude').value);
    const lng = parseFloat(document.getElementById('longitude').value);
    
    if (lat && lng) {
        marker = L.marker([lat, lng], {
            draggable: true
        }).addTo(map);
        
        marker.on('dragend', function(e) {
            const position = e.target.getLatLng();
            updateCoordinates(position.lat, position.lng);
        });
    }
}

// Click on map to set location
map.on('click', function(e) {
    const lat = e.latlng.lat;
    const lng = e.latlng.lng;
    
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng], {
            draggable: true
        }).addTo(map);
        
        marker.on('dragend', function(e) {
            const position = e.target.getLatLng();
            updateCoordinates(position.lat, position.lng);
        });
    }
    
    updateCoordinates(lat, lng);
});

function updateCoordinates(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(8);
    document.getElementById('longitude').value = lng.toFixed(8);
}

// Icon Image Preview
const iconInput = document.getElementById('icon_image');
const iconPreview = document.getElementById('iconPreview');
const iconPreviewImg = document.getElementById('iconPreviewImg');
let currentIconFile = null;

if (iconInput) {
    iconInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size
            if (file.size > 2 * 1024 * 1024) {
                alert('Icon file is too large. Maximum size is 2MB.');
                iconInput.value = '';
                return;
            }
            
            currentIconFile = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                iconPreviewImg.src = e.target.result;
                iconPreview.style.display = 'block';
                
                // Update file input label text
                const label = document.querySelector('label[for="icon_image"].file-input-label');
                if (label) {
                    label.querySelector('span').textContent = file.name;
                }
                
                // Add remove button if it doesn't exist
                if (!iconPreview.querySelector('.remove-icon-preview-btn')) {
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn btn-sm btn-danger mt-1 remove-icon-preview-btn';
                    removeBtn.style.cssText = 'font-size: 0.7rem; padding: 0.25rem 0.5rem;';
                    removeBtn.innerHTML = '<i class="fa-solid fa-trash"></i> Remove';
                    removeBtn.onclick = function() {
                        iconInput.value = '';
                        currentIconFile = null;
                        iconPreview.style.display = 'none';
                        iconPreviewImg.src = '';
                        // Reset label text
                        const label = document.querySelector('label[for="icon_image"].file-input-label');
                        if (label) {
                            label.querySelector('span').textContent = 'Choose Icon Image';
                        }
                        this.remove();
                    };
                    iconPreview.appendChild(removeBtn);
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

// Gallery Images Preview
const galleryInput = document.getElementById('gallery_images');
const galleryPreview = document.getElementById('galleryPreview');
const galleryPreviewContainer = document.getElementById('galleryPreviewContainer');
let selectedFiles = [];

if (galleryInput) {
    galleryInput.addEventListener('change', function(e) {
        const newFiles = Array.from(e.target.files);
        
        if (newFiles.length === 0) return;
        
        // Add new files to existing selection
        const combinedFiles = [...selectedFiles, ...newFiles];
        
        // Limit to 10 images
        if (combinedFiles.length > 10) {
            alert(`Maximum 10 images allowed. You can add ${10 - selectedFiles.length} more image(s).`);
            return;
        }
        
        // Check file sizes
        for (let file of newFiles) {
            if (file.size > 5 * 1024 * 1024) { // 5MB
                alert(`File ${file.name} is too large. Maximum size is 5MB.`);
                return;
            }
        }
        
        // Add new files to selection
        selectedFiles = combinedFiles;
        
        // Update file input label text
        const label = document.querySelector('label[for="gallery_images"].file-input-label');
        if (label) {
            const span = label.querySelector('span');
            if (span) {
                span.textContent = `${selectedFiles.length} image(s) selected`;
            }
        }
        
        displayGalleryPreviews();
    });
}

function displayGalleryPreviews() {
    if (!galleryPreviewContainer) return;
    
    galleryPreviewContainer.innerHTML = '';
    
    if (selectedFiles.length === 0) {
        galleryPreview.style.display = 'none';
        return;
    }
    
    galleryPreview.style.display = 'block';
    
    selectedFiles.forEach((file, index) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'gallery-item';
            div.style.cssText = 'position: relative; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 0.5rem;';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Gallery ${index + 1}" 
                     class="img-thumbnail"
                     style="width: 100%; height: 100px; object-fit: cover; border: 1px solid #E2E8F0;">
                <button type="button" class="btn btn-sm btn-danger mt-1" data-index="${index}" 
                        style="font-size: 0.7rem; padding: 0.25rem 0.5rem; width: 100%;">
                    <i class="fa-solid fa-trash"></i> Remove
                </button>
            `;
            
            // Add click event to remove button
            div.querySelector('button').addEventListener('click', function() {
                removeGalleryPreview(parseInt(this.getAttribute('data-index')));
            });
            
            galleryPreviewContainer.appendChild(div);
        };
        
        reader.readAsDataURL(file);
    });
}

function removeGalleryPreview(index) {
    selectedFiles.splice(index, 1);
    
    // Update the file input with remaining files
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    if (galleryInput) {
        galleryInput.files = dataTransfer.files;
    }
    
    // Update label text
    const label = document.querySelector('label[for="gallery_images"].file-input-label');
    if (label) {
        const span = label.querySelector('span');
        if (span) {
            if (selectedFiles.length > 0) {
                span.textContent = `${selectedFiles.length} image(s) selected`;
            } else {
                span.textContent = 'Choose Gallery Images';
            }
        }
    }
    
    displayGalleryPreviews();
}

// Form validation before submit
document.querySelector('form').addEventListener('submit', function(e) {
    const lat = document.getElementById('latitude').value;
    const lng = document.getElementById('longitude').value;
    
    if (!lat || !lng || lat === '0' || lng === '0') {
        e.preventDefault();
        alert('Please select a location on the map');
        return false;
    }
    
    // Check if icon is required (for create form)
    const isEditForm = window.landmarkData !== undefined;
    const iconFile = currentIconFile !== null;
    const removeIconChecked = document.getElementById('remove_icon')?.checked;
    
    if (!isEditForm && !iconFile) {
        e.preventDefault();
        alert('Please upload an icon image');
        return false;
    }
    
    // For edit form, ensure icon exists or is being uploaded
    if (isEditForm && removeIconChecked && !iconFile) {
        e.preventDefault();
        alert('You cannot remove the icon without uploading a new one');
        return false;
    }
    
    // Update gallery input with selected files before submit
    if (selectedFiles.length > 0) {
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        if (galleryInput) {
            galleryInput.files = dataTransfer.files;
        }
    }
    
    // Validate gallery images count
    const totalGalleryImages = selectedFiles.length;
    if (totalGalleryImages > 10) {
        e.preventDefault();
        alert('Maximum 10 gallery images allowed');
        return false;
    }
    
    return true;
});

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
