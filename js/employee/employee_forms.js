/**
 * employee_forms.js
 * Gère les modals Add et Edit de l'employé
 * + Photo upload avec preview et validation
 */

/* ═══════════════════════════════════════════
   CLASSE PARTAGÉE : photo upload
═══════════════════════════════════════════ */
class EmpPhotoUpload {
    constructor(frameId, inputId, previewId) {
        this.frame   = document.getElementById(frameId);
        this.input   = document.getElementById(inputId);
        this.preview = document.getElementById(previewId);
        this.file    = null;
        if (this.frame && this.input) this._bind();
    }

    _bind() {
        this.frame.addEventListener('click', () => this.input.click());

        this.input.addEventListener('change', e => {
            if (e.target.files?.[0]) this._load(e.target.files[0]);
        });

        // Drag & drop
        this.frame.addEventListener('dragover',  e => { e.preventDefault(); this.frame.style.borderColor = '#3b82f6'; });
        this.frame.addEventListener('dragleave', () => { this.frame.style.borderColor = ''; });
        this.frame.addEventListener('drop',      e => {
            e.preventDefault();
            this.frame.style.borderColor = '';
            const f = e.dataTransfer.files?.[0];
            if (f) this._load(f);
        });
    }

    _load(file) {
        const allowed = ['image/jpeg','image/png','image/gif'];
        if (!allowed.includes(file.type)) {
            NotificationManager.error('Format non autorisé. Utilisez JPG, PNG ou GIF.', 'Photo');
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            NotificationManager.error('Photo trop lourde (max 2 Mo).', 'Photo');
            return;
        }
        this.file = file;
        const reader = new FileReader();
        reader.onload = e => {
            this.preview.src = e.target.result;
            this.frame.classList.add('has-photo');
        };
        reader.readAsDataURL(file);
    }

    reset(defaultSrc = 'img/employee/user.png') {
        this.file = null;
        this.preview.src = defaultSrc;
        this.input.value = '';
        this.frame.classList.remove('has-photo');
    }

    setPhoto(src) {
        this.preview.src = src;
        this.frame.classList.add('has-photo');
    }
}

/* ═══════════════════════════════════════════
   MODAL AJOUT EMPLOYÉ
═══════════════════════════════════════════ */
class AddEmployee {
    constructor() {
        this.photo        = new EmpPhotoUpload('add-photo-frame', 'add-photo-input', 'add-photo-preview');
        this.nextMatricule= 0;
        this._init();
    }

    _init() {
        this._bindClose();
        this._bindDateToggle();
        this._bindSitFamiliale();
        this._bindSubmit();

        // Rechargement à chaque ouverture
        document.addEventListener('modalOpened', e => {
            if (e.detail === 'add_employee') {
                this.reset();
                this.loadNextMatricule();
            }
        });
    }

    // ── Matricule ──
    async loadNextMatricule() {
        try {
            const r = await fetch('php/employee/get_next_matricule.php');
            const d = await r.json();
            if (d.success) {
                this.nextMatricule = d.next_matricule;
                const el = document.getElementById('auto-matricule');
                if (el) el.textContent = String(d.next_matricule).padStart(4,'0');
            }
        } catch(e) { console.error('Matricule:', e); }
    }

    // ── Toggle date / présumée ──
    _bindDateToggle() {
        const btnDate    = document.getElementById('add-toggle-date');
        const btnPresume = document.getElementById('add-toggle-presume');
        const fDate      = document.getElementById('add-date-naissance');
        const fPresume   = document.getElementById('add-presume');
        const pField     = document.getElementById('add-presume-field');

        btnDate?.addEventListener('click', () => {
            btnDate.classList.add('active'); btnPresume.classList.remove('active');
            fDate.disabled    = false; fDate.required = false;
            fPresume.disabled = true;  fPresume.value  = '';
            pField.style.display = 'none';
        });

        btnPresume?.addEventListener('click', () => {
            btnPresume.classList.add('active'); btnDate.classList.remove('active');
            fDate.value     = ''; fDate.disabled = true;
            fPresume.disabled = false;
            pField.style.display = '';
        });
    }

