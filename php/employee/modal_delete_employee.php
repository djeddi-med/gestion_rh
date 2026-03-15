<!-- Modal Confirmation Suppression Employé -->
<div id="modal-delete-employee-confirm" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all duration-300 scale-95 opacity-0">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-red-600 to-red-700 text-white">
            <div class="flex items-center space-x-3">
                <i class="fas fa-exclamation-triangle text-xl"></i>
                <h2 class="text-xl font-bold">Confirmation de suppression</h2>
            </div>
            <button class="modal-close-btn p-2 rounded-full hover:bg-white hover:bg-opacity-20 transition-colors duration-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6">
            <div class="text-center">
                <i class="fas fa-user-slash text-4xl text-red-500 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Êtes-vous sûr de vouloir supprimer cet employé ?</h3>
                <p id="delete-employee-info" class="text-gray-600 mb-6">Les informations de l'employé apparaîtront ici</p>
                
                <div class="flex justify-center space-x-4">
                    <button id="confirm-delete-employee" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 flex items-center space-x-2">
                        <i class="fas fa-check"></i>
                        <span>Valider</span>
                    </button>
                    <button id="cancel-delete-employee" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200 flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Annuler</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>