/**
 * PetPal - Main JavaScript
 * Client-side functionality for cart, forms, and animations
 */

// DOM Ready
document.addEventListener('DOMContentLoaded', function () {
    initializeApp();
});

/**
 * Initialize application
 */
function initializeApp() {
    // Initialize cart buttons
    initCartButtons();

    // Initialize smooth scrolling
    initSmoothScroll();

    // Initialize form validation
    initFormValidation();

    // Initialize animations on scroll
    initScrollAnimations();

    // Initialize tooltips
    initTooltips();
}

/**
 * Cart button handlers
 */
function initCartButtons() {
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', handleAddToCart);
    });
}

/**
 * Handle add to cart click
 */
function handleAddToCart(e) {
    const button = e.currentTarget;
    const productId = button.dataset.productId;

    if (!productId) return;

    // Check if user is logged in (this check should be done server-side too)
    // The actual login check is handled in the API

    // Disable button
    button.disabled = true;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    // Send request
    fetch((window.SITE_URL || '') + '/api/cart-actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=add&product_id=' + productId
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count
                updateCartCount(data.cart_count);

                // Show success
                button.innerHTML = '<i class="fas fa-check"></i>';
                button.classList.add('btn-success');

                // Show toast notification
                showToast('Added to cart!', 'success');

                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.classList.remove('btn-success');
                    button.disabled = false;
                }, 1500);
            } else {
                if (data.message && data.message.includes('login')) {
                    showToast('Please login to add items to cart', 'warning');
                } else {
                    showToast(data.message || 'Error adding to cart', 'error');
                }
                button.innerHTML = originalHTML;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
            button.innerHTML = originalHTML;
            button.disabled = false;
        });
}

/**
 * Update cart count in navbar
 */
function updateCartCount(count) {
    let cartBadge = document.querySelector('.cart-count');
    const cartIcon = document.querySelector('.cart-icon');

    if (count > 0) {
        if (!cartBadge && cartIcon) {
            cartBadge = document.createElement('span');
            cartBadge.className = 'cart-count';
            cartIcon.appendChild(cartBadge);
        }
        if (cartBadge) {
            cartBadge.textContent = count;
            cartBadge.style.animation = 'pulse 0.3s ease';
        }
    } else if (cartBadge) {
        cartBadge.remove();
    }
}

/**
 * Initialize smooth scrolling for anchor links
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Validate a form
 */
function validateForm(form) {
    let isValid = true;

    // Clear previous errors
    form.querySelectorAll('.form-error').forEach(el => el.remove());
    form.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));

    // Check required fields
    form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        }
    });

    // Check email fields
    form.querySelectorAll('input[type="email"]').forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            showFieldError(field, 'Please enter a valid email');
            isValid = false;
        }
    });

    // Check password match
    const password = form.querySelector('input[name="password"]');
    const confirmPassword = form.querySelector('input[name="confirm_password"]');
    if (password && confirmPassword && password.value !== confirmPassword.value) {
        showFieldError(confirmPassword, 'Passwords do not match');
        isValid = false;
    }

    return isValid;
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    field.classList.add('error');
    const error = document.createElement('span');
    error.className = 'form-error';
    error.textContent = message;
    field.parentNode.appendChild(error);
}

/**
 * Validate email format
 */
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * Initialize scroll animations
 */
function initScrollAnimations() {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        },
        { threshold: 0.1 }
    );

    // Observe cards and sections
    document.querySelectorAll('.card, .product-card, .hospital-card, .tip-card, .section-title').forEach(el => {
        el.classList.add('animate-ready');
        observer.observe(el);
    });
}

/**
 * Initialize tooltips
 */
function initTooltips() {
    document.querySelectorAll('[title]').forEach(el => {
        el.addEventListener('mouseenter', showTooltip);
        el.addEventListener('mouseleave', hideTooltip);
    });
}

/**
 * Show tooltip
 */
function showTooltip(e) {
    const el = e.currentTarget;
    const text = el.getAttribute('title');
    if (!text) return;

    // Store and remove title to prevent default tooltip
    el.dataset.tooltip = text;
    el.removeAttribute('title');

    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    tooltip.textContent = text;
    document.body.appendChild(tooltip);

    const rect = el.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + window.scrollY + 'px';

    setTimeout(() => tooltip.classList.add('visible'), 10);
}

/**
 * Hide tooltip
 */
function hideTooltip(e) {
    const el = e.currentTarget;

    // Restore title
    if (el.dataset.tooltip) {
        el.setAttribute('title', el.dataset.tooltip);
        delete el.dataset.tooltip;
    }

    document.querySelectorAll('.custom-tooltip').forEach(t => t.remove());
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    // Remove existing toasts
    document.querySelectorAll('.toast').forEach(t => t.remove());

    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;

    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };

    toast.innerHTML = `
        <i class="fas ${icons[type] || icons.info}"></i>
        <span>${message}</span>
    `;

    document.body.appendChild(toast);

    // Trigger animation
    setTimeout(() => toast.classList.add('visible'), 10);

    // Auto-remove
    setTimeout(() => {
        toast.classList.remove('visible');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toFixed(2);
}

/**
 * Format date
 */
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Add toast styles dynamically
const toastStyles = document.createElement('style');
toastStyles.textContent = `
    .toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 15px 25px;
        background: var(--dark);
        color: white;
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: var(--shadow-xl);
        transform: translateX(120%);
        transition: transform 0.3s ease;
        z-index: 10000;
    }
    
    .toast.visible {
        transform: translateX(0);
    }
    
    .toast-success { background: var(--success); }
    .toast-error { background: var(--danger); }
    .toast-warning { background: #f39c12; }
    .toast-info { background: var(--primary); }
    
    .custom-tooltip {
        position: absolute;
        background: var(--dark);
        color: white;
        padding: 8px 12px;
        border-radius: var(--radius-sm);
        font-size: 0.85rem;
        z-index: 10000;
        opacity: 0;
        transition: opacity 0.2s ease;
        pointer-events: none;
    }
    
    .custom-tooltip.visible {
        opacity: 1;
    }
    
    .animate-ready {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.5s ease, transform 0.5s ease;
    }
    
    .animate-in {
        opacity: 1;
        transform: translateY(0);
    }
    
    .form-control.error {
        border-color: var(--danger);
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
`;
document.head.appendChild(toastStyles);
