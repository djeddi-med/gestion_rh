/**
 * modals.js — Gestionnaire de modals avec pile (stack)
 * Corrige: modals qui s'ouvrent derrière, overlay partagé mal géré,
 * modals multiples simultanés.
 */
class ModalManager {
    // Pile des modals ouverts (LIFO)
    static _stack = [];

    /**
     * Ouvrir un modal.
     * Les sous-modals s'empilent au-dessus du modal parent.
     */
    static openModal(modalId) {
        const modal = document.getElementById(`modal-${modalId}`);
        if (!modal) {
            console.warn(`[ModalManager] Modal introuvable: modal-${modalId}`);
            return;
        }

        // Éviter les doublons dans la pile
        if (this._stack.includes(modalId)) return;

        const overlay = document.getElementById('modal-overlay');
        const depth = this._stack.length; // 0 = premier modal, 1 = sous-modal, etc.

        // Calcul du z-index: overlay base 1000, modals au-dessus
        // Premier modal: 1010, deuxième: 1020, etc.
        const baseZ = 1010 + depth * 10;

        // Mettre à jour le z-index du modal
        modal.style.zIndex = baseZ;

        // L'overlay s'adapte: visible depuis le 1er modal, assombri plus à chaque niveau
        if (overlay) {
            overlay.style.zIndex = baseZ - 5;
            overlay.classList.remove('hidden');
            const opacity = Math.min(0.5 + depth * 0.15, 0.8);
            overlay.style.backgroundColor = `rgba(0,0,0,${opacity})`;
        }

        // Afficher le modal
        modal.classList.remove('hidden');
        // Forcer le reflow pour l'animation
        modal.offsetHeight;

        const modalContent = modal.querySelector('div');
        if (modalContent) {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }

        this._stack.push(modalId);
        document.body.style.overflow = 'hidden';

        // Attacher les listeners de fermeture
        this._attachCloseListeners(modal, modalId);

        // Event custom pour signaler l'ouverture
        document.dispatchEvent(new CustomEvent('modalOpened', { detail: { modalId } }));
    }

    /**
     * Fermer un modal spécifique (ou le dernier ouvert si non précisé).
     */
    static closeModal(modalId) {
        // Si modalId non précisé, fermer le dernier ouvert
        if (!modalId) {
            modalId = this._stack[this._stack.length - 1];
        }
        if (!modalId) return;

        const modal = document.getElementById(`modal-${modalId}`);
        if (!modal) return;

        // Animation de fermeture
        const modalContent = modal.querySelector('div');
        if (modalContent) {
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
        }

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.style.zIndex = '';

            // Retirer de la pile
            this._stack = this._stack.filter(id => id !== modalId);

            const overlay = document.getElementById('modal-overlay');
            if (overlay) {
                if (this._stack.length === 0) {
                    // Plus de modals: cacher l'overlay
                    overlay.classList.add('hidden');
                    overlay.style.zIndex = '';
                    overlay.style.backgroundColor = '';
                    document.body.style.overflow = '';
                } else {
                    // Remettre l'overlay au niveau du modal restant
                    const depth = this._stack.length - 1;
                    const baseZ = 1010 + depth * 10;
                    overlay.style.zIndex = baseZ - 5;
                    const opacity = Math.min(0.5 + depth * 0.15, 0.8);
                    overlay.style.backgroundColor = `rgba(0,0,0,${opacity})`;
                }
            }

            document.dispatchEvent(new CustomEvent('modalClosed', { detail: { modalId } }));
        }, 300);
    }

    /**
     * Fermer tous les modals ouverts.
     */
    static closeAllModals() {
        // Fermer dans l'ordre inverse (du plus récent au plus ancien)
        const stackCopy = [...this._stack].reverse();
        stackCopy.forEach(id => {
            const modal = document.getElementById(`modal-${id}`);
            if (modal) {
                modal.classList.add('hidden');
                modal.style.zIndex = '';
                const modalContent = modal.querySelector('div');
                if (modalContent) {
                    modalContent.classList.remove('scale-100', 'opacity-100');
                    modalContent.classList.add('scale-95', 'opacity-0');
                }
            }
        });
        this._stack = [];

        const overlay = document.getElementById('modal-overlay');
        if (overlay) {
            overlay.classList.add('hidden');
            overlay.style.zIndex = '';
            overlay.style.backgroundColor = '';
        }
        document.body.style.overflow = '';
    }

    /**
     * Attacher les listeners de fermeture (bouton X, Escape).
     * Utilise des listeners avec option { once: false } gérés proprement.
     */
    static _attachCloseListeners(modal, modalId) {
        // Bouton de fermeture (classe générique .modal-close-btn)
        const closeBtns = modal.querySelectorAll('.modal-close-btn');
        closeBtns.forEach(btn => {
            // Cloner pour supprimer les anciens listeners
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            newBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.closeModal(modalId);
            });
        });

        // Touche Escape: ferme le dernier modal ouvert
        const escHandler = (e) => {
            if (e.key === 'Escape' && this._stack[this._stack.length - 1] === modalId) {
                this.closeModal(modalId);
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    }
}

// Fermeture de l'overlay désactivée (ne ferme pas les modals par clic en dehors)
document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.getElementById('modal-overlay');
    if (overlay) {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                // Optionnel: décommenter pour fermer au clic sur overlay
                // ModalManager.closeModal();
            }
        });
    }
});
