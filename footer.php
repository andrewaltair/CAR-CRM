</div>
    <div class="bg-gray-800 text-white py-4 text-center">
        <p>Created By Andrew Altair</p>
    </div>
    <script>
        // Initialize AOS and Feather Icons
        if (typeof AOS !== 'undefined') {
            AOS.init();
        }
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
        
        // Show notifications from PHP session
        <?php if (isset($_SESSION['success'])): ?>
            // showNotification is now defined in script.js, checking for its existence
            if (typeof showNotification === 'function') {
                showNotification('<?php echo addslashes($_SESSION['success']); ?>', 'success');
            }
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            // showNotification is now defined in script.js, checking for its existence
            if (typeof showNotification === 'function') {
                showNotification('<?php echo addslashes($_SESSION['error']); ?>', 'error');
            }
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>