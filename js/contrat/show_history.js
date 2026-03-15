/**
 * js/contrat/show_history.js
 * Classe HistoriqueContrat
 *
 * Gère le modal #modal-employee-history :
 *  - Ouverture au clic sur une ligne du tableau contrats
 *  - Chargement des données via php/contrat/get_employee_history.php
 *  - Affichage fiche employé + tableau historique
 *  - Suppression d'un contrat depuis le tableau historique
 */
class HistoriqueContrat {
    constructor() {
        this._currentEmployeeId = null;
        this._deleting          = false;
        this._init();
    }

    /* ══ Initialisation ══════════════════════════════════════ */
    _init() {
        this._bindModalClose();

        // Écouter quand showContrat a rendu le tableau → rendre les lignes cliquables
        document.addEventListener('contratsRendered', () => this._makeRowsClickable());

        // Aussi écouter le rechargement via modalOpened
        document.addEventListener('modalOpened', e => {
            if (e.detail === 'contrat') {
                setTimeout(() => this._makeRowsClickable(), 300);
            }
        });
    }

    /* ══ Rendre les lignes cliquables ════════════════════════ */
    _makeRowsClickable() {
        const tbody = document.getElementById('contrat-table-body');
        if (!tbody) return;

        tbody.querySelectorAll('tr[data-employee-id]').forEach(tr => {
            if (tr.dataset.histBound) return;
            tr.dataset.histBound = '1';

            tr.addEventListener('click', e => {
                if (e.target.closest('button')) return;
                const empId = tr.dataset.employeeId;
                if (!empId) return;
                this.open(empId);
            });
        });
    }

    /* ══ Ouvrir le modal ═════════════════════════════════════ */
    open(employeeId) {
        this._currentEmployeeId = employeeId;

        // Ouvrir le modal manuellement (contourne ModalManager pour z-index custom)
        const modal   = document.getElementById('modal-employee-history');
        const overlay = document.getElementById('modal-overlay');
        if (!modal) { console.error('modal-employee-history introuvable'); return; }

        overlay?.classList.remove('hidden');
        modal.classList.remove('hidden');
        setTimeout(() => {
            overlay?.classList.add('opacity-100');
            const card = modal.querySelector('.hc-card');
            card?.classList.remove('scale-95', 'opacity-0');
            card?.classList.add('scale-100', 'opacity-100');
        }, 10);
        document.body.style.overflow = 'hidden';

        // Réinitialiser UI
        this._showSkeleton();
        document.getElementById('hc-header-sub').textContent = 'Chargement…';
        document.getElementById('hc-count-num').textContent  = '—';

        this._load(employeeId);
    }

