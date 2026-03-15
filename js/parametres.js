/**
 * parametres.js — Module Paramètres (Admin uniquement)
 * Gestion des utilisateurs + permissions par module avec toggles ON/OFF
 */

class Parametres {
    constructor() {
        this.users        = [];
        this.currentUser  = null;  // user sélectionné dans l'onglet permissions
        this.deleteTarget = null;  // user à supprimer
        this.isEditing    = false; // mode édition vs ajout

        this.modules = [
            { key: 'employee',            label: 'Employés',             icon: 'fas fa-users' },
            { key: 'contrat',             label: 'Contrats',             icon: 'fas fa-file-contract' },
            { key: 'cnas',                label: 'CNAS',                 icon: 'fas fa-heartbeat' },
            { key: 'certificat',          label: 'Certificats',          icon: 'fas fa-certificate' },
            { key: 'fin_relation',        label: 'Fin de Relation',      icon: 'fas fa-handshake' },
            { key: 'mise_en_demeure',     label: 'Mise en Demeure',      icon: 'fas fa-exclamation-triangle' },
            { key: 'titre_conge',         label: 'Titre de Congé',       icon: 'fas fa-umbrella-beach' },
            { key: 'reprise_travail',     label: 'Reprise de Travail',   icon: 'fas fa-briefcase' },
            { key: 'absences',            label: 'Absences',             icon: 'fas fa-clock' },
            { key: 'pointages',           label: 'Pointages',            icon: 'fas fa-fingerprint' },
            { key: 'situation_effectifs', label: 'Situation Effectifs',  icon: 'fas fa-chart-bar' },
            { key: 'ordre_mission',       label: 'Ordre de Mission',     icon: 'fas fa-plane' },
            { key: 'decharge',            label: 'Décharge',             icon: 'fas fa-file-signature' },
            { key: 'global',              label: 'Global',               icon: 'fas fa-globe' },
        ];

        this.actions = ['view', 'add', 'edit', 'delete', 'print'];

        this.init();
    }

    init() {
        this.bindTabs();
        this.bindUserForm();
        this.bindDeleteModal();
        this.bindResetPwd();
        this.bindSearch();
        this.bindPermissionSave();
        this.bindPhotoUpload();
    }

    // ─────────────────────────────────────────
    // TABS
    // ─────────────────────────────────────────
    bindTabs() {
        document.querySelectorAll('.param-tab').forEach(btn => {
            btn.addEventListener('click', () => {
                const tab = btn.dataset.tab;

                // Update tab styles
                document.querySelectorAll('.param-tab').forEach(t => {
                    t.classList.remove('border-slate-700', 'text-slate-700');
                    t.classList.add('border-transparent', 'text-gray-500');
                });
                btn.classList.add('border-slate-700', 'text-slate-700');
                btn.classList.remove('border-transparent', 'text-gray-500');

                // Show/hide content
                document.querySelectorAll('.param-tab-content').forEach(c => c.classList.add('hidden'));
                document.getElementById(`tab-${tab}`).classList.remove('hidden');

                // Load data for tab
                if (tab === 'users') this.loadUsers();
                if (tab === 'permissions') this.loadPermissionsTab();
            });
        });
    }

