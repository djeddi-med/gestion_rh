<!-- Modal Contrat -->
<div id="modal-contrat" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-emerald-600 to-emerald-800 text-white">
            <div class="flex items-center space-x-3">
                <i class="fas fa-file-contract text-2xl"></i>
                <div>
                    <h2 class="text-xl font-bold">Gestion des Contrats</h2>
                    <p class="text-sm text-emerald-100 opacity-90">Gestion et suivi des contrats de travail</p>
                </div>
            </div>
            <button class="modal-close-btn p-2 rounded-full hover:bg-white hover:bg-opacity-20 transition-colors duration-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto max-h-[70vh]">
            <!-- Barre de recherche et bouton nouveau -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6 space-y-4 lg:space-y-0">
                <!-- Barre de recherche -->
                <div class="relative flex-1 max-w-lg">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-emerald-400"></i>
                    </div>
                    <input type="text" id="search-contrat" class="block w-full pl-10 pr-3 py-3 border border-emerald-300 rounded-xl bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200 text-gray-700 placeholder-emerald-400" placeholder="Rechercher par référence, matricule, nom, poste...">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-xs text-emerald-400 bg-emerald-50 px-2 py-1 rounded-full">Recherche DB</span>
                    </div>
                </div>
                
                <!-- Bouton Nouveau Contrat -->
                <button id="btn-new-contrat" class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-xl hover:from-emerald-700 hover:to-emerald-800 transition-all duration-200 flex items-center space-x-3 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="fas fa-plus-circle text-lg"></i>
                    <div class="text-left">
                        <span class="font-semibold">NOUVEAU CONTRAT</span>
                        <p class="text-xs opacity-90">Créer un nouveau contrat</p>
                    </div>
                </button>
            </div>

            <!-- Nombre d'enregistrements -->
            <div class="mb-4 px-2">
                <p id="record-count" class="text-sm font-medium text-emerald-700 bg-emerald-50 inline-block px-3 py-1 rounded-lg">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Chargement des données...
                </p>
            </div>

            <!-- Tableau des contrats -->
            <div class="bg-white rounded-xl border border-emerald-200 overflow-hidden mb-6 shadow-sm" id="contrat-table-container">
                <div class="table-container overflow-x-auto">
                    <table class="min-w-full divide-y divide-emerald-100">
                        <thead class="bg-emerald-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-hashtag text-emerald-500"></i>
                                        <span>Référence</span>
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-id-card text-emerald-500"></i>
                                        <span>Matricule</span>
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-camera text-emerald-500"></i>
                                        <span>Photo</span>
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-user text-emerald-500"></i>
                                        <span>Nom et Prénom</span>
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-briefcase text-emerald-500"></i>
                                        <span>Fonction</span>
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-file-contract text-emerald-500"></i>
                                        <span>Type Contrat</span>
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-chart-line text-emerald-500"></i>
                                        <span>État</span>
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-calendar-start text-emerald-500"></i>
                                        <span>Date début</span>
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-calendar-check text-emerald-500"></i>
                                        <span>Date fin</span>
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-cogs text-emerald-500"></i>
                                        <span>Actions</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="contrat-table-body" class="bg-white divide-y divide-emerald-50">
                            <!-- Les données seront chargées ici par JavaScript -->
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-emerald-600 mb-4"></div>
                                        <p class="text-gray-700 font-medium">Chargement des contrats...</p>
                                        <p class="text-sm text-gray-500 mt-1">Récupération des données depuis la base</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div id="pagination-container" class="bg-emerald-50 rounded-xl border border-emerald-200 overflow-hidden">
                <!-- La pagination sera générée ici par JavaScript -->
            </div>
        </div>
    </div>
</div>