// Modal Enhancements for Car Wash Management System
// This file ensures all modals work properly on all devices

document.addEventListener('DOMContentLoaded', function() {
    console.log('Modal Enhancements: Initializing...');
    
    // Initialize all modals with enhanced functionality
    initializeModals();
    
    // Add responsive behavior
    addResponsiveBehavior();
    
    // Fix iOS Safari issues
    fixIOSSafariIssues();
    
    // Force enable all form elements
    forceEnableFormElements();
    
    // Test form functionality
    testFormFunctionality();
});

function initializeModals() {
    console.log('Modal Enhancements: Initializing modals...');
    
    // Get all modals
    const modals = document.querySelectorAll('.modal');
    console.log('Found modals:', modals.length);
    
    modals.forEach((modal, index) => {
        console.log(`Modal ${index + 1}:`, modal.id);
        
        // Ensure proper backdrop
        modal.addEventListener('show.bs.modal', function() {
            console.log('Modal showing:', this.id);
            
            // Remove any existing backdrop
            const existingBackdrop = document.querySelector('.modal-backdrop');
            if (existingBackdrop) {
                existingBackdrop.remove();
            }
            
            // Force enable form elements before modal shows
            forceEnableFormElementsInModal(this);
        });
        
        // Fix modal positioning and enable form elements after modal is shown
        modal.addEventListener('shown.bs.modal', function() {
            console.log('Modal shown:', this.id);
            fixModalPosition(this);
            
            // Force enable form elements after modal is shown
            forceEnableFormElementsInModal(this);
            
            // Test form elements after modal is shown
            setTimeout(() => testModalForms(this), 100);
            
            // Force focus on first input
            setTimeout(() => focusFirstInput(this), 200);
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (modal.classList.contains('show')) {
                fixModalPosition(modal);
                forceEnableFormElementsInModal(modal);
            }
        });
        
        // Prevent modal from closing when clicking outside on mobile
        if (window.innerWidth <= 768) {
            modal.setAttribute('data-bs-backdrop', 'static');
            modal.setAttribute('data-bs-keyboard', 'false');
        }
    });
}

function forceEnableFormElementsInModal(modal) {
    console.log('Force enabling form elements for modal:', modal.id);
    
    // Get all form elements
    const formElements = modal.querySelectorAll('input, select, textarea, label, button');
    console.log('Found form elements:', formElements.length);
    
    formElements.forEach((element, index) => {
        console.log(`Form element ${index + 1}:`, element.tagName, element.type || 'N/A');
        
        // Force remove any inline styles that might interfere
        element.style.removeProperty('pointer-events');
        element.style.removeProperty('user-select');
        element.style.removeProperty('webkit-user-select');
        element.style.removeProperty('moz-user-select');
        element.style.removeProperty('ms-user-select');
        element.style.removeProperty('opacity');
        element.style.removeProperty('visibility');
        element.style.removeProperty('display');
        element.style.removeProperty('position');
        element.style.removeProperty('z-index');
        
        // Force set proper styles
        element.style.pointerEvents = 'auto';
        element.style.userSelect = 'auto';
        element.style.webkitUserSelect = 'auto';
        element.style.mozUserSelect = 'auto';
        element.style.msUserSelect = 'auto';
        element.style.opacity = '1';
        element.style.visibility = 'visible';
        element.style.position = 'relative';
        element.style.zIndex = '1000';
        
        // Remove any disabled states
        if (element.tagName === 'INPUT' || element.tagName === 'SELECT' || element.tagName === 'TEXTAREA') {
            element.disabled = false;
            element.readOnly = false;
            element.removeAttribute('disabled');
            element.removeAttribute('readonly');
        }
        
        // Add event listeners for debugging and ensuring interactivity
        element.addEventListener('click', function(e) {
            console.log('Element clicked:', this.tagName, this.type || 'N/A');
            e.stopPropagation();
            
            // Force focus if it's an input element
            if (this.tagName === 'INPUT' || this.tagName === 'SELECT' || this.tagName === 'TEXTAREA') {
                this.focus();
            }
        });
        
        element.addEventListener('focus', function(e) {
            console.log('Element focused:', this.tagName, this.type || 'N/A');
            this.style.zIndex = '1001';
            this.style.borderColor = '#00d4ff';
        });
        
        element.addEventListener('blur', function(e) {
            this.style.zIndex = '1000';
        });
        
        if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
            element.addEventListener('input', function(e) {
                console.log('Input received:', this.tagName, this.type || 'N/A', this.value);
            });
            
            element.addEventListener('keydown', function(e) {
                console.log('Key pressed:', e.key, 'on', this.tagName, this.type || 'N/A');
            });
        }
        
        if (element.tagName === 'SELECT') {
            element.addEventListener('change', function(e) {
                console.log('Select changed:', this.value);
            });
        }
    });
    
    // Force enable any Bootstrap form classes
    const formControls = modal.querySelectorAll('.form-control, .form-select');
    formControls.forEach(control => {
        control.classList.remove('disabled');
        control.removeAttribute('disabled');
        control.style.pointerEvents = 'auto';
        control.style.userSelect = 'auto';
    });
}

