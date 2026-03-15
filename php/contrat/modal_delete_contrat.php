<!-- ══════════════════════════════════════════════════
     MODAL SUPPRIMER CONTRAT  #modal-delete-confirm
     ══════════════════════════════════════════════════ -->
<div id="modal-delete-confirm" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="ct-card" style="max-width:420px">

        <div class="ct-header" style="background:linear-gradient(135deg,#7f1d1d,#dc2626,#f87171)">
            <div class="ct-header-inner">
                <div class="ct-header-icon" style="background:rgba(255,255,255,.18)">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <div class="ct-header-title">SUPPRIMER CONTRAT</div>
                    <div class="ct-header-sub">Cette action est irréversible</div>
                </div>
                <button class="ct-close modal-close-btn" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="ct-body" style="padding:20px">
            <!-- Fiche résumé -->
            <div style="display:flex;align-items:center;gap:14px;background:#fef2f2;border:1.5px solid #fca5a5;
                        border-radius:12px;padding:12px 14px;margin-bottom:16px">
                <div style="width:44px;height:44px;border-radius:10px;background:#fee2e2;
                            display:flex;align-items:center;justify-content:center;
                            color:#dc2626;font-size:20px;flex-shrink:0">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div style="flex:1;min-width:0">
                    <div id="del-emp-name" style="font-size:14px;font-weight:800;color:#1f2937"></div>
                    <div id="del-contrat-info" style="font-size:11.5px;color:#6b7280;margin-top:2px"></div>
                </div>
            </div>
            <p style="font-size:13px;color:#374151;line-height:1.6;text-align:center;padding:0 10px">
                Vous êtes sur le point de <strong style="color:#dc2626">supprimer définitivement</strong>
                ce contrat. Cette action ne peut pas être annulée.
            </p>
        </div>

        <div class="ct-footer" style="justify-content:center;gap:12px">
            <button type="button" id="cancel-delete" class="ct-btn ct-btn-cancel" style="min-width:120px">
                <i class="fas fa-arrow-left"></i> Annuler
            </button>
            <button type="button" id="confirm-delete" class="ct-btn"
                    style="background:linear-gradient(135deg,#7f1d1d,#dc2626);color:#fff;
                           box-shadow:0 4px 14px rgba(220,38,38,.38);min-width:140px">
                <i class="fas fa-trash-alt"></i> Supprimer
            </button>
        </div>
    </div>
</div>