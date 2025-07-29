    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="text-gradient mb-3">
                        <i class="fas fa-rocket me-2"></i><?= APP_NAME ?>
                    </h5>
                    <p class="text-muted">
                        Complete PHP Blog Application showcasing advanced web development skills 
                        including authentication, CRUD operations, search functionality, and 
                        role-based access control.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light">
                            <i class="fab fa-github fa-lg"></i>
                        </a>
                        <a href="#" class="text-light">
                            <i class="fab fa-linkedin fa-lg"></i>
                        </a>
                        <a href="#" class="text-light">
                            <i class="fab fa-twitter fa-lg"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="mb-3">Navigation</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="<?= BASE_URL ?>" class="text-muted text-decoration-none">
                                <i class="fas fa-home me-1"></i>Home
                            </a>
                        </li>
                        <?php if (isLoggedIn()): ?>
                            <li class="mb-2">
                                <a href="<?= BASE_URL ?>/dashboard.php" class="text-muted text-decoration-none">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                                </a>
                            </li>
                            <?php if (isEditor()): ?>
                                <li class="mb-2">
                                    <a href="<?= BASE_URL ?>/create-post.php" class="text-muted text-decoration-none">
                                        <i class="fas fa-plus me-1"></i>New Post
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php else: ?>
                            <li class="mb-2">
                                <a href="<?= BASE_URL ?>/login.php" class="text-muted text-decoration-none">
                                    <i class="fas fa-sign-in-alt me-1"></i>Login
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="<?= BASE_URL ?>/register.php" class="text-muted text-decoration-none">
                                    <i class="fas fa-user-plus me-1"></i>Register
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="mb-3">Features</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <span class="text-muted">
                                <i class="fas fa-shield-alt me-1 text-success"></i>Secure Authentication
                            </span>
                        </li>
                        <li class="mb-2">
                            <span class="text-muted">
                                <i class="fas fa-search me-1 text-info"></i>Advanced Search
                            </span>
                        </li>
                        <li class="mb-2">
                            <span class="text-muted">
                                <i class="fas fa-users me-1 text-warning"></i>Role-Based Access
                            </span>
                        </li>
                        <li class="mb-2">
                            <span class="text-muted">
                                <i class="fas fa-mobile-alt me-1 text-primary"></i>Responsive Design
                            </span>
                        </li>
                        <li class="mb-2">
                            <span class="text-muted">
                                <i class="fas fa-database me-1 text-danger"></i>Secure Database
                            </span>
                        </li>
                    </ul>
                </div>
                
                <div class="col-lg-3 mb-4">
                    <h6 class="mb-3">Project Info</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <span class="text-muted">
                                <i class="fas fa-code me-1"></i>PHP 8.0+
                            </span>
                        </li>
                        <li class="mb-2">
                            <span class="text-muted">
                                <i class="fas fa-database me-1"></i>MySQL 5.7+
                            </span>
                        </li>
                        <li class="mb-2">
                            <span class="text-muted">
                                <i class="fab fa-bootstrap me-1"></i>Bootstrap 5.3
                            </span>
                        </li>
                        <li class="mb-2">
                            <span class="text-muted">
                                <i class="fas fa-graduation-cap me-1"></i>Internship Project
                            </span>
                        </li>
                        <li class="mb-2">
                            <span class="text-muted">
                                <i class="fas fa-calendar me-1"></i>Version <?= APP_VERSION ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">
                        &copy; <?= date('Y') ?> <?= APP_NAME ?>. 
                        Developed by <strong><?= APP_AUTHOR ?></strong> for Aerospace Internship Program.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">
                        <i class="fas fa-rocket me-1"></i>Task 5: Final Project & Certification
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-primary position-fixed bottom-0 end-0 m-3" 
            style="display: none; z-index: 1000;" title="Back to Top">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Back to top button functionality
        window.addEventListener('scroll', function() {
            const backToTopButton = document.getElementById('backToTop');
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });

        document.getElementById('backToTop').addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });

        // Form validation enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('.needs-validation');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                });
            });
        });

        // CSRF token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            // Set up CSRF token for all AJAX requests
            if (typeof $ !== 'undefined') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                    }
                });
            }
        }

        // Search form enhancement
        const searchForm = document.querySelector('form[method="GET"]');
        if (searchForm) {
            const searchInput = searchForm.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.addEventListener('keyup', function(e) {
                    if (e.key === 'Enter') {
                        searchForm.submit();
                    }
                });
            }
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Loading state for buttons
        function showLoading(button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<span class="loading"></span> Loading...';
            button.disabled = true;
            
            return function() {
                button.innerHTML = originalText;
                button.disabled = false;
            };
        }

        // Image lazy loading fallback
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img[data-src]');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            images.forEach(img => imageObserver.observe(img));
        });

        // Console message for developers
        console.log('%c🚀 Task 5: Final Project & Certification', 'color: #667eea; font-size: 16px; font-weight: bold;');
        console.log('%cAerospace Internship Program - Complete PHP Blog Application', 'color: #764ba2; font-size: 12px;');
        console.log('%cDeveloped by: <?= APP_AUTHOR ?>', 'color: #6c757d; font-size: 10px;');
    </script>

    <!-- Additional page-specific scripts -->
    <?php if (isset($additionalScripts)): ?>
        <?= $additionalScripts ?>
    <?php endif; ?>
</body>
</html>