function focusFirstInput(modal) {
    const firstInput = modal.querySelector('input, select, textarea');
    if (firstInput) {
        console.log('Focusing first input:', firstInput.tagName, firstInput.type || 'N/A');
        firstInput.focus();
        
        // Force click to ensure it's interactive
        firstInput.click();
    }
}

function testModalForms(modal) {
    console.log('Testing form functionality for modal:', modal.id);
    
    const inputs = modal.querySelectorAll('input, textarea');
    const selects = modal.querySelectorAll('select');
    
    console.log('Inputs found:', inputs.length);
    console.log('Selects found:', selects.length);
    
    // Test each input
    inputs.forEach((input, index) => {
        console.log(`Testing input ${index + 1}:`, input.tagName, input.type || 'N/A');
        
        // Check if input is interactive
        const isInteractive = input.style.pointerEvents !== 'none' && 
                             !input.disabled && 
                             !input.readOnly;
        
        console.log(`Input ${index + 1} interactive:`, isInteractive);
        
        // Try to focus the input
        try {
            input.focus();
            console.log(`Input ${index + 1} focus successful`);
        } catch (error) {
            console.error(`Input ${index + 1} focus failed:`, error);
        }
    });
    
    // Test each select
    selects.forEach((select, index) => {
        console.log(`Testing select ${index + 1}:`, select.tagName);
        
        const isInteractive = select.style.pointerEvents !== 'none' && !select.disabled;
        console.log(`Select ${index + 1} interactive:`, isInteractive);
    });
}

function fixModalPosition(modal) {
    const modalDialog = modal.querySelector('.modal-dialog');
    const modalContent = modal.querySelector('.modal-content');
    
    if (!modalDialog || !modalContent) return;
    
    // Reset any inline styles
    modalDialog.style.margin = '';
    modalContent.style.margin = '';
    
    // Check if modal is too tall for viewport
    const viewportHeight = window.innerHeight;
    const modalHeight = modalContent.offsetHeight;
    
    if (modalHeight > viewportHeight - 40) {
        // Modal is too tall, adjust positioning
        modalDialog.style.margin = '20px auto';
        modalContent.style.maxHeight = (viewportHeight - 40) + 'px';
        modalContent.style.overflowY = 'auto';
    }
    
    // Optimize backdrop filter for mobile
    if (window.innerWidth <= 768) {
        modalContent.style.backdropFilter = 'none';
        modal.style.backdropFilter = 'none';
    }
}

function addResponsiveBehavior() {
    console.log('Adding responsive behavior...');
    
    // Handle form inputs in modals
    const modalInputs = document.querySelectorAll('.modal .form-control, .modal .form-select');
    console.log('Modal inputs found:', modalInputs.length);
    
    modalInputs.forEach((input, index) => {
        console.log(`Setting up input ${index + 1}:`, input.tagName, input.type || 'N/A');
        
        // Prevent zoom on iOS
        if (input.type === 'text' || input.type === 'email' || input.type === 'password') {
            input.style.fontSize = '16px';
        }
        
        // Add focus enhancement
        input.addEventListener('focus', function() {
            console.log('Input focused:', this.tagName, this.type || 'N/A');
            this.parentElement.classList.add('focused');
            this.style.backdropFilter = 'none';
            this.style.zIndex = '1001';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
            this.style.zIndex = '1000';
        });
        
        // Ensure input is interactive
        input.addEventListener('click', function(e) {
            console.log('Input clicked:', this.tagName, this.type || 'N/A');
            e.stopPropagation();
            this.focus();
        });
        
        // Enable typing
        input.addEventListener('input', function(e) {
            console.log('Input value changed:', this.tagName, this.type || 'N/A', this.value);
        });
        
        // Test if input is working
        setTimeout(() => {
            if (input.style.pointerEvents === 'none') {
                console.warn('Input still has pointer-events: none:', input);
                input.style.pointerEvents = 'auto';
            }
        }, 100);
    });
    
    // Handle modal buttons
    const modalButtons = document.querySelectorAll('.modal .btn');
    
    modalButtons.forEach(button => {
        // Add touch feedback
        button.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.95)';
        });
        
        button.addEventListener('touchend', function() {
            this.style.transform = '';
        });
        
        // Ensure button is clickable
        button.addEventListener('click', function(e) {
            console.log('Button clicked:', this.textContent.trim());
            e.stopPropagation();
        });
    });
}

