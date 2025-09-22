// Function to update appointment/reservation status via AJAX (Point 5)
document.addEventListener('DOMContentLoaded', () => {
    const toggleCheckboxes = document.querySelectorAll('.toggle-checkbox');
    const modal = document.getElementById('mainGalleryModal');
    const modalImage = document.getElementById('modalMainImage');
    const prevButton = document.getElementById('prevMainPhoto');
    const nextButton = document.getElementById('nextMainPhoto');
    const dropAreas = document.querySelectorAll('.drop-area');

    let currentGalleryImages = [];
    let currentImageIndex = 0;
    
    // ----------------------------------------------------
    // Point 5: Appointment Checkbox Toggle (Updated to use consolidated API)
    // ----------------------------------------------------
    toggleCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const isChecked = this.checked;
            const vehicleId = this.dataset.vehicleId;
            const field = this.dataset.field;
            const newValue = isChecked ? 1 : 0;
            
            // The label/toggle UI update is now managed by the change event
            const label = this.nextElementSibling;
            if (label) {
                label.classList.toggle('bg-green-500', isChecked);
                label.classList.toggle('bg-red-500', !isChecked);
            }

            // AJAX call to update the flag
            fetch('api/toggle_vehicle_flag.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `vehicle_id=${vehicleId}&field=${field}&new_value=${newValue}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                    // Revert the checkbox state on error
                    this.checked = !isChecked;
                    if (label) {
                         label.classList.toggle('bg-green-500', !isChecked);
                         label.classList.toggle('bg-red-500', isChecked);
                    }
                }
            })
            .catch(error => {
                showNotification('Network error during status update.', 'error');
                console.error('Toggle Error:', error);
                 // Revert the checkbox state on error
                this.checked = !isChecked;
                if (label) {
                    label.classList.toggle('bg-green-500', !isChecked);
                    label.classList.toggle('bg-red-500', isChecked);
                }
            });
        });
    });
    
    // ----------------------------------------------------
    // Photo Gallery Modal Navigation Logic
    // ----------------------------------------------------
    function showNextPhoto() {
        if (currentGalleryImages.length > 0) {
            currentImageIndex = (currentImageIndex + 1) % currentGalleryImages.length;
            modalImage.src = currentGalleryImages[currentImageIndex].image_path;
            document.getElementById('modalImageDetails').textContent = currentGalleryImages[currentImageIndex].details;
        }
    }

    function showPrevPhoto() {
        if (currentGalleryImages.length > 0) {
            currentImageIndex = (currentImageIndex - 1 + currentGalleryImages.length) % currentGalleryImages.length;
            modalImage.src = currentGalleryImages[currentImageIndex].image_path;
            document.getElementById('modalImageDetails').textContent = currentGalleryImages[currentImageIndex].details;
        }
    }

    prevButton.addEventListener('click', showPrevPhoto);
    nextButton.addEventListener('click', showNextPhoto);

    // Close modal on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === "Escape" && !modal.classList.contains('hidden')) {
            window.closeMainGalleryModal();
        }
    });

    // Close modal on backdrop click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            window.closeMainGalleryModal();
        }
    });

    // ----------------------------------------------------
    // Image Deletion Function
    // ----------------------------------------------------
    window.deleteImage = function(imageId, element) {
        if (!window.CAN_MANAGE_PHOTOS || !confirm('Are you sure you want to delete this image?')) {
            return;
        }

        // Optimistically hide the element
        element.style.opacity = '0.5';

        fetch('api/delete_image.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `image_id=${imageId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                // Fully remove the element on success
                element.remove();
                // Refresh images in modal context
                fetchImages(window.VEHICLE_ID);
            } else {
                showNotification(data.message, 'error');
                // Revert hiding on error
                element.style.opacity = '1';
            }
        })
        .catch(error => {
            showNotification('Error deleting image.', 'error');
            console.error('Delete Error:', error);
            // Revert hiding on error
            element.style.opacity = '1';
        });
    }

    // ----------------------------------------------------
    // Gallery AJAX Fetch and Sortable Initialization
    // ----------------------------------------------------

    // Object to hold Sortable instances to prevent re-initialization
    let sortableInstances = {};

    /**
     * Re-initializes Sortable.js for all galleries and drag/drop listeners.
     */
    window.initializeDragAndDropAndSortable = function() {
        // 1. Destroy old Sortable instances
        for (const type in sortableInstances) {
            if (sortableInstances[type]) {
                sortableInstances[type].destroy();
            }
        }
        sortableInstances = {}; // Reset container

        // 2. Initialize Sortable for each gallery
        document.querySelectorAll('.image-grid').forEach(grid => {
            const galleryType = grid.dataset.galleryType;
            // Documents are not sortable for now, only images
            if (galleryType !== 'document') {
                sortableInstances[galleryType] = Sortable.create(grid, {
                    animation: 150,
                    ghostClass: 'bg-blue-200',
                    filter: '.text-gray-500', // Exclude "No images" message
                    onEnd: function (evt) {
                        const order = Array.from(evt.to.children)
                            .filter(el => el.dataset.id) // Only elements with image ID
                            .map(el => parseInt(el.dataset.id));
                        
                        updateImageOrder(galleryType, order);
                    }
                });
            }
        });

        // 3. Re-initialize Feather Icons for newly loaded content
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }

    /**
     * Sends the new image order to the server.
     * @param {string} galleryType 
     * @param {array} order 
     */
    function updateImageOrder(galleryType, order) {
         fetch('api/update_image_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ gallery_type: galleryType, order: order })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                // Reload images to update gallery for correct main gallery modal context
                fetchImages(window.VEHICLE_ID);
            } else {
                showNotification(data.message, 'error');
                // Simple page reload on failure to restore correct order
                setTimeout(() => window.location.reload(), 1000);
            }
        })
        .catch(error => {
            showNotification('Error updating image order.', 'error');
            console.error('Order Update Error:', error);
            setTimeout(() => window.location.reload(), 1000);
        });
    }


    /**
     * Fetches and injects all gallery content via AJAX.
     * @param {number} vehicleId 
     * @returns {Promise}
     */
    window.fetchImages = function(vehicleId) {
        return fetch(`api/fetch_images.php?vehicle_id=${vehicleId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update HTML containers
                    document.getElementById('preGalleryContainer').innerHTML = data.galleries.pre;
                    document.getElementById('postGalleryContainer').innerHTML = data.galleries.post;
                    document.getElementById('damageGalleryContainer').innerHTML = data.galleries.damage; // ADDED
                    document.getElementById('documentGalleryContainer').innerHTML = data.galleries.document;
                    
                    // Update global images array for modal navigation
                    currentGalleryImages = data.all_images.map(img => ({
                        image_path: img.image_path,
                        details: `Uploaded by ${img.username} on ${new Date(img.created_at).toLocaleDateString()}`
                    }));
                    
                    // Update tab counts (simplified for demo, relies on re-rendering)
                    
                    // Re-initialize drag/sortable after content update
                    initializeDragAndDropAndSortable();

                } else {
                    showNotification('Failed to fetch images: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Network error fetching images.', 'error');
                console.error('Fetch Images Error:', error);
            });
    }


    // ----------------------------------------------------
    // Drag & Drop Initialization
    // ----------------------------------------------------
    const dropAreaIds = [
        'preDropArea', 
        'postDropArea', 
        'damageDropArea', // ADDED: Damage drop area
        'documentDropArea'
    ];
    
    dropAreaIds.forEach(areaId => {
        const dropArea = document.getElementById(areaId);
        
        // Skip if the drop area doesn't exist (e.g., for users without photo_manager role)
        if (!dropArea) return; 

        // Extract image type from the ID (preDropArea -> pre)
        const imageType = areaId.replace('DropArea', '');
        // Corresponding file input ID
        const inputId = imageType + 'FileInput';
        
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, e => {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });
        
        // Highlight drop area when item is dragged over
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.add('drag-over', 'border-blue-500', 'bg-blue-50'), false);
        });

        // Remove highlight when drag leaves or ends
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.remove('drag-over', 'border-blue-500', 'bg-blue-50'), false);
        });

        // Handle dropped files
        dropArea.addEventListener('drop', function(e) {
            e.preventDefault();
            const files = Array.from(e.dataTransfer.files);
            // The VEHICLE_ID constant is defined in details.php <script> block
            window.uploadFiles(files, areaId, window.VEHICLE_ID, imageType); 
        }, false);
        
        // Handle click on drop area
        dropArea.addEventListener('click', function() {
            document.getElementById(inputId).click();
        });
    });


});


// Re-declare showNotification from footer.php for global use in AJAX handlers
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-2 rounded-md shadow-md z-50 transition-transform duration-300 ease-out transform translate-y-0 opacity-100 ${
        type === 'success' ? 'bg-green-500 text-white' : (type === 'error' ? 'bg-red-500 text-white' : 'bg-yellow-500 text-white')
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.classList.add('translate-y-full', 'opacity-0');
        // Remove element after transition completes
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}