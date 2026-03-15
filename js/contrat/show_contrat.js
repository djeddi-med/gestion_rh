class ShowContrat {
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
        this.loadContrats();
        this.setupEventListeners();
    }

    async loadContrats(page = 1, search = '') {
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

            const response = await fetch(`php/contrat/show_contrat.php?${params}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.currentData = result.data;
                this.totalPages = result.pagination.totalPages;
                this.totalRecords = result.pagination.total;
                
                this.renderTable();
                this.updateRecordCount();
                this.renderPagination();
            } else {
                this.showError('Erreur lors du chargement des contrats: ' + result.message);
            }
        } catch (error) {
            console.error('Erreur de chargement:', error);
            this.showError('Erreur de connexion: ' + error.message);
        } finally {
            this.hideLoading();
            this.isLoading = false;
        }
    }

    getContratStatus(contrat) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (contrat.date_fin) {
            const dateFin = new Date(contrat.date_fin);
            dateFin.setHours(0, 0, 0, 0);
            
            if (dateFin >= today) {
                return {
                    text: 'En vigueur',
                    class: 'status-active',
                    icon: 'fa-check-circle'
                };
            } else {
                return {
                    text: 'Expiré',
                    class: 'status-expired',
                    icon: 'fa-times-circle'
                };
            }
        }
        
        // Pas de date_fin
        if (contrat.type_contrat === 'CDI') {
            if (contrat.etat === 'actif') {
                return {
                    text: 'En vigueur',
                    class: 'status-active',
                    icon: 'fa-check-circle'
                };
            } else {
                return {
                    text: 'Fin de relation',
                    class: 'status-ended',
                    icon: 'fa-user-slash'
                };
            }
        }
        
        // Autres cas
        return {
            text: 'À déterminer',
            class: 'status-expired',
            icon: 'fa-question-circle'
        };
    }

    getContratTypeClass(typeContrat) {
        if (!typeContrat) return 'badge-cdi';
        
        const type = typeContrat.toLowerCase();
        if (type.includes('cdi')) return 'badge-cdi';
        if (type.includes('cdd')) return 'badge-cdd';
        if (type.includes('temps plein') || type.includes('full time')) return 'badge-temps-plein';
        if (type.includes('temps partiel') || type.includes('part time')) return 'badge-temps-partiel';
        return 'badge-cdi';
    }

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    formatCurrency(amount) {
        if (!amount) return '0 DA';
        return new Intl.NumberFormat('fr-DZ', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount) + ' DA';
    }

    renderTable() {
        const tbody = document.getElementById('contrat-table-body');
        
        if (this.currentData.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-search text-3xl mb-3 text-gray-400"></i>
                        <p class="text-lg font-medium mb-2">Aucun contrat trouvé</p>
                        <p class="text-sm text-gray-400">${this.searchTerm ? 'Aucun résultat pour votre recherche' : 'Commencez par créer un contrat'}</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = this.currentData.map(contrat => {
            const status = this.getContratStatus(contrat);
            const typeClass = this.getContratTypeClass(contrat.type_contrat);
            
            return `
            <tr class="hover:bg-gray-50 transition-colors duration-150 group hc-row-clickable" data-employee-id="${contrat.id_employee}" data-contrat-id="${contrat.id}" style="cursor:pointer">
                <!-- Référence -->
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="flex items-center">
                        <span class="font-bold text-blue-600">
                            ${contrat.ref || 'N/R'}
                        </span>
                    </div>
                </td>
                
                <!-- Matricule -->
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                    <div class="flex items-center">
                         <span class="font-bold bg-gray-100 px-2 py-1 rounded">
                            ${contrat.matricule || 'N/A'}
                        </span>
                    </div>
                </td>
                
                <!-- Photo -->
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full object-cover border-2 border-gray-200 group-hover:border-green-500 transition-all duration-200" 
                             src="img/employee/${contrat.photo || 'user.png'}" 
                             alt="${contrat.nom} ${contrat.prenom}"
                             onerror="this.src='img/employee/user.png'">
                    </div>
                </td>
                
                <!-- Nom et Prénom -->
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="flex flex-col">
                        <span class="font-semibold text-gray-800">
                            ${contrat.nom} ${contrat.prenom}
                        </span>
                        <span class="text-xs text-gray-500 capitalize">
                            ${contrat.civilite || ''}
                        </span>
                    </div>
                </td>
                
                <!-- Fonction -->
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    ${contrat.poste ? `
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-briefcase mr-1 text-xs"></i>
                            ${contrat.poste}
                        </span>
                    ` : `
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            <i class="fas fa-question-circle mr-1 text-gray-400" style="font-size: 6px;"></i>
                            Non défini
                        </span>
                    `}
                </td>
                
                <!-- Type de contrat -->
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${typeClass}">
                        <i class="fas fa-file-contract mr-1 text-xs"></i>
                        ${contrat.type_contrat || 'CDI'}
                    </span>
                </td>
                
                <!-- État -->
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${status.class}">
                        <i class="fas ${status.icon} mr-1 text-xs"></i>
                        ${status.text}
                    </span>
                </td>
                
                <!-- Date début -->
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-start mr-2 text-green-500 text-xs"></i>
                        ${this.formatDate(contrat.date_debut)}
                    </div>
                </td>
                
                <!-- Date fin -->
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-check mr-2 ${contrat.type_contrat === 'CDI' ? 'text-blue-500' : 'text-red-500'} text-xs"></i>
                        ${contrat.type_contrat === 'CDI' ? 'Indéterminé' : this.formatDate(contrat.date_fin)}
                    </div>
                </td>
                
                <!-- Actions -->
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-1">
                        <!-- Visualiser -->
                        <button class="view-btn text-blue-600 hover:text-blue-900 p-2 rounded hover:bg-blue-50 transition-colors duration-200" 
                                data-id="${contrat.id}" 
                                title="Visualiser le contrat">
                            <i class="fas fa-eye"></i>
                        </button>
                        
                        <!-- Modifier -->
                        <button class="edit-btn text-green-600 hover:text-green-900 p-2 rounded hover:bg-green-50 transition-colors duration-200" 
                                data-id="${contrat.id}" 
                                data-employee-id="${contrat.id_employee}"
                                title="Modifier le contrat">
                            <i class="fas fa-edit"></i>
                        </button>
                        
                        <!-- Imprimer -->
                        <button class="print-btn text-purple-600 hover:text-purple-900 p-2 rounded hover:bg-purple-50 transition-colors duration-200" 
                                data-id="${contrat.id}" 
                                title="Imprimer le contrat">
                            <i class="fas fa-print"></i>
                        </button>
                        
                        <!-- Supprimer -->
                        <button class="delete-btn text-red-600 hover:text-red-900 p-2 rounded hover:bg-red-50 transition-colors duration-200" 
                                data-id="${contrat.id}" 
                                data-employee-id="${contrat.id_employee}"
                                data-nom="${contrat.nom}" 
                                data-prenom="${contrat.prenom}" 
                                data-ref="${contrat.ref || ''}"
                                data-poste="${contrat.poste}"
                                title="Supprimer le contrat">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `}).join('');

        this.attachActionListeners();
        // Notifier HistoriqueContrat que le tableau est prêt
        document.dispatchEvent(new CustomEvent('contratsRendered'));
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
                    <button onclick="showContrat.goToPage(${this.currentPage - 1})" 
                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 ${!this.currentPage > 1 ? 'opacity-50 cursor-not-allowed' : ''}"
                            ${this.currentPage <= 1 ? 'disabled' : ''}>
                        <i class="fas fa-chevron-left mr-1 text-xs"></i>
                        Précédent
                    </button>
                    <button onclick="showContrat.goToPage(${this.currentPage + 1})" 
                            class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 ${!this.currentPage < this.totalPages ? 'opacity-50 cursor-not-allowed' : ''}"
                            ${this.currentPage >= this.totalPages ? 'disabled' : ''}>
                        Suivant
                        <i class="fas fa-chevron-right ml-1 text-xs"></i>
                    </button>
                </div>
                
                <!-- Desktop -->
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Affichage de <span class="font-medium">${(this.currentPage - 1) * 100 + 1}</span>
                            à <span class="font-medium">${Math.min(this.currentPage * 100, this.totalRecords)}</span>
                            sur <span class="font-medium">${this.totalRecords}</span> contrats
                        </p>
                    </div>
                    <div>
                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
        `;

        // Bouton précédent
        paginationHTML += `
            <button onclick="showContrat.goToPage(${this.currentPage - 1})" 
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
                <button onclick="showContrat.goToPage(1)" 
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
                <button onclick="showContrat.goToPage(${i})" 
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ${i === this.currentPage ? 'bg-green-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0'}">
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
                <button onclick="showContrat.goToPage(${this.totalPages})" 
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                    ${this.totalPages}
                </button>
            `;
        }

        // Bouton suivant
        paginationHTML += `
            <button onclick="showContrat.goToPage(${this.currentPage + 1})" 
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
        this.loadContrats(page, this.searchTerm);
        
        // Scroll vers le haut du tableau
        const tableContainer = document.querySelector('#contrat-table-body');
        if (tableContainer) {
            tableContainer.scrollTop = 0;
        }
    }

    setupEventListeners() {
        // Barre de recherche avec debounce et focus automatique
        const searchInput = document.getElementById('search-contrat');
        let searchTimeout;
        
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.filterTable(e.target.value);
                }, 300);
            });

            // Touche Enter pour rechercher immédiatement
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    clearTimeout(searchTimeout);
                    this.filterTable(e.target.value);
                }
            });
        }

        // Bouton nouveau contrat
        const newContratBtn = document.getElementById('btn-new-contrat');
        if (newContratBtn) {
            newContratBtn.addEventListener('click', () => {
                if (ModalManager) {
                    ModalManager.openModal('new_contract');
                }
            });
        }

        // Focus sur la barre de recherche à l'ouverture du modal
        document.addEventListener('modalOpened', (e) => {
            if (e.detail === 'contrat' && searchInput) {
                setTimeout(() => {
                    searchInput.focus();
                    searchInput.select();
                }, 300);
            }
        });
    }

    attachActionListeners() {
        // Boutons visualiser
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                this.viewContrat(id);
            });
        });

        // Boutons modifier
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                const employeeId = e.currentTarget.getAttribute('data-employee-id');
                this.editContrat(id, employeeId);
            });
        });

        // Boutons imprimer
        document.querySelectorAll('.print-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                this.printContrat(id);
            });
        });

        // Boutons supprimer
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                const employeeId = e.currentTarget.getAttribute('data-employee-id');
                const nom = e.currentTarget.getAttribute('data-nom');
                const prenom = e.currentTarget.getAttribute('data-prenom');
                const ref = e.currentTarget.getAttribute('data-ref');
                const poste = e.currentTarget.getAttribute('data-poste');
                this.confirmDelete(id, employeeId, nom, prenom, ref, poste);
            });
        });
    }

    filterTable(searchTerm) {
        // Réinitialiser à la première page lors d'une nouvelle recherche
        this.loadContrats(1, searchTerm);
    }

    updateRecordCount() {
        const countElement = document.getElementById('record-count');
        if (countElement) {
            countElement.textContent = `${this.totalRecords} contrat(s) trouvé(s) - Page ${this.currentPage}/${this.totalPages}`;
        }
    }

    showLoading() {
        const tbody = document.getElementById('contrat-table-body');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mb-4"></div>
                            <p class="text-gray-600">Chargement des contrats...</p>
                            <p class="text-sm text-gray-400 mt-1">Veuillez patienter</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    hideLoading() {
        // Pas besoin d'action spécifique
    }

    showError(message) {
        const tbody = document.getElementById('contrat-table-body');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="px-6 py-8 text-center text-red-600">
                        <i class="fas fa-exclamation-triangle text-3xl mb-3"></i>
                        <p class="text-lg font-semibold mb-2">Erreur de chargement</p>
                        <p class="text-sm text-gray-600 max-w-md mx-auto">${message}</p>
                        <button onclick="showContrat.loadContrats()" 
                                class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                            <i class="fas fa-redo mr-2"></i>
                            Réessayer
                        </button>
                    </td>
                </tr>
            `;
        }
    }

    // Méthodes d'actions
    async viewContrat(id) {
        try {
            const response = await fetch(`php/contrat/show_contrat_details.php?id=${id}`);
            const result = await response.json();
            
            if (result.success) {
                this.showContratDetails(result.data);
            } else {
                if (NotificationManager) {
                    NotificationManager.show('error', 'Erreur', result.message);
                }
            }
        } catch (error) {
            console.error('Erreur lors du chargement des détails:', error);
            if (NotificationManager) {
                NotificationManager.show('error', 'Erreur', 'Erreur lors du chargement des détails');
            }
        }
    }

    showContratDetails(contrat) {
        const detailsContainer = document.getElementById('contrat-details');
        
        if (detailsContainer) {
            const status = this.getContratStatus(contrat);
            
            detailsContainer.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Informations Employé -->
                    <div class="p-6 rounded-lg shadow-sm border-l-4 border-blue-500 bg-blue-50">
                        <h3 class="text-lg font-semibold text-blue-700 mb-4 flex items-center">
                            <i class="fas fa-user-circle mr-3 text-blue-500"></i>
                            Informations de l'Employé
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-700">
                                    <i class="fas fa-id-card mr-2 text-blue-400"></i>
                                    Matricule:
                                </span>
                                <span class="font-semibold text-gray-900">${contrat.matricule || 'N/A'}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-700">
                                    <i class="fas fa-user-tag mr-2 text-blue-400"></i>
                                    Nom et Prénom:
                                </span>
                                <span class="font-semibold text-gray-900">${contrat.civilite || ''} ${contrat.nom || ''} ${contrat.prenom || ''}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-700">
                                    <i class="fas fa-calendar-alt mr-2 text-blue-400"></i>
                                    Date de naissance:
                                </span>
                                <span class="text-gray-900">${this.formatDate(contrat.date_naissance) || 'Non spécifié'}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Informations Contrat -->
                    <div class="p-6 rounded-lg shadow-sm border-l-4 border-green-500 bg-green-50">
                        <h3 class="text-lg font-semibold text-green-700 mb-4 flex items-center">
                            <i class="fas fa-file-contract mr-3 text-green-500"></i>
                            Informations du Contrat
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-700">
                                    <i class="fas fa-hashtag mr-2 text-green-400"></i>
                                    Référence:
                                </span>
                                <span class="font-bold text-green-800">${contrat.ref || 'N/R'}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-700">
                                    <i class="fas fa-briefcase mr-2 text-green-400"></i>
                                    Type Contrat:
                                </span>
                                <span class="font-semibold text-gray-900">${contrat.type_contrat || 'CDI'}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-700">
                                    <i class="fas fa-calendar-check mr-2 text-green-400"></i>
                                    Date Début:
                                </span>
                                <span class="font-semibold text-gray-900">${this.formatDate(contrat.date_debut) || 'N/A'}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-700">
                                    <i class="fas fa-calendar mr-2 text-green-400"></i>
                                    Date Fin:
                                </span>
                                <span class="font-semibold text-gray-900">${contrat.type_contrat === 'CDI' ? 'Indéterminé' : this.formatDate(contrat.date_fin)}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-700">
                                    <i class="fas fa-chart-line mr-2 text-green-400"></i>
                                    État:
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${status.class}">
                                    <i class="fas ${status.icon} mr-1"></i>
                                    ${status.text}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Ouvrir le modal
        if (ModalManager) {
            ModalManager.openModal('show-contrat');
            
            // Configurer le bouton d'impression dans le modal
            setTimeout(() => {
                const modalPrintBtn = document.querySelector('#modal-show-contrat .bg-blue-600');
                if (modalPrintBtn) {
                    modalPrintBtn.onclick = (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        this.printContratFromDetails(contrat.id);
                    };
                }
            }, 100);
        }
    }

    // Nouvelle méthode pour l'impression depuis les détails
    async printContratFromDetails(contratId) {
        console.log('Impression du contrat ID:', contratId);
        
        try {
            // Charger les données pour l'impression
            const response = await fetch(`php/contrat/print_contrat.php?id=${contratId}`);
            const result = await response.json();
            
            if (result.success) {
                this.generatePrintWindow(result.data);
            } else {
                console.error('Erreur lors du chargement:', result.message);
                if (NotificationManager) {
                    NotificationManager.show('error', 'Erreur', result.message);
                }
            }
        } catch (error) {
            console.error('Erreur:', error);
            if (NotificationManager) {
                NotificationManager.show('error', 'Erreur', 'Erreur de connexion');
            }
        }
    }

    

    editContrat(id, employeeId) {
        if (window.editContrat) { window.editContrat.open(id); }
        else { NotificationManager?.show('error','Erreur','Module modification non disponible'); }
    }

    confirmDelete(id, employeeId, nom, prenom, ref, poste) {
        if (window.deleteContrat) { window.deleteContrat.confirm(id, nom, prenom, ref, poste); }
        else { NotificationManager?.show('error','Erreur','Module suppression non disponible'); }
    }

    printContrat(id) {
        console.log('Imprimer contrat:', id);
        // Utiliser PrintContrat au lieu de l'ancienne méthode
        if (window.printContrat) {
            window.printContrat.printContrat(id);
        } else {
            console.error('Module d\'impression non disponible');
            if (NotificationManager) {
                NotificationManager.show('error', 'Erreur', 'Module d\'impression non disponible');
            }
        }
    }
}

// Initialisation globale
let showContrat = null;

document.addEventListener('DOMContentLoaded', () => {
    // Vérifier si le tableau existe sur la page
    if (document.getElementById('contrat-table-body')) {
        showContrat = new ShowContrat();
        window.showContrat = showContrat;
        console.log('ShowContrat initialisé avec succès');
    }
});

// Réinitialisation lors de l'ouverture du modal
document.addEventListener('modalOpened', (e) => {
    if (e.detail === 'contrat') {
        if (!showContrat) {
            showContrat = new ShowContrat();
            window.showContrat = showContrat;
        }
        
        // Recharger les données si le modal est rouvert
        setTimeout(() => {
            showContrat.loadContrats();
        }, 100);
    }
});