    // ── Situation familiale → enfants ──
    _bindSitFamiliale() {
        document.getElementById('add-situation-familiale')?.addEventListener('change', e => {
            const enfField = document.getElementById('add-enfants-field');
            const enfInput = enfField?.querySelector('input');
            const hideFor  = ['célibataire'];
            if (hideFor.includes(e.target.value)) {
                if (enfInput) { enfInput.value = 0; }
            }
        });
    }

    // ── Fermeture ──
    _bindClose() {
        document.querySelectorAll('.modal-close-btn-add').forEach(btn => {
            btn.addEventListener('click', () => {
                ModalManager.closeModal('add_employee');
                this.reset();
            });
        });
    }

    // ── Soumission ──
    _bindSubmit() {
        document.getElementById('btn-add-employee-submit')?.addEventListener('click', () => this._submit());
    }

    _validate() {
        const form = document.getElementById('form-add-employee');
        let ok = true;

        form.querySelectorAll('[required]').forEach(el => {
            el.classList.remove('invalid');
            if (!el.value.trim()) { el.classList.add('invalid'); ok = false; }
        });

        const dn = form.querySelector('[name="date_naissance"]');
        const pr = form.querySelector('[name="presume"]');
        if (!dn.value && !pr.value) {
            NotificationManager.error('La date de naissance ou l\'année présumée est requise.', 'Validation');
            ok = false;
        }

        const tel = form.querySelector('[name="telephone"]');
        if (tel && !/^0[0-9]{9}$/.test(tel.value.trim())) {
            tel.classList.add('invalid');
            NotificationManager.error('Le numéro de téléphone doit commencer par 0 et avoir 10 chiffres.', 'Validation');
            ok = false;
        }
        return ok;
    }

    async _submit() {
        if (!this._validate()) return;

        const btn = document.getElementById('btn-add-employee-submit');
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="emp-spinner"></span> Recrutement en cours…';

        try {
            const fd = new FormData(document.getElementById('form-add-employee'));
            if (this.photo.file) fd.append('photo', this.photo.file);

            const r = await fetch('php/employee/add_employee.php', { method:'POST', body:fd });
            const d = await r.json();

            if (d.success) {
                NotificationManager.success(`Employé recruté · Matricule : ${d.matricule}`, 'Succès');
                ModalManager.closeModal('add_employee');
                this.reset();
                window.showEmployee?.loadEmployees(1, '');
            } else {
                NotificationManager.error(d.message, 'Erreur');
            }
        } catch(e) {
            NotificationManager.error('Erreur de connexion.', 'Erreur');
        } finally {
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    }

    reset() {
        document.getElementById('form-add-employee')?.reset();
        this.photo.reset();
        // remettre toggle date
        document.getElementById('add-toggle-date')?.click();
        document.getElementById('auto-matricule').textContent = '—';
        document.querySelectorAll('#form-add-employee .emp-input.invalid,#form-add-employee .emp-select.invalid')
            .forEach(el => el.classList.remove('invalid'));
    }
}

/* ═══════════════════════════════════════════
   MODAL MODIFICATION EMPLOYÉ
═══════════════════════════════════════════ */
class EditEmployee {
    constructor() {
        this.photo     = new EmpPhotoUpload('edit-photo-frame', 'edit-photo-input', 'edit-photo-preview');
        this.currentId = null;
        this._init();
    }

    _init() {
        this._bindClose();
        this._bindDateToggle();
        this._bindSubmit();
    }

    // ── Charger + ouvrir ──
    async open(employeeId) {
        this.currentId = employeeId;
        ModalManager.openModal('edit-employee');

        try {
            const r = await fetch(`php/employee/get_employee_details.php?id=${employeeId}`);
            const d = await r.json();
            if (!d.success) throw new Error(d.message);
            this._fill(d.employee);
        } catch(e) {
            NotificationManager.error(e.message || 'Erreur de chargement', 'Modifier');
            ModalManager.closeModal('edit-employee');
        }
    }

    // ── Remplir le formulaire ──
    _fill(emp) {
        const set = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val ?? '';
        };

        document.getElementById('edit-emp-id').value        = emp.id;
        document.getElementById('edit-emp-subtitle').textContent =
            `${emp.civilite} ${emp.nom} ${emp.prenom} — Matricule ${emp.matricule}`;
        document.getElementById('edit-matricule-val').textContent =
            String(emp.matricule).padStart(4,'0');

