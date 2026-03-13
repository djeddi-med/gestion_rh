<!-- Modal Visualisation Contrat -->
<div id="modal-show-contrat" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-700 text-white">
            <div class="flex items-center space-x-3">
                <i class="fas fa-eye text-xl"></i>
                <h2 class="text-xl font-bold">Visualisation du Contrat</h2>
            </div>
            <button class="modal-close-btn p-2 rounded-full hover:bg-white hover:bg-opacity-20 transition-colors duration-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto max-h-[70vh]">
            <div id="contrat-details" class="space-y-6">
                <!-- Les détails du contrat seront chargés ici -->
                <div class="text-center py-8">
                    <i class="fas fa-file-contract text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">Chargement des détails du contrat...</p>
                </div>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50">
            <button class="modal-close-btn px-6 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                Fermer
            </button>
            <button class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-print mr-2"></i>Imprimer
            </button>
        </div>
    </div>
</div>