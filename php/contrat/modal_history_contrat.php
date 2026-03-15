<!-- ════════════════════════════════════════════════════════════
     MODAL HISTORIQUE DES CONTRATS  #modal-employee-history
     z-index: 9999 → au-dessus de tous les autres modals
     ════════════════════════════════════════════════════════════ -->
<div id="modal-employee-history"
     class="fixed inset-0 hidden flex items-center justify-center p-3"
     style="z-index:9999">
    <div class="hc-modal-wrap hc-card scale-95 opacity-0 transform transition-all duration-300">

        <!-- Accent bar top -->
        <div class="hc-accent-bar"></div>

        <!-- Header -->
        <div class="hc-header">
            <div class="hc-header-left">
                <div class="hc-header-icon"><i class="fas fa-history"></i></div>
                <div>
                    <div class="hc-header-title">Historique des Contrats</div>
                    <div class="hc-header-sub" id="hc-header-sub">—</div>
                </div>
            </div>
            <div class="hc-header-right">
                <div class="hc-count-pill">
                    <i class="fas fa-file-contract"></i>
                    <span class="num" id="hc-count-num">—</span>
                    <span>contrat(s)</span>
                </div>
                <button class="hc-close-btn modal-close-btn" type="button" title="Fermer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Fiche employé -->
        <div class="hc-emp-strip" id="hc-emp-strip">
            <div class="hc-skeleton">
                <div class="hc-skel-box" style="width:62px;height:74px;flex-shrink:0;border-radius:11px"></div>
                <div style="flex:1">
                    <div class="hc-skel-box" style="width:55%;height:16px;margin-bottom:8px;border-radius:6px"></div>
                    <div class="hc-skel-box" style="width:32%;height:11px;border-radius:5px"></div>
                </div>
            </div>
        </div>

        <!-- Zone tableau -->
        <div class="hc-table-area">
            <!-- Toolbar -->
            <div class="hc-table-toolbar">
                <div class="hc-toolbar-left">
                    <i class="fas fa-list-alt"></i>
                    Historique complet
                    <span id="hc-toolbar-sub" style="color:#64748b;font-weight:400;text-transform:none;letter-spacing:0;font-size:11px"></span>
                </div>
                <div class="hc-toolbar-right">
                    <i class="fas fa-sort-amount-down"></i>
                    Du plus récent au plus ancien
                </div>
            </div>

            <!-- Contenu table (rendu par JS) -->
            <div id="hc-table-container">
                <div class="hc-loading">
                    <div class="hc-spinner"></div>
                    <span>Chargement…</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="hc-footer">
            <div class="hc-footer-info" id="hc-footer-info">
                <i class="fas fa-info-circle"></i> —
            </div>
            <button class="hc-btn-close modal-close-btn" type="button">
                <i class="fas fa-times"></i> Fermer
            </button>
        </div>

    </div>
</div>