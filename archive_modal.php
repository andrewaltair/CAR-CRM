<div id="archiveModal" class="modal hidden fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg" role="dialog" aria-modal="true" aria-labelledby="archiveTitle">
        <h3 id="archiveTitle" class="text-xl font-bold text-gray-900 mb-6 border-b pb-2">Archive Vehicle (<span id="archive-vin-display"></span>)</h3>
        
        <form action="archive_vehicle.php" method="POST" class="space-y-4">
            <input type="hidden" name="vin" id="archive-vin-input">
            
            <div>
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Archiving</label>
                <select name="reason" id="reason" required onchange="document.getElementById('other_reason_field').classList.toggle('hidden', this.value !== 'other'); document.getElementById('other_reason').toggleAttribute('required', this.value === 'other')" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="" disabled selected>Select a reason</option>
                    <option value="cancelled">Order Cancelled</option>
                    <option value="delivered_issue">Delivered with Issue</option>
                    <option value="no_show">Driver No-Show</option>
                    <option value="stuck_at_auction">Stuck at Auction/Warehouse</option>
                    <option value="duplicate">Duplicate Entry</option>
                    <option value="other">Other (Specify Below)</option>
                </select>
            </div>
            
            <div id="other_reason_field" class="hidden">
                <label for="other_reason" class="block text-sm font-medium text-gray-700 mb-1">Specify Other Reason</label>
                <textarea name="other_reason" id="other_reason" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>

            <div>
                <label for="driver_phone" class="block text-sm font-medium text-gray-700 mb-1">Driver Phone (Optional)</label>
                <input type="text" name="driver_phone" id="driver_phone" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" onclick="closeArchiveModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white font-medium rounded-md text-sm shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Confirm Archive
                </button>
            </div>
        </form>
    </div>
</div>