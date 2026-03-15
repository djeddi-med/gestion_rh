/**
 * js/contrat/delete_contrat.js
 * Gère le modal de confirmation #modal-delete-confirm
 */
class DeleteContrat {
    constructor() {
        this._pendingId = null;
        this._submitting = false;
        this._init();
    }

    _init() {
        document.getElementById('confirm-delete')
            ?.addEventListener('click', () => this._doDelete());

        document.getElementById('cancel-delete')
            ?.addEventListener('click', () => {
                this._pendingId  = null;
                this._submitting = false;
                ModalManager.closeModal('delete-confirm');
            });

        // Fermer via la croix (modal-close-btn dans le modal delete)
        document.querySelectorAll('#modal-delete-confirm .modal-close-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                this._pendingId = null;
                ModalManager.closeModal('delete-confirm');
            });
        });
    }

    /* Appelée depuis show_contrat.js */
    confirm(id, nom, prenom, ref, poste) {
        this._pendingId  = id;
        this._submitting = false;

        document.getElementById('del-emp-name').textContent =
            `${nom} ${prenom}`;
        document.getElementById('del-contrat-info').textContent =
            `Réf : ${ref || '—'} · ${poste || '—'}`;

        ModalManager.openModal('delete-confirm');
    }

    async _doDelete() {
        if (!this._pendingId || this._submitting) return;
        this._submitting = true;

        const btn  = document.getElementById('confirm-delete');
        const orig = btn?.innerHTML;
        if (btn) {
            btn.disabled  = true;
            btn.innerHTML = '<span class="ct-spinner"></span> Suppression…';
            btn.style.background = '#b91c1c';
        }

        try {
            const r    = await fetch('php/contrat/delete_contrat.php', {
                method : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body   : JSON.stringify({ id: this._pendingId })
            });
            const text = await r.text();
            let d;
            try { d = JSON.parse(text); }
            catch(_) {
                NotificationManager.show('error','Serveur','Réponse inattendue');
                return;
            }

            if (d.success) {
                NotificationManager.show('success','Supprimé', d.message || 'Contrat supprimé');
                ModalManager.closeModal('delete-confirm');
                window.showContrat?.loadContrats(
                    window.showContrat.currentPage,
                    window.showContrat.searchTerm
                );
            } else {
                NotificationManager.show('error','Erreur', d.message);
            }
        } catch(e) {
            console.error(e);
            NotificationManager.show('error','Connexion','Impossible de joindre le serveur');
        } finally {
            this._pendingId  = null;
            this._submitting = false;
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = orig;
                btn.style.background = '';
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.deleteContrat = new DeleteContrat();
});