<!-- Modal RECRUTEMENT -->
<div id="modal-add_employee" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-3">
<div class="rec-card">
    <div class="rec-header">
        <div class="rec-header-inner">
            <div class="rec-header-icon"><i class="fas fa-user-plus"></i></div>
            <div>
                <div class="rec-header-title">Nouveau Recrutement</div>
                <div class="rec-header-sub">Renseignez toutes les informations de l'employé(e)</div>
            </div>
            <div class="rec-mat-badge"><i class="fas fa-id-badge"></i><span id="auto-matricule">—</span></div>
            <button class="rec-close modal-close-btn" title="Fermer"><i class="fas fa-times"></i></button>
        </div>
    </div>
    <div class="rec-body">
    <form id="form-add-employee" novalidate autocomplete="off">
        <div class="rec-photo-row">
            <div class="rec-photo-wrap">
                <div class="rec-photo-frame" id="add-photo-frame">
                    <img src="img/employee/user.png" id="add-photo-preview" onerror="this.src='img/employee/user.png'" alt="Photo">
                    <div class="rec-photo-overlay"><i class="fas fa-camera"></i><span>Ajouter photo</span></div>
                </div>
                <input type="file" id="add-photo-input" name="photo" accept="image/jpeg,image/png,image/gif" class="hidden">
                <p class="rec-photo-hint"><i class="fas fa-info-circle"></i> JPG · PNG · Max 2 Mo<br><em>Nommée automatiquement matricule.ext</em></p>
            </div>
        </div>

        <div class="rec-section">
            <div class="rec-sec-title">
                <div class="rec-sec-icon" style="background:#eff6ff;color:#2563eb"><i class="fas fa-user"></i></div>
                <span>Identité</span>
            </div>
            <div class="rec-row rec-civils">
                <div class="rec-field">
                    <label class="rec-label"><i class="fas fa-venus-mars"></i> Civilité <span class="rec-req">*</span></label>
                    <select name="civilite" class="rec-select" required>
                        <option value="">—</option>
                        <option value="Mr">Mr</option>
                        <option value="Mme">Mme</option>
                        <option value="Mlle">Mlle</option>
                    </select>
                </div>
                <div class="rec-field">
                    <label class="rec-label"><i class="fas fa-font"></i> Nom <span class="rec-req">*</span></label>
                    <div class="rec-input-wrap"><i class="rec-ico fas fa-font"></i>
                        <input type="text" name="nom" class="rec-input" placeholder="NOM" required style="text-transform:uppercase">
                    </div>
                </div>
                <div class="rec-field">
                    <label class="rec-label"><i class="fas fa-font"></i> Prénom <span class="rec-req">*</span></label>
                    <div class="rec-input-wrap"><i class="rec-ico fas fa-font"></i>
                        <input type="text" name="prenom" class="rec-input" placeholder="Prénom" required>
                    </div>
                </div>
            </div>
            <div class="rec-toggle-bar">
                <button type="button" class="rec-toggle-btn active" id="add-toggle-date"><i class="fas fa-calendar-day"></i> Date connue</button>
                <button type="button" class="rec-toggle-btn" id="add-toggle-presume"><i class="fas fa-calendar-alt"></i> Année présumée</button>
            </div>
            <div class="rec-row rec-birth">
                <div class="rec-field" id="add-date-field">
                    <label class="rec-label"><i class="fas fa-calendar-day"></i> Date de naissance</label>
                    <input type="date" name="date_naissance" id="add-date-naissance" class="rec-input">
                </div>
                <div class="rec-field" id="add-presume-field" style="display:none">
                    <label class="rec-label"><i class="fas fa-calendar-alt"></i> Année présumée</label>
                    <input type="number" name="presume" id="add-presume" class="rec-input" placeholder="ex: 1985" min="1930" max="2010" disabled>
                </div>
                <div class="rec-field">
                    <label class="rec-label"><i class="fas fa-map-marker-alt"></i> Lieu de naissance <span class="rec-req">*</span></label>
                    <input type="text" name="lieu_naissance" class="rec-input" placeholder="Wilaya / Commune" required>
                </div>
                <div class="rec-field">
                    <label class="rec-label"><i class="fas fa-file-alt"></i> N° Acte de naissance <span class="rec-req">*</span></label>
                    <input type="text" name="no_acte_naissance" class="rec-input" placeholder="ex: 42/2024" required>
                </div>
            </div>
            <div class="rec-row rec-family">
                <div class="rec-field">
                    <label class="rec-label"><i class="fas fa-heart"></i> Situation familiale <span class="rec-req">*</span></label>
                    <select name="situation_familiale" id="add-sit-fam" class="rec-select" required>
                        <option value="">Sélectionner</option>
                        <option value="célibataire">Célibataire</option>
                        <option value="Marié(e)">Marié(e)</option>
                        <option value="divorcé(e)">Divorcé(e)</option>
                        <option value="veuf(ve)">Veuf / Veuve</option>
                    </select>
                </div>
                <div class="rec-field">
                    <label class="rec-label"><i class="fas fa-baby"></i> Nbre d'enfants</label>
                    <input type="number" name="nombre_enfants" class="rec-input" min="0" max="20" value="0">
                </div>
            </div>
        </div>

        <div class="rec-section">
            <div class="rec-sec-title">
                <div class="rec-sec-icon" style="background:#f5f3ff;color:#7c3aed"><i class="fas fa-users"></i></div>
                <span>Filiation</span>
            </div>
            <div class="rec-row rec-cols2">
                <div class="rec-field">
                    <label class="rec-label"><i class="fas fa-male"></i> Prénom du père <span class="rec-req">*</span></label>
                    <input type="text" name="prenom_pere" class="rec-input" placeholder="Prénom" required>
                </div>
                <div class="rec-field">
                    <label class="rec-label"><i class="fas fa-female"></i> Nom &amp; Prénom de la mère <span class="rec-req">*</span></label>
                    <input type="text" name="nom_prenom_mere" class="rec-input" placeholder="NOM Prénom" required>
                </div>
            </div>
        </div>

        <div class="rec-section">
            <div class="rec-sec-title">
                <div class="rec-sec-icon" style="background:#ecfdf5;color:#059669"><i class="fas fa-address-card"></i></div>
                <span>Coordonnées</span>
            </div>
            <div class="rec-row rec-cols1">
                <div class="rec-field">
                    <label class="rec-label"><i class="fas fa-home"></i> Adresse complète <span class="rec-req">*</span></label>
                    <div class="rec-input-wrap"><i class="rec-ico fas fa-home"></i>
                        <input type="text" name="adresse" class="rec-input" placeholder="Rue, N°, Cité, Commune…" required>
                    </div>
                </div>
            </div>
            <div class="rec-row rec-cols2">
                <div class="rec-field">
                    <label class="rec-label"><i class="fas fa-map"></i> Wilaya de résidence <span class="rec-req">*</span></label>
                    <select name="wilaya_residence" class="rec-select" required>
                        <option value="">Sélectionner une wilaya</option>
                        <?php
                        $wilayas = ['Adrar','Chlef','Laghouat','Oum El Bouaghi','Batna','Béjaïa','Biskra','Béchar','Blida','Bouira','Tamanrasset','Tébessa','Tlemcen','Tiaret','Tizi Ouzou','Alger','Djelfa','Jijel','Sétif','Saïda','Skikda','Sidi Bel Abbès','Annaba','Guelma','Constantine','Médéa','Mostaganem','M\'Sila','Mascara','Ouargla','Oran','El Bayadh','Illizi','Bordj Bou Arréridj','Boumerdès','El Tarf','Tindouf','Tissemsilt','El Oued','Khenchela','Souk Ahras','Tipaza','Mila','Aïn Defla','Naâma','Aïn Témouchent','Ghardaïa','Relizane','Timimoun','Bordj Badji Mokhtar','Ouled Djellal','Béni Abbès','Aïn Salah','Aïn Guezzam','Touggourt','Djanet','El M\'Ghair','El Menia'];
                        foreach ($wilayas as $w): ?>
                        <option value="<?= htmlspecialchars($w) ?>"><?= htmlspecialchars($w) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="rec-field">
                    <label class="rec-label"><i class="fas fa-phone"></i> Téléphone <span class="rec-req">*</span></label>
                    <div class="rec-input-wrap"><i class="rec-ico fas fa-phone"></i>
                        <input type="tel" name="telephone" class="rec-input" placeholder="0X XX XX XX XX" maxlength="10" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="rec-section">
            <div class="rec-sec-title">
                <div class="rec-sec-icon" style="background:#fff7ed;color:#ea580c"><i class="fas fa-id-card"></i></div>
                <span>Informations administratives</span>
            </div>
            <div class="rec-row rec-cols2">
                <div class="rec-field">
                    <label class="rec-label"><i class="fas fa-heartbeat"></i> N° Assurance CNAS <span class="rec-req">*</span></label>
                    <div class="rec-input-wrap"><i class="rec-ico fas fa-shield-alt"></i>
                        <input type="text" name="no_assurance_cnas" class="rec-input" placeholder="ex: 09123456789" required>
                    </div>
                </div>
                <div class="rec-field">
                    <label class="rec-label"><i class="fas fa-university"></i> Compte à payer <span class="rec-req">*</span></label>
                    <div class="rec-input-wrap"><i class="rec-ico fas fa-credit-card"></i>
                        <input type="text" name="compte_a_paye" class="rec-input" placeholder="Numéro de compte" required>
                    </div>
                </div>
            </div>
        </div>
    </form>
    </div>
    <div class="rec-footer">
        <span class="rec-required-note"><span style="color:#ef4444">*</span> Champs obligatoires</span>
        <div class="rec-footer-btns">
            <button type="button" class="rec-btn rec-btn-cancel modal-close-btn"><i class="fas fa-times"></i> Annuler</button>
            <button type="button" class="rec-btn rec-btn-submit" id="btn-add-employee-submit">
                <i class="fas fa-user-plus"></i> <span>Recruter l'employé(e)</span>
            </button>
        </div>
    </div>
</div>
</div>
