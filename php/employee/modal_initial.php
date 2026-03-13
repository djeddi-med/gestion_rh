<!-- Modal EMPLOYEE -->
<div id="modal-employee" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full  max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-700 text-white">
            <div class="flex items-center space-x-3">
                <i class="fas fa-users text-xl"></i>
                <h2 class="text-xl font-bold">Gestion des Employés</h2>
            </div>
            <button class="modal-close-btn p-2 rounded-full hover:bg-white hover:bg-opacity-20 transition-colors duration-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto max-h-[70vh]">
            <!-- Barre de recherche et boutons -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6 space-y-4 lg:space-y-0">
                <!-- Barre de recherche -->
                <div class="relative flex-1 max-w-lg">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="search-employee" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" placeholder="Rechercher par nom, matricule, téléphone, poste...">
                </div>
                
                <!-- Boutons d'action -->
                <div class="flex items-center space-x-3">
                    <!-- Télécharger Excel -->
                    <button id="btn-excel-employee" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center space-x-2">
                        <i class="fas fa-file-excel"></i>
                        <span>Excel</span>
                    </button>
                    
                    <!-- Télécharger PDF -->
                    <button id="btn-pdf-employee" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 flex items-center space-x-2">
                        <i class="fas fa-file-pdf"></i>
                        <span>PDF</span>
                    </button>
                    
                    <!-- Ajouter Employé -->
                    <button id="btn-add-employee" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center space-x-2">
                        <i class="fas fa-user-plus"></i>
                        <span>+ RECRUTEMENT</span>
                    </button>
                </div>
            </div>

            <!-- Nombre d'enregistrements -->
            <div class="mb-4">
                <p id="employee-record-count" class="text-sm text-gray-600 font-medium">Chargement des données...</p>
            </div>

            <!-- Tableau des employés -->
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-4" id="employee-table-container">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Matricule
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nom et Prénom
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date Naissance
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Téléphone
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fonction
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type Contrat
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Expiration
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    État
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="employee-table-body" class="bg-white divide-y divide-gray-200 max-h-96 overflow-y-auto">
                            <!-- Les données seront chargées ici par JavaScript -->
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                                        <p class="text-gray-600">Chargement des employés...</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div id="pagination-container">
                <!-- La pagination sera générée ici par JavaScript -->
            </div>
        </div>
    </div>
</div>