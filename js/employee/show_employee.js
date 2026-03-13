class ShowEmployee {
    constructor() {
        this.currentData = [];
        this.currentPage = 1;
        this.totalPages = 1;
        this.totalRecords = 0;
        this.searchTerm = '';
        this.isLoading = false;
        this.init();
    }

    init() {
        this.loadEmployees();
        this.setupEventListeners();
    }

    async loadEmployees(page = 1, search = '') {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading();
        this.currentPage = page;
        this.searchTerm = search;

        try {
            const params = new URLSearchParams({
                page: page,
                search: search
            });

            const response = await fetch(`php/employee/show_employee.php?${params}`);
            const result = await response.json();
            
            if (result.success) {
                this.currentData = result.data;
                this.totalPages = result.pagination.totalPages;
                this.totalRecords = result.pagination.total;
                
                this.renderTable();
                this.updateRecordCount();
                this.renderPagination();
            } else {
                this.showError('Erreur lors du chargement des employés');
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showError('Erreur de connexion');
        } finally {
            this.hideLoading();
            this.isLoading = false;
        }
    }

    renderTable() {
        const tbody = document.getElementById('employee-table-body');
        
        if (this.currentData.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-search text-3xl mb-3 text-gray-400"></i>
                        <p class="text-lg font-medium mb-2">Aucun employé trouvé</p>
                        <p class="text-sm text-gray-400">${this.searchTerm ? 'Aucun résultat pour votre recherche' : 'Commencez par ajouter un employé'}</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = this.currentData.map(employee => `
            <tr class="hover:bg-gray-50 transition-colors duration-150">
                <!-- Matricule -->
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                    
                    <b>${employee.matricule || 'N/A'}</b>
                </td>
                
                <!-- Photo -->
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full object-cover border-2 border-gray-200" 
                             src="img/employee/${employee.photo || 'user.png'}" 
                             alt="${employee.nom} ${employee.prenom}"
                             onerror="this.src='img/employee/user.png'">
                    
                    </div>
                </td>
                
                <!-- Nom et Prénom -->
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    <div class="font-semibold text-gray-800">${employee.nom} ${employee.prenom}</div>
                    <div class="text-xs text-gray-500 capitalize">${employee.civilite || ''}</div>
                </td>
                
                <!-- Date de naissance -->
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    ${employee.date_naissance ? new Date(employee.date_naissance).toLocaleDateString('fr-FR') : 'N/A'}
                </td>
                
                <!-- Téléphone -->
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    ${employee.telephone || 'N/A'}
                </td>
                
                <!-- Poste -->
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    ${employee.poste ? `
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            ${employee.poste}
                        </span>
                    ` : `
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-circle mr-1 text-red-400" style="font-size: 6px;"></i>
                            Récemment embauché
                        </span>
                    `}
                </td>
                
                <!-- Type Contrat -->
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    ${employee.type_contrat ? `
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            ${employee.type_contrat === 'CDI' ? 'bg-green-100 text-green-800' : 
                              employee.type_contrat.toLowerCase().includes('temps plein') ? 'bg-blue-100 text-blue-800' :
                              employee.type_contrat.toLowerCase().includes('temps partiel') ? 'bg-purple-100 text-purple-800' :
                              'bg-yellow-100 text-yellow-800'}">
                            ${employee.type_contrat}
                        </span>
                    ` : `
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-circle mr-1 text-red-400" style="font-size: 6px;"></i>
                            Récemment embauché
                        </span>
                    `}
                </td>
                
                <!-- Date expiration -->
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    ${employee.date_fin ? `
                        <span class="${this.isContractExpired(employee.date_fin) ? 'text-red-600' : 'text-green-600'}">
                            ${new Date(employee.date_fin).toLocaleDateString('fr-FR')}
                        </span>
                    ` : (
                        employee.type_contrat === 'CDI' ? `
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Indéterminé
                            </span>
                        ` : `
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Récemment embauché
                            </span>
                        `
                    )}
                </td>
                
                <!-- État -->
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        ${employee.etat === 'actif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        <i class="fas fa-circle mr-1 ${employee.etat === 'actif' ? 'text-green-400' : 'text-red-400'}" style="font-size: 6px;"></i>
                        ${employee.etat === 'actif' ? 'Actif' : 'Inactif'}
                    </span>
                </td>
                
                <!-- Actions -->
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-1">
                        <!-- Voir -->
                        <button class="view-btn text-blue-600 hover:text-blue-900 p-2 rounded hover:bg-blue-50 transition-colors duration-200" 
                                data-id="${employee.id}" title="Voir">
                            <i class="fas fa-eye"></i>
                        </button>
                        
                        <!-- Éditer -->
                        <button class="edit-btn text-green-600 hover:text-green-900 p-2 rounded hover:bg-green-50 transition-colors duration-200" 
                                data-id="${employee.id}" title="Éditer">
                            <i class="fas fa-edit"></i>
                        </button>
                        
                        <!-- Imprimer -->
                        <button class="print-btn text-purple-600 hover:text-purple-900 p-2 rounded hover:bg-purple-50 transition-colors duration-200" 
                                data-id="${employee.id}" title="Imprimer">
                            <i class="fas fa-print"></i>
                        </button>
                        
                        <!-- Supprimer -->
                        <button class="delete-btn text-red-600 hover:text-red-900 p-2 rounded hover:bg-red-50 transition-colors duration-200" 
                                data-id="${employee.id}" 
                                data-matricule="${employee.matricule}"
                                data-nom="${employee.nom}" 
                                data-prenom="${employee.prenom}" 
                                data-poste="${employee.poste}"
                                title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        this.attachActionListeners();
    }

    isContractExpired(dateString) {
        const contractDate = new Date(dateString);
        const today = new Date();
        return contractDate < today;
    }

    renderPagination() {
        const paginationContainer = document.getElementById('pagination-container');
        if (!paginationContainer) return;

        // Ne pas afficher la pagination s'il n'y a qu'une page
        if (this.totalPages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let paginationHTML = `
            <div class="flex items-center justify-between px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                <!-- Mobile -->
                <div class="flex flex-1 justify-between sm:hidden">
                    <button onclick="showEmployee.goToPage(${this.currentPage - 1})" 
                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 ${!this.currentPage > 1 ? 'opacity-50 cursor-not-allowed' : ''}"
                            ${this.currentPage <= 1 ? 'disabled' : ''}>
                        Précédent
                    </button>
                    <button onclick="showEmployee.goToPage(${this.currentPage + 1})" 
                            class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 ${!this.currentPage < this.totalPages ? 'opacity-50 cursor-not-allowed' : ''}"
                            ${this.currentPage >= this.totalPages ? 'disabled' : ''}>
                        Suivant
                    </button>
                </div>
                
                <!-- Desktop -->
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Affichage de <span class="font-medium">${(this.currentPage - 1) * 100 + 1}</span>
                            à <span class="font-medium">${Math.min(this.currentPage * 100, this.totalRecords)}</span>
                            sur <span class="font-medium">${this.totalRecords}</span> résultats
                        </p>
                    </div>
                    <div>
                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
        `;

        // Bouton précédent
        paginationHTML += `
            <button onclick="showEmployee.goToPage(${this.currentPage - 1})" 
                    class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 ${this.currentPage <= 1 ? 'opacity-50 cursor-not-allowed' : ''}"
                    ${this.currentPage <= 1 ? 'disabled' : ''}>
                <span class="sr-only">Précédent</span>
                <i class="fas fa-chevron-left h-4 w-4"></i>
            </button>
        `;

        // Numéros de page
        const maxVisiblePages = 5;
        let startPage = Math.max(1, this.currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(this.totalPages, startPage + maxVisiblePages - 1);

        // Ajuster si on est proche de la fin
        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        // Première page
        if (startPage > 1) {
            paginationHTML += `
                <button onclick="showEmployee.goToPage(1)" 
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                    1
                </button>
            `;
            if (startPage > 2) {
                paginationHTML += `
                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">
                        ...
                    </span>
                `;
            }
        }

        // Pages visibles
        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `
                <button onclick="showEmployee.goToPage(${i})" 
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ${i === this.currentPage ? 'bg-blue-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0'}">
                    ${i}
                </button>
            `;
        }

        // Dernière page
        if (endPage < this.totalPages) {
            if (endPage < this.totalPages - 1) {
                paginationHTML += `
                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">
                        ...
                    </span>
                `;
            }
            paginationHTML += `
                <button onclick="showEmployee.goToPage(${this.totalPages})" 
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                    ${this.totalPages}
                </button>
            `;
        }

        // Bouton suivant
        paginationHTML += `
            <button onclick="showEmployee.goToPage(${this.currentPage + 1})" 
                    class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 ${this.currentPage >= this.totalPages ? 'opacity-50 cursor-not-allowed' : ''}"
                    ${this.currentPage >= this.totalPages ? 'disabled' : ''}>
                <span class="sr-only">Suivant</span>
                <i class="fas fa-chevron-right h-4 w-4"></i>
            </button>
        `;

        paginationHTML += `
                        </nav>
                    </div>
                </div>
            </div>
        `;

        paginationContainer.innerHTML = paginationHTML;
    }

    goToPage(page) {
        if (page < 1 || page > this.totalPages || page === this.currentPage) return;
        this.loadEmployees(page, this.searchTerm);
        
        // Scroll vers le haut du tableau
        const tableContainer = document.querySelector('#employee-table-body');
        if (tableContainer) {
            tableContainer.scrollTop = 0;
        }
    }

    setupEventListeners() {
        // Barre de recherche avec debounce
        const searchInput = document.getElementById('search-employee');
        let searchTimeout;
        
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.filterTable(e.target.value);
            }, 300); // 300ms de délai pour éviter trop de requêtes
        });

        // Touche Enter pour rechercher immédiatement
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                clearTimeout(searchTimeout);
                this.filterTable(e.target.value);
            }
        });

        // Boutons d'export
        document.getElementById('btn-excel-employee').addEventListener('click', () => {
            this.exportExcel();
        });

        document.getElementById('btn-pdf-employee').addEventListener('click', () => {
            this.exportPDF();
        });

        // Bouton ajouter employé
        document.getElementById('btn-add-employee').addEventListener('click', () => {
            ModalManager.openModal('add_employee');
        });

        // Focus sur la barre de recherche à l'ouverture du modal
        document.addEventListener('modalOpened', (e) => {
            if (e.detail === 'employee') {
                setTimeout(() => {
                    searchInput.focus();
                }, 300);
            }
        });
    }

    attachActionListeners() {
        // Boutons voir
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                this.viewEmployee(id);
            });
        });

        // Boutons éditer
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                this.editEmployee(id);
            });
        });

        // Boutons imprimer
        document.querySelectorAll('.print-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                this.printEmployee(id);
            });
        });

        // Boutons supprimer
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                const matricule = e.currentTarget.getAttribute('data-matricule');
                const nom = e.currentTarget.getAttribute('data-nom');
                const prenom = e.currentTarget.getAttribute('data-prenom');
                const poste = e.currentTarget.getAttribute('data-poste');
                this.confirmDelete(id, matricule, nom, prenom, poste);
            });
        });
    }

    filterTable(searchTerm) {
        // Réinitialiser à la première page lors d'une nouvelle recherche
        this.loadEmployees(1, searchTerm);
    }

    updateRecordCount() {
        const countElement = document.getElementById('employee-record-count');
        if (countElement) {
            countElement.textContent = `${this.totalRecords} employé(s) trouvé(s) - Page ${this.currentPage}/${this.totalPages}`;
        }
    }

    showLoading() {
        const tbody = document.getElementById('employee-table-body');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                            <p class="text-gray-600">Chargement des données...</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    hideLoading() {
        // Pas besoin d'action spécifique, le tableau se met à jour automatiquement
    }

    showError(message) {
        const tbody = document.getElementById('employee-table-body');
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="px-6 py-8 text-center text-red-600">
                    <i class="fas fa-exclamation-triangle text-3xl mb-3"></i>
                    <p class="text-lg font-semibold mb-2">Erreur</p>
                    <p class="text-sm text-gray-600">${message}</p>
                </td>
            </tr>
        `;
    }

    viewEmployee(id) {
        if (window.viewEmployee) {
            window.viewEmployee.open(id);
        } else {
            NotificationManager?.show('error', 'Erreur', 'Module de visualisation non chargé');
        }
    }

    editEmployee(id) {
        if (window.editEmployee) {
            window.editEmployee.open(id);
        } else {
            NotificationManager?.show('error', 'Erreur', "Module d'édition non chargé");
        }
    }

    printEmployee(id) {
        NotificationManager?.show('info', 'Impression', "Fonction d'impression à développer");
    }

    confirmDelete(id, matricule, nom, prenom, poste) {
        const deleteInfo = document.getElementById('delete-employee-info');
        if (deleteInfo) {
            deleteInfo.textContent = `Matricule: ${matricule} - ${nom} ${prenom} - ${poste || 'Non défini'}`;
        }
        
        const confirmBtn = document.getElementById('confirm-delete-employee');
        if (confirmBtn) {
            confirmBtn.onclick = () => this.deleteEmployee(id);
        }
        
        ModalManager?.openModal('delete-employee-confirm');
    }

    async deleteEmployee(id) {
        try {
            const response = await fetch('php/employee/delete_employee.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            });
            
            const result = await response.json();
            
            if (result.success) {
                NotificationManager?.show('success', 'Succès', 'Employé désactivé avec succès');
                ModalManager?.closeModal('delete-employee-confirm');
                
                // Recharger les données
                this.loadEmployees(this.currentPage, this.searchTerm);
            } else {
                NotificationManager?.show('error', 'Erreur', result.message);
            }
        } catch (error) {
            NotificationManager?.show('error', 'Erreur', 'Erreur lors de la suppression');
        }
    }

    exportExcel() {
        console.log('Export Excel');
        NotificationManager?.show('info', 'Export', 'Fonction d\'export Excel à développer');
    }

    exportPDF() {
        console.log('Export PDF');
        NotificationManager?.show('info', 'Export', 'Fonction d\'export PDF à développer');
    }
}

// Initialisation globale
let showEmployee = null;

document.addEventListener('DOMContentLoaded', () => {
    showEmployee = new ShowEmployee();
    window.showEmployee = showEmployee;
});