<!-- ═══════════════════════════════════════════════════════════════
     MODAL RECRUTEMENT EMPLOYÉ  — id="modal-add_employee"
     ═══════════════════════════════════════════════════════════════ -->
<div id="modal-add_employee" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
<div class="emp-modal-card">

    <!-- ── HEADER ── -->
    <div class="emp-modal-header">
        <div class="emp-modal-icon">
            <i class="fas fa-user-plus"></i>
        </div>
        <div style="position:relative;z-index:2">
            <div class="emp-modal-title">Nouveau Recrutement</div>
            <div class="emp-modal-subtitle">Renseignez les informations de l'employé(e)</div>
        </div>
        <div class="emp-matricule-badge" id="add-matricule-display">
            <i class="fas fa-id-badge" style="margin-right:5px;opacity:.7"></i>
            <span id="auto-matricule">—</span>
        </div>
        <button class="emp-modal-close modal-close-btn-add" title="Fermer">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- ── BODY ── -->
    <div class="emp-modal-body">
    <form id="form-add-employee" novalidate autocomplete="off">

        <!-- Photo centrée en haut -->
        <div class="emp-photo-wrap">
            <div class="emp-photo-frame" id="add-photo-frame" title="Cliquer pour choisir une photo">
                <img src="img/employee/user.png"
                     id="add-photo-preview"
                     onerror="this.src='img/employee/user.png'"
                     alt="Photo">
                <div class="emp-photo-overlay">
                    <i class="fas fa-camera"></i>
                    <span>Changer</span>
                </div>
            </div>
            <input type="file" id="add-photo-input" name="photo" accept="image/jpeg,image/png,image/gif" class="hidden">
            <div class="emp-photo-hint">
                <i class="fas fa-info-circle text-blue-400"></i>
                JPG, PNG · Max 2 Mo<br>La photo sera nommée <em>matricule.ext</em>
            </div>
        </div>

        <!-- ══ 1. IDENTITÉ ══ -->
        <div class="emp-section">
            <div class="emp-section-title">
                <div class="emp-section-icon"><i class="fas fa-user"></i></div>
                Identité
            </div>

            <!-- Civilité · Nom · Prénom (3 en ligne) -->
            <div class="emp-row civils">
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-venus-mars"></i> Civilité <span class="req">*</span></label>
                    <select name="civilite" class="emp-select" required>
                        <option value="">—</option>
                        <option value="Mr">Mr</option>
                        <option value="Mme">Mme</option>
                        <option value="Mlle">Mlle</option>
                    </select>
                </div>
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-font"></i> Nom <span class="req">*</span></label>
                    <div class="emp-input-group">
                        <i class="group-icon fas fa-font"></i>
                        <input type="text" name="nom" class="emp-input" placeholder="NOM" required
                               style="text-transform:uppercase">
                    </div>
                </div>
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-font"></i> Prénom <span class="req">*</span></label>
                    <div class="emp-input-group">
                        <i class="group-icon fas fa-font"></i>
                        <input type="text" name="prenom" class="emp-input" placeholder="Prénom" required>
                    </div>
                </div>
            </div>

            <!-- Date naissance · Lieu · N° Acte (3 en ligne) -->
            <div class="emp-date-toggle">
                <button type="button" class="emp-toggle-btn active" id="add-toggle-date">Date connue</button>
                <button type="button" class="emp-toggle-btn" id="add-toggle-presume">Année présumée</button>
            </div>
            <div class="emp-row birth">
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-calendar-day"></i> Date naissance</label>
                    <input type="date" name="date_naissance" id="add-date-naissance" class="emp-input">
                </div>
                <div class="emp-field" id="add-presume-field" style="display:none">
                    <label class="emp-label"><i class="fas fa-calendar-alt"></i> Année présumée</label>
                    <input type="number" name="presume" id="add-presume"
                           class="emp-input" placeholder="ex: 1985" min="1930" max="2010">
                </div>
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-map-marker-alt"></i> Lieu de naissance <span class="req">*</span></label>
                    <input type="text" name="lieu_naissance" class="emp-input" placeholder="Wilaya / Commune" required>
                </div>
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-file-alt"></i> N° Acte de naissance <span class="req">*</span></label>
                    <input type="text" name="no_acte_naissance" class="emp-input" placeholder="ex: 42/2024" required>
                </div>
            </div>

            <!-- Situation familiale · Nombre d'enfants (2 en ligne) -->
            <div class="emp-row family">
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-heart"></i> Situation familiale <span class="req">*</span></label>
                    <select name="situation_familiale" id="add-situation-familiale" class="emp-select" required>
                        <option value="">Sélectionner</option>
                        <option value="célibataire">Célibataire</option>
                        <option value="Marié(e)">Marié(e)</option>
                        <option value="divorcé(e)">Divorcé(e)</option>
                        <option value="veuf(ve)">Veuf/Veuve</option>
                    </select>
                </div>
                <div class="emp-field" id="add-enfants-field">
                    <label class="emp-label"><i class="fas fa-baby"></i> Nbre d'enfants</label>
                    <input type="number" name="nombre_enfants" class="emp-input"
                           placeholder="0" min="0" max="20" value="0">
                </div>
            </div>
        </div>

        <!-- ══ 2. FILIATION ══ -->
        <div class="emp-section">
            <div class="emp-section-title">
                <div class="emp-section-icon"><i class="fas fa-users"></i></div>
                Filiation
            </div>
            <!-- Prénom père · Nom prénom mère (2 en ligne) -->
            <div class="emp-row cols-2">
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-male"></i> Prénom du père <span class="req">*</span></label>
                    <input type="text" name="prenom_pere" class="emp-input" placeholder="Prénom" required>
                </div>
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-female"></i> Nom &amp; Prénom de la mère <span class="req">*</span></label>
                    <input type="text" name="nom_prenom_mere" class="emp-input" placeholder="NOM Prénom" required>
                </div>
            </div>
        </div>

        <!-- ══ 3. COORDONNÉES ══ -->
        <div class="emp-section">
            <div class="emp-section-title">
                <div class="emp-section-icon"><i class="fas fa-address-card"></i></div>
                Coordonnées
            </div>

            <!-- Adresse complète (1 ligne) -->
            <div class="emp-row cols-1">
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-home"></i> Adresse complète <span class="req">*</span></label>
                    <div class="emp-input-group">
                        <i class="group-icon fas fa-home"></i>
                        <input type="text" name="adresse" class="emp-input" placeholder="Rue, N°, Cité…" required>
                    </div>
                </div>
            </div>

            <!-- Wilaya · Téléphone (2 en ligne) -->
            <div class="emp-row contact">
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-map"></i> Wilaya de résidence <span class="req">*</span></label>
                    <select name="wilaya_residence" class="emp-select" required>
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
                        <input type="tel" name="telephone" class="emp-input"
                               placeholder="0X XX XX XX XX" maxlength="10" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ 4. ADMINISTRATIF ══ -->
        <div class="emp-section">
            <div class="emp-section-title">
                <div class="emp-section-icon"><i class="fas fa-id-card"></i></div>
                Informations administratives
            </div>

            <!-- CNAS · Compte à payer (2 en ligne) -->
            <div class="emp-row cols-2">
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-heartbeat"></i> N° Assurance CNAS <span class="req">*</span></label>
                    <div class="emp-input-group">
                        <i class="group-icon fas fa-shield-alt"></i>
                        <input type="text" name="no_assurance_cnas" class="emp-input"
                               placeholder="ex: 09123456789" required>
                    </div>
                </div>
                <div class="emp-field">
                    <label class="emp-label"><i class="fas fa-university"></i> Compte à payer <span class="req">*</span></label>
                    <div class="emp-input-group">
                        <i class="group-icon fas fa-credit-card"></i>
                        <input type="text" name="compte_a_paye" class="emp-input"
                               placeholder="Numéro de compte" required>
                    </div>
                </div>
            </div>
        </div>

    </form>
    </div><!-- /body -->

    <!-- ── FOOTER ── -->
    <div class="emp-modal-footer">
        <button type="button" class="emp-btn emp-btn-cancel modal-close-btn-add">
            <i class="fas fa-times"></i> Annuler
        </button>
        <button type="button" class="emp-btn emp-btn-submit" id="btn-add-employee-submit">
            <i class="fas fa-user-plus"></i> Recruter l'employé(e)
        </button>
    </div>

</div><!-- /card -->
</div><!-- /modal -->