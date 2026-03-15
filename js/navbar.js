// ─────────────────────────────────────────────
// NAVBAR
// ─────────────────────────────────────────────
class NavBar {
    constructor() {
        this.navbar          = document.getElementById('navbar');
        this.datetimeElement = document.getElementById('datetime');
        this.ipElement       = document.getElementById('ip-address');
        this.menuBtn         = document.getElementById('user-menu-btn');
        this.dropdown        = document.getElementById('user-dropdown');
        this.isDropdownOpen  = false;

        this.init();
    }

    init() {
        this.updateDateTime();
        this.getIPAddress();
        this.handleScroll();
        this.handleDropdown();
        setInterval(() => this.updateDateTime(), 1000);
    }

    updateDateTime() {
        const now = new Date();
        this.datetimeElement.textContent = now.toLocaleDateString('fr-FR', {
            weekday: 'long', year: 'numeric', month: 'long',
            day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit'
        });
    }

    async getIPAddress() {
        try {
            const res  = await fetch('https://api.ipify.org?format=json');
            const data = await res.json();
            this.ipElement.textContent = `IP: ${data.ip}`;
        } catch {
            this.ipElement.textContent = 'IP: Non disponible';
        }
    }

    handleScroll() {
        window.addEventListener('scroll', () => {
            this.navbar.classList.toggle('scrolled', window.scrollY > 10);
        });
    }

    // ── Dropdown géré en JS (click) — plus de group-hover CSS ──
    handleDropdown() {
        if (!this.menuBtn || !this.dropdown) return;

        // Ouvrir / fermer au clic sur le bouton
        this.menuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.isDropdownOpen ? this.closeDropdown() : this.openDropdown();
        });

        // Fermer si clic ailleurs dans la page
        document.addEventListener('click', (e) => {
            if (!this.menuBtn.contains(e.target) && !this.dropdown.contains(e.target)) {
                this.closeDropdown();
            }
        });

        // Fermer avec Échap
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') this.closeDropdown();
        });
    }

    openDropdown() {
        this.dropdown.classList.remove('opacity-0', 'invisible', 'translate-y-1');
        this.dropdown.classList.add('opacity-100', 'visible', 'translate-y-0');
        document.getElementById('chevron-user-menu')?.classList.add('rotate-180');
        this.isDropdownOpen = true;
    }

    closeDropdown() {
        this.dropdown.classList.add('opacity-0', 'invisible', 'translate-y-1');
        this.dropdown.classList.remove('opacity-100', 'visible', 'translate-y-0');
        document.getElementById('chevron-user-menu')?.classList.remove('rotate-180');
        this.isDropdownOpen = false;
    }
}

document.addEventListener('DOMContentLoaded', () => { new NavBar(); });


// ─────────────────────────────────────────────
// POPUP DÉCONNEXION
// ─────────────────────────────────────────────
class LogoutPopup {
    constructor() {
        this.overlay = document.getElementById('logout-overlay');
        this.popup   = document.getElementById('logout-popup');
        this.trigger = document.getElementById('btn-logout-trigger');
        this.cancel  = document.getElementById('btn-logout-cancel');

        if (!this.overlay) return;
        this.bind();
    }

    bind() {
        this.trigger?.addEventListener('click', (e) => {
            e.stopPropagation(); // empêche la fermeture du dropdown de bloquer
            this.open();
        });

        this.cancel?.addEventListener('click', () => this.close());

        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay) this.close();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.overlay.classList.contains('hidden')) {
                this.close();
            }
        });
    }

    open() {
        this.overlay.classList.remove('hidden');
        this.overlay.classList.add('flex');
        requestAnimationFrame(() => {
            this.popup.classList.remove('scale-90', 'opacity-0');
            this.popup.classList.add('scale-100', 'opacity-100');
        });
    }

    close() {
        this.popup.classList.remove('scale-100', 'opacity-100');
        this.popup.classList.add('scale-90', 'opacity-0');
        setTimeout(() => {
            this.overlay.classList.add('hidden');
            this.overlay.classList.remove('flex');
        }, 250);
    }
}

document.addEventListener('DOMContentLoaded', () => { new LogoutPopup(); });