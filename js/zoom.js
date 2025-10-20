// Zoom Controls for 3-Minute Meditation
(function() {
    'use strict';

    // Configuration
    const MIN_ZOOM = 100;
    const MAX_ZOOM = 250;
    const ZOOM_STEP = 10;
    const STORAGE_KEY = 'meditationZoomLevel';

    // Elements (will be set in init)
    let zoomInBtn;
    let zoomOutBtn;
    let resetZoomBtn;
    let devotionContent;
    let devotionContainer;

    // Current zoom level
    let currentZoom = MIN_ZOOM;

    // Initialize
    function init() {
        // Get elements
        zoomInBtn = document.getElementById('zoomInBtn');
        zoomOutBtn = document.getElementById('zoomOutBtn');
        resetZoomBtn = document.getElementById('resetZoomBtn');
        devotionContent = document.querySelector('.devotion-content');
        devotionContainer = document.querySelector('.devotion-container');

        // Load saved zoom level
        const savedZoom = localStorage.getItem(STORAGE_KEY);
        if (savedZoom) {
            currentZoom = parseInt(savedZoom, 10);
            if (currentZoom < MIN_ZOOM) currentZoom = MIN_ZOOM;
            if (currentZoom > MAX_ZOOM) currentZoom = MAX_ZOOM;
        }

        // Apply initial zoom
        applyZoom(currentZoom);

        // Add event listeners
        if (zoomInBtn) {
            zoomInBtn.addEventListener('click', zoomIn);
        }

        if (zoomOutBtn) {
            zoomOutBtn.addEventListener('click', zoomOut);
        }

        if (resetZoomBtn) {
            resetZoomBtn.addEventListener('click', resetZoom);
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', handleKeyboard);
    }

    // Zoom In
    function zoomIn() {
        if (currentZoom < MAX_ZOOM) {
            currentZoom += ZOOM_STEP;
            applyZoom(currentZoom);
            showNotification(`Zoomed to ${currentZoom}%`);
        }
    }

    // Zoom Out
    function zoomOut() {
        if (currentZoom > MIN_ZOOM) {
            currentZoom -= ZOOM_STEP;
            applyZoom(currentZoom);
            showNotification(`Zoomed to ${currentZoom}%`);
        }
    }

    // Reset Zoom
    function resetZoom() {
        currentZoom = MIN_ZOOM;
        applyZoom(currentZoom);
        showNotification('Zoom reset to 100%');
    }

    // Apply Zoom
    function applyZoom(zoom) {
        // Calculate font size (base font is typically 16px)
        const baseFontSize = 16;
        const newFontSize = (baseFontSize * zoom) / 100;

        // Apply to devotion content (main target) with !important
        if (devotionContent) {
            devotionContent.style.setProperty('font-size', newFontSize + 'px', 'important');
        }

        // Also apply to container for any text outside content sections
        if (devotionContainer) {
            devotionContainer.style.setProperty('font-size', newFontSize + 'px', 'important');
        }

        // Apply to all section elements inside content
        const sections = document.querySelectorAll('.devotion-content .section');
        sections.forEach(section => {
            section.style.setProperty('font-size', newFontSize + 'px', 'important');
        });

        // Apply to all paragraphs and text elements
        const textElements = document.querySelectorAll('.devotion-content p, .devotion-content span, .devotion-content blockquote, .devotion-content li');
        textElements.forEach(element => {
            element.style.setProperty('font-size', newFontSize + 'px', 'important');
        });

        // Update button states
        updateButtonStates();

        // Save to localStorage
        localStorage.setItem(STORAGE_KEY, zoom.toString());
    }

    // Update Button States
    function updateButtonStates() {
        if (zoomInBtn) {
            zoomInBtn.disabled = currentZoom >= MAX_ZOOM;
            zoomInBtn.style.opacity = currentZoom >= MAX_ZOOM ? '0.5' : '1';
            zoomInBtn.style.cursor = currentZoom >= MAX_ZOOM ? 'not-allowed' : 'pointer';
        }

        if (zoomOutBtn) {
            zoomOutBtn.disabled = currentZoom <= MIN_ZOOM;
            zoomOutBtn.style.opacity = currentZoom <= MIN_ZOOM ? '0.5' : '1';
            zoomOutBtn.style.cursor = currentZoom <= MIN_ZOOM ? 'not-allowed' : 'pointer';
        }

        if (resetZoomBtn) {
            resetZoomBtn.disabled = currentZoom === MIN_ZOOM;
            resetZoomBtn.style.opacity = currentZoom === MIN_ZOOM ? '0.5' : '1';
            resetZoomBtn.style.cursor = currentZoom === MIN_ZOOM ? 'not-allowed' : 'pointer';
        }
    }

    // Keyboard Shortcuts
    function handleKeyboard(e) {
        // Ctrl/Cmd + Plus: Zoom In
        if ((e.ctrlKey || e.metaKey) && (e.key === '+' || e.key === '=')) {
            e.preventDefault();
            zoomIn();
        }

        // Ctrl/Cmd + Minus: Zoom Out
        if ((e.ctrlKey || e.metaKey) && e.key === '-') {
            e.preventDefault();
            zoomOut();
        }

        // Ctrl/Cmd + 0: Reset Zoom
        if ((e.ctrlKey || e.metaKey) && e.key === '0') {
            e.preventDefault();
            resetZoom();
        }
    }

    // Show Notification
    function showNotification(message) {
        // Remove existing notification
        const existing = document.querySelector('.zoom-notification');
        if (existing) {
            existing.remove();
        }

        // Create notification
        const notification = document.createElement('div');
        notification.className = 'zoom-notification';
        notification.textContent = message;
        document.body.appendChild(notification);

        // Show notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        // Hide and remove notification
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 2000);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
