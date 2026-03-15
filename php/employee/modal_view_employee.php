<!-- ═══════════════════════════════════════════════════════════ -->
<!-- MODAL VISUALISATION EMPLOYÉ                               -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div id="modal-view-employee" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[92vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0">

        <!-- Header dynamique (couleur selon etat) -->
        <div id="view-emp-header" class="relative flex items-center justify-between px-6 py-5 bg-gradient-to-r from-blue-700 to-blue-900 text-white overflow-hidden">
            <!-- Décoration fond -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute -top-6 -right-6 w-40 h-40 rounded-full bg-white"></div>
                <div class="absolute -bottom-8 -left-4 w-28 h-28 rounded-full bg-white"></div>
            </div>

            <div class="relative flex items-center space-x-4">
                <!-- Photo -->
                <div class="relative">
                    <img id="view-emp-photo"
                         src="img/employee/user.png"
                         onerror="this.src='img/employee/user.png'"
                         class="w-16 h-16 rounded-2xl object-cover border-3 border-white border-opacity-40 shadow-xl">
                    <span id="view-emp-etat-dot"
                          class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-2 border-white bg-green-400 shadow"></span>
                </div>
                <div>
                    <div class="flex items-center space-x-2 mb-0.5">
                        <span id="view-emp-civilite" class="text-xs font-medium bg-white bg-opacity-20 px-2 py-0.5 rounded-full"></span>
                        <span id="view-emp-etat-badge" class="text-xs font-semibold bg-green-400 bg-opacity-90 px-2 py-0.5 rounded-full"></span>
                    </div>
                    <h2 id="view-emp-nom" class="text-xl font-bold tracking-wide"></h2>
                    <p id="view-emp-matricule" class="text-sm text-blue-200 font-mono"></p>
                </div>
            </div>

            <button class="modal-close-btn relative p-2 rounded-xl hover:bg-white hover:bg-opacity-20 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Body scrollable -->
        <div id="view-emp-body" class="overflow-y-auto" style="max-height: calc(92vh - 90px);">

            <!-- Skeleton loading -->
            <div id="view-emp-skeleton" class="p-6 space-y-4">
                <div class="animate-pulse space-y-3">
                    <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                    <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                </div>
            </div>

            <!-- Contenu réel (injecté par JS) -->
            <div id="view-emp-content" class="hidden p-6 space-y-5">

                <!-- ── SECTION 1 : Informations personnelles ── -->
                <div class="emp-section">
                    <div class="emp-section-title">
                        <i class="fas fa-user text-blue-500"></i>
                        <span>Informations personnelles</span>
                    </div>
                    <div class="emp-grid">
                        <div class="emp-field">
                            <span class="emp-label"><i class="fas fa-calendar-day"></i> Date de naissance</span>
                            <span id="vf-date-naissance" class="emp-value"></span>
                        </div>
                        <div class="emp-field">
                            <span class="emp-label"><i class="fas fa-map-marker-alt"></i> Lieu de naissance</span>
                            <span id="vf-lieu-naissance" class="emp-value"></span>
                        </div>
                        <div class="emp-field">
                            <span class="emp-label"><i class="fas fa-file-alt"></i> N° Acte de naissance</span>
                            <span id="vf-acte-naissance" class="emp-value font-mono"></span>
                        </div>
                        <div class="emp-field">
                            <span class="emp-label"><i class="fas fa-heart"></i> Situation familiale</span>
                            <span id="vf-situation-familiale" class="emp-value"></span>
                        </div>
                        <div class="emp-field">
                            <span class="emp-label"><i class="fas fa-baby"></i> Nombre d'enfants</span>
                            <span id="vf-nombre-enfants" class="emp-value"></span>
                        </div>
                    </div>
                </div>

                <!-- ── SECTION 2 : Filiation ── -->
                <div class="emp-section">
                    <div class="emp-section-title">
                        <i class="fas fa-users text-purple-500"></i>
                        <span>Filiation</span>
                    </div>
                    <div class="emp-grid">
                        <div class="emp-field">
                            <span class="emp-label"><i class="fas fa-male"></i> Prénom du père</span>
                            <span id="vf-prenom-pere" class="emp-value"></span>
                        </div>
                        <div class="emp-field">
                            <span class="emp-label"><i class="fas fa-female"></i> Nom & Prénom de la mère</span>
                            <span id="vf-nom-mere" class="emp-value"></span>
                        </div>
                    </div>
                </div>

                <!-- ── SECTION 3 : Coordonnées ── -->
                <div class="emp-section">
                    <div class="emp-section-title">
                        <i class="fas fa-address-card text-emerald-500"></i>
                        <span>Coordonnées</span>
                    </div>
                    <div class="emp-grid">
                        <div class="emp-field md:col-span-2">
                            <span class="emp-label"><i class="fas fa-home"></i> Adresse</span>
                            <span id="vf-adresse" class="emp-value"></span>
                        </div>
                        <div class="emp-field">
                            <span class="emp-label"><i class="fas fa-map"></i> Wilaya de résidence</span>
                            <span id="vf-wilaya" class="emp-value"></span>
                        </div>
                        <div class="emp-field">
                            <span class="emp-label"><i class="fas fa-phone"></i> Téléphone</span>
                            <span id="vf-telephone" class="emp-value font-mono"></span>
                        </div>
                    </div>
                </div>

                <!-- ── SECTION 4 : Informations administratives ── -->
                <div class="emp-section">
                    <div class="emp-section-title">
                        <i class="fas fa-id-card text-amber-500"></i>
                        <span>Informations administratives</span>
                    </div>
                    <div class="emp-grid">
                        <div class="emp-field">
                            <span class="emp-label"><i class="fas fa-heartbeat"></i> N° Assurance CNAS</span>
                            <span id="vf-cnas" class="emp-value font-mono"></span>
                        </div>
                        <div class="emp-field">
                            <span class="emp-label"><i class="fas fa-university"></i> Compte à payer</span>
                            <span id="vf-compte" class="emp-value font-mono"></span>
                        </div>
                        <div class="emp-field">
                            <span class="emp-label"><i class="fas fa-clock"></i> Date de création</span>
                            <span id="vf-date-creation" class="emp-value"></span>
                        </div>
                        <div class="emp-field">
                            <span class="emp-label"><i class="fas fa-user-edit"></i> Créé par</span>
                            <span id="vf-user" class="emp-value"></span>
                        </div>
                    </div>
                </div>

                <!-- ── SECTION 5 : Historique des contrats ── -->
                <div class="emp-section">
                    <div class="emp-section-title">
                        <i class="fas fa-file-contract text-indigo-500"></i>
                        <span>Historique des contrats</span>
                        <span id="vf-contrats-count" class="ml-auto text-xs font-semibold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full"></span>
                    </div>

                    <!-- Timeline contrats -->
                    <div id="vf-contrats-list" class="space-y-3 mt-1"></div>

                    <!-- Aucun contrat -->
                    <div id="vf-no-contrat" class="hidden text-center py-8 text-gray-400">
                        <i class="fas fa-file-slash text-3xl mb-2 block opacity-30"></i>
                        <p class="text-sm">Aucun contrat enregistré</p>
                    </div>
                </div>

                <!-- ── BOUTONS D'ACTION EN BAS ── -->
                <div class="sticky bottom-0 bg-white border-t border-gray-100 pt-4 pb-2 mt-4">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- Imprimer fiche employé -->
                        <button id="btn-print-fiche"
                                class="flex-1 flex items-center justify-center space-x-3 px-5 py-3 rounded-xl
                                       bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold text-sm
                                       hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-blue-200
                                       hover:shadow-lg transition-all duration-200 hover:-translate-y-0.5">
                            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-print"></i>
                            </div>
                            <div class="text-left">
                                <div class="font-bold leading-tight">Imprimer la fiche</div>
                                <div class="text-xs text-blue-200 font-normal">Fiche employé(e) complète</div>
                            </div>
                        </button>

                        <!-- Imprimer affectation -->
                        <button id="btn-print-affectation"
                                class="flex-1 flex items-center justify-center space-x-3 px-5 py-3 rounded-xl
                                       bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-semibold text-sm
                                       hover:from-emerald-700 hover:to-emerald-800 shadow-md hover:shadow-emerald-200
                                       hover:shadow-lg transition-all duration-200 hover:-translate-y-0.5">
                            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-export"></i>
                            </div>
                            <div class="text-left">
                                <div class="font-bold leading-tight">Imprimer affectation</div>
                                <div class="text-xs text-emerald-200 font-normal">Document d'affectation</div>
                            </div>
                        </button>

                        <!-- Fermer -->
                        <button class="modal-close-btn sm:w-auto flex items-center justify-center space-x-2 px-5 py-3 rounded-xl
                                       border-2 border-gray-200 text-gray-600 font-semibold text-sm
                                       hover:bg-gray-50 hover:border-gray-300 transition-all duration-200">
                            <i class="fas fa-times text-sm"></i>
                            <span>Fermer</span>
                        </button>
                    </div>
                </div>

            </div><!-- /view-emp-content -->
        </div><!-- /view-emp-body -->
    </div>
</div>