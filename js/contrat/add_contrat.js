/**
 * js/contrat/add_contrat.js
 * Gère le modal #modal-new_contract
 * IDs HTML : nc-*
 */
class AddContrat {
    constructor() {
        this._submitting = false;
        this._searchTimer = null;
        this._selectedEmployee = null;
        this._init();
    }

    _init() {
        this._bindType();
        this._bindSearch();
        this._bindSubmit();
        this._bindCancel();
        this._bindClose();

        document.addEventListener('modalOpened', e => {
            if (e.detail === 'new_contract') {
                this._submitting = false;
                this._reset();
            }
        });
    }

    /* ── Toggle CDI / CDD ── */
    _bindType() {
        ['CDI','CDD'].forEach(v => {
            document.getElementById(`nc-btn-${v.toLowerCase()}`)
                ?.addEventListener('click', () => this._setType(v));
        });
    }

    _setType(val) {
        document.getElementById('nc-type-contrat').value = val;

        document.getElementById('nc-btn-cdi').classList.toggle('active', val === 'CDI');
        document.getElementById('nc-btn-cdd').classList.toggle('active', val === 'CDD');

        // Date fin : visible + obligatoire seulement CDD
        const row = document.getElementById('nc-date-fin-row');
        const inp = document.getElementById('nc-date-fin');
        const req = document.getElementById('nc-fin-req');

        if (val === 'CDI') {
            row?.classList.add('ct-dimmed');
            if (inp) { inp.value = ''; inp.required = false; }
            if (req) req.style.display = 'none';
        } else {
            row?.classList.remove('ct-dimmed');
            if (inp) inp.required = true;
            if (req) req.style.display = '';
        }
    }

    /* ── Recherche employé ── */
    _bindSearch() {
        const input    = document.getElementById('nc-emp-search-input');
        const dropdown = document.getElementById('nc-emp-dropdown');
        if (!input || !dropdown) return;

        input.addEventListener('input', () => {
            clearTimeout(this._searchTimer);
            const q = input.value.trim();
            if (q.length < 1) { dropdown.classList.remove('open'); return; }
            this._searchTimer = setTimeout(() => this._searchEmployee(q), 260);
        });

        // Fermer dropdown au clic extérieur
        document.addEventListener('click', e => {
            if (!e.target.closest('.ct-emp-search')) {
                dropdown.classList.remove('open');
            }
        });
    }

    async _searchEmployee(q) {
        const dropdown = document.getElementById('nc-emp-dropdown');
        dropdown.innerHTML = '<div class="ct-emp-dd-empty"><i class="fas fa-spinner fa-spin"></i> Recherche…</div>';
        dropdown.classList.add('open');

        try {
            const r    = await fetch(`php/contrat/get_employees_list.php?search=${encodeURIComponent(q)}`);
            const text = await r.text();
            let d;
            try { d = JSON.parse(text); }
            catch(_) {
                dropdown.innerHTML = '<div class="ct-emp-dd-empty">Erreur serveur</div>';
                return;
            }
            if (!d.success || !d.data.length) {
                dropdown.innerHTML = '<div class="ct-emp-dd-empty">Aucun employé trouvé</div>';
                return;
            }
            dropdown.innerHTML = d.data.map(emp => `
                <div class="ct-emp-item" data-id="${emp.id}"
                     data-nom="${emp.nom}" data-prenom="${emp.prenom}"
                     data-mat="${emp.matricule}" data-photo="${emp.photo || 'user.png'}">
                    <img class="ct-emp-item-img"
                         src="img/employee/${emp.photo || 'user.png'}"
                         onerror="this.src='img/employee/user.png'">
                    <div>
                        <div class="ct-emp-item-name">${emp.nom} ${emp.prenom}</div>
                        <div class="ct-emp-item-mat">${emp.matricule}</div>
                    </div>
                </div>`).join('');

            dropdown.querySelectorAll('.ct-emp-item').forEach(el => {
                el.addEventListener('click', () => this._selectEmployee(el));
            });
        } catch(e) {
            dropdown.innerHTML = '<div class="ct-emp-dd-empty">Connexion impossible</div>';
        }
    }

    _selectEmployee(el) {
        const id     = el.dataset.id;
        const nom    = el.dataset.nom;
        const prenom = el.dataset.prenom;
        const mat    = el.dataset.mat;
        const photo  = el.dataset.photo;

        this._selectedEmployee = { id, nom, prenom, mat, photo };

        document.getElementById('nc-id-employee').value = id;
        document.getElementById('nc-emp-name').textContent = `${nom} ${prenom}`;
        document.getElementById('nc-emp-mat').textContent  = `Matricule : ${mat}`;

        // Remplacer avatar
        const wrap = document.getElementById('nc-emp-avatar-wrap');
        if (wrap) {
            wrap.outerHTML = `<img class="ct-emp-avatar" id="nc-emp-avatar-wrap"
                src="img/employee/${photo}" onerror="this.src='img/employee/user.png'"
                alt="${nom}">`;
        }

        document.getElementById('nc-emp-search-input').value = '';
        document.getElementById('nc-emp-dropdown').classList.remove('open');
    }

