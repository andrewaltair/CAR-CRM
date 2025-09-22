<div id="mainGalleryModal" class="hidden fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 p-4" role="dialog" aria-modal="true" aria-labelledby="modalImageCaption">
    
    <button id="closeModalButton" class="absolute top-4 right-4 text-white text-3xl opacity-75 hover:opacity-100 transition-opacity" title="Close" aria-label="Close">
        &times;
    </button>
    
    <div class="relative max-w-7xl max-h-[90vh] flex items-center justify-center">
        
        <button id="prevMainPhoto" class="absolute left-0 top-1/2 transform -translate-y-1/2 p-3 text-white bg-black bg-opacity-50 hover:bg-opacity-75 rounded-full ml-4 disabled:opacity-30 disabled:cursor-not-allowed transition" aria-label="Previous image">
            <i data-feather="chevron-left" class="w-8 h-8"></i>
        </button>

        <img id="modalMainImage" src="" alt="Vehicle Image" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl">
        
        <button id="nextMainPhoto" class="absolute right-0 top-1/2 transform -translate-y-1/2 p-3 text-white bg-black bg-opacity-50 hover:bg-opacity-75 rounded-full mr-4 disabled:opacity-30 disabled:cursor-not-allowed transition" aria-label="Next image">
            <i data-feather="chevron-right" class="w-8 h-8"></i>
        </button>

        <div id="modalImageCaption" class="absolute bottom-0 w-full text-center py-2 bg-black bg-opacity-50 text-white text-sm">
            </div>
    </div>
</div>