<!-- ══════════════════════════════════════════════════
     MODAL NOUVEAU CONTRAT  #modal-new_contract
     ══════════════════════════════════════════════════ -->
<div id="modal-new_contract" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="ct-card">

        <!-- Header -->
        <div class="ct-header">
            <div class="ct-header-inner">
                <div class="ct-header-icon">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div>
                    <div class="ct-header-title">NOUVEAU CONTRAT</div>
                    <div class="ct-header-sub">Création d'un contrat de travail</div>
                </div>
                <div class="ct-ref-badge" id="nc-ref-badge">
                    <i class="fas fa-hashtag"></i>
                    <span id="nc-ref-display">—</span>
                </div>
                <button class="ct-close modal-close-btn" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Bandeau employé -->
        <div class="ct-emp-banner">
            <div class="ct-emp-avatar-ph" id="nc-emp-avatar-wrap">
                <i class="fas fa-user"></i>
            </div>
            <div style="flex:1;min-width:0">
                <div class="ct-emp-name" id="nc-emp-name">— Sélectionnez un employé —</div>
                <div class="ct-emp-mat"  id="nc-emp-mat"></div>
            </div>
            <div class="ct-emp-search">
                <i class="fas fa-search"></i>
                <input type="text" id="nc-emp-search-input"
                       placeholder="Rechercher un employé…" autocomplete="off">
                <div class="ct-emp-dropdown" id="nc-emp-dropdown"></div>
            </div>
        </div>

        <!-- Body -->
        <div class="ct-body">
            <form id="form-new-contrat" autocomplete="off">
                <input type="hidden" name="id_employee" id="nc-id-employee" value="">

                <!-- Type CDI / CDD -->
                <div class="ct-section">
                    <div class="ct-sec-title">
                        <span class="ct-sec-icon"><i class="fas fa-toggle-on"></i></span>
                        Type de contrat
                    </div>
                    <div class="ct-type-toggle">
                        <button type="button" class="ct-type-btn active" data-val="CDI" id="nc-btn-cdi">
                            <i class="fas fa-infinity"></i> CDI
                            <span style="font-size:10px;font-weight:400;opacity:.75">Durée indéterminée</span>
                        </button>
                        <button type="button" class="ct-type-btn" data-val="CDD" id="nc-btn-cdd">
                            <i class="fas fa-hourglass-half"></i> CDD
                            <span style="font-size:10px;font-weight:400;opacity:.75">Durée déterminée</span>
                        </button>
                    </div>
                    <input type="hidden" name="type_contrat" id="nc-type-contrat" value="CDI">
                </div>

                <!-- Référence + Période d'essai -->
                <div class="ct-section">
                    <div class="ct-sec-title">
                        <span class="ct-sec-icon"><i class="fas fa-info-circle"></i></span>
                        Identifiant &amp; Période
                    </div>
                    <div class="ct-row ct-cols2">
                        <div class="ct-field">
                            <label class="ct-label"><i class="fas fa-hashtag"></i> Référence contrat</label>
                            <div class="ct-input-wrap">
                                <i class="ct-ico fas fa-hashtag"></i>
                                <input type="text" name="ref" id="nc-ref"
                                       class="ct-input" placeholder="Ex: 001/2025" maxlength="10">
                            </div>
                        </div>
                        <div class="ct-field">
                            <label class="ct-label"><i class="fas fa-clock"></i> Période d'essai <span class="ct-req">*</span></label>
                            <div class="ct-input-wrap">
                                <i class="ct-ico fas fa-clock"></i>
                                <select name="periode_essai" id="nc-periode-essai" class="ct-select" style="padding-left:30px">
                                    <option value="1 mois">1 mois</option>
                                    <option value="2 mois">2 mois</option>
                                    <option value="3 mois">3 mois</option>
                                    <option value="4 mois">4 mois</option>
                                    <option value="5 mois">5 mois</option>
                                    <option value="6 mois" selected>6 mois</option>
                                    <option value="1 an">1 an</option>
                                    <option value="Concluante">Concluante</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dates -->
                <div class="ct-section">
                    <div class="ct-sec-title">
                        <span class="ct-sec-icon"><i class="fas fa-calendar-alt"></i></span>
                        Dates du contrat
                    </div>
                    <div class="ct-row ct-cols2">
                        <div class="ct-field">
                            <label class="ct-label"><i class="fas fa-play-circle"></i> Date de début <span class="ct-req">*</span></label>
                            <div class="ct-input-wrap">
                                <i class="ct-ico fas fa-calendar-day"></i>
                                <input type="date" name="date_debut" id="nc-date-debut"
                                       class="ct-input" required>
                            </div>
                        </div>
                        <div class="ct-field" id="nc-date-fin-row">
                            <label class="ct-label"><i class="fas fa-stop-circle"></i> Date de fin <span class="ct-req" id="nc-fin-req">*</span></label>
                            <div class="ct-input-wrap">
                                <i class="ct-ico fas fa-calendar-check"></i>
                                <input type="date" name="date_fin" id="nc-date-fin" class="ct-input">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Poste + Affectation + Salaire -->
                <div class="ct-section">
                    <div class="ct-sec-title">
                        <span class="ct-sec-icon"><i class="fas fa-briefcase"></i></span>
                        Poste &amp; Rémunération
                    </div>
                    <div class="ct-row ct-cols2">
                        <div class="ct-field">
                            <label class="ct-label"><i class="fas fa-user-tie"></i> Poste <span class="ct-req">*</span></label>
                            <div class="ct-input-wrap">
                                <i class="ct-ico fas fa-user-tie"></i>
                                <input type="text" name="poste" id="nc-poste"
                                       class="ct-input" placeholder="Ex: Ingénieur" required>
                            </div>
                        </div>
                        <div class="ct-field">
                            <label class="ct-label"><i class="fas fa-money-bill-wave"></i> Salaire net (DA)</label>
                            <div class="ct-input-wrap">
                                <i class="ct-ico fas fa-coins"></i>
                                <input type="number" name="salaire" id="nc-salaire"
                                       class="ct-input" placeholder="Ex: 80000" min="0" step="100">
                            </div>
                        </div>
                    </div>
                    <div class="ct-row ct-cols1" style="margin-top:8px">
                        <div class="ct-field">
                            <label class="ct-label"><i class="fas fa-map-marker-alt"></i> Affectation <span class="ct-req">*</span></label>
                            <div class="ct-input-wrap">
                                <i class="ct-ico fas fa-building"></i>
                                <input type="text" name="affectation" id="nc-affectation"
                                       class="ct-input" placeholder="Ex: Direction des Ressources Humaines" required>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div><!-- /ct-body -->

        <!-- Footer -->
        <div class="ct-footer">
            <span class="ct-required-note"><span class="ct-req">*</span> Champs obligatoires</span>
            <div class="ct-footer-btns">
                <button type="button" class="ct-btn ct-btn-cancel" id="nc-btn-cancel">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button type="button" class="ct-btn ct-btn-submit" id="nc-btn-submit">
                    <i class="fas fa-plus-circle"></i> Créer le contrat
                </button>
            </div>
        </div>
    </div>
</div>