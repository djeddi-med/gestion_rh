// SideBar functionality
class SideBar {
    constructor() {
        this.sidebarItems = document.querySelectorAll('.sidebar-item');
        this.init();
    }

    init() {
        this.sidebarItems.forEach(item => {
            item.addEventListener('click', (e) => {
                this.handleSidebarClick(e, item);
            });
        });
    }

    handleSidebarClick(e, item) {
        e.preventDefault();
        
        // Remove active class from all items
        this.sidebarItems.forEach(i => i.classList.remove('active'));
        
        // Add active class to clicked item
        item.classList.add('active');
        
        // Get modal identifier
        const modalId = item.getAttribute('data-modal');
        
        // Show notification
        NotificationManager.show(
            'success',
            `Ouverture de ${item.textContent.trim()}`,
            `Modal ${item.textContent.trim()} ouvert avec succès`
        );
        
        // Open modal
        ModalManager.openModal(modalId);
    }
}

// Initialize SideBar when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new SideBar();
});