    // ─────────────────────────────────────────
    // LOAD USERS (tableau)
    // ─────────────────────────────────────────
    async loadUsers(search = '') {
        const tbody = document.getElementById('users-param-tbody');
        tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-slate-600 mx-auto mb-3"></div>
            <p class="text-gray-500 text-sm">Chargement...</p></td></tr>`;

        try {
            const params = new URLSearchParams({ search });
            const res    = await fetch(`php/parametres/show_users.php?${params}`);
            const data   = await res.json();

            if (!data.success) throw new Error(data.message);
            this.users = data.users;
            this.renderUsersTable(data.users);

        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-red-500 text-sm">
                <i class="fas fa-exclamation-circle mr-2"></i>${e.message}</td></tr>`;
        }
    }

    renderUsersTable(users) {
        const tbody = document.getElementById('users-param-tbody');

        if (!users.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">
                <i class="fas fa-users-slash text-3xl mb-3 block opacity-30"></i>
                <p class="text-sm">Aucun utilisateur trouvé</p></td></tr>`;
            return;
        }

        tbody.innerHTML = users.map(u => `
            <tr class="hover:bg-slate-50 transition-colors duration-150 ${u.roles === 'admin' ? 'bg-amber-50' : ''}">
                <!-- Photo -->
                <td class="px-4 py-3">
                    <img src="img/user/${u.photo || 'user.png'}" alt="${u.nom_prenom}"
                         onerror="this.src='img/user/user.png'"
                         class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 shadow-sm">
                </td>
                <!-- Nom -->
                <td class="px-4 py-3">
                    <div class="font-semibold text-gray-800 text-sm">${u.nom_prenom}</div>
                    <div class="text-xs text-gray-400">${u.date_creation ? new Date(u.date_creation).toLocaleDateString('fr-FR') : ''}</div>
                </td>
                <!-- Email -->
                <td class="px-4 py-3 text-sm text-gray-600">${u.email}</td>
                <!-- Rôle -->
                <td class="px-4 py-3">
                    ${u.roles === 'admin'
                        ? `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800"><i class="fas fa-crown mr-1"></i>Admin</span>`
                        : `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700"><i class="fas fa-user mr-1"></i>Utilisateur</span>`}
                </td>
                <!-- Statut -->
                <td class="px-4 py-3">
                    ${u.statut === 'actif'
                        ? `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700"><i class="fas fa-circle text-[8px] mr-1"></i>Actif</span>`
                        : `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500"><i class="fas fa-circle text-[8px] mr-1"></i>Inactif</span>`}
                </td>
                <!-- Actions -->
                <td class="px-4 py-3">
                    <div class="flex items-center space-x-1">
                        <button onclick="parametres.openEditUser(${u.id})" title="Modifier"
                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <i class="fas fa-pen text-xs"></i>
                        </button>
                        <button onclick="parametres.openResetPwd(${u.id}, '${u.nom_prenom.replace(/'/g, "\\'")}')" title="Réinitialiser mot de passe"
                                class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors">
                            <i class="fas fa-key text-xs"></i>
                        </button>
                        <button onclick="parametres.toggleStatut(${u.id}, '${u.statut}')" title="${u.statut === 'actif' ? 'Désactiver' : 'Activer'}"
                                class="p-2 ${u.statut === 'actif' ? 'text-orange-500 hover:bg-orange-50' : 'text-emerald-600 hover:bg-emerald-50'} rounded-lg transition-colors">
                            <i class="fas ${u.statut === 'actif' ? 'fa-user-slash' : 'fa-user-check'} text-xs"></i>
                        </button>
                        ${u.roles !== 'admin' ? `
                        <button onclick="parametres.openDeleteUser(${u.id}, '${u.nom_prenom.replace(/'/g, "\\'")}')" title="Supprimer"
                                class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                            <i class="fas fa-trash text-xs"></i>
                        </button>` : ''}
                    </div>
                </td>
            </tr>
        `).join('');
    }

    // ─────────────────────────────────────────
    // FORMULAIRE UTILISATEUR (Ajout / Édition)
    // ─────────────────────────────────────────
    bindUserForm() {
        document.getElementById('btn-add-user-param').addEventListener('click', () => this.openAddUser());
        document.getElementById('close-user-form').addEventListener('click',   () => this.closeUserForm());
        document.getElementById('cancel-user-form').addEventListener('click',  () => this.closeUserForm());
        document.getElementById('submit-user-form').addEventListener('click',  () => this.submitUserForm());

        // Toggle password visibility
        document.getElementById('toggle-form-pwd').addEventListener('click', () => {
            const input = document.getElementById('user-form-password');
            const icon  = document.querySelector('#toggle-form-pwd i');
            input.type  = input.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }

    openAddUser() {
        this.isEditing = false;
        document.getElementById('user-form-title').textContent     = 'Nouvel utilisateur';
        document.getElementById('user-form-id').value              = '';
        document.getElementById('user-form-nom').value             = '';
        document.getElementById('user-form-email').value           = '';
        document.getElementById('user-form-password').value        = '';
        document.getElementById('user-form-role').value            = 'user';
        document.getElementById('user-form-statut').value          = 'actif';
        document.getElementById('user-form-photo-preview').src     = 'img/user/user.png';
        document.getElementById('user-form-photo-input').value     = '';
        document.getElementById('pwd-required-star').classList.remove('hidden');
        document.getElementById('pwd-optional-hint').classList.add('hidden');
        this.openSubModal('modal-user-form');
    }

    openEditUser(id) {
        const user = this.users.find(u => u.id == id);
        if (!user) return;

        this.isEditing = true;
        document.getElementById('user-form-title').textContent     = 'Modifier l\'utilisateur';
        document.getElementById('user-form-id').value              = user.id;
        document.getElementById('user-form-nom').value             = user.nom_prenom;
        document.getElementById('user-form-email').value           = user.email;
        document.getElementById('user-form-password').value        = '';
        document.getElementById('user-form-role').value            = user.roles;
        document.getElementById('user-form-statut').value          = user.statut;
        document.getElementById('user-form-photo-preview').src     = `img/user/${user.photo || 'user.png'}`;
        document.getElementById('user-form-photo-input').value     = '';
        document.getElementById('pwd-required-star').classList.add('hidden');
        document.getElementById('pwd-optional-hint').classList.remove('hidden');
        this.openSubModal('modal-user-form');
    }

    closeUserForm() {
        this.closeSubModal('modal-user-form');
    }

    async submitUserForm() {
        const id       = document.getElementById('user-form-id').value;
        const nom      = document.getElementById('user-form-nom').value.trim();
        const email    = document.getElementById('user-form-email').value.trim();
        const password = document.getElementById('user-form-password').value;
        const role     = document.getElementById('user-form-role').value;
        const statut   = document.getElementById('user-form-statut').value;
        const photoFile= document.getElementById('user-form-photo-input').files[0];

        if (!nom || !email) {
            return NotificationManager.show('error', 'Nom et email sont obligatoires');
        }
        if (!this.isEditing && !password) {
            return NotificationManager.show('error', 'Le mot de passe est obligatoire pour un nouvel utilisateur');
        }
        if (password && password.length < 6) {
            return NotificationManager.show('error', 'Le mot de passe doit contenir au moins 6 caractères');
        }

        const formData = new FormData();
        formData.append('action',  this.isEditing ? 'edit' : 'add');
        if (id) formData.append('id', id);
        formData.append('nom_prenom', nom);
        formData.append('email',      email);
        formData.append('roles',      role);
        formData.append('statut',     statut);
        if (password) formData.append('password', password);
        if (photoFile) formData.append('photo', photoFile);

        try {
            const res  = await fetch('php/parametres/manage_user.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (!data.success) throw new Error(data.message);
            NotificationManager.show('success', data.message);
            this.closeUserForm();
            this.loadUsers();
            this.loadPermissionsTab(); // Rafraîchir le select aussi

        } catch (e) {
            NotificationManager.show('error', e.message);
        }
    }

    bindPhotoUpload() {
        const zone    = document.getElementById('photo-upload-zone');
        const input   = document.getElementById('user-form-photo-input');
        const preview = document.getElementById('user-form-photo-preview');

        zone.addEventListener('click', () => input.click());
        input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => { preview.src = e.target.result; };
            reader.readAsDataURL(file);
        });
    }

    // ─────────────────────────────────────────
    // TOGGLE STATUT
    // ─────────────────────────────────────────
    async toggleStatut(id, currentStatut) {
        const newStatut = currentStatut === 'actif' ? 'inactif' : 'actif';
        const label     = newStatut === 'actif' ? 'activé' : 'désactivé';

        try {
            const formData = new FormData();
            formData.append('action', 'toggle_statut');
            formData.append('id',     id);
            formData.append('statut', newStatut);

            const res  = await fetch('php/parametres/manage_user.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (!data.success) throw new Error(data.message);
            NotificationManager.show('success', `Utilisateur ${label} avec succès`);
            this.loadUsers();

        } catch (e) {
            NotificationManager.show('error', e.message);
        }
    }

    // ─────────────────────────────────────────
    // SUPPRESSION
    // ─────────────────────────────────────────
    bindDeleteModal() {
        document.getElementById('cancel-delete-user-param').addEventListener('click',  () => this.closeSubModal('modal-delete-user-param'));
        document.getElementById('confirm-delete-user-param').addEventListener('click', () => this.confirmDeleteUser());
    }

    openDeleteUser(id, nom) {
        this.deleteTarget = id;
        document.getElementById('delete-user-param-info').textContent = `Supprimer « ${nom} » ? Cette action est irréversible.`;
        this.openSubModal('modal-delete-user-param');
    }

    async confirmDeleteUser() {
        if (!this.deleteTarget) return;

        try {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id',     this.deleteTarget);

            const res  = await fetch('php/parametres/manage_user.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (!data.success) throw new Error(data.message);
            NotificationManager.show('success', 'Utilisateur supprimé');
            this.closeSubModal('modal-delete-user-param');
            this.deleteTarget = null;
            this.loadUsers();
            this.loadPermissionsTab();

        } catch (e) {
            NotificationManager.show('error', e.message);
        }
    }

    // ─────────────────────────────────────────
    // RÉINITIALISATION MOT DE PASSE
    // ─────────────────────────────────────────
    bindResetPwd() {
        document.getElementById('close-reset-pwd').addEventListener('click',   () => this.closeSubModal('modal-reset-pwd'));
        document.getElementById('cancel-reset-pwd').addEventListener('click',  () => this.closeSubModal('modal-reset-pwd'));
        document.getElementById('confirm-reset-pwd').addEventListener('click', () => this.submitResetPwd());

        document.getElementById('toggle-reset-pwd').addEventListener('click', () => {
            const input = document.getElementById('reset-pwd-input');
            const icon  = document.querySelector('#toggle-reset-pwd i');
            input.type  = input.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }

    openResetPwd(id, nom) {
        document.getElementById('reset-pwd-user-id').value  = id;
        document.getElementById('reset-pwd-user-name').textContent = `Utilisateur : ${nom}`;
        document.getElementById('reset-pwd-input').value    = '';
        this.openSubModal('modal-reset-pwd');
    }

    async submitResetPwd() {
        const id  = document.getElementById('reset-pwd-user-id').value;
        const pwd = document.getElementById('reset-pwd-input').value;

        if (!pwd || pwd.length < 6) {
            return NotificationManager.show('error', 'Le mot de passe doit contenir au moins 6 caractères');
        }

        try {
            const formData = new FormData();
            formData.append('action',   'reset_password');
            formData.append('id',       id);
            formData.append('password', pwd);

            const res  = await fetch('php/parametres/manage_user.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (!data.success) throw new Error(data.message);
            NotificationManager.show('success', 'Mot de passe réinitialisé avec succès');
            this.closeSubModal('modal-reset-pwd');

        } catch (e) {
            NotificationManager.show('error', e.message);
        }
    }

    // ─────────────────────────────────────────
    // PERMISSIONS
    // ─────────────────────────────────────────
    async loadPermissionsTab() {
        const select = document.getElementById('perm-user-select');
        const currentVal = select.value;

        try {
            const res  = await fetch('php/parametres/show_users.php?roles_filter=user');
            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            select.innerHTML = '<option value="">-- Choisir un utilisateur --</option>';
            data.users
                .filter(u => u.roles !== 'admin')
                .forEach(u => {
                    const opt = document.createElement('option');
                    opt.value       = u.id;
                    opt.textContent = `${u.nom_prenom} (${u.email})`;
                    if (u.id == currentVal) opt.selected = true;
                    select.appendChild(opt);
                });

            // Si un user était sélectionné, recharger ses permissions
            if (currentVal && select.value) this.loadUserPermissions(currentVal);

        } catch (e) {
            NotificationManager.show('error', e.message);
        }

        select.addEventListener('change', () => {
            const uid = select.value;
            document.getElementById('btn-save-permissions').disabled = !uid;
            if (uid) {
                this.loadUserPermissions(uid);
            } else {
                document.getElementById('permissions-grid').classList.add('hidden');
                document.getElementById('permissions-placeholder').classList.remove('hidden');
            }
        });
    }

    async loadUserPermissions(userId) {
        this.currentUser = userId;
        document.getElementById('permissions-placeholder').classList.add('hidden');
        document.getElementById('permissions-grid').classList.remove('hidden');

        const rows = document.getElementById('permissions-rows');
        rows.innerHTML = `<div class="px-5 py-6 text-center text-sm text-gray-400">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-slate-600 mx-auto mb-2"></div>Chargement...</div>`;

        try {
            const res  = await fetch(`php/parametres/get_permissions.php?user_id=${userId}`);
            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            this.renderPermissionsGrid(data.permissions);

        } catch (e) {
            rows.innerHTML = `<div class="px-5 py-4 text-sm text-red-500 text-center">${e.message}</div>`;
        }
    }

    renderPermissionsGrid(permissions) {
        const rows = document.getElementById('permissions-rows');

        rows.innerHTML = this.modules.map((mod, idx) => {
            const perm = permissions[mod.key] || { view: false, add: false, edit: false, delete: false, print: false };
            const bg   = idx % 2 === 0 ? 'bg-white' : 'bg-slate-50/50';

            const toggles = this.actions.map(action => `
                <div class="flex justify-center items-center">
                    <button type="button"
                            class="perm-toggle w-11 h-6 rounded-full relative transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-1
                                   ${perm[action] ? 'bg-emerald-500' : 'bg-gray-300'}"
                            data-module="${mod.key}" data-action="${action}" data-state="${perm[action] ? '1' : '0'}">
                        <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-300
                                     ${perm[action] ? 'translate-x-5' : 'translate-x-0'}"></span>
                    </button>
                </div>
            `).join('');

            return `
                <div class="grid grid-cols-[1fr_repeat(5,80px)] gap-0 px-5 py-3 ${bg} items-center hover:bg-blue-50/30 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                            <i class="${mod.icon} text-slate-500 text-xs"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700">${mod.label}</span>
                    </div>
                    ${toggles}
                </div>
            `;
        }).join('');

        // Bind toggle clicks
        rows.querySelectorAll('.perm-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const state   = btn.dataset.state === '1';
                const newState = !state;
                btn.dataset.state = newState ? '1' : '0';
                btn.classList.toggle('bg-emerald-500', newState);
                btn.classList.toggle('bg-gray-300',    !newState);
                const thumb = btn.querySelector('span');
                thumb.classList.toggle('translate-x-5', newState);
                thumb.classList.toggle('translate-x-0', !newState);
            });
        });
    }

    bindPermissionSave() {
        document.getElementById('btn-save-permissions').addEventListener('click', () => this.savePermissions());
    }

    async savePermissions() {
        if (!this.currentUser) return;

        const toggles     = document.querySelectorAll('.perm-toggle');
        const permissions = {};

        toggles.forEach(btn => {
            const mod    = btn.dataset.module;
            const action = btn.dataset.action;
            if (!permissions[mod]) permissions[mod] = {};
            permissions[mod][action] = btn.dataset.state === '1' ? 1 : 0;
        });

        try {
            const formData = new FormData();
            formData.append('user_id',     this.currentUser);
            formData.append('permissions', JSON.stringify(permissions));

            const res  = await fetch('php/parametres/save_permissions.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (!data.success) throw new Error(data.message);
            NotificationManager.show('success', 'Permissions enregistrées avec succès');

        } catch (e) {
            NotificationManager.show('error', e.message);
        }
    }

    // ─────────────────────────────────────────
    // RECHERCHE
    // ─────────────────────────────────────────
    bindSearch() {
        let timer;
        document.getElementById('search-user-param').addEventListener('input', e => {
            clearTimeout(timer);
            timer = setTimeout(() => this.loadUsers(e.target.value.trim()), 350);
        });
    }

    // ─────────────────────────────────────────
    // HELPERS : sous-modals
    // ─────────────────────────────────────────
    openSubModal(id) {
        const modal   = document.getElementById(id);
        const overlay = document.getElementById('modal-overlay');
        if (!modal) return;
        modal.classList.remove('hidden');
        overlay.classList.remove('hidden');
        setTimeout(() => {
            const inner = modal.querySelector('div');
            inner.classList.remove('scale-95', 'opacity-0');
            inner.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    closeSubModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        const inner = modal.querySelector('div');
        inner.classList.remove('scale-100', 'opacity-100');
        inner.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }
}

// ─────────────────────────────────────────
// INIT — déclenché quand le modal s'ouvre
// ─────────────────────────────────────────
let parametres;

document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('modal-parametres');
    if (!modalEl) return; // Non-admin : modal absent → on stoppe

    parametres = new Parametres();

    // Charger les users au premier ouverture
    const observer = new MutationObserver(() => {
        if (!modalEl.classList.contains('hidden')) {
            parametres.loadUsers();
            observer.disconnect();
        }
    });
    observer.observe(modalEl, { attributes: true, attributeFilter: ['class'] });
});