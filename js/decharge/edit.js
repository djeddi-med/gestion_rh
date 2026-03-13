// DNK/js/decharge/edit.js
class DechargeEdit {
    static async editDecharge(id) {
        try {
            console.log('Début modification décharge ID:', id);
            
            if (window.showLoadingNotification) {
                window.showLoadingNotification('Chargement des données...');
            }

            // CORRECTION: Utilisation de 'show.php' au lieu de 'edit.php'
            const response = await fetch(`php/decharge/show.php?id=${id}`);
            
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Données reçues pour modification:', data);

            if (data.success) {
                setTimeout(() => {
                    DechargeEdit.showEditModal(data.data);
                }, 100);
            } else {
                throw new Error(data.message || 'Erreur lors du chargement des données');
            }
        } catch (error) {
            console.error('Erreur modification:', error);
            if (window.showError) {
                window.showError('Erreur lors du chargement: ' + error.message);
            }
        }
    }

    static showEditModal(decharge) {
        console.log('Affichage modal modification pour:', decharge.reference);
        
        if (window.modalSystem && typeof window.modalSystem.createCustomModal === 'function') {
            try {
                const modalElement = window.modalSystem.createCustomModal(
                    'editDechargeModalContent',
                    `Modifier - ${decharge.reference}`,
                    'fas fa-edit'
                );

                setTimeout(() => {
                    DechargeEdit.setupEditModal(modalElement, decharge);
                }, 50);

            } catch (error) {
                console.error('Erreur création modal modification:', error);
                DechargeEdit.showEditModalFallback(decharge);
            }
        } else {
            DechargeEdit.showEditModalFallback(decharge);
        }
    }

    static setupEditModal(modalElement, decharge) {
        DechargeEdit.fillEditForm(modalElement, decharge);
        DechargeEdit.setupEditFormEvents(modalElement, decharge.id);
        
        console.log('Modal modification configuré avec succès');
    }

    static fillEditForm(modalElement, decharge) {
        const form = modalElement.querySelector('#editDechargeForm');
        if (form) {
            // Le script ignore simplement les champs en trop (ex: date_creation)
            form.querySelector('#editReference').value = decharge.reference || '';
            form.querySelector('#editName').value = decharge.name || '';
            form.querySelector('#editPoste').value = decharge.poste || '';
            form.querySelector('#editType').value = decharge.type || '';
            form.querySelector('#editUser').value = decharge.user || '';
            form.querySelector('#editStatut').value = decharge.statut || 'recevoire';
        }
    }

    static setupEditFormEvents(modalElement, dechargeId) {
        const form = modalElement.querySelector('#editDechargeForm');
        const cancelBtn = modalElement.querySelector('.cancel-edit-btn');

        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Soumission formulaire modification');
                DechargeEdit.handleEditFormSubmit(form, dechargeId);
            });
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Annulation modification');
                if (window.closeModal) {
                    window.closeModal();
                }
                if (window.showInfo) {
                    window.showInfo('Modification annulée');
                }
            });
        }
    }

    static async handleEditFormSubmit(form, dechargeId) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        console.log('Données formulaire modification:', data);

        if (!DechargeEdit.validateEditForm(data)) {
            return;
        }

        try {
            if (window.showLoadingNotification) {
                window.showLoadingNotification('Mise à jour en cours...');
            }

            const response = await fetch('php/decharge/update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ...data, id: dechargeId })
            });

            const result = await response.json();
            console.log('Réponse modification:', result);

            if (result.success) {
                if (window.showSuccess) {
                    window.showSuccess('Décharge modifiée avec succès');
                }
                
                if (window.closeModal) {
                    window.closeModal();
                }
                
                if (window.dechargeModal && typeof window.dechargeModal.loadDecharges === 'function') {
                    setTimeout(() => {
                        window.dechargeModal.loadDecharges();
                    }, 500);
                }
            } else {
                throw new Error(result.message || 'Erreur lors de la modification');
            }
        } catch (error) {
            console.error('Erreur modification:', error);
            if (window.showError) {
                window.showError('Erreur lors de la modification: ' + error.message);
            }
        }
    }

    static validateEditForm(data) {
        const required = ['reference', 'name', 'poste', 'type', 'user'];
        for (const field of required) {
            if (!data[field] || data[field].trim() === '') {
                if (window.showError) {
                    window.showError(`Le champ "${field}" est obligatoire`);
                }
                return false;
            }
        }
        return true;
    }

    static showEditModalFallback(decharge) {
        alert(`Modification de la décharge: ${decharge.reference}\n\nCette fonctionnalité nécessite le système de modals.`);
    }
}

window.editDecharge = DechargeEdit.editDecharge;