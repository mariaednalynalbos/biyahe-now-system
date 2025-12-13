// Auto-responsive mobile detection and setup
document.addEventListener('DOMContentLoaded', function() {
    // Mobile detection
    function isMobile() {
        return window.innerWidth <= 768 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }
    
    // Auto-setup mobile layout
    function setupMobileLayout() {
        if (isMobile()) {
            document.body.classList.add('mobile-layout');
            
            // Auto-show mobile menu toggle
            const mobileToggle = document.getElementById('mobileMenuToggle');
            if (mobileToggle) {
                mobileToggle.style.display = 'flex';
            }
            
            // Auto-hide desktop navigation
            const topNav = document.getElementById('topNav');
            if (topNav) {
                topNav.classList.add('mobile-nav');
            }
        }
    }
    
    // Mobile menu functionality
    function initMobileMenu() {
        const mobileToggle = document.getElementById('mobileMenuToggle');
        const topNav = document.getElementById('topNav');
        const overlay = document.getElementById('mobileOverlay');
        
        if (mobileToggle && topNav) {
            mobileToggle.addEventListener('click', function() {
                topNav.classList.toggle('open');
                overlay.classList.toggle('show');
                mobileToggle.classList.toggle('active');
            });
            
            // Close on overlay click
            if (overlay) {
                overlay.addEventListener('click', function() {
                    topNav.classList.remove('open');
                    overlay.classList.remove('show');
                    mobileToggle.classList.remove('active');
                });
            }
        }
    }
    
    // Responsive table handling
    function handleResponsiveTables() {
        const tables = document.querySelectorAll('.upcoming-trips-table, .history-table');
        tables.forEach(table => {
            if (isMobile()) {
                table.style.fontSize = '11px';
                const cells = table.querySelectorAll('th, td');
                cells.forEach(cell => {
                    cell.style.padding = '4px 2px';
                });
            }
        });
    }
    
    // Auto-resize handler
    function handleResize() {
        setupMobileLayout();
        handleResponsiveTables();
    }
    
    // Initialize everything
    setupMobileLayout();
    initMobileMenu();
    handleResponsiveTables();
    
    // Listen for resize events
    window.addEventListener('resize', handleResize);
});