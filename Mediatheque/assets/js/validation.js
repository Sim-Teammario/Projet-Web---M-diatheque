/**
 * Form validation for the Media Library Management System
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get all forms with validation
    const forms = document.querySelectorAll('form[novalidate]');
    
    // Add validation to each form
    forms.forEach(form => {
        initFormValidation(form);
    });
});

/**
 * Initialize form validation
 * @param {HTMLFormElement} form - The form element to validate
 */
function initFormValidation(form) {
    // Add submit event listener
    form.addEventListener('submit', function(event) {
        // If the form is invalid, prevent submission
        if (!validateForm(form)) {
            event.preventDefault();
        }
    });
    
    // Add input event listeners for real-time validation
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        // Skip submit buttons and hidden inputs
        if (input.type === 'submit' || input.type === 'hidden') {
            return;
        }
        
        // Validate on blur (when user leaves field)
        input.addEventListener('blur', function() {
            validateInput(input);
        });
        
        // For password confirmation, validate on input
        if (input.id === 'confirm_password') {
            input.addEventListener('input', function() {
                validateInput(input);
            });
        }
    });
}

/**
 * Validate the entire form
 * @param {HTMLFormElement} form - The form to validate
 * @returns {boolean} - Whether the form is valid
 */
function validateForm(form) {
    let isValid = true;
    
    // Validate all inputs
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        // Skip submit buttons and hidden inputs
        if (input.type === 'submit' || input.type === 'hidden') {
            return;
        }
        
        // If any input is invalid, the form is invalid
        if (!validateInput(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Validate a single input field
 * @param {HTMLInputElement|HTMLTextAreaElement|HTMLSelectElement} input - The input to validate
 * @returns {boolean} - Whether the input is valid
 */
function validateInput(input) {
    // Reset previous error
    removeError(input);

    // Check for required fields
    if (input.required && !input.value.trim()) {
        showError(input, 'Ce champ est obligatoire');
        return false;
    }

    // Password validation
    if (input.type === 'password' && input.id === 'password') {
        const isEditUserPage = input.closest('form').classList.contains('edit-user-form');
        if (!isEditUserPage || input.value.trim()) {
            if (input.minLength && input.value.length < input.minLength) {
                showError(input, `Le mot de passe doit contenir au moins ${input.minLength} caractères`);
                return false;
            }
        }
    }

    // Password confirmation validation
    if (input.id === 'confirm_password') {
        const passwordInput = document.getElementById('password');
        if (passwordInput && input.value !== passwordInput.value) {
            showError(input, 'Les mots de passe ne correspondent pas');
            return false;
        }
    }
    
    // Number validation
    if (input.type === 'number') {
        if (input.min && parseInt(input.value) < parseInt(input.min)) {
            showError(input, `La valeur minimale est ${input.min}`);
            return false;
        }
        if (input.max && parseInt(input.value) > parseInt(input.max)) {
            showError(input, `La valeur maximale est ${input.max}`);
            return false;
        }
    }
    
    // ISBN validation for books
    if (input.id === 'isbn' && input.value.trim()) {
        const isbn = input.value.replace(/[-\s]/g, ''); // Remove hyphens and spaces
        if (!/^(\d{10}|\d{13})$/.test(isbn)) {
            showError(input, 'ISBN doit être composé de 10 ou 13 chiffres');
            return false;
        }
    }
    
    return true;
}

/**
 * Show error message for an input
 * @param {HTMLInputElement|HTMLTextAreaElement|HTMLSelectElement} input - The input with error
 * @param {string} message - The error message to display
 */
function showError(input, message) {
    // Add error class to input
    input.classList.add('input-error');
    
    // Create error message element if it doesn't exist
    let errorElement = input.nextElementSibling;
    if (!errorElement || !errorElement.classList.contains('form-error')) {
        errorElement = document.createElement('div');
        errorElement.className = 'form-error';
        input.parentNode.insertBefore(errorElement, input.nextElementSibling);
    }
    
    // Set error message
    errorElement.textContent = message;
}

/**
 * Remove error message from an input
 * @param {HTMLInputElement|HTMLTextAreaElement|HTMLSelectElement} input - The input to remove error from
 */
function removeError(input) {
    // Remove error class from input
    input.classList.remove('input-error');
    
    // Remove error message element if it exists
    let errorElement = input.nextElementSibling;
    if (errorElement && errorElement.classList.contains('form-error')) {
        errorElement.remove();
    }
}