    /* ══ Fermer ══════════════════════════════════════════════ */
    close() {
        const modal   = document.getElementById('modal-employee-history');
        const overlay = document.getElementById('modal-overlay');
        if (!modal) return;

        const card = modal.querySelector('.hc-card');
        card?.classList.remove('scale-100','opacity-100');
        card?.classList.add('scale-95','opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
            overlay?.classList.add('hidden');
            overlay?.classList.remove('opacity-100');
            document.body.style.overflow = '';
        }, 260);
    }

    _bindModalClose() {
        document.addEventListener('click', e => {
            const btn = e.target.closest('.hc-close-btn, .hc-btn-close');
            if (btn && btn.closest('#modal-employee-history')) {
                e.stopPropagation();
                this.close();
            }
        });

        // Escape key
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                const modal = document.getElementById('modal-employee-history');
                if (modal && !modal.classList.contains('hidden')) this.close();
            }
        });
    }

    /* ══ Chargement données ══════════════════════════════════ */
    async _load(employeeId) {
        try {
            const r    = await fetch(`php/contrat/get_employee_history.php?employee_id=${employeeId}`);
            const text = await r.text();
            let d;
            try { d = JSON.parse(text); }
            catch(_) {
                console.error('[Historique] Réponse non-JSON:', text.substring(0,300));
                this._showError('Erreur de réponse serveur');
                return;
            }

            if (d.success) {
                this._renderEmployee(d.employee);
                this._renderTable(d.contrats, d.total);
            } else {
                this._showError(d.message);
            }
        } catch(e) {
            console.error('[Historique]', e);
            this._showError('Connexion impossible');
        }
    }

    /* ══ Rendu fiche employé ═════════════════════════════════ */
    _renderEmployee(emp) {
        const sub = document.getElementById('hc-header-sub');
        if (sub) sub.textContent = `${emp.civilite || ''} ${emp.nom} ${emp.prenom}`.trim();

        const strip = document.getElementById('hc-emp-strip');
        if (!strip) return;

        const photo     = emp.photo || 'user.png';
        const actif     = emp.etat === 'actif';
        const badgeCls  = actif ? '' : 'off';
        const badgeTxt  = actif ? 'Actif' : 'Inactif';

        strip.innerHTML = `
            <div class="hc-emp-photo-wrap">
                <img class="hc-emp-photo"
                     src="img/employee/${photo}"
                     onerror="this.src='img/employee/user.png'"
                     alt="${emp.nom}">
                <span class="hc-emp-badge ${badgeCls}" title="${badgeTxt}"></span>
            </div>

            <div class="hc-emp-meta">
                <div class="hc-emp-name">
                    ${emp.civilite || ''} ${emp.nom} ${emp.prenom}
                </div>
                <div class="hc-emp-mat">
                    <i class="fas fa-id-badge"></i>
                    ${emp.matricule}
                </div>
                <div class="hc-emp-chips">
                    ${emp.poste_actuel ? `
                    <span class="hc-chip hc-chip-blue">
                        <i class="fas fa-briefcase"></i>
                        ${emp.poste_actuel}
                    </span>` : ''}
                    ${emp.affectation_actuelle ? `
                    <span class="hc-chip hc-chip-amber">
                        <i class="fas fa-building"></i>
                        ${emp.affectation_actuelle}
                    </span>` : ''}
                    <span class="hc-chip" style="background:${actif?'#d1fae5':'#f1f5f9'};color:${actif?'#065f46':'#64748b'};border:1px solid ${actif?'#a7f3d0':'#e2e8f0'}">
                        <i class="fas fa-circle" style="font-size:7px;color:${actif?'#10b981':'#94a3b8'}"></i>
                        ${badgeTxt}
                    </span>
                </div>
            </div>
        `;
    }

    /* ══ Rendu tableau historique ════════════════════════════ */
    _renderTable(contrats, total) {
        // Compteur
        const numEl = document.getElementById('hc-count-num');
        if (numEl) numEl.textContent = total;

        const sub = document.getElementById('hc-toolbar-sub');
        if (sub) sub.textContent = `${total} contrat(s)`;

        const area = document.getElementById('hc-table-container');
        if (!area) return;

        if (!contrats || contrats.length === 0) {
            area.innerHTML = `
                <div class="hc-empty">
                    <div class="hc-empty-ico"><i class="fas fa-folder-open"></i></div>
                    <h3>Aucun contrat</h3>
                    <p>Cet employé n'a pas encore de contrat enregistré.</p>
                </div>`;
            // Footer info
            const fi = document.getElementById('hc-footer-info');
            if (fi) fi.innerHTML = '<i class="fas fa-info-circle"></i> Aucun contrat trouvé';
            return;
        }

        const today = new Date(); today.setHours(0,0,0,0);

        const rows = contrats.map((c, idx) => {
            const isFirst  = idx === 0;
            const typeCls  = c.type_contrat === 'CDI' ? 'hc-type-cdi' : 'hc-type-cdd';
            const status   = this._calcStatus(c, today);

            const dateFin  = c.type_contrat === 'CDI'
                ? `<span class="hc-date-indef"><i class="fas fa-infinity" style="font-size:10px"></i> Indéterminé</span>`
                : (c.date_fin
                    ? `<div class="hc-date">${this._fmt(c.date_fin)}</div>`
                    : `<span class="hc-date-indef">—</span>`);

            const duration = this._calcDuration(c.date_debut, c.date_fin, c.type_contrat);

            return `
            <tr data-id="${c.id}" class="${isFirst ? 'hc-row-first' : ''}">
                <!-- Référence -->
                <td>
                    ${c.ref
                        ? `<span class="hc-ref">${c.ref}</span>`
                        : `<span class="hc-ref-nr">N/R</span>`}
                    ${isFirst ? `<span class="hc-latest-tag"><i class="fas fa-star" style="font-size:7px"></i> Actuel</span>` : ''}
                </td>

                <!-- Type -->
                <td>
                    <span class="hc-type ${typeCls}">
                        <i class="fas ${c.type_contrat==='CDI'?'fa-infinity':'fa-hourglass-half'}" style="font-size:10px"></i>
                        ${c.type_contrat}
                    </span>
                </td>

                <!-- Date début -->
                <td>
                    <div class="hc-date">${this._fmt(c.date_debut)}</div>
                    ${duration ? `<div class="hc-duration"><i class="fas fa-stopwatch"></i>${duration}</div>` : ''}
                </td>

                <!-- Date fin -->
                <td>${dateFin}</td>

                <!-- État -->
                <td>
                    <span class="hc-etat ${status.cls}">
                        <i class="fas ${status.ico}"></i>
                        ${status.txt}
                    </span>
                </td>

                <!-- Action supprimer -->
                <td class="center">
                    <button class="hc-del-btn" data-id="${c.id}" title="Supprimer ce contrat">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>`;
        }).join('');

        area.innerHTML = `
            <table class="hc-table">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Type</th>
                        <th>Date début</th>
                        <th>Date fin</th>
                        <th>État</th>
                        <th class="center">Action</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>`;

        // Footer info
        const actifs   = contrats.filter(c => this._isActive(c, today)).length;
        const expires  = total - actifs;
        const fi = document.getElementById('hc-footer-info');
        if (fi) fi.innerHTML = `
            <i class="fas fa-info-circle"></i>
            <span>${total} contrat(s) —</span>
            <span style="color:#065f46;font-weight:700">${actifs} en vigueur</span>
            <span style="color:#64748b"> · </span>
            <span style="color:#991b1b">${expires} périmé(s)</span>`;

        // Lier les boutons supprimer
        area.querySelectorAll('.hc-del-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                e.stopPropagation();
                this._confirmDelete(btn.dataset.id, contrats, total);
            });
        });
    }

    /* ══ Statut contrat ══════════════════════════════════════ */
    _calcStatus(c, today) {
        if (this._isActive(c, today)) {
            return { cls:'hc-etat-ok',  ico:'fa-circle-check', txt:'En vigueur' };
        }
        return { cls:'hc-etat-exp', ico:'fa-clock',       txt:'Périmé' };
    }

    _isActive(c, today) {
        if (c.type_contrat === 'CDI') return c.etat === 'actif';
        if (!c.date_fin) return c.etat === 'actif';
        const fin = new Date(c.date_fin); fin.setHours(0,0,0,0);
        return fin >= today && c.etat === 'actif';
    }

    /* ══ Durée ═══════════════════════════════════════════════ */
    _calcDuration(debut, fin, type) {
        if (!debut) return '';
        const d1 = new Date(debut);
        const d2 = fin ? new Date(fin) : (type === 'CDI' ? null : null);
        if (!d2) return type === 'CDI' ? '' : '';

        const months = (d2.getFullYear() - d1.getFullYear()) * 12
                     + (d2.getMonth() - d1.getMonth());
        if (months <= 0) return '';
        if (months < 12) return `${months} mois`;
        const y = Math.floor(months / 12);
        const m = months % 12;
        return m > 0 ? `${y} an${y>1?'s':''} ${m} mois` : `${y} an${y>1?'s':''}`;
    }

    /* ══ Format date ═════════════════════════════════════════ */
    _fmt(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        if (isNaN(d)) return dateStr;
        return d.toLocaleDateString('fr-FR', { day:'2-digit', month:'2-digit', year:'numeric' });
    }

    /* ══ Skeleton ════════════════════════════════════════════ */
    _showSkeleton() {
        const strip = document.getElementById('hc-emp-strip');
        if (strip) strip.innerHTML = `
            <div class="hc-skeleton">
                <div class="hc-skel-box" style="width:62px;height:74px;flex-shrink:0"></div>
                <div style="flex:1">
                    <div class="hc-skel-box" style="width:60%;height:16px;margin-bottom:8px;border-radius:6px"></div>
                    <div class="hc-skel-box" style="width:35%;height:11px;border-radius:5px"></div>
                </div>
            </div>`;

        const area = document.getElementById('hc-table-container');
        if (area) area.innerHTML = `
            <div class="hc-loading">
                <div class="hc-spinner"></div>
                <span>Chargement de l'historique…</span>
            </div>`;
    }

    _showError(msg) {
        const area = document.getElementById('hc-table-container');
        if (area) area.innerHTML = `
            <div class="hc-empty">
                <div class="hc-empty-ico" style="color:#f43f5e;border-color:#fca5a5">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 style="color:#991b1b">Erreur de chargement</h3>
                <p>${msg}</p>
            </div>`;
    }

    /* ══ Suppression depuis l'historique ═════════════════════ */
    _confirmDelete(id, contrats, total) {
        const c = contrats.find(x => x.id == id);
        if (!c) return;

        // Petite confirmation inline (toast + confirm natif simple)
        const ok = confirm(
            `Supprimer le contrat ${c.ref || 'N/R'} (${c.type_contrat}) ?\n` +
            `Début : ${this._fmt(c.date_debut)}` +
            (c.date_fin ? ` — Fin : ${this._fmt(c.date_fin)}` : '') +
            `\n\nCette action est irréversible.`
        );
        if (!ok) return;
        this._doDelete(id);
    }

    async _doDelete(id) {
        if (this._deleting) return;
        this._deleting = true;

        // Griser le bouton
        const btn = document.querySelector(`#hc-table-container [data-id="${id}"].hc-del-btn`);
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; }

        try {
            const r    = await fetch('php/contrat/delete_contrat.php', {
                method : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body   : JSON.stringify({ id })
            });
            const text = await r.text();
            let d;
            try { d = JSON.parse(text); }
            catch(_) { NotificationManager.show('error','Serveur','Réponse inattendue'); return; }

            if (d.success) {
                NotificationManager.show('success','Supprimé','Contrat supprimé avec succès');
                // Recharger l'historique
                this._showSkeleton();
                await this._load(this._currentEmployeeId);
                // Rafraîchir aussi le tableau principal
                window.showContrat?.loadContrats(
                    window.showContrat.currentPage,
                    window.showContrat.searchTerm
                );
            } else {
                NotificationManager.show('error','Erreur', d.message);
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-trash-alt"></i>'; }
            }
        } catch(e) {
            console.error(e);
            NotificationManager.show('error','Connexion','Impossible de joindre le serveur');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-trash-alt"></i>'; }
        } finally {
            this._deleting = false;
        }
    }
}

/* ══ Initialisation globale ══════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    window.historiqueContrat = new HistoriqueContrat();
});