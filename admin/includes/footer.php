</div> <!-- End admin-wrapper -->
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const body = document.body;
            
            if (sidebar && overlay) {
                const isOpen = sidebar.classList.contains('show');
                
                if (isOpen) {
                    // Close sidebar
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                    body.classList.remove('sidebar-open');
                } else {
                    // Open sidebar
                    sidebar.classList.add('show');
                    overlay.classList.add('show');
                    body.classList.add('sidebar-open');
                }
            }
        }
        
        // Close sidebar when clicking overlay
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('sidebarOverlay');
            const sidebarClose = document.getElementById('sidebarClose');
            
            if (overlay) {
                overlay.addEventListener('click', function() {
                    toggleSidebar();
                });
            }
            
            if (sidebarClose) {
                sidebarClose.addEventListener('click', function() {
                    toggleSidebar();
                });
            }
        });
        
        // Auto-hide flash messages
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
        
        // Confirm delete actions
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('confirm-delete') || e.target.closest('.confirm-delete')) {
                if (!confirm('क्या आप वाकई इसे हटाना चाहते हैं? यह क्रिया पूर्ववत नहीं की जा सकती।')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    </script>
</body>
</html>
