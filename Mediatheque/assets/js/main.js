/**
 * Main JavaScript file for the Media Library Management System
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    initMobileMenu();
    
    // Initialize dropdowns
    initDropdowns();
    
    // Auto-dismiss alerts after 5 seconds
    autoDismissAlerts();
});

/**
 * Initialize mobile navigation menu
 */
function initMobileMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            mainNav.classList.toggle('show');
        });
    }
    
    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.main-nav') && !e.target.closest('.menu-toggle')) {
            if (mainNav && mainNav.classList.contains('show')) {
                mainNav.classList.remove('show');
            }
        }
    });
}

/**
 * Initialize dropdown menus for mobile
 */
function initDropdowns() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    // For small screens, make dropdowns toggle on click
    if (window.innerWidth < 768) {
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Close all other dropdowns
                const allDropdowns = document.querySelectorAll('.dropdown');
                allDropdowns.forEach(dropdown => {
                    if (dropdown !== this.closest('.dropdown')) {
                        dropdown.classList.remove('show');
                    }
                });
                
                // Toggle this dropdown
                this.closest('.dropdown').classList.toggle('show');
            });
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                const allDropdowns = document.querySelectorAll('.dropdown');
                allDropdowns.forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            }
        });
    }
}

/**
 * Auto-dismiss alert messages after delay
 */
function autoDismissAlerts() {
    const alerts = document.querySelectorAll('.alert');
    
    if (alerts.length > 0) {
        setTimeout(function() {
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            });
        }, 5000);
    }
}

/**
 * Confirm deletion before submitting delete forms
 */
document.addEventListener('submit', function(e) {
    if (e.target.classList.contains('delete-form')) {
        const confirmed = confirm('Êtes-vous sûr de vouloir supprimer cet élément ?');
        if (!confirmed) {
            e.preventDefault();
        }
    }
});

/**
 * Toggle password visibility in password fields
 * @param {string} inputId - The ID of the password input field
 */
function togglePasswordVisibility(inputId) {
    const passwordInput = document.getElementById(inputId);
    
    if (passwordInput) {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
        } else {
            passwordInput.type = 'password';
        }
    }
}
