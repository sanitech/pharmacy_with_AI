    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        });

        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.remove('show');
            document.getElementById('sidebarOverlay').classList.remove('show');
        });

        // Set active nav link based on current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.sidebar-menu .nav-link');

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
        });

        // Prescription approval functions (for dashboard)
        function approvePrescription(prescriptionId) {
            document.getElementById('approve_prescription_id').value = prescriptionId;
            new bootstrap.Modal(document.getElementById('approveModal')).show();
        }

        function rejectPrescription(prescriptionId) {
            document.getElementById('reject_prescription_id').value = prescriptionId;
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }
    </script>
    </body>

    </html>