<?php
// Sécurité : ce modal ne s'affiche que pour l'admin
if (!function_exists('isAdmin') || !isAdmin()) return;
?>

<!-- ============================================================ -->
<!-- MODAL PARAMETRES — Réservé administrateur                    -->
<!-- ============================================================ -->
<div id="modal-parametres" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0">

        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-slate-700 to-slate-900 text-white">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-cog text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold">Paramètres</h2>
                    <p class="text-sm text-slate-300">Gestion des utilisateurs et permissions</p>
                </div>
            </div>
            <button class="modal-close-btn p-2 rounded-full hover:bg-white hover:bg-opacity-20 transition-colors duration-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Tabs navigation -->
        <div class="flex border-b border-gray-200 bg-gray-50">
            <button class="param-tab active-tab px-6 py-4 text-sm font-semibold flex items-center space-x-2 border-b-2 border-slate-700 text-slate-700" data-tab="users">
                <i class="fas fa-users"></i>
                <span>Utilisateurs</span>
            </button>
            <button class="param-tab px-6 py-4 text-sm font-semibold flex items-center space-x-2 border-b-2 border-transparent text-gray-500 hover:text-slate-700 transition-colors" data-tab="permissions">
                <i class="fas fa-shield-alt"></i>
                <span>Permissions</span>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="overflow-y-auto max-h-[calc(90vh-160px)]">

            <!-- ─── TAB : UTILISATEURS ─── -->
            <div id="tab-users" class="param-tab-content p-6">

                <!-- Barre actions -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div class="relative flex-1 max-w-sm">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="search-user-param" placeholder="Rechercher un utilisateur..."
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 transition-all">
                    </div>
                    <button id="btn-add-user-param"
                            class="px-5 py-2.5 bg-slate-700 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold flex items-center space-x-2 transition-colors shadow-sm">
                        <i class="fas fa-user-plus"></i>
                        <span>Nouvel utilisateur</span>
                    </button>
                </div>

                <!-- Table utilisateurs -->
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Photo</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Nom & Prénom</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Rôle</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Statut</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-param-tbody" class="divide-y divide-gray-50">
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-slate-600 mx-auto mb-3"></div>
                                    <p class="text-gray-500 text-sm">Chargement...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ─── TAB : PERMISSIONS ─── -->
            <div id="tab-permissions" class="param-tab-content p-6 hidden">

                <!-- Sélecteur utilisateur -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-6">
                    <div class="flex-1 max-w-sm">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Sélectionner un utilisateur</label>
                        <select id="perm-user-select"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 transition-all bg-white">
                            <option value="">-- Choisir un utilisateur --</option>
                        </select>
                    </div>
                    <div class="sm:pt-6">
                        <button id="btn-save-permissions"
                                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold flex items-center space-x-2 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                            <i class="fas fa-save"></i>
                            <span>Enregistrer</span>
                        </button>
                    </div>
                </div>

                <!-- Grille permissions -->
                <div id="permissions-grid" class="hidden">
                    <div class="bg-slate-50 rounded-xl border border-slate-200 overflow-hidden shadow-sm">

                        <!-- Légende colonnes -->
                        <div class="grid grid-cols-[1fr_repeat(5,80px)] gap-0 px-5 py-3 bg-slate-700 text-white text-xs font-semibold uppercase tracking-wider">
                            <div>Module</div>
                            <div class="text-center">
                                <i class="fas fa-eye block mb-0.5"></i>Voir
                            </div>
                            <div class="text-center">
                                <i class="fas fa-plus block mb-0.5"></i>Ajouter
                            </div>
                            <div class="text-center">
                                <i class="fas fa-pen block mb-0.5"></i>Modifier
                            </div>
                            <div class="text-center">
                                <i class="fas fa-trash block mb-0.5"></i>Supprimer
                            </div>
                            <div class="text-center">
                                <i class="fas fa-print block mb-0.5"></i>Imprimer
                            </div>
                        </div>

                        <!-- Lignes modules — générées par JS -->
                        <div id="permissions-rows" class="divide-y divide-slate-200"></div>
                    </div>

                    <!-- Info -->
                    <p class="text-xs text-gray-400 mt-3 flex items-center space-x-1">
                        <i class="fas fa-info-circle"></i>
                        <span>Les modifications s'appliquent à la prochaine connexion de l'utilisateur.</span>
                    </p>
                </div>

                <!-- Placeholder vide -->
                <div id="permissions-placeholder" class="text-center py-16 text-gray-400">
                    <i class="fas fa-user-shield text-5xl mb-3 opacity-30"></i>
                    <p class="text-sm">Sélectionnez un utilisateur pour gérer ses permissions</p>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ─── MODAL : AJOUTER / ÉDITER UTILISATEUR ─── -->
