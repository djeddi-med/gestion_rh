// Main application initialization
class DNKApp {
    constructor() {
        this.init();
    }

    init() {
        console.log('DNK Application Initialized');
        
        // Initialize all components
        this.initializeComponents();
        
        // Add global event listeners
        this.addGlobalListeners();
    }

    initializeComponents() {
        // Components are initialized in their respective files
        // This is a placeholder for future global initializations
    }

    addGlobalListeners() {
        // Global click handler to close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            // Close user dropdown if clicking outside
            const userMenu = document.getElementById('user-dropdown');
            const userBtn = document.getElementById('user-menu-btn');
            
            if (userMenu && userBtn && !userBtn.contains(e.target) && !userMenu.contains(e.target)) {
                // Dropdown closes automatically on mouse leave due to CSS
            }
        });
    }
}

// Initialize application when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new DNKApp();
});