/**
 * js/contrat/edit_contrat.js
 * Gère le modal #modal-edit-contrat
 * IDs HTML : ec-*
 */
class EditContrat {
    constructor() {
        this._submitting = false;
        this._init();
    }

    _init() {
        this._bindType();
        this._bindSubmit();
        this._bindCancel();
        // Les boutons fermer sont gérés par ModalManager via .modal-close-btn
        // Pas besoin de _bindClose() séparé
    }

    /* ── Ouvrir et remplir ── */
    async open(id) {
        ModalManager.openModal('edit-contrat');

        const btn = document.getElementById('ec-btn-submit');
        const orig = btn?.innerHTML;
        if (btn) {
            btn.disabled  = true;
            btn.innerHTML = '<span class="ct-spinner"></span> Chargement…';
        }

        try {
            const r    = await fetch(`php/contrat/show_contrat_details.php?id=${id}`);
            const text = await r.text();
            let d;
            try { d = JSON.parse(text); }
            catch(_) {
                NotificationManager.show('error','Erreur','Réponse inattendue du serveur');
                ModalManager.closeModal('edit-contrat');
                return;
            }

            if (d.success) {
                this._fill(d.data);
            } else {
                NotificationManager.show('error','Erreur', d.message);
                ModalManager.closeModal('edit-contrat');
            }
        } catch(e) {
            console.error(e);
            NotificationManager.show('error','Connexion','Impossible de charger le contrat');
            ModalManager.closeModal('edit-contrat');
        } finally {
            if (btn) {
                btn.disabled  = false;
                btn.innerHTML = orig || '<i class="fas fa-save"></i> Enregistrer';
            }
        }
    }

    _fill(c) {
        // ID caché
        document.getElementById('ec-id').value = c.id || '';

        // Header
        const sub = document.getElementById('ec-header-sub');
        if (sub) sub.textContent = `Réf : ${c.ref || '—'} · ${c.type_contrat}`;

        document.getElementById('ec-ref-display').textContent = c.ref || '—';

        // Employé bandeau
        document.getElementById('ec-emp-name').textContent =
            `${c.civilite || ''} ${c.nom} ${c.prenom}`.trim();
        document.getElementById('ec-emp-mat').textContent =
            `Matricule : ${c.matricule}`;

        // Avatar
        const avatarWrap = document.getElementById('ec-emp-avatar-wrap');
        if (avatarWrap) {
            const photo = c.photo || 'user.png';
            if (avatarWrap.tagName === 'DIV') {
                const img = document.createElement('img');
                img.className = 'ct-emp-avatar';
                img.id        = 'ec-emp-avatar-wrap';
                img.src       = `img/employee/${photo}`;
                img.onerror   = () => { img.src = 'img/employee/user.png'; };
                img.alt       = c.nom;
                avatarWrap.replaceWith(img);
            } else {
                avatarWrap.src     = `img/employee/${photo}`;
                avatarWrap.onerror = () => { avatarWrap.src = 'img/employee/user.png'; };
            }
        }

        // Type contrat (toggle visuel + hidden input)
        this._setType(c.type_contrat || 'CDI');

        // Champs formulaire
        const set = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val ?? '';
        };
        set('ec-ref',           c.ref);
        set('ec-periode-essai', c.periode_essai || '6 mois');
        set('ec-date-debut',    c.date_debut);
        set('ec-date-fin',      c.date_fin);
        set('ec-poste',         c.poste);
        set('ec-salaire',       c.salaire);
        set('ec-affectation',   c.affectation);
        set('ec-etat',          c.etat || 'actif');

