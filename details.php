<?php
require_once 'config.php';
checkAuth();
// Get VIN from URL parameter
$vin = isset($_GET['vin']) ? $_GET['vin'] : '';
$current_role = $_SESSION['role'] ?? 'guest';
$is_manager_or_admin = in_array($current_role, ['admin', 'vehicle_manager']);
$can_manage_photos = in_array($current_role, ['admin', 'photo_manager']);
$can_manage_payments = in_array($current_role, ['admin', 'payment_manager']);


// Fetch vehicle information from the database
try {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE vin = ?");
    $stmt->execute([$vin]);
    $vehicle = $stmt->fetch();
    // If vehicle is not found, redirect to home
    if (!$vehicle) {
        $_SESSION['error'] = "Vehicle not found.";
        header('Location: index.php');
        exit();
    }
    $vehicle_id = $vehicle['id'];
    
    // Fetch images for this vehicle
    $stmt = $pdo->prepare("
        SELECT vi.*, u.username 
        FROM vehicle_images vi 
        LEFT JOIN users u ON vi.uploaded_by = u.id 
        WHERE vi.vehicle_id = ? 
        ORDER BY vi.image_type, vi.image_order, vi.created_at
    ");
    $stmt->execute([$vehicle_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group images by type for display
    $groupedImages = [
        'pre' => [],
        'post' => [],
        'damage' => [], // Added 'damage'
        'document' => []
    ];
    foreach ($images as $image) {
        if (isset($groupedImages[$image['image_type']])) {
            $groupedImages[$image['image_type']][] = $image;
        }
    }
    
    // Fetch comments for this vehicle
    $stmt_comments = $pdo->prepare("
        SELECT vc.*, u.username, u.role
        FROM vehicle_comments vc
        JOIN users u ON vc.user_id = u.id
        WHERE vc.vehicle_id = ?
        ORDER BY vc.created_at DESC
    ");
    $stmt_comments->execute([$vehicle_id]);
    $comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    // Log the error for debugging
    error_log("Data fetch error in details.php: " . $e->getMessage());
    $_SESSION['error'] = "Data fetch error: A database error occurred.";
    header('Location: index.php');
    exit();
}

$page_title = "Vehicle Details - " . $vin;
include 'header.php';

// JavaScript data for client-side use
echo "<script>const VEHICLE_ID = {$vehicle_id};</script>";
?>

<main class="container mx-auto px-4 py-6 flex-grow">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 border-b pb-4">
        <h2 class="text-3xl font-bold text-gray-800 break-all mb-4 md:mb-0">
            Details: <?php echo htmlspecialchars($vehicle['vin']); ?>
            <span class="status-badge bg-<?php echo $vehicle['is_archived'] ? 'red' : 'green'; ?>-500 text-white ml-3 text-sm font-semibold">
                <?php echo $vehicle['is_archived'] ? 'ARCHIVED' : 'ACTIVE'; ?>
            </span>
        </h2>
        
        <div class="flex space-x-3">
            <?php if ($is_manager_or_admin && !$vehicle['is_archived']): ?>
            <a href="edit_vehicle.php?vin=<?php echo urlencode($vehicle['vin']); ?>" class="btn-gradient bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition text-sm">
                <i data-feather="edit-3" class="w-4 h-4 inline-block mr-1"></i> Edit Details
            </a>
            <button onclick="openArchiveModal('<?php echo htmlspecialchars($vehicle['vin']); ?>')" class="btn-gradient bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition text-sm">
                <i data-feather="archive" class="w-4 h-4 inline-block mr-1"></i> Archive
            </button>
            <?php elseif ($current_role == 'admin' && $vehicle['is_archived']): ?>
            <form action="unarchive_vehicle.php" method="POST" onsubmit="return confirm('Are you sure you want to UNARCHIVE this vehicle?');">
                <input type="hidden" name="vin" value="<?php echo htmlspecialchars($vehicle['vin']); ?>">
                <button type="submit" class="btn-gradient bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition text-sm">
                    <i data-feather="folder-plus" class="w-4 h-4 inline-block mr-1"></i> Unarchive
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 bg-white p-6 rounded-lg shadow-lg">
            <h3 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Vehicle Information</h3>
            <div class="space-y-3 text-sm text-gray-600">
                <p><strong>VIN:</strong> <span class="font-mono text-gray-800 break-all"><?php echo htmlspecialchars($vehicle['vin']); ?></span></p>
                <p><strong>Make:</strong> <?php echo htmlspecialchars($vehicle['make'] ?? 'N/A'); ?></p>
                <p><strong>Model:</strong> <?php echo htmlspecialchars($vehicle['model'] ?? 'N/A'); ?></p>
                <p><strong>Year:</strong> <?php echo htmlspecialchars($vehicle['year'] ?? 'N/A'); ?></p>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($vehicle['body_type'] ?? $vehicle['vehicle_type'] ?? $vehicle['type'] ?? 'N/A'); ?></p>
                <p><strong>Price:</strong> $<span class="text-green-600 font-bold"><?php echo htmlspecialchars(number_format($vehicle['price'], 2)); ?></span></p>
                <p><strong>Created:</strong> <?php echo date('d.m.Y H:i', strtotime($vehicle['created_at'])); ?></p>
                <p><strong>Last Updated:</strong> <?php echo date('d.m.Y H:i', strtotime($vehicle['updated_at'])); ?></p>
            </div>
            
            <h3 class="text-xl font-semibold mt-6 mb-4 text-gray-700 border-b pb-2">Logistics</h3>
            <div class="space-y-3 text-sm text-gray-600">
                <p><strong>From:</strong> <?php echo htmlspecialchars($vehicle['from_location'] . ', ' . $vehicle['from_state']); ?></p>
                <p><strong>To:</strong> <?php echo htmlspecialchars($vehicle['to_location'] . ', ' . $vehicle['to_state']); ?></p>
                <p><strong>Distance:</strong> <?php echo htmlspecialchars($vehicle['distance'] ?? 'N/A'); ?></p>
                <p><strong>Est. Time:</strong> <?php echo htmlspecialchars($vehicle['estimated_time'] ?? 'N/A'); ?></p>
                <p><strong>Pickup Date:</strong> <span class="text-indigo-600"><?php echo date('d.m.Y', strtotime($vehicle['pickup_date'])); ?></span></p>
                <p><strong>Delivery Date:</strong> <span class="text-indigo-600"><?php echo $vehicle['delivery_date'] ? date('d.m.Y', strtotime($vehicle['delivery_date'])) : 'N/A'; ?></span></p>
            </div>
            
            <h3 class="text-xl font-semibold mt-6 mb-4 text-gray-700 border-b pb-2">Transporter</h3>
            <div class="space-y-3 text-sm text-gray-600">
                <p><strong>Contact:</strong> <?php echo htmlspecialchars($vehicle['transporter_contact'] ?? 'N/A'); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($vehicle['transporter_phone'] ?? 'N/A'); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($vehicle['transporter_email'] ?? 'N/A'); ?></p>
            </div>
            
            <h3 class="text-xl font-semibold mt-6 mb-4 text-gray-700 border-b pb-2">Status & Notes</h3>
            <div class="space-y-3 text-sm text-gray-600">
                <?php if ($can_manage_payments): ?>
                    <form action="update_payment.php?vin=<?php echo urlencode($vehicle['vin']); ?>" method="POST" class="flex flex-col space-y-2">
                        <input type="hidden" name="vehicle_id" value="<?php echo $vehicle_id; ?>">
                        <label for="payment_status" class="block font-semibold">Payment Status:</label>
                        <select name="payment_status" id="payment_status" onchange="this.form.submit()" class="p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm w-full">
                            <option value="pending" <?php echo ($vehicle['payment_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="paid" <?php echo ($vehicle['payment_status'] == 'paid') ? 'selected' : ''; ?>>Paid</option>
                            <option value="partially_paid" <?php echo ($vehicle['payment_status'] == 'partially_paid') ? 'selected' : ''; ?>>Partially Paid</option>
                            <option value="failed" <?php echo ($vehicle['payment_status'] == 'failed') ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </form>
                <?php else: ?>
                    <p><strong>Payment Status:</strong> 
                        <span class="font-semibold <?php echo ($vehicle['payment_status'] == 'paid') ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $vehicle['payment_status']))); ?>
                        </span>
                    </p>
                <?php endif; ?>

                <p><strong>Fines/Tolls:</strong> $<span class="text-red-600 font-bold"><?php echo htmlspecialchars(number_format($vehicle['fines'], 2)); ?></span></p>

                <div class="flex justify-between items-center py-2">
                    <span class="font-semibold">Auction:</span>
                    <?php if ($is_manager_or_admin): ?>
                        <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                            <input type="checkbox" data-vehicle-id="<?php echo $vehicle_id; ?>" data-field="auction" id="auction_toggle" <?php echo $vehicle['auction'] ? 'checked' : ''; ?> class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <label for="auction_toggle" class="toggle-label block overflow-hidden h-6 rounded-full cursor-pointer <?php echo $vehicle['auction'] ? 'bg-green-500' : 'bg-red-500'; ?>"></label>
                        </div>
                    <?php else: ?>
                        <span class="font-bold <?php echo $vehicle['auction'] ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $vehicle['auction'] ? 'Yes' : 'No'; ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="flex justify-between items-center py-2">
                    <span class="font-semibold">Warehouse:</span>
                    <?php if ($is_manager_or_admin): ?>
                        <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                            <input type="checkbox" data-vehicle-id="<?php echo $vehicle_id; ?>" data-field="warehouse" id="warehouse_toggle" <?php echo $vehicle['warehouse'] ? 'checked' : ''; ?> class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <label for="warehouse_toggle" class="toggle-label block overflow-hidden h-6 rounded-full cursor-pointer <?php echo $vehicle['warehouse'] ? 'bg-green-500' : 'bg-red-500'; ?>"></label>
                        </div>
                    <?php else: ?>
                        <span class="font-bold <?php echo $vehicle['warehouse'] ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $vehicle['warehouse'] ? 'Yes' : 'No'; ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="flex justify-between items-center py-2">
                    <span class="font-semibold">Appointment:</span>
                    <?php if ($is_manager_or_admin): ?>
                        <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                            <input type="checkbox" data-vehicle-id="<?php echo $vehicle_id; ?>" data-field="appointment" id="appointment_toggle" <?php echo $vehicle['appointment'] ? 'checked' : ''; ?> class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <label for="appointment_toggle" class="toggle-label block overflow-hidden h-6 rounded-full cursor-pointer <?php echo $vehicle['appointment'] ? 'bg-green-500' : 'bg-red-500'; ?>"></label>
                        </div>
                    <?php else: ?>
                        <span class="font-bold <?php echo $vehicle['appointment'] ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $vehicle['appointment'] ? 'Set' : 'No'; ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="flex justify-between items-center py-2">
                    <span class="font-semibold">Auction Reservation:</span>
                    <?php if ($is_manager_or_admin): ?>
                        <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                            <input type="checkbox" data-vehicle-id="<?php echo $vehicle_id; ?>" data-field="auction_reservation" id="auction_reservation_toggle" <?php echo $vehicle['auction_reservation'] ? 'checked' : ''; ?> class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <label for="auction_reservation_toggle" class="toggle-label block overflow-hidden h-6 rounded-full cursor-pointer <?php echo $vehicle['auction_reservation'] ? 'bg-green-500' : 'bg-red-500'; ?>"></label>
                        </div>
                    <?php else: ?>
                        <span class="font-bold <?php echo $vehicle['auction_reservation'] ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $vehicle['auction_reservation'] ? 'Set' : 'No'; ?>
                        </span>
                    <?php endif; ?>
                </div>

                <p class="pt-3"><strong>Notes:</strong></p>
                <div class="p-3 bg-gray-50 border border-gray-200 rounded-md whitespace-pre-wrap text-xs max-h-32 overflow-y-auto">
                    <?php echo nl2br(htmlspecialchars($vehicle['notes'] ?? 'No notes available.')); ?>
                </div>
                
                <?php if ($vehicle['is_archived']): ?>
                <p class="pt-3"><strong>Archive Reason:</strong></p>
                <div class="p-3 bg-red-50 border border-red-200 rounded-md whitespace-pre-wrap text-xs max-h-32 overflow-y-auto font-semibold text-red-700">
                    <?php echo nl2br(htmlspecialchars($vehicle['archive_reason'] ?? 'Reason not specified.')); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-lg">
            <h3 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Vehicle Gallery</h3>

            <div class="flex border-b border-gray-200 mb-4 overflow-x-auto">
                <button data-tab="pre" class="tab-btn active px-4 py-2 text-sm font-medium text-blue-600 border-b-2 border-blue-600 hover:text-blue-800 focus:outline-none transition">Pre-load (<?php echo count($groupedImages['pre']); ?>)</button>
                <button data-tab="post" class="tab-btn px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 border-b-2 border-transparent hover:border-gray-300 focus:outline-none transition">Post-load (<?php echo count($groupedImages['post']); ?>)</button>
                <button data-tab="damage" class="tab-btn px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 border-b-2 border-transparent hover:border-gray-300 focus:outline-none transition">Damage (<?php echo count($groupedImages['damage']); ?>)</button>
                <button data-tab="document" class="tab-btn px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 border-b-2 border-transparent hover:border-gray-300 focus:outline-none transition">Documents (<?php echo count($groupedImages['document']); ?>)</button>
            </div>

            <div id="gallery-container">
                <div class="text-center py-10 text-gray-500">Loading images...</div>
            </div>
        </div>
        
        <div class="lg:col-span-3 bg-white p-6 rounded-lg shadow-lg comments-container">
            <h3 class="text-xl font-semibold mb-6 text-gray-700 border-b pb-2">Comments (<?php echo count($comments); ?>)</h3>
            
            <form action="add_comment.php" method="POST" enctype="multipart/form-data" class="comment-form mb-8 border p-4 rounded-lg bg-gray-50">
                <input type="hidden" name="vehicle_id" value="<?php echo $vehicle_id; ?>">
                <input type="hidden" name="vin" value="<?php echo htmlspecialchars($vehicle['vin']); ?>">
                <textarea name="comment" required rows="3" placeholder="Add your comment..." class="w-full mb-3 text-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                
                <div class="flex justify-between items-center space-x-4">
                    <?php if ($can_manage_photos): ?>
                    <label for="comment_image" class="flex-grow flex items-center px-4 py-2 bg-white text-blue-600 border border-blue-600 rounded-md cursor-pointer hover:bg-blue-50 transition text-sm font-medium">
                        <i data-feather="upload" class="w-4 h-4 mr-2"></i> 
                        Attach Image (Optional)
                        <input type="file" name="comment_image" id="comment_image" class="hidden" accept="image/*, .pdf">
                    </label>
                    <?php endif; ?>

                    <button type="submit" class="flex-shrink-0 btn-gradient bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition text-sm">
                        Post Comment
                    </button>
                </div>
            </form>

            <div id="comments-list">
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $comment): ?>
                        <div id="comment-<?php echo $comment['id']; ?>" class="comment-item flex items-start space-x-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center bg-blue-100 text-blue-600 font-semibold text-xs">
                                <?php echo htmlspecialchars(strtoupper(substr($comment['username'], 0, 1))); ?>
                            </div>
                            <div class="flex-grow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($comment['username']); ?>
                                            <span class="text-xs font-normal text-gray-500 ml-2">
                                                (<?php echo htmlspecialchars(ucfirst($comment['role'])); ?>)
                                            </span>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?>
                                        </p>
                                    </div>
                                    
                                    <?php if ($is_manager_or_admin): ?>
                                    <button 
                                        type="button" 
                                        data-comment-id="<?php echo $comment['id']; ?>" 
                                        class="delete-comment-btn text-red-500 hover:text-red-700 transition" 
                                        aria-label="Delete comment">
                                        <i data-feather="trash-2" class="w-4 h-4"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <p class="mt-2 text-gray-700 whitespace-pre-wrap text-sm"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                
                                <?php if ($comment['image_path']): ?>
                                    <?php 
                                        $ext = strtolower(pathinfo($comment['image_path'], PATHINFO_EXTENSION));
                                        $is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                    ?>
                                    <div class="mt-3">
                                        <?php if ($is_image): ?>
                                            <a href="<?php echo htmlspecialchars($comment['image_path']); ?>" target="_blank">
                                                <img src="<?php echo htmlspecialchars($comment['image_path']); ?>" alt="Comment image" class="h-32 w-auto object-cover rounded-lg shadow cursor-pointer border border-gray-200">
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo htmlspecialchars($comment['image_path']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                                                <i data-feather="file-text" class="w-4 h-4 mr-1"></i> View Document (<?php echo strtoupper($ext); ?>)
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">No comments yet. Be the first to add one!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include 'archive_modal.php'; ?>

<div id="mainGalleryModal" class="modal hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4">
    <div class="relative w-full h-full max-w-5xl max-h-5xl">
        <button onclick="closeMainGalleryModal()" class="absolute top-4 right-4 text-white z-50 bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75 transition" aria-label="Close modal">
            <i data-feather="x" class="w-6 h-6"></i>
        </button>
        <div class="flex items-center justify-center h-full w-full">
            <button id="prevMainPhoto" class="absolute left-0 ml-4 text-white bg-black bg-opacity-50 p-3 rounded-full hover:bg-opacity-75 transition z-40" aria-label="Previous image">
                <i data-feather="chevron-left" class="w-8 h-8"></i>
            </button>
            <img id="modalMainImage" src="" alt="Vehicle Image" class="max-w-full max-h-full object-contain cursor-pointer" role="img">
            <button id="nextMainPhoto" class="absolute right-0 mr-4 text-white bg-black bg-opacity-50 p-3 rounded-full hover:bg-opacity-75 transition z-40" aria-label="Next image">
                <i data-feather="chevron-right" class="w-8 h-8"></i>
            </button>
            
            <div id="modalInfoBox" class="absolute bottom-0 w-full bg-black bg-opacity-50 text-white p-3 text-center pointer-events-none">
                <p id="modalImageName" class="font-semibold"></p>
                <p id="modalImageDate" class="text-sm"></p>
                <p id="modalImageUploader" class="text-xs"></p>
                <div id="deleteImageContainer" class="pointer-events-auto mt-2 <?php echo $can_manage_photos ? '' : 'hidden'; ?>">
                    <button id="deleteImageBtn" class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-md text-xs font-medium transition" data-image-id="" data-image-path="">
                        Delete Image
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    // Flatpickr calendar initialization for archive modal (if needed, though not directly in this file)
    // The JS below focuses on gallery and comments

    // Gallery Tabs Logic
    document.querySelectorAll('.tab-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active', 'text-blue-600', 'border-blue-600');
                btn.classList.add('text-gray-600', 'border-transparent', 'hover:border-gray-300');
            });
            this.classList.add('active', 'text-blue-600', 'border-blue-600');
            this.classList.remove('text-gray-600', 'border-transparent', 'hover:border-gray-300');
            
            const type = this.dataset.tab;
            fetchGallery(type);
        });
    });

    // Initial gallery load (pre-load tab)
    document.addEventListener('DOMContentLoaded', () => {
        fetchGallery('pre');
    });

    /**
     * Fetches and displays the gallery for a given type.
     * @param {string} type - 'pre', 'post', 'damage', or 'document'.
     */
    function fetchGallery(type) {
        const galleryContainer = document.getElementById('gallery-container');
        galleryContainer.innerHTML = '<div class="text-center py-10 text-gray-500">Loading ' + type + ' images...</div>';
        
        fetch(`fetch_images.php?vehicle_id=${VEHICLE_ID}&type=${type}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update main gallery container with the specific type's HTML
                    galleryContainer.innerHTML = data.galleries[type] || '';
                    
                    // Re-init feather icons for new content
                    feather.replace();
                    
                    // Re-initialize SortableJS on the new gallery content
                    initSortableGallery(type);

                    // Update global image data for the modal
                    window.currentGalleryImages = data.allImages;
                    
                    // Attach event listeners for image click
                    document.querySelectorAll('.gallery-img').forEach((img, index) => {
                        img.addEventListener('click', () => {
                            // Find the index of the clicked image in the ALL images array (currentGalleryImages)
                            const imageId = parseInt(img.dataset.imageId);
                            const allImageIndex = window.currentGalleryImages.findIndex(i => i.id === imageId);
                            
                            if (allImageIndex !== -1) {
                                openMainGalleryModal(allImageIndex);
                            }
                        });
                    });
                } else {
                    galleryContainer.innerHTML = `<div class="text-center py-10 text-red-500">${data.message}</div>`;
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                galleryContainer.innerHTML = '<div class="text-center py-10 text-red-500">Failed to load gallery.</div>';
                console.error('Fetch Gallery Error:', error);
            });
    }

    /**
     * Initializes SortableJS for drag-and-drop ordering in the gallery.
     * @param {string} type - 'pre', 'post', 'damage', or 'document'.
     */
    function initSortableGallery(type) {
        const sortableElement = document.getElementById(type + '-gallery-grid');
        // Only allow sorting if the user has photo management permissions
        if (sortableElement && sortableElement.dataset.canManagePhotos === '1') {
            new Sortable(sortableElement, {
                animation: 150,
                ghostClass: 'bg-blue-100',
                onEnd: function (evt) {
                    const newOrder = Array.from(evt.from.children).map(item => item.dataset.imageId);
                    updateImageOrder(type, newOrder);
                },
            });
        }
    }

    /**
     * Sends the new image order to the server via AJAX.
     * @param {string} galleryType - 'pre', 'post', 'damage', or 'document'.
     * @param {array} order - Array of image IDs in the new sequence.
     */
    function updateImageOrder(galleryType, order) {
        fetch('update_image_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                gallery_type: galleryType,
                order: order
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Failed to update image order due to network error.', 'error');
            console.error('Update Order Error:', error);
        });
    }
    
    // --- Gallery Modal Functions (Main Viewer) ---
    
    function openMainGalleryModal(index) {
        window.currentImageIndex = index;
        updateMainGalleryModal();
        document.getElementById('mainGalleryModal').classList.remove('hidden');
    }

    function closeMainGalleryModal() {
        document.getElementById('mainGalleryModal').classList.add('hidden');
    }

    function updateMainGalleryModal() {
        const image = window.currentGalleryImages[window.currentImageIndex];
        const modalImage = document.getElementById('modalMainImage');
        const modalName = document.getElementById('modalImageName');
        const modalDate = document.getElementById('modalImageDate');
        const modalUploader = document.getElementById('modalImageUploader');
        const deleteBtn = document.getElementById('deleteImageBtn');
        const prevBtn = document.getElementById('prevMainPhoto');
        const nextBtn = document.getElementById('nextMainPhoto');

        if (!image) return;

        modalImage.src = image.image_path;
        modalName.textContent = image.image_path.split('/').pop();
        modalDate.textContent = 'Uploaded on: ' + new Date(image.created_at).toLocaleDateString('en-GB');
        modalUploader.textContent = 'By: ' + image.username;
        
        // Update delete button data attributes and visibility
        deleteBtn.dataset.imageId = image.id;
        deleteBtn.dataset.imagePath = image.image_path;
        
        // Navigation button visibility
        prevBtn.classList.toggle('hidden', window.currentImageIndex === 0);
        nextBtn.classList.toggle('hidden', window.currentImageIndex === window.currentGalleryImages.length - 1);
    }

    document.getElementById('prevMainPhoto').addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent image click listener if it were attached to the button
        if (window.currentImageIndex > 0) {
            window.currentImageIndex--;
            updateMainGalleryModal();
        }
    });

    document.getElementById('nextMainPhoto').addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent image click listener
        if (window.currentImageIndex < window.currentGalleryImages.length - 1) {
            window.currentImageIndex++;
            updateMainGalleryModal();
        }
    });

    document.getElementById('deleteImageBtn').addEventListener('click', function() {
        if (!confirm('Are you sure you want to permanently delete this image?')) {
            return;
        }

        const imageId = this.dataset.imageId;
        
        fetch('delete_image.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `image_id=${imageId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                closeMainGalleryModal();
                // Simple page reload to refresh all galleries and data
                setTimeout(() => window.location.reload(), 500);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Failed to delete image due to network error.', 'error');
            console.error('Delete Image Error:', error);
        });
    });

    // --- Image Upload Logic (Consolidated) ---

    // Array to store drop area configs
    const dropAreaConfigs = [
        { areaId: 'preDropArea', inputId: 'preFileInput', imageType: 'pre' },
        { areaId: 'postDropArea', inputId: 'postFileInput', imageType: 'post' },
        { areaId: 'documentDropArea', inputId: 'documentFileInput', imageType: 'document' }
        // Note: 'damage' uploads happen via the modal and use a different input, or are uploaded as 'pre/post' initially
    ];

    /**
     * Universal function to handle file uploads via AJAX.
     * @param {Array<File>} files - Array of File objects to upload.
     * @param {string} areaId - The ID of the drop area element.
     * @param {number} vehicleId - The ID of the vehicle.
     * @param {string} imageType - 'pre', 'post', 'damage', or 'document'.
     */
    window.uploadFiles = function(files, areaId, vehicleId, imageType) {
        if (files.length === 0) return;

        const dropArea = document.getElementById(areaId);
        const originalText = dropArea.innerHTML;
        const formData = new FormData();

        formData.append('vehicle_id', vehicleId);
        formData.append('image_type', imageType);
        files.forEach(file => {
            formData.append('images[]', file);
        });

        // UI update: show loading state
        dropArea.innerHTML = 'Uploading ' + files.length + ' file(s)... Please wait.';
        dropArea.classList.add('bg-blue-100');

        fetch('upload_image.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json().then(data => ({ status: response.status, data: data })))
        .then(response => {
            dropArea.classList.remove('bg-blue-100');
            dropArea.innerHTML = originalText;
            if (response.data.success) {
                showNotification(response.data.message, 'success');
                // Reload page to see new images
                setTimeout(() => window.location.reload(), 1000); 
            } else {
                showNotification(response.data.message, 'error');
            }
        })
        .catch(error => {
            dropArea.classList.remove('bg-blue-100');
            dropArea.innerHTML = originalText;
            showNotification('Upload failed due to network or server error.', 'error');
            console.error('Upload Error:', error);
        });
    }
    
    // Attach file input change listeners
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('preFileInput').addEventListener('change', function(e) {
            window.uploadFiles(Array.from(e.target.files), 'preDropArea', VEHICLE_ID, 'pre');
        });
        document.getElementById('postFileInput').addEventListener('change', function(e) {
            window.uploadFiles(Array.from(e.target.files), 'postDropArea', VEHICLE_ID, 'post');
        });
        document.getElementById('documentFileInput').addEventListener('change', function(e) {
            window.uploadFiles(Array.from(e.target.files), 'documentDropArea', VEHICLE_ID, 'document');
        });

        // --- Comment Deletion Logic ---
        document.querySelectorAll('.delete-comment-btn').forEach(button => {
            button.addEventListener('click', function() {
                if (!confirm('Are you sure you want to permanently delete this comment?')) {
                    return;
                }
                
                const commentId = this.dataset.commentId;

                fetch('delete_comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `comment_id=${commentId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        // Remove the comment element from the DOM
                        document.getElementById(`comment-${commentId}`).remove();
                        // Update the comment count in the header (if needed, but a reload is safer/simpler)
                        // setTimeout(() => window.location.reload(), 500); // Too disruptive, removing element is better
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    showNotification('Failed to delete comment due to network error.', 'error');
                    console.error('Delete Comment Error:', error);
                });
            });
        });

    });
</script>

<?php include 'footer.php'; ?>