    /* ── Boutons ── */
    _bindCancel() {
        document.getElementById('nc-btn-cancel')?.addEventListener('click', () => {
            this._reset();
            ModalManager.closeModal('new_contract');
        });
    }

    _bindClose() {
        document.querySelectorAll('#modal-new_contract .modal-close-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                this._reset();
                ModalManager.closeModal('new_contract');
            });
        });
    }

    _bindSubmit() {
        document.getElementById('nc-btn-submit')
            ?.addEventListener('click', () => this._submit());
    }

    /* ── Validation ── */
    _validate() {
        let ok = true;
        const form = document.getElementById('form-new-contrat');

        if (!document.getElementById('nc-id-employee').value) {
            NotificationManager.show('error','Employé','Veuillez sélectionner un employé');
            // Highlight search
            document.getElementById('nc-emp-search-input')?.classList.add('ct-input','invalid');
            return false;
        }

        const type = document.getElementById('nc-type-contrat').value;
        const debut = document.getElementById('nc-date-debut').value;
        const fin   = document.getElementById('nc-date-fin').value;

        if (!debut) {
            document.getElementById('nc-date-debut').classList.add('invalid');
            NotificationManager.show('error','Dates','La date de début est obligatoire');
            ok = false;
        }
        if (type === 'CDD' && !fin) {
            document.getElementById('nc-date-fin').classList.add('invalid');
            NotificationManager.show('error','Dates','La date de fin est obligatoire pour un CDD');
            ok = false;
        }
        if (debut && fin && fin <= debut) {
            document.getElementById('nc-date-fin').classList.add('invalid');
            NotificationManager.show('error','Dates','La date de fin doit être après la date de début');
            ok = false;
        }

        ['nc-poste','nc-affectation'].forEach(id => {
            const el = document.getElementById(id);
            if (el && !el.value.trim()) {
                el.classList.add('invalid');
                ok = false;
            }
        });
        if (!ok && !document.getElementById('nc-poste').value.trim()) {
            NotificationManager.show('error','Champs','Poste et Affectation sont obligatoires');
        }

        return ok;
    }

    async _submit() {
        if (this._submitting) return;
        if (!this._validate())  return;

        this._submitting = true;
        const btn  = document.getElementById('nc-btn-submit');
        const orig = btn?.innerHTML;
        if (btn) {
            btn.disabled  = true;
            btn.innerHTML = '<span class="ct-spinner"></span> Création…';
        }

        try {
            const fd = new FormData(document.getElementById('form-new-contrat'));
            const r    = await fetch('php/contrat/add_contrat.php', { method:'POST', body:fd });
            const text = await r.text();
            let d;
            try { d = JSON.parse(text); }
            catch(_) {
                console.error('Réponse non-JSON:', text.substring(0,300));
                NotificationManager.show('error','Serveur','Réponse inattendue du serveur');
                return;
            }

            if (d.success) {
                NotificationManager.show('success','Succès', d.message || 'Contrat créé avec succès');
                this._reset();
                ModalManager.closeModal('new_contract');
                window.showContrat?.loadContrats(1,'');
            } else {
                NotificationManager.show('error','Erreur', d.message);
            }
        } catch(e) {
            console.error(e);
            NotificationManager.show('error','Connexion','Impossible de joindre le serveur');
        } finally {
            this._submitting = false;
            if (btn) { btn.disabled = false; btn.innerHTML = orig; }
        }
    }

    /* ── Reset ── */
    _reset() {
        document.getElementById('form-new-contrat')?.reset();
        this._submitting       = false;
        this._selectedEmployee = null;

        document.getElementById('nc-id-employee').value = '';
        document.getElementById('nc-emp-name').textContent = '— Sélectionnez un employé —';
        document.getElementById('nc-emp-mat').textContent  = '';
        document.getElementById('nc-ref-display').textContent = '—';

        // Remettre avatar placeholder
        const avatarEl = document.getElementById('nc-emp-avatar-wrap');
        if (avatarEl && avatarEl.tagName === 'IMG') {
            const ph = document.createElement('div');
            ph.className = 'ct-emp-avatar-ph';
            ph.id = 'nc-emp-avatar-wrap';
            ph.innerHTML = '<i class="fas fa-user"></i>';
            avatarEl.replaceWith(ph);
        }

        // Défaut CDI
        this._setType('CDI');

        // Nettoyer validations
        document.getElementById('form-new-contrat')
            ?.querySelectorAll('.valid,.invalid')
            .forEach(el => el.classList.remove('valid','invalid'));

        document.getElementById('nc-emp-dropdown')?.classList.remove('open');
        document.getElementById('nc-emp-search-input').value = '';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.addContrat = new AddContrat();
});