        // Nettoyer les classes de validation précédentes
        document.getElementById('form-edit-contrat')
            ?.querySelectorAll('.valid,.invalid')
            .forEach(el => el.classList.remove('valid','invalid'));
    }

    /* ── Toggle CDI / CDD ── */
    _bindType() {
        ['CDI','CDD'].forEach(v => {
            document.getElementById(`ec-btn-${v.toLowerCase()}`)
                ?.addEventListener('click', () => this._setType(v));
        });
    }

    _setType(val) {
        const hidden = document.getElementById('ec-type-contrat');
        if (hidden) hidden.value = val;

        document.getElementById('ec-btn-cdi')?.classList.toggle('active', val === 'CDI');
        document.getElementById('ec-btn-cdd')?.classList.toggle('active', val === 'CDD');

        const row = document.getElementById('ec-date-fin-row');
        const inp = document.getElementById('ec-date-fin');
        const req = document.getElementById('ec-fin-req');

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

    /* ── Annuler ── */
    _bindCancel() {
        document.getElementById('ec-btn-cancel')
            ?.addEventListener('click', () => ModalManager.closeModal('edit-contrat'));
    }

    /* ── Soumettre ── */
    _bindSubmit() {
        document.getElementById('ec-btn-submit')
            ?.addEventListener('click', () => this._submit());
    }

    _validate() {
        let ok   = true;
        const type  = document.getElementById('ec-type-contrat')?.value;
        const debut = document.getElementById('ec-date-debut')?.value?.trim();
        const fin   = document.getElementById('ec-date-fin')?.value?.trim();
        const poste = document.getElementById('ec-poste')?.value?.trim();
        const aff   = document.getElementById('ec-affectation')?.value?.trim();

        if (!debut) {
            document.getElementById('ec-date-debut')?.classList.add('invalid');
            NotificationManager.show('error','Validation','La date de début est obligatoire');
            ok = false;
        }
        if (type === 'CDD' && !fin) {
            document.getElementById('ec-date-fin')?.classList.add('invalid');
            NotificationManager.show('error','Validation','La date de fin est obligatoire pour un CDD');
            ok = false;
        }
        if (debut && fin && fin <= debut) {
            document.getElementById('ec-date-fin')?.classList.add('invalid');
            NotificationManager.show('error','Validation','La date de fin doit être après la date de début');
            ok = false;
        }
        if (!poste) {
            document.getElementById('ec-poste')?.classList.add('invalid');
            NotificationManager.show('error','Validation','Le poste est obligatoire');
            ok = false;
        }
        if (!aff) {
            document.getElementById('ec-affectation')?.classList.add('invalid');
            NotificationManager.show('error','Validation','L\'affectation est obligatoire');
            ok = false;
        }

        return ok;
    }

    async _submit() {
        if (this._submitting) return;
        if (!this._validate())  return;

        this._submitting = true;
        const btn  = document.getElementById('ec-btn-submit');
        const orig = btn?.innerHTML;
        if (btn) {
            btn.disabled  = true;
            btn.innerHTML = '<span class="ct-spinner"></span> Enregistrement…';
        }

        try {
            const form = document.getElementById('form-edit-contrat');
            const fd   = new FormData(form);

            // ec-etat est hors du form → l'ajouter manuellement
            const etatVal = document.getElementById('ec-etat')?.value || 'actif';
            fd.set('etat', etatVal);

            // Debug : vérifier les valeurs envoyées
            console.log('[EditContrat] POST data:');
            for (const [k,v] of fd.entries()) console.log(' ', k, '=', v);

            const r    = await fetch('php/contrat/edit_contrat.php', {
                method : 'POST',
                body   : fd
            });
            const text = await r.text();
            let d;
            try { d = JSON.parse(text); }
            catch(_) {
                console.error('[EditContrat] Réponse non-JSON:', text.substring(0, 400));
                NotificationManager.show('error','Serveur',
                    'Réponse inattendue — vérifiez la console');
                return;
            }

            if (d.success) {
                NotificationManager.show('success','Succès',
                    d.message || 'Contrat modifié avec succès');
                ModalManager.closeModal('edit-contrat');
                window.showContrat?.loadContrats(
                    window.showContrat.currentPage,
                    window.showContrat.searchTerm
                );
            } else {
                NotificationManager.show('error','Erreur', d.message);
            }

        } catch(e) {
            console.error('[EditContrat] fetch error:', e);
            NotificationManager.show('error','Connexion',
                'Impossible de joindre le serveur — XAMPP est-il démarré ?');
        } finally {
            this._submitting = false;
            if (btn) { btn.disabled = false; btn.innerHTML = orig; }
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.editContrat = new EditContrat();
});