/**
 * NotificationManager — DNK GRH
 * Système de notifications dynamique, coloré, animé
 * Toujours au premier plan (z-index: 99999)
 * Durée fixe : 5 secondes
 */
class NotificationManager {

    static DURATION = 5000; // 5 secondes

    static _config = {
        success: {
            icon:  'fas fa-check-circle',
            title: 'Succès',
        },
        error: {
            icon:  'fas fa-times-circle',
            title: 'Erreur',
        },
        warning: {
            icon:  'fas fa-exclamation-triangle',
            title: 'Attention',
        },
        info: {
            icon:  'fas fa-info-circle',
            title: 'Information',
        },
    };

    /**
     * Affiche une notification
     *
     * @param {string} type     'success' | 'error' | 'warning' | 'info'
     * @param {string} message  Texte principal
     * @param {string} [title]  Titre optionnel (sinon titre par défaut du type)
     */
    static show(type, message, title = null) {
        const container = this._getContainer();
        const cfg       = this._config[type] ?? this._config.info;

        const notif = document.createElement('div');
        notif.className = `notif notif-${type}`;
        notif.setAttribute('role', 'alert');
        notif.setAttribute('aria-live', 'assertive');

        notif.innerHTML = `
            <div class="notif-icon-wrap">
                <i class="${cfg.icon}"></i>
            </div>
            <div class="notif-body">
                <div class="notif-title">${title ?? cfg.title}</div>
                ${message ? `<div class="notif-message">${message}</div>` : ''}
            </div>
            <button class="notif-close" aria-label="Fermer">
                <i class="fas fa-times"></i>
            </button>
            <div class="notif-progress">
                <div class="notif-progress-bar"></div>
            </div>
        `;

        // Fermeture manuelle
        notif.querySelector('.notif-close').addEventListener('click', () => {
            this._remove(notif);
        });

        // Clic sur la notif pour la fermer aussi
        notif.addEventListener('click', (e) => {
            if (!e.target.closest('.notif-close')) this._remove(notif);
        });

        container.appendChild(notif);

        // Auto-suppression après DURATION
        const timer = setTimeout(() => this._remove(notif), this.DURATION);

        // Stocker le timer pour pouvoir l'annuler si fermeture manuelle
        notif._timer = timer;

        // Pause du timer au survol
        notif.addEventListener('mouseenter', () => {
            clearTimeout(notif._timer);
        });
        notif.addEventListener('mouseleave', () => {
            notif._timer = setTimeout(() => this._remove(notif), 1500);
        });

        return notif;
    }

    // ── Raccourcis ──
    static success(message, title = null) { return this.show('success', message, title); }
    static error(message,   title = null) { return this.show('error',   message, title); }
    static warning(message, title = null) { return this.show('warning', message, title); }
    static info(message,    title = null) { return this.show('info',    message, title); }

    // ── Suppression avec animation de sortie ──
    static _remove(notif) {
        if (!notif || notif.classList.contains('notif-hiding')) return;
        clearTimeout(notif._timer);
        notif.classList.add('notif-hiding');
        setTimeout(() => notif.remove(), 400);
    }

    // ── Supprimer toutes les notifications ──
    static clear() {
        document.querySelectorAll('.notif').forEach(n => this._remove(n));
    }

    // ── Obtenir ou créer le conteneur ──
    static _getContainer() {
        let container = document.getElementById('notification-container');

        if (!container) {
            container = document.createElement('div');
            container.id = 'notification-container';
            document.body.appendChild(container);
        }

        // Forcer le z-index maximal à chaque appel
        container.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
            max-width: 380px;
            width: 100%;
        `;

        return container;
    }
}