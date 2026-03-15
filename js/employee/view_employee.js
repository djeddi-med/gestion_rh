/**
 * ViewEmployee — Modal de visualisation d'un employé
 * Affiche toutes les infos + historique contrats (dates desc, en vigueur / périmé)
 * Boutons : Imprimer fiche, Imprimer affectation
 */
class ViewEmployee {

    constructor() {
        this.currentEmployee = null;
        this.currentContrats = [];
        this.modal           = document.getElementById('modal-view-employee');
        if (!this.modal) return;
        this.bindClose();
        this.bindPrintButtons();
    }

    // ─────────────────────────────────────────
    // OUVRIR + CHARGER
    // ─────────────────────────────────────────
    async open(employeeId) {
        this._showModal();
        this._showSkeleton();

        try {
            const res  = await fetch(`php/employee/get_employee_details.php?id=${employeeId}`);
            const data = await res.json();

            if (!data.success) throw new Error(data.message);

            this.currentEmployee = data.employee;
            this.currentContrats = data.contrats;

            this._renderHeader(data.employee);
            this._renderFields(data.employee);
            this._renderContrats(data.contrats);
            this._showContent();

        } catch (e) {
            this._showSkeleton();
            document.getElementById('view-emp-skeleton').innerHTML = `
                <div class="text-center py-10 text-red-500">
                    <i class="fas fa-exclamation-triangle text-3xl mb-3 block"></i>
                    <p class="font-semibold">${e.message}</p>
                </div>`;
        }
    }

    // ─────────────────────────────────────────
    // RENDU HEADER
    // ─────────────────────────────────────────
    _renderHeader(emp) {
        const isActif = emp.etat === 'actif';

        // Photo
        document.getElementById('view-emp-photo').src =
            `img/employee/${emp.photo || 'user.png'}`;

        // Nom
        document.getElementById('view-emp-nom').textContent =
            `${emp.nom} ${emp.prenom}`;

        // Matricule
        document.getElementById('view-emp-matricule').textContent =
            `Matricule : ${emp.matricule || 'Non attribué'}`;

        // Civilité
        document.getElementById('view-emp-civilite').textContent = emp.civilite || '';

        // Badge état
        const etatBadge = document.getElementById('view-emp-etat-badge');
        const etatDot   = document.getElementById('view-emp-etat-dot');
        if (isActif) {
            etatBadge.textContent = 'Actif';
            etatBadge.className   = 'text-xs font-semibold bg-emerald-400 bg-opacity-90 px-2 py-0.5 rounded-full';
            etatDot.className     = 'absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-2 border-white bg-emerald-400 shadow';
        } else {
            etatBadge.textContent = 'Inactif';
            etatBadge.className   = 'text-xs font-semibold bg-red-400 bg-opacity-90 px-2 py-0.5 rounded-full';
            etatDot.className     = 'absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-2 border-white bg-red-400 shadow';
        }

        // Couleur header selon état
        const header = document.getElementById('view-emp-header');
        header.className = header.className.replace(
            /from-\S+ to-\S+/g,
            isActif
                ? 'from-blue-700 to-blue-900'
                : 'from-gray-600 to-gray-800'
        );
    }

    // ─────────────────────────────────────────
    // RENDU CHAMPS
    // ─────────────────────────────────────────
    _renderFields(emp) {
        const fmt = (v) => v || '<span class="text-gray-400 text-xs italic">—</span>';
        const fmtDate = (d) => d
            ? new Date(d).toLocaleDateString('fr-FR', { day:'2-digit', month:'long', year:'numeric' })
            : '<span class="text-gray-400 text-xs italic">—</span>';

        // Personnelles
        document.getElementById('vf-date-naissance').innerHTML  =
            emp.date_naissance ? fmtDate(emp.date_naissance)
            : emp.presume      ? `Année présumée : ${emp.presume}`
            : fmt(null);
        document.getElementById('vf-lieu-naissance').innerHTML  = fmt(emp.lieu_naissance);
        document.getElementById('vf-acte-naissance').innerHTML  = fmt(emp.no_acte_naissance);
        document.getElementById('vf-situation-familiale').innerHTML = fmt(emp.situation_familiale);
        document.getElementById('vf-nombre-enfants').innerHTML  = fmt(emp.nombre_enfants);

        // Filiation
        document.getElementById('vf-prenom-pere').innerHTML = fmt(emp.prenom_pere);
        document.getElementById('vf-nom-mere').innerHTML    = fmt(emp.nom_prenom_mere);

        // Coordonnées
        document.getElementById('vf-adresse').innerHTML   = fmt(emp.adresse);
        document.getElementById('vf-wilaya').innerHTML    = fmt(emp.wilaya_residence);
        document.getElementById('vf-telephone').innerHTML = fmt(emp.telephone);

        // Administratif
        document.getElementById('vf-cnas').innerHTML          = fmt(emp.no_assurance_cnas);
        document.getElementById('vf-compte').innerHTML        = fmt(emp.compte_a_paye);
        document.getElementById('vf-date-creation').innerHTML = fmtDate(emp.date_creation);
        document.getElementById('vf-user').innerHTML          = fmt(emp.user);
    }