<div id="modal-user-form" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg transform transition-all duration-300 scale-95 opacity-0">

        <div class="flex items-center justify-between p-5 bg-gradient-to-r from-slate-700 to-slate-900 text-white rounded-t-2xl">
            <div class="flex items-center space-x-3">
                <i class="fas fa-user-edit text-lg"></i>
                <h3 id="user-form-title" class="font-bold text-lg">Nouvel utilisateur</h3>
            </div>
            <button id="close-user-form" class="p-2 rounded-full hover:bg-white hover:bg-opacity-20 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="p-6 space-y-4">
            <input type="hidden" id="user-form-id">

            <!-- Photo -->
            <div class="flex flex-col items-center space-y-3">
                <div class="relative cursor-pointer group" id="photo-upload-zone">
                    <img id="user-form-photo-preview" src="img/user/user.png"
                         class="w-24 h-24 rounded-full object-cover border-4 border-slate-200 group-hover:border-slate-400 transition-all shadow-md">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 rounded-full flex items-center justify-center transition-all">
                        <i class="fas fa-camera text-white opacity-0 group-hover:opacity-100 text-xl transition-all"></i>
                    </div>
                </div>
                <input type="file" id="user-form-photo-input" accept="image/jpeg,image/png,image/gif" class="hidden">
                <p class="text-xs text-gray-400">Cliquez sur la photo pour changer</p>
            </div>

            <!-- Nom complet -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    <i class="fas fa-user text-slate-500 mr-1"></i>Nom et Prénom <span class="text-red-500">*</span>
                </label>
                <input type="text" id="user-form-nom"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 transition-all"
                       placeholder="Ex: BENALI Mohamed" oninput="this.value=this.value.toUpperCase()">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    <i class="fas fa-envelope text-slate-500 mr-1"></i>Email <span class="text-red-500">*</span>
                </label>
                <input type="email" id="user-form-email"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 transition-all"
                       placeholder="exemple@entreprise.dz">
            </div>

            <!-- Mot de passe -->
            <div id="user-form-password-section">
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    <i class="fas fa-lock text-slate-500 mr-1"></i>Mot de passe <span class="text-red-500" id="pwd-required-star">*</span>
                    <span id="pwd-optional-hint" class="text-xs font-normal text-gray-400 hidden">(laisser vide = inchangé)</span>
                </label>
                <div class="relative">
                    <input type="password" id="user-form-password"
                           class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 transition-all"
                           placeholder="Minimum 6 caractères">
                    <button type="button" id="toggle-form-pwd" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Rôle + Statut -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fas fa-shield-alt text-slate-500 mr-1"></i>Rôle
                    </label>
                    <select id="user-form-role"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 transition-all bg-white">
                        <option value="user">Utilisateur</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fas fa-toggle-on text-slate-500 mr-1"></i>Statut
                    </label>
                    <select id="user-form-statut"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 transition-all bg-white">
                        <option value="actif">Actif</option>
                        <option value="inactif">Inactif</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-3 px-6 pb-6">
            <button id="cancel-user-form"
                    class="px-5 py-2.5 border border-gray-300 text-gray-600 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
                Annuler
            </button>
            <button id="submit-user-form"
                    class="px-6 py-2.5 bg-slate-700 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold flex items-center space-x-2 transition-colors shadow-sm">
                <i class="fas fa-save"></i>
                <span>Enregistrer</span>
            </button>
        </div>
    </div>
</div>

<!-- ─── MODAL : CONFIRMATION SUPPRESSION USER ─── -->
<div id="modal-delete-user-param" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm transform transition-all duration-300 scale-95 opacity-0">
        <div class="p-6 text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-slash text-2xl text-red-600"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Supprimer l'utilisateur ?</h3>
            <p id="delete-user-param-info" class="text-gray-500 text-sm mb-6">Cette action est irréversible.</p>
            <div class="flex justify-center space-x-3">
                <button id="cancel-delete-user-param"
                        class="px-5 py-2 border border-gray-300 text-gray-600 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
                    Annuler
                </button>
                <button id="confirm-delete-user-param"
                        class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold transition-colors">
                    Supprimer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ─── MODAL : RÉINITIALISATION MOT DE PASSE ─── -->
<div id="modal-reset-pwd" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm transform transition-all duration-300 scale-95 opacity-0">
        <div class="flex items-center justify-between p-5 bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-t-2xl">
            <div class="flex items-center space-x-2">
                <i class="fas fa-key"></i>
                <h3 class="font-bold">Réinitialiser le mot de passe</h3>
            </div>
            <button id="close-reset-pwd" class="p-1 rounded-full hover:bg-white hover:bg-opacity-20 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="reset-pwd-user-id">
            <p id="reset-pwd-user-name" class="text-sm text-gray-600 font-medium text-center"></p>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nouveau mot de passe <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="password" id="reset-pwd-input"
                           class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition-all"
                           placeholder="Minimum 6 caractères">
                    <button type="button" id="toggle-reset-pwd" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button id="cancel-reset-pwd"
                        class="px-5 py-2 border border-gray-300 text-gray-600 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
                    Annuler
                </button>
                <button id="confirm-reset-pwd"
                        class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-semibold transition-colors">
                    <i class="fas fa-key mr-1"></i>Réinitialiser
                </button>
            </div>
        </div>
    </div>
</div>