<div id="archiveModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Archive Vehicle</h3>
            <div class="mt-2 px-7 py-3">
                <form action="archive_vehicle.php" method="POST" class="space-y-4">
                    <input type="hidden" name="vin" value="<?php echo htmlspecialchars($vehicle['vin']); ?>">
                    
                    <div>
                        <label for="archive_reason" class="block text-sm font-medium text-gray-700 text-left mb-1">Reason for Archiving</label>
                        <select id="archive_reason" name="reason" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" onchange="toggleOtherReason(this.value)">
                            <option value="" disabled selected>Select a reason</option>
                            <option value="canceled_order">Canceled Order</option>
                            <option value="duplicate_entry">Duplicate Entry</option>
                            <option value="problem_vehicle">Problem Vehicle/No Contact</option>
                            <option value="other">Other (Specify)</option>
                        </select>
                    </div>

                    <div id="otherReasonGroup" class="hidden">
                        <label for="other_reason" class="block text-sm font-medium text-gray-700 text-left mb-1">Other Reason Details</label>
                        <textarea id="other_reason" name="other_reason" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                    </div>

                    <div class="items-center px-4 py-3">
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50">
                            Confirm Archive
                        </button>
                        <button type="button" onclick="document.getElementById('archiveModal').classList.add('hidden')" class="mt-3 px-4 py-2 bg-gray-200 text-gray-700 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-300 focus:outline-none">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleOtherReason(reason) {
    const otherGroup = document.getElementById('otherReasonGroup');
    if (reason === 'other') {
        otherGroup.classList.remove('hidden');
    } else {
        otherGroup.classList.add('hidden');
    }
}
</script>