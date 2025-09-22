<?php
require_once 'config.php';
checkAuth();

// Set default header
header('Content-Type: application/json');

if (!isset($_GET['vehicle_id']) || !filter_var($_GET['vehicle_id'], FILTER_VALIDATE_INT)) {
    echo json_encode(['success' => false, 'message' => 'Invalid Vehicle ID.']);
    exit;
}

$vehicle_id = (int)$_GET['vehicle_id'];
$vehicle_vin = $_GET['vin'] ?? ''; // VIN for redirect if needed, but not used here.

try {
    global $pdo;

    // Fetch images for this vehicle
    $stmt = $pdo->prepare("
        SELECT vi.*, u.username 
        FROM vehicle_images vi 
        JOIN users u ON vi.uploaded_by = u.id 
        WHERE vi.vehicle_id = ? 
        ORDER BY vi.image_type, vi.image_order, vi.created_at
    ");
    $stmt->execute([$vehicle_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group images by type for display
    $groupedImages = [
        'pre' => [],
        'post' => [],
        'damage' => [], // ADDED DAMAGE GALLERY TYPE
        'document' => []
    ];
    foreach ($images as $image) {
        if (isset($groupedImages[$image['image_type']])) {
            $groupedImages[$image['image_type']][] = $image;
        }
    }

    /**
     * Renders the HTML for an image gallery section.
     * @param string $type The gallery type ('pre', 'post', 'damage', 'document').
     * @param array $images The array of image data for this type.
     * @param bool $can_manage_photos
     * @return string
     */
    function renderGallery($type, $images, $can_manage_photos) {
        $html = '<div id="' . htmlspecialchars($type) . 'GalleryContainer">';
        $html .= '<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4" data-gallery-type="' . htmlspecialchars($type) . '">';
        
        if (!empty($images)) {
            foreach ($images as $image) {
                $html .= '<div 
                            class="image-item relative group bg-gray-100 rounded-lg overflow-hidden shadow-md cursor-pointer aspect-square" 
                            data-image-id="' . htmlspecialchars($image['id']) . '"
                            onclick="openMainGallery(' . htmlspecialchars($image['id']) . ', \'' . htmlspecialchars($image['image_type']) . '\')">';
                $html .= '<img src="' . htmlspecialchars($image['image_path']) . '" alt="Image" class="object-cover w-full h-full transition duration-300 group-hover:opacity-75">';
                $html .= '<div class="absolute inset-0 flex flex-col justify-end p-2 bg-gradient-to-t from-black/50 to-transparent text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity">';
                
                if ($can_manage_photos) {
                    $html .= '<button 
                                class="absolute top-2 right-2 bg-red-600 hover:bg-red-700 p-1 rounded-full text-white z-10 opacity-0 group-hover:opacity-100 transition" 
                                onclick="event.stopPropagation(); deleteImage(' . htmlspecialchars($image['id']) . ', \'' . htmlspecialchars($type) . '\')"
                                aria-label="Delete image"
                            >';
                    $html .= '<i data-feather="trash-2" class="w-3 h-3"></i>';
                    $html .= '</button>';
                    
                    // Drag handle for sorting
                    $html .= '<button 
                                class="absolute top-2 left-2 bg-gray-800/70 hover:bg-gray-800 p-1 rounded-full text-white z-10 cursor-move opacity-0 group-hover:opacity-100 transition drag-handle" 
                                aria-label="Drag to reorder"
                                onclick="event.stopPropagation();"
                            >';
                    $html .= '<i data-feather="move" class="w-3 h-3"></i>';
                    $html .= '</button>';
                }
                
                $html .= '<p class="text-white text-xs mt-auto font-medium" title="Uploaded by ' . htmlspecialchars($image['username']) . ' on ' . date('d.m.Y', strtotime($image['created_at'])) . '">';
                $html .= htmlspecialchars($image['username']) . ' / ' . date('d.m.Y', strtotime($image['created_at']));
                $html .= '</p>';
                $html .= '</div>';
                $html .= '</div>';
            }
        } else {
             $html .= '<div class="text-gray-500 text-center col-span-5 py-4">No ' . htmlspecialchars($type) . ' images uploaded yet.</div>';
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    $is_manager_or_admin = in_array($_SESSION['role'] ?? 'guest', ['admin', 'photo_manager']);
    
    // Render all gallery sections
    $preHtml = renderGallery('pre', $groupedImages['pre'], $is_manager_or_admin);
    $postHtml = renderGallery('post', $groupedImages['post'], $is_manager_or_admin);
    $damageHtml = renderGallery('damage', $groupedImages['damage'], $is_manager_or_admin); // RENDER DAMAGE
    $documentHtml = renderGallery('document', $groupedImages['document'], $is_manager_or_admin);

    echo json_encode([
        'success' => true,
        'galleries' => [
            'pre' => $preHtml,
            'post' => $postHtml,
            'damage' => $damageHtml, // RETURN DAMAGE HTML
            'document' => $documentHtml,
        ],
        // Also send image data for the main gallery viewer (modal)
        'all_images_data' => $images
    ]);
    
} catch(PDOException $e) {
    error_log("Error fetching vehicle images: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: Failed to fetch images.']);
}