        set('edit-civilite', emp.civilite);
        set('edit-nom',      emp.nom);
        set('edit-prenom',   emp.prenom);
        set('edit-lieu-naissance',  emp.lieu_naissance);
        set('edit-no-acte',         emp.no_acte_naissance);
        set('edit-situation-familiale', emp.situation_familiale);
        set('edit-nombre-enfants',  emp.nombre_enfants);
        set('edit-prenom-pere',     emp.prenom_pere);
        set('edit-nom-mere',        emp.nom_prenom_mere);
        set('edit-adresse',         emp.adresse);
        set('edit-wilaya',          emp.wilaya_residence);
        set('edit-telephone',       emp.telephone);
        set('edit-cnas',            emp.no_assurance_cnas);
        set('edit-compte',          emp.compte_a_paye);
        set('edit-etat',            emp.etat);

        // Date naissance vs présumée
        if (emp.date_naissance) {
            set('edit-date-naissance', emp.date_naissance);
            document.getElementById('edit-toggle-date')?.click();
        } else if (emp.presume) {
            set('edit-presume', emp.presume);
            document.getElementById('edit-toggle-presume')?.click();
        }

        // Photo
        const src = `img/employee/${emp.photo || 'user.png'}`;
        this.photo.setPhoto(src);
        this.photo.file = null; // reset le fichier en attente
        document.getElementById('edit-photo-input').value = '';
    }

    _bindDateToggle() {
        const btnDate    = document.getElementById('edit-toggle-date');
        const btnPresume = document.getElementById('edit-toggle-presume');
        const fDate      = document.getElementById('edit-date-naissance');
        const fPresume   = document.getElementById('edit-presume');
        const pField     = document.getElementById('edit-presume-field');

        btnDate?.addEventListener('click', () => {
            btnDate.classList.add('active'); btnPresume.classList.remove('active');
            fDate.disabled    = false;
            fPresume.disabled = true; fPresume.value = '';
            pField.style.display = 'none';
        });

        btnPresume?.addEventListener('click', () => {
            btnPresume.classList.add('active'); btnDate.classList.remove('active');
            fDate.value = ''; fDate.disabled = true;
            fPresume.disabled = false;
            pField.style.display = '';
        });
    }

    _bindClose() {
        document.querySelectorAll('.modal-close-btn-edit').forEach(btn => {
            btn.addEventListener('click', () => ModalManager.closeModal('edit-employee'));
        });
    }

    _bindSubmit() {
        document.getElementById('btn-edit-employee-submit')?.addEventListener('click', () => this._submit());
    }

    _validate() {
        const form = document.getElementById('form-edit-employee');
        let ok = true;

        form.querySelectorAll('[required]').forEach(el => {
            el.classList.remove('invalid');
            if (!el.value.trim()) { el.classList.add('invalid'); ok = false; }
        });

        const tel = form.querySelector('[name="telephone"]');
        if (tel && !/^0[0-9]{9}$/.test(tel.value.trim())) {
            tel.classList.add('invalid');
            NotificationManager.error('Téléphone invalide (0XXXXXXXXX).', 'Validation');
            ok = false;
        }
        return ok;
    }

    async _submit() {
        if (!this._validate()) return;

        const btn = document.getElementById('btn-edit-employee-submit');
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="emp-spinner"></span> Enregistrement…';

        try {
            const fd = new FormData(document.getElementById('form-edit-employee'));
            if (this.photo.file) fd.append('photo', this.photo.file);

            const r = await fetch('php/employee/edit_employee.php', { method:'POST', body:fd });
            const d = await r.json();

            if (d.success) {
                NotificationManager.success('Employé modifié avec succès.', 'Succès');
                ModalManager.closeModal('edit-employee');
                window.showEmployee?.loadEmployees(
                    window.showEmployee.currentPage,
                    window.showEmployee.searchTerm
                );
            } else {
                NotificationManager.error(d.message, 'Erreur');
            }
        } catch(e) {
            NotificationManager.error('Erreur de connexion.', 'Erreur');
        } finally {
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    }
}

/* ═══════════════════════════════════════════
   INIT
═══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    window.addEmployee  = new AddEmployee();
    window.editEmployee = new EditEmployee();
});