function fixIOSSafariIssues() {
    // Fix for iOS Safari modal scrolling
    if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
        console.log('iOS device detected, applying Safari fixes...');
        
        const modals = document.querySelectorAll('.modal');
        
        modals.forEach(modal => {
            modal.addEventListener('shown.bs.modal', function() {
                // Force reflow
                this.offsetHeight;
                
                // Fix scrolling
                const modalBody = this.querySelector('.modal-body');
                if (modalBody) {
                    modalBody.style.webkitOverflowScrolling = 'touch';
                }
                
                // Remove blur effects on iOS for better performance
                this.style.backdropFilter = 'none';
                const modalContent = this.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.style.backdropFilter = 'none';
                }
                
                // Force enable form elements
                forceEnableFormElementsInModal(this);
            });
        });
    }
}

function forceEnableFormElements() {
    console.log('Force enabling all form elements globally...');
    
    // Enable all form elements on the page
    const allFormElements = document.querySelectorAll('input, select, textarea, label, button');
    console.log('Total form elements found:', allFormElements.length);
    
    allFormElements.forEach(element => {
        element.style.pointerEvents = 'auto';
        element.style.userSelect = 'auto';
        element.style.webkitUserSelect = 'auto';
        element.style.mozUserSelect = 'auto';
        element.style.msUserSelect = 'auto';
    });
}

function testFormFunctionality() {
    console.log('Testing overall form functionality...');
    
    // Test if we can create and interact with form elements
    const testInput = document.createElement('input');
    testInput.type = 'text';
    testInput.value = 'test';
    
    console.log('Test input created:', testInput);
    console.log('Test input value:', testInput.value);
    console.log('Test input type:', testInput.type);
    
    // Clean up test element
    testInput.remove();
}

// Enhanced modal opening function
function openModal(modalId, options = {}) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    
    const modalInstance = new bootstrap.Modal(modal, {
        backdrop: options.backdrop || true,
        keyboard: options.keyboard !== false,
        focus: options.focus !== false
    });
    
    // Pre-open setup
    modal.addEventListener('show.bs.modal', function() {
        // Clear any previous form data
        const forms = modal.querySelectorAll('form');
        forms.forEach(form => form.reset());
        
        // Force enable form elements
        forceEnableFormElementsInModal(this);
        
        // Focus first input if specified
        if (options.focusFirstInput) {
            const firstInput = modal.querySelector('input, select, textarea');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        }
        
        // Optimize for mobile
        if (window.innerWidth <= 768) {
            this.style.backdropFilter = 'none';
            const modalContent = this.querySelector('.modal-content');
            if (modalContent) {
                modalContent.style.backdropFilter = 'none';
            }
        }
    });
    
    modalInstance.show();
    return modalInstance;
}

// Enhanced modal closing function
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    
    const modalInstance = bootstrap.Modal.getInstance(modal);
    if (modalInstance) {
        modalInstance.hide();
    }
}

// Auto-resize modals on orientation change
window.addEventListener('orientationchange', function() {
    setTimeout(() => {
        const openModals = document.querySelectorAll('.modal.show');
        openModals.forEach(modal => {
            fixModalPosition(modal);
            forceEnableFormElementsInModal(modal);
        });
    }, 100);
});

// Export functions for global use
window.ModalEnhancements = {
    openModal,
    closeModal,
    fixModalPosition,
    forceEnableFormElementsInModal,
    testFormFunctionality
};
