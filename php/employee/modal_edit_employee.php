<!-- ═══════════════════════════════════════════════════════════════
     MODAL MODIFICATION EMPLOYÉ  — id="modal-edit-employee"
     ═══════════════════════════════════════════════════════════════ -->
<div id="modal-edit-employee" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
<div class="emp-modal-card">

    <!-- ── HEADER ── -->
    <div class="emp-modal-header" style="background:linear-gradient(135deg,#064e3b 0%,#059669 60%,#34d399 100%)">
        <div class="emp-modal-icon">
            <i class="fas fa-user-edit"></i>
        </div>
        <div style="position:relative;z-index:2">
            <div class="emp-modal-title">Modifier l'Employé(e)</div>
            <div class="emp-modal-subtitle" id="edit-emp-subtitle">Mise à jour des informations</div>
        </div>
        <div class="emp-matricule-badge" id="edit-matricule-display"
             style="background:rgba(255,255,255,.2);border-color:rgba(255,255,255,.35);">
            <i class="fas fa-id-badge" style="margin-right:5px;opacity:.7"></i>
            <span id="edit-matricule-val">—</span>
        </div>
        <button class="emp-modal-close modal-close-btn-edit" title="Fermer">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- ── BODY ── -->
    <div class="emp-modal-body">
    <form id="form-edit-employee" novalidate autocomplete="off">
        <input type="hidden" name="id" id="edit-emp-id">

        <!-- Photo -->
        <div class="emp-photo-wrap">
            <div class="emp-photo-frame has-photo" id="edit-photo-frame" title="Cliquer pour changer la photo">
                <img src="img/employee/user.png"
                     id="edit-photo-preview"
                     onerror="this.src='img/employee/user.png'"
                     alt="Photo">
                <div class="emp-photo-overlay">
                    <i class="fas fa-camera"></i>
                    <span>Changer</span>
                </div>
            </div>
            <input type="file" id="edit-photo-input" name="photo" accept="image/jpeg,image/png,image/gif" class="hidden">
            <div class="emp-photo-hint">
                <i class="fas fa-info-circle text-emerald-400"></i>
                Laisser vide pour garder la photo actuelle
            </div>
        </div>

        <!-- ══ 1. IDENTITÉ ══ -->
        <div class="emp-section">
            <div class="emp-section-title" style="color:#065f46;border-color:#a7f3d0">
                <div class="emp-section-icon" style="background:#ecfdf5;color:#059669"><i class="fas fa-user"></i></div>
                Identité
            </div>

            <div class="emp-row civils">
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-venus-mars"></i> Civilité <span class="req">*</span></label>
                    <select name="civilite" id="edit-civilite" class="emp-select" required>
                        <option value="Mr">Mr</option>
                        <option value="Mme">Mme</option>
                        <option value="Mlle">Mlle</option>
                    </select>
                </div>
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-font"></i> Nom <span class="req">*</span></label>
                    <div class="emp-input-group">
                        <i class="group-icon fas fa-font"></i>
                        <input type="text" name="nom" id="edit-nom" class="emp-input" required style="text-transform:uppercase">
                    </div>
                </div>
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-font"></i> Prénom <span class="req">*</span></label>
                    <div class="emp-input-group">
                        <i class="group-icon fas fa-font"></i>
                        <input type="text" name="prenom" id="edit-prenom" class="emp-input" required>
                    </div>
                </div>
            </div>

            <div class="emp-date-toggle">
                <button type="button" class="emp-toggle-btn active" id="edit-toggle-date">Date connue</button>
                <button type="button" class="emp-toggle-btn" id="edit-toggle-presume">Année présumée</button>
            </div>
            <div class="emp-row birth">
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-calendar-day"></i> Date naissance</label>
                    <input type="date" name="date_naissance" id="edit-date-naissance" class="emp-input">
                </div>
                <div class="emp-field" id="edit-presume-field" style="display:none">
                    <label class="emp-label"><i class="fas fa-calendar-alt"></i> Année présumée</label>
                    <input type="number" name="presume" id="edit-presume"
                           class="emp-input" placeholder="ex: 1985" min="1930" max="2010">
                </div>
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-map-marker-alt"></i> Lieu de naissance <span class="req">*</span></label>
                    <input type="text" name="lieu_naissance" id="edit-lieu-naissance" class="emp-input" required>
                </div>
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-file-alt"></i> N° Acte de naissance <span class="req">*</span></label>
                    <input type="text" name="no_acte_naissance" id="edit-no-acte" class="emp-input" required>
                </div>
            </div>

            <div class="emp-row family">
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-heart"></i> Situation familiale <span class="req">*</span></label>
                    <select name="situation_familiale" id="edit-situation-familiale" class="emp-select" required>
                        <option value="célibataire">Célibataire</option>
                        <option value="Marié(e)">Marié(e)</option>
                        <option value="divorcé(e)">Divorcé(e)</option>
                        <option value="veuf(ve)">Veuf/Veuve</option>
                    </select>
                </div>
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-baby"></i> Nbre d'enfants</label>
                    <input type="number" name="nombre_enfants" id="edit-nombre-enfants"
                           class="emp-input" min="0" max="20" value="0">
                </div>
            </div>
        </div>

        <!-- ══ 2. FILIATION ══ -->
        <div class="emp-section">
            <div class="emp-section-title" style="color:#065f46;border-color:#a7f3d0">
                <div class="emp-section-icon" style="background:#ecfdf5;color:#059669"><i class="fas fa-users"></i></div>
                Filiation
            </div>
            <div class="emp-row cols-2">
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-male"></i> Prénom du père <span class="req">*</span></label>
                    <input type="text" name="prenom_pere" id="edit-prenom-pere" class="emp-input" required>
                </div>
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-female"></i> Nom &amp; Prénom de la mère <span class="req">*</span></label>
                    <input type="text" name="nom_prenom_mere" id="edit-nom-mere" class="emp-input" required>
                </div>
            </div>
        </div>

        <!-- ══ 3. COORDONNÉES ══ -->
        <div class="emp-section">
            <div class="emp-section-title" style="color:#065f46;border-color:#a7f3d0">
                <div class="emp-section-icon" style="background:#ecfdf5;color:#059669"><i class="fas fa-address-card"></i></div>
                Coordonnées
            </div>
            <div class="emp-row cols-1">
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-home"></i> Adresse complète <span class="req">*</span></label>
                    <div class="emp-input-group">
                        <i class="group-icon fas fa-home"></i>
                        <input type="text" name="adresse" id="edit-adresse" class="emp-input" required>
                    </div>
                </div>
            </div>
            <div class="emp-row contact">
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-map"></i> Wilaya de résidence <span class="req">*</span></label>
                    <select name="wilaya_residence" id="edit-wilaya" class="emp-select" required>
                        <option value="">Sélectionner</option>
                        <?php
                        $wilayas = ['Adrar','Chlef','Laghouat','Oum El Bouaghi','Batna','Béjaïa','Biskra',
                            'Béchar','Blida','Bouira','Tamanrasset','Tébessa','Tlemcen','Tiaret','Tizi Ouzou',
                            'Alger','Djelfa','Jijel','Sétif','Saïda','Skikda','Sidi Bel Abbès','Annaba',
                            'Guelma','Constantine','Médéa','Mostaganem','M\'Sila','Mascara','Ouargla','Oran',
                            'El Bayadh','Illizi','Bordj Bou Arréridj','Boumerdès','El Tarf','Tindouf',
                            'Tissemsilt','El Oued','Khenchela','Souk Ahras','Tipaza','Mila','Aïn Defla',
                            'Naâma','Aïn Témouchent','Ghardaïa','Relizane','Timimoun','Bordj Badji Mokhtar',
                            'Ouled Djellal','Béni Abbès','Aïn Salah','Aïn Guezzam','Touggourt','Djanet',
                            'El M\'Ghair','El Menia'];
                        foreach ($wilayas as $w):
                        ?>
                        <option value="<?= htmlspecialchars($w) ?>"><?= htmlspecialchars($w) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-phone"></i> Téléphone <span class="req">*</span></label>
                    <div class="emp-input-group">
                        <i class="group-icon fas fa-phone"></i>
                        <input type="tel" name="telephone" id="edit-telephone"
                               class="emp-input" maxlength="10" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ 4. ADMINISTRATIF ══ -->
        <div class="emp-section">
            <div class="emp-section-title" style="color:#065f46;border-color:#a7f3d0">
                <div class="emp-section-icon" style="background:#ecfdf5;color:#059669"><i class="fas fa-id-card"></i></div>
                Informations administratives
            </div>
            <div class="emp-row cols-2">
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-heartbeat"></i> N° Assurance CNAS <span class="req">*</span></label>
                    <div class="emp-input-group">
                        <i class="group-icon fas fa-shield-alt"></i>
                        <input type="text" name="no_assurance_cnas" id="edit-cnas" class="emp-input" required>
                    </div>
                </div>
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-university"></i> Compte à payer <span class="req">*</span></label>
                    <div class="emp-input-group">
                        <i class="group-icon fas fa-credit-card"></i>
                        <input type="text" name="compte_a_paye" id="edit-compte" class="emp-input" required>
                    </div>
                </div>
            </div>

            <!-- Statut -->
            <div class="emp-row cols-2">
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-toggle-on"></i> Statut</label>
                    <select name="etat" id="edit-etat" class="emp-select">
                        <option value="actif">Actif</option>
                        <option value="inactif">Inactif</option>
                    </select>
                </div>
            </div>
        </div>

    </form>
    </div><!-- /body -->

    <!-- ── FOOTER ── -->
    <div class="emp-modal-footer">
        <button type="button" class="emp-btn emp-btn-cancel modal-close-btn-edit">
            <i class="fas fa-times"></i> Annuler
        </button>
        <button type="button" class="emp-btn emp-btn-submit" id="btn-edit-employee-submit"
                style="background:linear-gradient(135deg,#065f46,#059669);box-shadow:0 4px 14px rgba(5,150,105,.35)">
            <i class="fas fa-save"></i> Enregistrer les modifications
        </button>
    </div>

</div>
</div>