    // ─────────────────────────────────────────
    // RENDU CONTRATS (timeline)
    // ─────────────────────────────────────────
    _renderContrats(contrats) {
        const list     = document.getElementById('vf-contrats-list');
        const noContrat= document.getElementById('vf-no-contrat');
        const count    = document.getElementById('vf-contrats-count');

        count.textContent = `${contrats.length} contrat${contrats.length !== 1 ? 's' : ''}`;

        if (!contrats.length) {
            list.innerHTML = '';
            noContrat.classList.remove('hidden');
            return;
        }

        noContrat.classList.add('hidden');
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        list.innerHTML = contrats.map((c, idx) => {
            const isCDI     = c.type_contrat === 'CDI' || !c.date_fin;
            const dateFin   = c.date_fin ? new Date(c.date_fin) : null;
            const enVigueur = isCDI || (dateFin && dateFin >= today);

            let cardClass, badgeClass, badgeIcon, badgeLabel;

            if (isCDI) {
                cardClass  = 'cdi';
                badgeClass = 'cdi';
                badgeIcon  = 'fa-infinity';
                badgeLabel = 'CDI — En vigueur';
            } else if (enVigueur) {
                cardClass  = 'en-vigueur';
                badgeClass = 'en-vigueur';
                badgeIcon  = 'fa-check-circle';
                badgeLabel = 'En vigueur';
            } else {
                cardClass  = 'perime';
                badgeClass = 'perime';
                badgeIcon  = 'fa-times-circle';
                badgeLabel = 'Périmé';
            }

            const fmtD = (d) => d
                ? new Date(d).toLocaleDateString('fr-FR', { day:'2-digit', month:'short', year:'numeric' })
                : '—';

            // Calcul durée
            let duree = '';
            if (c.date_debut && c.date_fin) {
                const debut = new Date(c.date_debut);
                const fin   = new Date(c.date_fin);
                const mois  = Math.round((fin - debut) / (1000 * 60 * 60 * 24 * 30));
                duree = mois < 12
                    ? `${mois} mois`
                    : `${Math.floor(mois / 12)} an${Math.floor(mois/12) > 1 ? 's' : ''} ${mois % 12 ? (mois % 12) + ' mois' : ''}`.trim();
            } else if (isCDI && c.date_debut) {
                const debut = new Date(c.date_debut);
                const mois  = Math.round((today - debut) / (1000 * 60 * 60 * 24 * 30));
                duree = `${Math.floor(mois / 12)} an${Math.floor(mois/12) > 1 ? 's' : ''} en cours`;
            }

            return `
            <div class="contrat-card ${cardClass}" style="animation-delay:${idx * 60}ms">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="contrat-num">${c.ref || `#${c.id}`}</span>
                        <span class="font-bold text-gray-800 text-sm">${c.type_contrat || '—'}</span>
                        ${idx === 0 ? '<span class="text-xs bg-yellow-100 text-yellow-700 font-semibold px-2 py-0.5 rounded-full"><i class="fas fa-star mr-1 text-[9px]"></i>Dernier</span>' : ''}
                    </div>
                    <span class="contrat-badge ${badgeClass}">
                        <i class="fas ${badgeIcon} text-[10px]"></i>${badgeLabel}
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-2 text-xs">
                    ${c.poste ? `
                    <div class="col-span-2 sm:col-span-4">
                        <span class="text-gray-400 font-semibold uppercase tracking-wide"><i class="fas fa-briefcase mr-1"></i>Poste</span>
                        <div class="font-semibold text-gray-700 mt-0.5">${c.poste}</div>
                    </div>` : ''}

                    ${c.affectation ? `
                    <div class="col-span-2">
                        <span class="text-gray-400 font-semibold uppercase tracking-wide"><i class="fas fa-building mr-1"></i>Affectation</span>
                        <div class="font-medium text-gray-600 mt-0.5">${c.affectation}</div>
                    </div>` : ''}

                    <div>
                        <span class="text-gray-400 font-semibold uppercase tracking-wide"><i class="fas fa-play mr-1"></i>Début</span>
                        <div class="font-medium text-gray-700 mt-0.5">${fmtD(c.date_debut)}</div>
                    </div>

                    <div>
                        <span class="text-gray-400 font-semibold uppercase tracking-wide"><i class="fas fa-stop mr-1"></i>Fin</span>
                        <div class="font-medium mt-0.5 ${isCDI ? 'text-blue-600 font-semibold' : enVigueur ? 'text-emerald-600' : 'text-red-500'}">
                            ${isCDI ? '<i class="fas fa-infinity mr-1 text-[10px]"></i>Indéterminée' : fmtD(c.date_fin)}
                        </div>
                    </div>

                    ${duree ? `
                    <div>
                        <span class="text-gray-400 font-semibold uppercase tracking-wide"><i class="fas fa-hourglass-half mr-1"></i>Durée</span>
                        <div class="font-medium text-gray-600 mt-0.5">${duree}</div>
                    </div>` : ''}

                    ${c.salaire ? `
                    <div>
                        <span class="text-gray-400 font-semibold uppercase tracking-wide"><i class="fas fa-money-bill mr-1"></i>Salaire</span>
                        <div class="font-semibold text-gray-700 mt-0.5">${parseFloat(c.salaire).toLocaleString('fr-DZ')} DA</div>
                    </div>` : ''}

                    ${c.periode_essai ? `
                    <div class="col-span-2">
                        <span class="text-gray-400 font-semibold uppercase tracking-wide"><i class="fas fa-clock mr-1"></i>Période d'essai</span>
                        <div class="font-medium text-gray-600 mt-0.5">${c.periode_essai}</div>
                    </div>` : ''}
                </div>
            </div>`;
        }).join('');
    }

    // ─────────────────────────────────────────
    // BOUTONS IMPRESSION
    // ─────────────────────────────────────────
    bindPrintButtons() {
        document.getElementById('btn-print-fiche')?.addEventListener('click', () => {
            if (!this.currentEmployee) return;
            window.open(`php/employee/print_fiche_employee.php?id=${this.currentEmployee.id}`, '_blank');
        });

        document.getElementById('btn-print-affectation')?.addEventListener('click', () => {
            if (!this.currentEmployee) return;
            // Prendre le dernier contrat qui a une affectation
            const contrat = this.currentContrats.find(c => c.affectation);
            if (!contrat) {
                NotificationManager.warning('Aucune affectation trouvée pour cet employé.', 'Affectation');
                return;
            }
            NotificationManager.info('Impression de l\'affectation en cours...', 'Impression');
            // TODO: window.open(`php/employee/print_affectation.php?id=${this.currentEmployee.id}`, '_blank');
        });
    }

    // ─────────────────────────────────────────
    // HELPERS MODAL
    // ─────────────────────────────────────────
    bindClose() {
        this.modal?.querySelectorAll('.modal-close-btn').forEach(btn => {
            btn.addEventListener('click', () => this.close());
        });
    }

    _showModal() {
        ModalManager.openModal('view-employee');
    }

    close() {
        ModalManager.closeModal('view-employee');
    }

    _showSkeleton() {
        document.getElementById('view-emp-skeleton').classList.remove('hidden');
        document.getElementById('view-emp-content').classList.add('hidden');
    }

    _showContent() {
        document.getElementById('view-emp-skeleton').classList.add('hidden');
        document.getElementById('view-emp-content').classList.remove('hidden');
    }
}

// ── Initialisation globale ──
let viewEmployee;
document.addEventListener('DOMContentLoaded', () => {
    viewEmployee = new ViewEmployee();
    window.viewEmployee = viewEmployee;
});