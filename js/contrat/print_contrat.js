class PrintContrat {
    constructor() {
        this.currentContrat = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('#modal-show-contrat .bg-blue-600') || 
                e.target.closest('#modal-show-contrat .fa-print')) {
                e.preventDefault();
                e.stopPropagation();
                
                const modal = document.getElementById('modal-show-contrat');
                const contratId = modal?.getAttribute('data-contrat-id');
                
                if (contratId) {
                    this.printContrat(contratId);
                }
            }
        });
    }

    async printContrat(contratId) {
        try {
            if (NotificationManager) {
                NotificationManager.show('info', 'Impression', 'Préparation du contrat...');
            }
            
            const response = await fetch(`php/contrat/print_contrat.php?id=${contratId}`);
            const result = await response.json();
            
            if (result.success) {
                this.currentContrat = result.data;
                this.generateWordDocument();
            } else {
                if (NotificationManager) {
                    NotificationManager.show('error', 'Erreur', result.message);
                }
            }
        } catch (error) {
            console.error('Erreur lors du chargement du contrat:', error);
            if (NotificationManager) {
                NotificationManager.show('error', 'Erreur', 'Erreur lors du chargement du contrat');
            }
        }
    }

    generateWordDocument() {
        const contrat = this.currentContrat;
        if (!contrat) return;

        const htmlContent = this.createWordHTML(contrat);
        
        const printWindow = window.open('', '_blank', 'width=900,height=700');
        
        if (!printWindow) {
            if (NotificationManager) {
                NotificationManager.show('error', 'Erreur', 'Veuillez autoriser les popups pour l\'impression');
            }
            return;
        }
        
        printWindow.document.open();
        printWindow.document.write(htmlContent);
        printWindow.document.close();
        
        printWindow.focus();
    }

    createWordHTML(contrat) {
        const isCDI = contrat.type_contrat === 'CDI';
        const civilite = contrat.civilite === 'Mme' ? 'MADAME' : 'MONSIEUR';
        const civiliteLower = contrat.civilite === 'Mme' ? 'Madame' : 'Monsieur';
        
        // Formater les dates
        const dateDebut = contrat.date_debut ? new Date(contrat.date_debut) : new Date();
        const dateDebutFormatted = dateDebut.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });
        
        const dateNaissance = contrat.date_naissance ? new Date(contrat.date_naissance) : null;
        const dateNaissanceFormatted = (dateNaissance && !isNaN(dateNaissance.getTime())) ? 
            dateNaissance.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            }) : 'Non spécifié';
        
        // Date de fin pour CDD
        let dateFinFormatted = '';
        if (!isCDI && contrat.date_fin) {
            const dateFin = new Date(contrat.date_fin);
            dateFinFormatted = dateFin.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });
        }
        
        // Format de référence
        let refFormatted = 'Réf: ----/DARH/2022';
        if (contrat.ref) {
           const [numero, annee] = contrat.ref.split('/');
            refFormatted = `${numero}/DARH/${annee}`;
            //refFormatted = `Réf: ${contrat.ref}`;
        }
        
        // Salaire formaté
        const salaireFormatted = contrat.salaire && !isNaN(contrat.salaire) ? 
            parseInt(contrat.salaire).toLocaleString('fr-DZ') + ' DA' : 
            'Non spécifié';

        // Données pour QR Code
        const qrData = encodeURIComponent(
            `Nom: ${contrat.nom || ''} ${contrat.prenom || ''}|` +
            `Fonction: ${contrat.poste || ''}|` +
            `Type: ${contrat.type_contrat || ''}|` +
            `Début: ${dateDebutFormatted}|` +
            `Fin: ${dateFinFormatted || 'Indéterminé'}|` +
            `Affectation: ${contrat.affectation || ''}|` +
            `Matricule: ${contrat.matricule || ''}`
        );

        return `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Audiowide|ABeeZee|Roboto|Montserrat|Poppins|Open+Sans" rel="stylesheet">

    <title>Contrat ${contrat.ref || contrat.matricule}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Google+Sans:ital,opsz,wght@0,17..18,400..700;1,17..18,400..700&display=swap');

        @page {
            size: A4;
            margin: 0.5cm !important;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
             font-family: 'ABeeZee', sans-serif;
            font-optical-sizing: auto;
            font-weight: 300;
            font-style: normal;
            font-variation-settings:
                "GRAD" 0;
            color: #000;
            background: white;
            width: 21cm;
            height: 29.7cm;
            position: relative;
        }
        
        .page {
            width: 20cm;
            height: 28.7cm;
            position: relative;
            overflow: hidden;
            border: 1px solid transparent;
        }
        
        /* Filigrane */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.1;
            z-index: -1;
            pointer-events: none;
        }
        
        .watermark img {
            width: 400px;
            height: auto;
        }
        
        /* En-tête */
        .page-header {
            width: 100%;
            height: 120px;
            position: relative;
            margin-bottom: 20px;
        }
        
        .page-header img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }
        
        /* Pied de page */
        .page-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 80px;
        }
        
        .page-footer img {
            width: 100%;
            height: 80px;
            object-fit: cover;
        }
        
        /* Contenu principal */
        .content {
            position: absolute;
            top: 140px;
            bottom: 100px;
            left: 0.5cm;
            right: 0.5cm;
            overflow: hidden;
        }
        
        /* Référence */
        .contract-ref {
            margin-bottom: 15px;
            color: #666;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        /* Titre principal */
        .contract-title {
            font-family: "Audiowide", sans-serif;
            text-align: center;
            font-size: 16pt;
            font-weight: 200;
            text-transform: uppercase;
            margin: 20px 0 30px 0;
            padding: 15px;
            border: 2px solid #000;
            letter-spacing: 2px;
        }
        
        /* Informations personnelles */
        .personal-info {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ccc;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .info-item {
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            color: #333;
            display: inline-block;
            min-width: 120px;
        }
        
        /* QR Code */
        .qr-container {
            position: absolute;
            right: 0.5cm;
            width: 100px;
            height: 100px;
            border: 1px solid #ddd;
            padding: 5px;
            background: white;
            z-index: 10;
            BOTTOM: 201PX;
        }
        
        .qr-container img {
            width: 100%;
            height: 100%;
        }
        
        .qr-label {
            font-size: 8pt;
            text-align: center;
            margin-top: 2px;
            color: #666;
        }
        
        /* Clauses */
        .clause-section {
            margin: 25px 0;
        }
        
        .clause-title {
            font-weight: 300;
            font-size: 12pt;
            margin-bottom: 5px;
            padding-bottom: 3px;
            border-bottom: 1px solid #eee;
            color: #2c3e50;
        }
        
        .clause-content {
            margin-left: 10px;
            text-align: justify;
            margin-bottom: 15px;
            font-size: 11pt;
        }
        
        .clause-content p {
            margin-bottom: 8px;
        }
        
        /* Mise en évidence */
        .highlight {
            font-weight: 300;
            background-color: #fffacd;
            padding: 0 3px;
            border-radius: 2px;
        }
        
        /* Signatures */
        .signature-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #000;
        }
        
        .signature-container {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .signature-box {
            width: 45%;
            text-align: center;
        }
        
        .signature-line {
            width: 200px;
            border-top: 1px solid #000;
            margin: 20px auto 5px;
        }
        
        .signature-label {
            font-size: 10pt;
            color: #666;
        }
        
        /* Section approbation */
        .approval-section {
            margin-top: 30px;
            padding: 20px;
            border: 1px dashed #000;
            border-radius: 4px;
            background: #fff;
        }
        
        .checkbox-container {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .checkbox-square {
            width: 15px;
            height: 15px;
            border: 1px solid #000;
            margin-right: 10px;
            margin-top: 2px;
            flex-shrink: 0;
        }
        
        /* Espace administratif */
        .admin-section {
            margin-top: 30px;
            padding: 15px;
            border: 1px solid #ccc;
            background: #f5f5f5;
            border-radius: 4px;
        }
        
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            font-size: 10pt;
        }
        
        .admin-field {
            margin-bottom: 5px;
        }
        
        .admin-label {
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }
        
        .admin-line {
            border-bottom: 1px solid #999;
            min-height: 18px;
        }
        
        /* Numérotation des pages */
        .page-number {
            position: absolute;
            bottom: 85px;
            right: 1cm;
            font-size: 9pt;
            color: #666;
        }
        
        /* Contrôles d'interface */
        .controls {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            z-index: 1000;
            display: flex;
            gap: 15px;
            border: 1px solid #ddd;
        }
        
        .controls button {
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 300;
            font-family: 'Source Sans Pro', sans-serif;
            font-size: 12pt;
            transition: all 0.3s ease;
        }
        
        .print-btn {
            background-color: #3498db;
            color: white;
        }
        
        .print-btn:hover {
            background-color: #2980b9;
        }
        
        .download-btn {
            background-color: #27ae60;
            color: white;
        }
        
        .download-btn:hover {
            background-color: #219653;
        }
        
        .close-btn {
            background-color: #95a5a6;
            color: white;
        }
        
        .close-btn:hover {
            background-color: #7f8c8d;
        }
        
        /* Masquer les contrôles lors de l'impression */
        @media print {
            .controls {
                display: none !important;
            }
            body {
                margin: 0 !important;
                padding: 0 !important;
                width: 21cm !important;
                height: 29.7cm !important;
            }
            .page {
                page-break-after: always;
            }
        }
        
        /* Style pour la prévisualisation */
        @media screen {
            body {
                padding: 20px;
                background: #f0f2f5;
            }
            .page {
                background: white;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
                margin-bottom: 20px;
            }
        }
        
        /* Listes */
        ul {
            margin-left: 25px;
            margin-top: 5px;
            margin-bottom: 5px;
        }
        
        li {
            margin-bottom: 3px;
        }
        
        /* Éléments de mise en forme */
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .mb-10 {
            margin-bottom: 10px;
        }
        
        .mb-20 {
            margin-bottom: 20px;
        }
        
        .mt-20 {
            margin-top: 20px;
        }
        
        .pt-10 {
            padding-top: 10px;
        }
        
        .pb-10 {
            padding-bottom: 10px;
        }
        
        .border-top {
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        
        .font-weig{
            font-size: x-large;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Contrôles d'interface -->
    <div class="controls" id="controls">
        <button class="print-btn" onclick="window.print()">🖨️ Imprimer</button>
        <button class="close-btn" onclick="window.close()">✖️ Fermer</button>
    </div>

    <!-- PAGE 1 - Page de garde -->
    <div class="page">
        <!-- Filigrane -->
        <div class="watermark">
            <img src="img/logo/logo.png" alt="Logo filigrane">
        </div>
        
        <!-- En-tête -->
        <div class="page-header">
            <img src="img/logo/head.png" alt="En-tête">
        </div>
        
        <!-- QR Code (premier) -->
        <div class="qr-container">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${qrData}" alt="QR Code">
            <div class="qr-label">Contrat ${refFormatted}</div>
        </div>
        
        <!-- Contenu principal -->
        <div class="content">
            <!-- Référence -->
            <div class="contract-ref">
                <b>Réf:</b> ${refFormatted} 
            </div>
            
            <!-- Titre -->
            <div class="contract-title">
                CONTRAT DE TRAVAIL À DURÉE INDÉTERMINÉE (CDI)

            </div>
            
            <!-- Informations personnelles -->
            <div class="personal-info">
                <h3 style="text-align: center; margin-bottom: 20px; font-weight: 300;">INFORMATIONS DU CONTRACTANT</h3>
                
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Matricule:</span>
                        <span class="highlight">${contrat.matricule || 'N/A'}</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Nom & Prénom:</span>
                        <span class="highlight">${contrat.nom || ''} ${contrat.prenom || ''}</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Date naissance:</span>
                        <span class="highlight">${dateNaissanceFormatted}</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Lieu naissance:</span>
                        <span class="highlight">${contrat.lieu_naissance || 'Non spécifié'}</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Fonction:</span>
                        <span class="highlight">${contrat.poste || 'Non spécifié'}</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Type contrat:</span>
                        <span class="highlight">${isCDI ? 'CDI' : 'CDD'}</span>
                    </div>
                    
                    ${contrat.affectation ? `
                    <div class="info-item">
                        <span class="info-label">Affectation:</span>
                        <span class="highlight">${contrat.affectation}</span>
                    </div>
                    ` : ''}
                    
                    ${contrat.salaire ? `
                    <div class="info-item">
                        <span class="info-label">Salaire net:</span>
                        <span class="highlight">${salaireFormatted}</span>
                    </div>
                    ` : ''}
                    
                    <div class="info-item">
                        <span class="info-label">Date début:</span>
                        <span class="highlight">${dateDebutFormatted}</span>
                    </div>
                    
                    ${!isCDI && dateFinFormatted ? `
                    <div class="info-item">
                        <span class="info-label">Date fin:</span>
                        <span class="highlight">${dateFinFormatted}</span>
                    </div>
                    ` : ''}
                </div>
            </div>
            
            <!-- Introduction -->
            <div class="clause-section">
                <p class="text-center mb-20">
                    <strong>ENTREPRISE:</strong> EPE EL DJAMIAYA LINAKL OUA EL KHADAMET SPA<br>
                    <strong>ET</strong><br>
                    <strong>EMPLOYÉ(E):</strong> ${civilite} ${contrat.nom || ''} ${contrat.prenom || ''}
                </p>
                
                <p class="text-center">
                    Le présent contrat d'engagement est conclu pour une durée 
                    <span class="highlight">${isCDI ? 'INDÉTERMINÉE' : 'DÉTERMINÉE'}</span>
                    conformément aux dispositions de la loi N°90-11 du 21 Avril 1990.
                </p>
            </div>
            
            <!-- Cadre juridique -->
            <div class="personal-info mt-20">
                <p class="text-center">
                    <strong>CADRE JURIDIQUE:</strong><br>
                    Titre III - Chapitre I, Article ${isCDI ? '11' : '12'} 
                    de la loi relative aux relations de travail
                </p>
            </div>
        </div>
        
        <!-- Pied de page -->
        <div class="page-footer">
            <img src="img/logo/footer.png" alt="Pied de page">
        </div>
        
        <!-- Numéro de page -->
        <div class="page-number">Page 1</div>
    </div>

    <!-- PAGE 2 - Clauses 1 à 5 -->
    <div class="page">
        <!-- Filigrane -->
        <div class="watermark">
            <img src="img/logo/logo.png" alt="Logo filigrane">
        </div>
        
        <!-- En-tête -->
        <div class="page-header">
            <img src="img/logo/head.png" alt="En-tête">
        </div>
        
        <!-- Contenu principal -->
        <div class="content">
            <div class="clause-section">
                <div class="clause-content">
                    Conformément aux dispositions de la loi N°90-11 du 21 Avril 1990, modifiée et complétée, relatives aux relations de travail, 
                    notamment en son titre III - chapitre I, article 11 concernant la relation de travail à durée indéterminée, il est conclu :
                    <P class="font-weig"><B>Entre :</B></P>
                </div>
                <div class="clause-content">
                    L'Entreprise El Djamaiya Linakl Oua El Khadamet sise à 280 logements CNEP El Hamma les Halles – Alger, représentée par son Directeur Général
                    <strong>Mr CHORFI BILEL</strong>.
                    <P class="font-weig"><B>Et : </B></P>
                </div>
                        
                <div class="clause-content">
                        <strong> ${civilite} ${contrat.nom || ''} ${contrat.prenom || ''} </strong> 
                        né le ${dateNaissanceFormatted} à ${contrat.lieu_naissance || 'Non spécifié'} 
                        demeurant à ${contrat.adresse || 'Non spécifié'}
                        Le présent contrat d'engagement est conclu pour une durée indéterminée. 
                </div>
            </div>
            <!-- Clause 1 -->
            <div class="clause-section">
                <div class="clause-title">CLAUSE 1 : ENGAGEMENT</div>
                <div class="clause-content">
                    <p>${civiliteLower} : <span class="highlight">${contrat.nom || ''} ${contrat.prenom || ''}</span> est engagé${contrat.civilite === 'Mme' ? 'e' : ''} à compter du <span class="highlight">${dateDebutFormatted}</span>${isCDI ? '' : ' pour une durée de Douze (12) mois,'} en qualité de <span class="highlight">${contrat.poste || 'Non spécifié'}</span>.</p>
                    ${isCDI ? '' : '<p>L\'intéressé(e) est recruté(e) pour l\'exécution d\'un travail à durée limitée.</p>'}
                </div>
            </div>
            
            <!-- Clause 2 -->
            <div class="clause-section">
                <div class="clause-title">CLAUSE 2 : AFFECTATION</div>
                <div class="clause-content">
                    <p>Le contractant est affecté au <span class="highlight">${contrat.affectation || 'Non spécifié'}</span>.</p>
                    <p>Cette affectation pourra être modifiée par l'Employeur en fonction des besoins du service, dans le respect des dispositions légales.</p>
                </div>
            </div>
            
            <!-- Clause 3 -->
            <div class="clause-section">
                <div class="clause-title">CLAUSE 3 : RÉMUNÉRATION</div>
                <div class="clause-content">
                    <p>Le contractant percevra un salaire net mensuel de <span class="highlight">${salaireFormatted}</span>.</p>
                    <p>Le paiement du salaire sera effectué mensuellement, à terme échu, selon les modalités en vigueur dans l'Entreprise.</p>
                </div>
            </div>
            
            <!-- Clause 4 -->
            <div class="clause-section">
                <div class="clause-title">CLAUSE 4 : CONGÉS ANNUELS</div>
                <div class="clause-content">
                    <p>Le contractant bénéficiera de trente (30) jours calendaires payés par année de service au titre de congé annuel.</p>
                    <p>Les congés seront pris selon les besoins du service et après accord de la hiérarchie.</p>
                </div>
            </div>
            
            <!-- Clause 5 -->
            <div class="clause-section">
                <div class="clause-title">CLAUSE 5 : PÉRIODE D'ESSAI</div>
                <div class="clause-content">
                    <p>Les <span class="highlight">${isCDI ? 'Six (06)' : 'Trois (03)'}</span> premiers mois seront considérés comme période d'essai.</p>
                    <p>Cette période permet aux deux parties d'évaluer l'adaptation réciproque.</p>
                </div>
            </div>
        </div>
        
        <!-- Pied de page -->
        <div class="page-footer">
            <img src="img/logo/footer.png" alt="Pied de page">
        </div>
        
        <!-- Numéro de page -->
        <div class="page-number">Page 2</div>
    </div>

    <!-- PAGE 3 - Clauses 6 à 10 -->
    <div class="page">
        <!-- Filigrane -->
        <div class="watermark">
            <img src="img/logo/logo.png" alt="Logo filigrane">
        </div>
        
        <!-- En-tête -->
        <div class="page-header">
            <img src="img/logo/head.png" alt="En-tête">
        </div>
        
        <!-- Contenu principal -->
        <div class="content">
            
            <!-- Clause 6 -->
            <div class="clause-section">
                <div class="clause-title">CLAUSE 6 : PÉRIODE PROBATOIRE</div>
                <div class="clause-content">
                    <p>Si la période d'essai est jugée insuffisante pour apprécier le contractant, cette dernière sera poursuivie d'une nouvelle et dernière période dite probatoire égale à celle qui a été accomplie.</p>
                </div>
            </div>
            
            <!-- Clause 7 -->
            <div class="clause-section">
                <div class="clause-title">CLAUSE 7 : CONFIRMATION</div>
                <div class="clause-content">
                    <p>Si à l'issue de la période d'essai, les résultats sont jugés satisfaisants, le contractant sera confirmé à son poste de travail.</p>
                    <p>La confirmation fera l'objet d'une notification écrite de l'Employeur.</p>
                </div>
            </div>
            
            <!-- Clause 8 -->
            <div class="clause-section">
                <div class="clause-title">CLAUSE 8 : RÉSILIATION PÉRIODE D'ESSAI</div>
                <div class="clause-content">
                    <p>Durant la période d'essai, le présent contrat pourra être résilié sans préavis, ni indemnités par chacune des deux parties.</p>
                    <p>La résiliation doit être notifiée par écrit à l'autre partie.</p>
                </div>
            </div>
            
            <!-- Clause 9 -->
            <div class="clause-section">
                <div class="clause-title">CLAUSE 9 : DÉMISSION</div>
                <div class="clause-content">
                    <p>Dans le cas où le contractant désire rompre la relation de travail, il devra informer sa hiérarchie au moins un (01) mois avant son départ.</p>
                    <p>La démission doit être présentée par écrit et mentionner la date de départ souhaitée.</p>
                </div>
            </div>
            
            <!-- Clause 10 -->
            <div class="clause-section">
                <div class="clause-title">CLAUSE 10 : EXÉCUTION DES INSTRUCTIONS</div>
                <div class="clause-content">
                    <p>Le contractant devra exécuter, au mieux de ses capacités professionnelles, toutes les instructions de travail qu'il recevra de sa hiérarchie.</p>
                    <p>Il s'engage à respecter les procédures établies par l'Entreprise.</p>
                </div>
            </div>
        </div>
        
        <!-- Pied de page -->
        <div class="page-footer">
            <img src="img/logo/footer.png" alt="Pied de page">
        </div>
        
        <!-- Numéro de page -->
        <div class="page-number">Page 3</div>
    </div>

    <!-- PAGE 4 - Clauses 11 à 15 et signatures -->
    <div class="page">
        <!-- Filigrane -->
        <div class="watermark">
            <img src="img/logo/logo.png" alt="Logo filigrane">
        </div>
        
        <!-- En-tête -->
        <div class="page-header">
            <img src="img/logo/head.png" alt="En-tête">
        </div>
        
        <!-- QR Code (dernier) -->
        <div class="qr-container">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${qrData}" alt="QR Code">
            <div class="qr-label">Validation finale</div>
        </div>
        
        <!-- Contenu principal -->
        <div class="content">
            
            <!-- Clause 11 -->
            <div class="clause-section">
                <div class="clause-title">CLAUSE 11 : OBLIGATIONS DU CONTRACTANT</div>
                <div class="clause-content">
                    <p>Le contractant doit respecter toutes les obligations mises à sa charge par la réglementation d'El Djamaiya Linaki Oua El Khadamet, notamment en matières :</p>
                    <ul>
                        <li>D'horaires et de conditions de travail,</li>
                        <li>De hiérarchie,</li>
                        <li>De sécurité et d'hygiène,</li>
                        <li>De secret professionnel.</li>
                    </ul>
                </div>
            </div>
            
            <!-- Clause 12 -->
            <div class="clause-section">
                <div class="clause-title">CLAUSE 12 : UTILISATION DES MOYENS</div>
                <div class="clause-content">
                    <p>Il est interdit au contractant d'utiliser à des fins personnelles ou extérieures au service, les biens, services et moyens de travail de l'Entreprise.</p>
                </div>
            </div>
            
            <!-- Clause 13 -->
            <div class="clause-section">
                <div class="clause-title">CLAUSE 13 : ACTIVITÉS EXTÉRIEURES</div>
                <div class="clause-content">
                    <p>Sauf cas prévu par la législation du travail en vigueur, le contractant ne doit avoir aucune activité lucrative ou non en dehors de l'Entreprise El Djamaiya Linaki Oua El Khadamet.</p>
                    <p>Il ne doit posséder ni par lui-même, ni par personne interposée, des intérêts dans une Entreprise ayant des relations avec l'Entreprise El Djamaiya Linaki Oua El Khadamet.</p>
                </div>
            </div>
            
            <!-- Clause 14 -->
            <div class="clause-section">
                <div class="clause-title">CLAUSE 14 : RÉFÉRENCE AUX TEXTES</div>
                <div class="clause-content">
                    <p>Pour toutes les questions non précisées dans le présent contrat, le contractant relèvera du règlement intérieur de l'Entreprise, de la convention collective et des textes règlementaires et législatifs en vigueur.</p>
                </div>
            </div>
            
            <!-- Clause 15 -->
            <div class="clause-section">
                <div class="clause-title">CLAUSE 15 : EFFET DU CONTRAT</div>
                <div class="clause-content">
                    <p>Le présent contrat ne peut prendre effet qu'après que le contractant aura retourné deux doubles revêtus ci-dessous de la mention « lu et approuvé${contrat.civilite === 'Mme' ? 'e' : ''} » de la date et de sa signature.</p>
                </div>
            </div>
            
            <!-- Signatures -->
            <div class="signature-section">
                <p class="text-center mb-20">
                    <strong>Fait à Alger, le ${dateDebutFormatted}</strong>
                </p>
                
                <div class="signature-container">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div class="signature-label">
                            <strong>POUR L'ENTREPRISE</strong><br>
                            LE DIRECTEUR GÉNÉRAL
                        </div>
                        <p style="margin-top: 10px; font-size: 9pt; color: #666;">
                            Signature électronique validée<br>
                            CHORFI BILEL
                        </p>
                    </div>
                    
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div class="signature-label">
                            <strong>${contrat.civilite === 'Mme' ? 'LA CONTRACTANTE' : 'LE CONTRACTANT'}</strong><br>
                            ${contrat.nom || ''} ${contrat.prenom || ''}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pied de page -->
        <div class="page-footer">
            <img src="img/logo/footer.png" alt="Pied de page">
        </div>
        
        <!-- Numéro de page -->
        <div class="page-number">Page 4</div>
    </div>

    <!-- PAGE 5 - Approbation finale -->
    <div class="page">
        <!-- Filigrane -->
        <div class="watermark">
            <img src="img/logo/logo.png" alt="Logo filigrane">
        </div>
        
        <!-- En-tête -->
        <div class="page-header">
            <img src="img/logo/head.png" alt="En-tête">
        </div>
        
        <!-- Contenu principal -->
        <div class="content">
            
            <h2 class="text-center mb-20" style="font-weight: 300; border-bottom: 2px solid #000; padding-bottom: 10px;">
                APPROBATION DU CONTRACTANT
            </h2>
            
            <!-- Section approbation -->
            <div class="approval-section">
                <div class="checkbox-container">
                    <div class="checkbox-square"></div>
                    <div style="flex: 1;">
                        <p><strong>Je soussigné(e) ${contrat.nom || ''} ${contrat.prenom || ''},</strong></p>
                        <p class="mt-20">déclare avoir pris connaissance du présent contrat de travail dans son intégralité, avoir reçu une copie du règlement intérieur, et approuver l'ensemble des clauses énoncées ci-dessus.</p>
                        <p class="mt-20">Je m'engage à respecter les obligations qui me sont imparties et à exercer mes fonctions avec diligence et loyauté.</p>
                    </div>
                </div>
                
                <div class="signature-container mt-20">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div class="signature-label">
                            <strong>Date</strong><br>
                            ________/________/________
                        </div>
                    </div>
                    
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div class="signature-label">
                            <strong>Signature</strong><br>
                            ${contrat.civilite === 'Mme' ? 'La contractante' : 'Le contractant'}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Espace administratif -->
            <div class="admin-section mt-20">
                <h4 class="text-center mb-10" style="font-weight: 300;">ESPACE RÉSERVÉ AU SERVICE DU PERSONNEL</h4>
                
                <div class="admin-grid">
                    <div class="admin-field">
                        <div class="admin-label">Date réception</div>
                        <div class="admin-line"></div>
                    </div>
                    
                    <div class="admin-field">
                        <div class="admin-label">N° Dossier</div>
                        <div class="admin-line"></div>
                    </div>
                    
                    <div class="admin-field">
                        <div class="admin-label">Agent RH</div>
                        <div class="admin-line"></div>
                    </div>
                    
                    <div class="admin-field">
                        <div class="admin-label">Cachet</div>
                        <div class="admin-line"></div>
                    </div>
                    
                    <div class="admin-field">
                        <div class="admin-label">Date archivage</div>
                        <div class="admin-line"></div>
                    </div>
                    
                    <div class="admin-field">
                        <div class="admin-label">Cote archives</div>
                        <div class="admin-line"></div>
                    </div>
                    
                    <div class="admin-field">
                        <div class="admin-label">Numérisé le</div>
                        <div class="admin-line"></div>
                    </div>
                    
                    <div class="admin-field">
                        <div class="admin-label">Référence</div>
                        <div class="admin-line">${contrat.ref || contrat.matricule}</div>
                    </div>
                </div>
            </div>
            
            <!-- Information de génération -->
            <div class="text-center mt-20" style="font-size: 9pt; color: #666; border-top: 1px solid #eee; padding-top: 10px;">
                <p>Document généré électroniquement le ${new Date().toLocaleDateString('fr-FR')}</p>
                <p>ID: ${contrat.ref || contrat.matricule} | Version: 2.0 | EPE EL DJAMIAYA LINAKL OUA EL KHADAMAT SPA</p>
            </div>
        </div>
        
        <!-- Pied de page -->
        <div class="page-footer">
            <img src="img/logo/footer.png" alt="Pied de page">
        </div>
        
        <!-- Numéro de page -->
        <div class="page-number">Page 5</div>
    </div>
    
    <script>
        
        // Masquer les contrôles lors de l'impression
        window.onbeforeprint = function() {
            document.getElementById('controls').style.display = 'none';
        };
        
        window.onafterprint = function() {
            document.getElementById('controls').style.display = 'flex';
        };
        
        // Initialisation
        window.onload = function() {
            // Forcer le format A4
            document.body.style.width = '21cm';
            document.body.style.height = '29.7cm';
            
            // Ajustement pour l'impression
            const style = document.createElement('style');
            style.textContent = \`
                @media print {
                    @page {
                        margin: 0.5cm !important;
                    }
                    body {
                        margin: 0 !important;
                        padding: 0 !important;
                        zoom: 1 !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    .page {
                        page-break-after: always;
                        page-break-inside: avoid;
                        break-after: page;
                    }
                }
            \`;
            document.head.appendChild(style);
            
            console.log('Document A4 prêt pour impression - Marges: 0.5cm');
        };
    </script>
</body>
</html>`;
    }

    addDocumentControls(printWindow, htmlContent, contrat) {
        setTimeout(() => {
            if (printWindow.document.body) {
                console.log('Document généré avec succès');
            }
        }, 500);
    }

    setupPrintButton(contratId) {
        const modal = document.getElementById('modal-show-contrat');
        if (modal) {
            modal.setAttribute('data-contrat-id', contratId);
            
            const printButton = modal.querySelector('.bg-blue-600');
            if (printButton) {
                printButton.onclick = (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.printContrat(contratId);
                };
            }
        }
    }
}

// Initialisation
let printContrat = null;

document.addEventListener('DOMContentLoaded', () => {
    printContrat = new PrintContrat();
    window.printContrat = printContrat;
});

document.addEventListener('contratLoaded', (e) => {
    if (printContrat && e.detail && e.detail.contratId) {
        printContrat.setupPrintButton(e.detail.contratId);
    }
});