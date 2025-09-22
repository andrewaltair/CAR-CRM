<?php
// This modal is included in details.php and contains the HTML structure
// for the main image gallery viewer, as referenced in script.js.
// ID: mainGalleryModal, modalMainImage, prevMainPhoto, nextMainPhoto
?>
<div id="mainGalleryModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-90 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="modalCaption">
    
    <button onclick="closeMainGalleryModal()" class="absolute top-4 right-4 text-white text-3xl hover:text-red-500 transition focus:outline-none" aria-label="Close modal">
        &times;
    </button>
    
    <div class="relative flex items-center justify-center w-full h-full max-w-7xl max-h-screen">
        
        <button id="prevMainPhoto" class="absolute left-0 ml-4 p-3 bg-gray-800 bg-opacity-50 text-white rounded-full hover:bg-opacity-75 transition focus:outline-none z-10" aria-label="Previous image">
            <i data-feather="chevron-left" class="w-8 h-8"></i>
        </button>

        <div class="flex flex-col items-center justify-center h-full w-full">
            <img id="modalMainImage" src="" alt="Vehicle Image" class="max-w-full max-h-full object-contain cursor-pointer">
            <p id="modalCaption" class="mt-4 text-white text-center text-sm max-w-lg truncate" aria-live="polite"></p>
        </div>

        <button id="nextMainPhoto" class="absolute right-0 mr-4 p-3 bg-gray-800 bg-opacity-50 text-white rounded-full hover:bg-opacity-75 transition focus:outline-none z-10" aria-label="Next image">
            <i data-feather="chevron-right" class="w-8 h-8"></i>
        </button>
    </div>
</div>