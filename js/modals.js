// Modal management
class ModalManager {
    static openModal(modalId) {
        const modal = document.getElementById(`modal-${modalId}`);
        const overlay = document.getElementById('modal-overlay');
        
        if (!modal) {
            console.error(`Modal ${modalId} not found`);
            return;
        }

        // Show overlay and modal
        overlay.classList.remove('hidden');
        modal.classList.remove('hidden');
        
        // Animate in
        setTimeout(() => {
            overlay.classList.add('opacity-100');
            const modalContent = modal.querySelector('div');
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);

        // Add event listeners for close buttons
        this.attachCloseListeners(modal, overlay);
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }

    static closeModal(modalId) {
        const modal = document.getElementById(`modal-${modalId}`);
        const overlay = document.getElementById('modal-overlay');
        
        if (!modal) return;

        // Animate out
        const modalContent = modal.querySelector('div');
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            overlay.classList.add('hidden');
            overlay.classList.remove('opacity-100');
            
            // Restore body scroll
            document.body.style.overflow = '';
        }, 300);
    }

    static attachCloseListeners(modal, overlay) {
        const closeBtn = modal.querySelector('.modal-close-btn');
        const modalId = modal.id.replace('modal-', '');
        
        // Close button
        closeBtn?.addEventListener('click', () => {
            this.closeModal(modalId);
        });
        
        // Overlay click (do nothing - modal doesn't close on overlay click)
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                e.stopPropagation();
            }
        });
        
        // Escape key
        const escapeHandler = (e) => {
            if (e.key === 'Escape') {
                this.closeModal(modalId);
                document.removeEventListener('keydown', escapeHandler);
            }
        };
        document.addEventListener('keydown', escapeHandler);
    }

    static closeAllModals() {
        const modals = document.querySelectorAll('[id^="modal-"]');
        const overlay = document.getElementById('modal-overlay');
        
        modals.forEach(modal => {
            modal.classList.add('hidden');
        });
        
        overlay.classList.add('hidden');
        overlay.classList.remove('opacity-100');
        document.body.style.overflow = '';
    }
}