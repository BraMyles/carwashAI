/**
 * AGGRESSIVE MODAL RESPONSIVENESS FIXES
 * This file ensures all modals and their form elements are fully interactive
 */

class ModalFixes {
    constructor() {
        this.init();
    }

    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupModalFixes());
        } else {
            this.setupModalFixes();
        }
    }

    setupModalFixes() {
        console.log('ModalFixes: Setting up comprehensive modal fixes...');
        
        // Fix existing modals
        this.fixAllModals();
        
        // Listen for new modals
        this.observeModalChanges();
        
        // Global click handler to force enable elements
        this.setupGlobalClickHandler();
    }

    fixAllModals() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => this.fixModal(modal));
    }

    fixModal(modal) {
        if (!modal) return;
        
        console.log('ModalFixes: Fixing modal:', modal.id || 'unnamed');
        
        // Force enable all form elements
        this.forceEnableFormElements(modal);
        
        // Fix modal positioning and stacking
        this.fixModalPosition(modal);
        
        // Add event listeners
        this.addModalEventListeners(modal);
    }

    forceEnableFormElements(modal) {
        const selectors = [
            'input', 'select', 'textarea', 'button', 'label',
            '.form-control', '.form-select', '.btn', '.form-label'
        ];
        
        selectors.forEach(selector => {
            const elements = modal.querySelectorAll(selector);
            elements.forEach(element => this.forceEnableElement(element));
        });
    }

    forceEnableElement(element) {
        if (!element) return;
        
        // Remove all problematic attributes and styles
        const problematicAttrs = ['disabled', 'readonly', 'style'];
        problematicAttrs.forEach(attr => {
            if (element.hasAttribute(attr)) {
                element.removeAttribute(attr);
            }
        });
        
        // Force remove problematic inline styles
        const problematicStyles = [
            'pointer-events', 'user-select', 'opacity', 'visibility',
            'position', 'z-index', 'display', 'overflow'
        ];
        
        problematicStyles.forEach(style => {
            element.style.removeProperty(style);
        });
        
        // Force set interactive properties
        element.style.setProperty('pointer-events', 'auto', 'important');
        element.style.setProperty('user-select', 'auto', 'important');
        element.style.setProperty('opacity', '1', 'important');
        element.style.setProperty('visibility', 'visible', 'important');
        element.style.setProperty('position', 'relative', 'important');
        element.style.setProperty('z-index', '100000', 'important');
        
        // Specific fixes for different element types
        if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
            element.style.setProperty('cursor', 'text', 'important');
            element.style.setProperty('background-color', 'rgba(255, 255, 255, 0.1)', 'important');
            element.style.setProperty('border', '1px solid rgba(255, 255, 255, 0.3)', 'important');
            element.style.setProperty('color', 'white', 'important');
            element.style.setProperty('padding', '8px 12px', 'important');
            element.style.setProperty('font-size', '14px', 'important');
            element.style.setProperty('line-height', '1.5', 'important');
            element.style.setProperty('border-radius', '4px', 'important');
        }
        
        if (element.tagName === 'SELECT') {
            element.style.setProperty('cursor', 'pointer', 'important');
            element.style.setProperty('background-color', 'rgba(255, 255, 255, 0.1)', 'important');
            element.style.setProperty('border', '1px solid rgba(255, 255, 255, 0.3)', 'important');
            element.style.setProperty('color', 'white', 'important');
            element.style.setProperty('padding', '8px 12px', 'important');
            element.style.setProperty('font-size', '14px', 'important');
            element.style.setProperty('line-height', '1.5', 'important');
            element.style.setProperty('border-radius', '4px', 'important');
        }
        
        if (element.tagName === 'BUTTON') {
            element.style.setProperty('cursor', 'pointer', 'important');
            element.style.setProperty('background-color', 'rgba(79, 172, 254, 0.8)', 'important');
            element.style.setProperty('color', 'white', 'important');
            element.style.setProperty('padding', '8px 16px', 'important');
            element.style.setProperty('font-size', '14px', 'important');
            element.style.setProperty('line-height', '1.5', 'important');
            element.style.setProperty('border-radius', '4px', 'important');
            element.style.setProperty('border', 'none', 'important');
        }
        
        if (element.tagName === 'LABEL') {
            element.style.setProperty('color', 'white', 'important');
            element.style.setProperty('font-weight', '500', 'important');
            element.style.setProperty('margin-bottom', '4px', 'important');
            element.style.setProperty('display', 'block', 'important');
        }
        
        // Ensure element is not disabled
        element.disabled = false;
        element.readOnly = false;
        
        // Add click handler to ensure focus
        element.addEventListener('click', (e) => {
            e.stopPropagation();
            if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA' || element.tagName === 'SELECT') {
                element.focus();
            }
        });
        
        // Add input handler for debugging
        if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA' || element.tagName === 'SELECT') {
            element.addEventListener('input', () => {
                console.log('ModalFixes: Element input detected:', element.tagName, element.value);
            });
        }
    }

    fixModalPosition(modal) {
        // Ensure modal is properly positioned
        modal.style.setProperty('position', 'fixed', 'important');
        modal.style.setProperty('top', '0', 'important');
        modal.style.setProperty('left', '0', 'important');
        modal.style.setProperty('width', '100%', 'important');
        modal.style.setProperty('height', '100%', 'important');
        modal.style.setProperty('z-index', '99999', 'important');
        
        // Fix modal dialog
        const dialog = modal.querySelector('.modal-dialog');
        if (dialog) {
            dialog.style.setProperty('position', 'relative', 'important');
            dialog.style.setProperty('z-index', '100000', 'important');
            dialog.style.setProperty('margin', '1.75rem auto', 'important');
        }
        
        // Fix modal content
        const content = modal.querySelector('.modal-content');
        if (content) {
            content.style.setProperty('position', 'relative', 'important');
            content.style.setProperty('z-index', '100001', 'important');
        }
    }

    addModalEventListeners(modal) {
        // Listen for modal show events
        modal.addEventListener('show.bs.modal', () => {
            console.log('ModalFixes: Modal showing, applying fixes...');
            setTimeout(() => this.fixModal(modal), 50);
        });
        
        modal.addEventListener('shown.bs.modal', () => {
            console.log('ModalFixes: Modal shown, applying final fixes...');
            setTimeout(() => this.fixModal(modal), 100);
            
            // Focus first input
            const firstInput = modal.querySelector('input, select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        });
        
        // Listen for modal hide events
        modal.addEventListener('hide.bs.modal', () => {
            console.log('ModalFixes: Modal hiding...');
        });
        
        modal.addEventListener('hidden.bs.modal', () => {
            console.log('ModalFixes: Modal hidden...');
        });
    }

    observeModalChanges() {
        // Watch for new modals being added to the DOM
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        if (node.classList && node.classList.contains('modal')) {
                            console.log('ModalFixes: New modal detected, fixing...');
                            setTimeout(() => this.fixModal(node), 100);
                        }
                        
                        // Check for modals within added nodes
                        const modals = node.querySelectorAll ? node.querySelectorAll('.modal') : [];
                        modals.forEach(modal => {
                            console.log('ModalFixes: Modal within added node detected, fixing...');
                            setTimeout(() => this.fixModal(modal), 100);
                        });
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    setupGlobalClickHandler() {
        // Global click handler to force enable elements when clicked
        document.addEventListener('click', (e) => {
            const target = e.target;
            
            // If clicking on a modal element, ensure it's enabled
            if (target.closest('.modal')) {
                const modal = target.closest('.modal');
                if (modal) {
                    this.forceEnableFormElements(modal);
                }
            }
        });
    }

    // Public method to force fix a specific modal
    forceFixModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            console.log('ModalFixes: Force fixing modal:', modalId);
            this.fixModal(modal);
        } else {
            console.warn('ModalFixes: Modal not found:', modalId);
        }
    }

    // Public method to fix all modals on the page
    forceFixAllModals() {
        console.log('ModalFixes: Force fixing all modals...');
        this.fixAllModals();
    }
}

// Initialize when DOM is ready
const modalFixes = new ModalFixes();

// Make it globally available
window.ModalFixes = modalFixes;

// Auto-fix modals every 2 seconds as a fallback
setInterval(() => {
    modalFixes.forceFixAllModals();
}, 2000);

console.log('ModalFixes: Loaded and ready');




