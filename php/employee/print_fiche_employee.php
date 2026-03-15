<?php
/**
 * php/employee/print_fiche_employee.php
 * Fiche employé imprimable — une seule page A4
 */
require_once __DIR__ . '/../auth.php';
requireLogin();
require_once __DIR__ . '/../connect_db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { http_response_code(400); die('ID manquant'); }

$stmt = $conn->prepare("
    SELECT e.*, u.nom_prenom AS imprime_par
    FROM employee e
    LEFT JOIN users u ON u.id = :user_id
    WHERE e.id = :id
");
$stmt->execute([':id' => $id, ':user_id' => $_SESSION['user_id']]);
$e = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$e) { http_response_code(404); die('Employé introuvable'); }

$stmtC = $conn->prepare("SELECT * FROM contrat WHERE id_employee = :id ORDER BY date_creation DESC");
$stmtC->execute([':id' => $id]);
$contrats = $stmtC->fetchAll(PDO::FETCH_ASSOC);

$isFemme    = in_array($e['civilite'], ['Mme', 'Mlle']);
$titreFiche = $isFemme ? 'FICHE EMPLOYÉE' : 'FICHE EMPLOYÉ';
$today      = new DateTime();

function fmtDate(?string $d): string {
    if (!$d) return '—';
    try { return (new DateTime($d))->format('d/m/Y'); }
    catch (Exception $ex) { return '—'; }
}
function safe(?string $v, string $def = '—'): string {
    return htmlspecialchars(trim($v ?? '') ?: $def, ENT_QUOTES, 'UTF-8');
}
function contractStatus(array $c): array {
    $t = new DateTime();
    if ($c['type_contrat'] === 'CDI' || empty($c['date_fin']))
        return ['label' => 'CDI', 'class' => 'badge-cdi'];
    $fin = new DateTime($c['date_fin']);
    return $fin >= $t
        ? ['label' => 'En vigueur', 'class' => 'badge-vigueur']
        : ['label' => 'Périmé',     'class' => 'badge-perime'];
}

$contratActif = null;
foreach ($contrats as $c) {
    $isCDI = $c['type_contrat'] === 'CDI' || empty($c['date_fin']);
    $ok    = $isCDI || (!empty($c['date_fin']) && new DateTime($c['date_fin']) >= $today);
    if ($ok) { $contratActif = $c; break; }
}
if (!$contratActif && count($contrats)) $contratActif = $contrats[0];

$historique = array_filter($contrats, fn($c) => !$contratActif || $c['id'] != $contratActif['id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Fiche — <?= safe($e['nom']) ?> <?= safe($e['prenom']) ?></title>
<style>
/* ── RESET ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f0f4f8;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    padding: 20px 0 60px;
}

/* ── PAGE A4 ── */
.page {
    width: 210mm;
    height: 297mm;          /* hauteur fixe A4 */
    overflow: hidden;       /* jamais de débordement */
    background: #fff;
    display: flex;
    flex-direction: column;
    padding: 7mm 10mm 6mm;
    position: relative;
    box-shadow: 0 4px 30px rgba(0,0,0,0.18);
}

/* ── EN-TÊTE : 100% largeur ── */
.header {
    width: 100%;
    border-bottom: 2.5px solid #1e40af;
    padding-bottom: 5px;
    margin-bottom: 5px;
    flex-shrink: 0;
}
.header img {
    width: 100%;            /* ← 100% largeur comme demandé */
    height: auto;
    max-height: 50px;
    object-fit: contain;
    object-position: center;
    display: block;
}

/* ── TITRE ── */
.fiche-title {
    text-align: center;
    font-size: 14pt;
    font-weight: 900;
    letter-spacing: 0.14em;
    color: #1e3a8a;
    text-transform: uppercase;
    padding: 4px 0;
    border-bottom: 1px solid #bfdbfe;
    margin-bottom: 6px;
    flex-shrink: 0;
}

/* ── ZONE IDENTITÉ ── */
.identity-zone {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    margin-bottom: 6px;
    padding: 7px 10px;
    background: linear-gradient(135deg, #eff6ff, #f0f9ff);
    border: 1.5px solid #bfdbfe;
    border-radius: 7px;
    flex-shrink: 0;
}

/* Infos (gauche) */
.identity-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 3px;
}
.emp-fullname {
    font-size: 13pt;
    font-weight: 900;
    color: #1e3a8a;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    line-height: 1.2;
}
.emp-poste {
    font-size: 10pt;
    font-weight: 700;
    color: #374151;
    margin-top: 2px;
}
.emp-affectation {
    font-size: 9pt;
    color: #6b7280;
    font-style: italic;
}
.emp-etat-badge {
    display: inline-block;
    margin-top: 5px;
    padding: 2px 9px;
    border-radius: 999px;
    font-size: 7.5pt;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.etat-actif   { background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; }
.etat-inactif { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }

/* Photo + Matricule (droite) ← déplacé à droite */
.identity-photo {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    order: 2;               /* ← force à droite */
}
.identity-photo img {
    width:  5cm;            /* ← 6cm haut × 5cm large comme demandé */
    height: 6cm;
    object-fit: cover;
    border: 2px solid #93c5fd;
    border-radius: 5px;
    display: block;
}
.matricule-box {
    width: 5cm;
    text-align: center;
    background: #1e40af;
    color: #fff;
    font-size: 8pt;
    font-weight: 800;
    padding: 3px 4px;
    border-radius: 4px;
    letter-spacing: 0.04em;
}

/* ── CORPS : 2 colonnes ── */
.body-cols {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 5px;
    flex: 1;
    min-height: 0;
    overflow: hidden;
}

/* ── SECTIONS ── */
.section {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    overflow: hidden;
    break-inside: avoid;
}
.section.full-width {
    grid-column: span 2;
}

.section-header {
    background: linear-gradient(90deg, #1e40af, #2563eb);
    color: #fff;
    font-size: 7.5pt;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 3px 8px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.section-body {
    padding: 5px 8px;
    background: #fff;
}

/* ── Grilles de champs ── */
.info-grid        { display:grid; grid-template-columns:1fr 1fr; gap:3px 10px; }
.info-grid.cols-3 { grid-template-columns:1fr 1fr 1fr; }
.info-grid.cols-1 { grid-template-columns:1fr; }

.info-field        { display:flex; flex-direction:column; gap:0; }
.info-field.full   { grid-column:span 2; }
.info-field.full3  { grid-column:span 3; }

.field-label {
    font-size: 6.5pt;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    line-height: 1.3;
}
.field-value {
    font-size: 8.5pt;
    font-weight: 500;
    color: #111827;
    border-bottom: 0.5px dotted #d1d5db;
    padding-bottom: 1px;
    line-height: 1.4;
}
.field-value.mono {
    font-family: 'Courier New', monospace;
    font-size: 8pt;
}

/* ── Contrats ── */
.contrat-actif-box {
    background: linear-gradient(135deg,#f0fdf4,#ecfdf5);
    border: 1px solid #6ee7b7;
    border-radius: 5px;
    padding: 5px 7px;
    margin-bottom: 4px;
}
.contrat-actif-title {
    font-size: 7pt;
    font-weight: 800;
    color: #065f46;
    text-transform: uppercase;
    letter-spacing:.06em;
    margin-bottom:4px;
    display:flex;
    align-items:center;
    gap:5px;
}

.contrat-row {
    display:flex;
    align-items:flex-start;
    gap:6px;
    padding:3px 6px;
    border:0.5px solid #e5e7eb;
    border-radius:4px;
    margin-bottom:3px;
    background:#fafafa;
    font-size:7.5pt;
}
.contrat-detail { flex:1; }
.contrat-ref  { font-weight:700; color:#374151; font-size:7.5pt; }
.contrat-meta { color:#6b7280; font-size:7pt; }

.badge-vigueur,.badge-perime,.badge-cdi {
    display:inline-block;
    padding:1px 6px;
    border-radius:999px;
    font-size:6.5pt;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.04em;
    flex-shrink:0;
    white-space:nowrap;
}
.badge-vigueur { background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; }
.badge-perime  { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }
.badge-cdi     { background:#dbeafe; color:#1e40af; border:1px solid #93c5fd; }

/* ── FOOTER ── */
.print-footer {
    flex-shrink: 0;
    margin-top: 4px;
    padding-top: 4px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    font-size: 7pt;
    color: #9ca3af;
}
.footer-user { font-weight:700; color:#374151; }

/* ── BOUTONS (non imprimés) ── */
.print-btn-bar {
    position:fixed; bottom:20px; right:20px;
    display:flex; gap:10px; z-index:999;
}
.btn-print {
    background:linear-gradient(135deg,#1e40af,#2563eb);
    color:#fff; border:none;
    padding:10px 22px; border-radius:10px;
    font-size:11pt; font-weight:700; cursor:pointer;
    box-shadow:0 4px 15px rgba(30,64,175,.4);
    display:flex; align-items:center; gap:8px;
    transition:transform .15s,box-shadow .15s;
}
.btn-print:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(30,64,175,.5); }
.btn-close {
    background:#fff; color:#374151;
    border:1.5px solid #d1d5db;
    padding:10px 18px; border-radius:10px;
    font-size:11pt; font-weight:600; cursor:pointer;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
    transition:background .15s;
}
.btn-close:hover { background:#f3f4f6; }

/* ── PRINT MEDIA ── */
@media print {
    body { background:#fff !important; padding:0; }
    .page {
        width:210mm; height:297mm;
        padding:7mm 10mm 6mm;
        box-shadow:none;
        /* Le scale JS prend le relais */
    }
    .no-print { display:none !important; }
    @page { size:A4 portrait; margin:0; }
}
</style>
</head>
<body>

<div class="page" id="fiche-page">

    <!-- ── EN-TÊTE ── -->
    <div class="header">
        <img src="../../img/logo/head.png"
             onerror="this.style.display='none'"
             alt="En-tête">
    </div>

    <!-- ── TITRE ── -->
    <div class="fiche-title"><?= $titreFiche ?></div>

    <!-- ── IDENTITÉ ── -->
    <div class="identity-zone">

        <!-- Infos (gauche) -->
        <div class="identity-info">
            <div class="emp-fullname">
                <?= safe($e['civilite']) ?> <?= safe($e['nom']) ?> <?= safe($e['prenom']) ?>
            </div>
            <?php if ($contratActif): ?>
                <div class="emp-poste"><?= safe($contratActif['poste'] ?? '', 'Poste non renseigné') ?></div>
                <?php if (!empty($contratActif['affectation'])): ?>
                    <div class="emp-affectation"><?= safe($contratActif['affectation']) ?></div>
                <?php endif; ?>
            <?php endif; ?>
            <span class="emp-etat-badge <?= $e['etat']==='actif' ? 'etat-actif' : 'etat-inactif' ?>">
                <?= $e['etat']==='actif' ? '● Actif' : '● Inactif' ?>
            </span>
        </div>

        <!-- Photo + Matricule (droite) -->
        <div class="identity-photo">
            <img src="../../img/employee/<?= safe($e['photo'] ?: 'user.png') ?>"
                 onerror="this.src='../../img/employee/user.png'"
                 alt="Photo">
            <div class="matricule-box"><?= safe($e['matricule'], 'N/A') ?></div>
        </div>
    </div>

    <!-- ── CORPS : 2 colonnes ── -->
    <div class="body-cols">

        <!-- COL GAUCHE -->
        <div style="display:flex;flex-direction:column;gap:5px;">

            <!-- État civil -->
            <div class="section">
                <div class="section-header">◆ État Civil</div>
                <div class="section-body">
                    <div class="info-grid">
                        <div class="info-field">
                            <span class="field-label">Date de naissance</span>
                            <span class="field-value">
                                <?php if ($e['date_naissance']): echo fmtDate($e['date_naissance']);
                                elseif ($e['presume']): echo 'Présumée : '.safe($e['presume']);
                                else: echo '—'; endif; ?>
                            </span>
                        </div>
                        <div class="info-field">
                            <span class="field-label">Lieu de naissance</span>
                            <span class="field-value"><?= safe($e['lieu_naissance']) ?></span>
                        </div>
                        <div class="info-field">
                            <span class="field-label">N° Acte naissance</span>
                            <span class="field-value mono"><?= safe($e['no_acte_naissance']) ?></span>
                        </div>
                        <div class="info-field">
                            <span class="field-label">Situation familiale</span>
                            <span class="field-value"><?= safe($e['situation_familiale']) ?></span>
                        </div>
                        <div class="info-field">
                            <span class="field-label">Nombre d'enfants</span>
                            <span class="field-value"><?= (int)($e['nombre_enfants'] ?? 0) ?></span>
                        </div>
                        <div class="info-field">
                            <span class="field-label">Prénom du père</span>
                            <span class="field-value"><?= safe($e['prenom_pere']) ?></span>
                        </div>
                        <div class="info-field full">
                            <span class="field-label">Nom &amp; Prénom de la mère</span>
                            <span class="field-value"><?= safe($e['nom_prenom_mere']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coordonnées -->
            <div class="section">
                <div class="section-header">◆ Coordonnées</div>
                <div class="section-body">
                    <div class="info-grid">
                        <div class="info-field full">
                            <span class="field-label">Adresse complète</span>
                            <span class="field-value"><?= safe($e['adresse']) ?></span>
                        </div>
                        <div class="info-field">
                            <span class="field-label">Wilaya de résidence</span>
                            <span class="field-value"><?= safe($e['wilaya_residence']) ?></span>
                        </div>
                        <div class="info-field">
                            <span class="field-label">Téléphone</span>
                            <span class="field-value mono"><?= safe($e['telephone']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assurance & Paiement -->
            <div class="section">
                <div class="section-header">◆ Assurance &amp; Paiement</div>
                <div class="section-body">
                    <div class="info-grid cols-1">
                        <div class="info-field">
                            <span class="field-label">N° Sécurité sociale (CNAS)</span>
                            <span class="field-value mono"><?= safe($e['no_assurance_cnas']) ?></span>
                        </div>
                        <div class="info-field">
                            <span class="field-label">Compte à payer</span>
                            <span class="field-value mono"><?= safe($e['compte_a_paye']) ?></span>
                        </div>
                        <div class="info-field">
                            <span class="field-label">Salaire (contrat actuel)</span>
                            <span class="field-value mono">
                                <?= ($contratActif && !empty($contratActif['salaire']))
                                    ? number_format((float)$contratActif['salaire'], 2, ',', ' ').' DA'
                                    : '—' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /col gauche -->

        <!-- COL DROITE : Contrats -->
        <div class="section" style="display:flex;flex-direction:column;overflow:hidden;">
            <div class="section-header">
                ◆ Contrats
                <span style="margin-left:auto;font-size:6.5pt;opacity:.85;font-weight:500;">
                    <?= count($contrats) ?> contrat<?= count($contrats)>1?'s':'' ?>
                </span>
            </div>
            <div class="section-body" style="flex:1;overflow:hidden;">

                <?php if ($contratActif):
                    $st = contractStatus($contratActif); ?>
                <div class="contrat-actif-box">
                    <div class="contrat-actif-title">
                        ★ Contrat actuel
                        <span class="<?= $st['class'] ?>"><?= $st['label'] ?></span>
                    </div>
                    <div class="info-grid cols-3">
                        <div class="info-field">
                            <span class="field-label">Référence</span>
                            <span class="field-value mono"><?= safe($contratActif['ref'] ?? '#'.$contratActif['id']) ?></span>
                        </div>
                        <div class="info-field">
                            <span class="field-label">Type</span>
                            <span class="field-value"><?= safe($contratActif['type_contrat']) ?></span>
                        </div>
                        <div class="info-field">
                            <span class="field-label">Poste</span>
                            <span class="field-value"><?= safe($contratActif['poste']) ?></span>
                        </div>
                        <div class="info-field">
                            <span class="field-label">Date début</span>
                            <span class="field-value"><?= fmtDate($contratActif['date_debut']) ?></span>
                        </div>
                        <div class="info-field">
                            <span class="field-label">Date fin</span>
                            <span class="field-value">
                                <?= ($contratActif['type_contrat']==='CDI' || empty($contratActif['date_fin']))
                                    ? 'Indéterminée (CDI)' : fmtDate($contratActif['date_fin']) ?>
                            </span>
                        </div>
                        <?php if (!empty($contratActif['periode_essai'])): ?>
                        <div class="info-field">
                            <span class="field-label">Période d'essai</span>
                            <span class="field-value"><?= safe($contratActif['periode_essai']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($contratActif['affectation'])): ?>
                        <div class="info-field full3">
                            <span class="field-label">Affectation</span>
                            <span class="field-value"><?= safe($contratActif['affectation']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (count($historique)): ?>
                <div style="font-size:7pt;font-weight:800;color:#6b7280;text-transform:uppercase;
                            letter-spacing:.06em;margin:4px 0 3px 2px;">Historique</div>
                <?php foreach ($historique as $c):
                    $st = contractStatus($c); ?>
                <div class="contrat-row">
                    <span class="<?= $st['class'] ?>"><?= $st['label'] ?></span>
                    <div class="contrat-detail">
                        <div class="contrat-ref">
                            <?= safe($c['ref'] ?? '#'.$c['id']) ?> — <?= safe($c['type_contrat']) ?>
                            <?php if (!empty($c['poste'])): ?> · <?= safe($c['poste']) ?><?php endif; ?>
                        </div>
                        <div class="contrat-meta">
                            Du <?= fmtDate($c['date_debut']) ?>
                            <?= !empty($c['date_fin']) ? 'au '.fmtDate($c['date_fin']) : '(indéterminé)' ?>
                            <?php if (!empty($c['affectation'])): ?> · <?= safe($c['affectation']) ?><?php endif; ?>
                            <?php if (!empty($c['salaire'])): ?> · <?= number_format((float)$c['salaire'],2,',',' ') ?> DA<?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!count($contrats)): ?>
                <p style="color:#9ca3af;font-size:8pt;text-align:center;padding:8px 0;">
                    Aucun contrat enregistré
                </p>
                <?php endif; ?>

            </div>
        </div><!-- /col droite -->

    </div><!-- /body-cols -->

    <!-- ── FOOTER ── -->
    <div class="print-footer">
        <div>
            Imprimé par : <span class="footer-user"><?= safe($e['imprime_par'] ?? ($_SESSION['user_name'] ?? 'Administrateur')) ?></span>
        </div>
        <div style="text-align:center;flex:1;">
            DNK — Gestion des Ressources Humaines &nbsp;|&nbsp; <em>Document confidentiel</em>
        </div>
        <div style="text-align:right;">
            Le <?= $today->format('d/m/Y à H:i') ?>
        </div>
    </div>

</div><!-- /page -->

<!-- Boutons (non imprimés) -->
<div class="print-btn-bar no-print">
    <button class="btn-close" onclick="window.close()">✕ Fermer</button>
    <button class="btn-print" onclick="window.print()">🖨 Imprimer</button>
</div>

<script>
/**
 * Technique "fit-to-one-page" :
 * Compare la hauteur réelle du contenu avec 297mm (A4).
 * Si ça dépasse, on applique un transform:scale() pour tout faire rentrer.
 * La valeur scale est aussi injectée dans @media print via une <style> dynamique.
 */
(function fitOnePage() {
    const page    = document.getElementById('fiche-page');
    const A4_H_PX = 297 * (96 / 25.4); // 297mm → px (96dpi)
    const A4_W_PX = 210 * (96 / 25.4);

    function apply() {
        // Reset pour mesure réelle
        page.style.transform       = '';
        page.style.transformOrigin = '';
        page.style.height          = '297mm';

        const realH = page.scrollHeight;
        const realW = page.scrollWidth;

        const scaleH = A4_H_PX / realH;
        const scaleW = A4_W_PX / realW;
        const scale  = Math.min(scaleH, scaleW, 1); // jamais > 1 (pas d'agrandissement)

        if (scale < 1) {
            page.style.transform       = `scale(${scale})`;
            page.style.transformOrigin = 'top center';
            // Compenser la perte de place visuellement dans le navigateur
            page.style.marginBottom    = `-${(1 - scale) * A4_H_PX}px`;
        }

        // Injecter le scale dans @media print aussi
        let styleEl = document.getElementById('print-scale-style');
        if (!styleEl) {
            styleEl    = document.createElement('style');
            styleEl.id = 'print-scale-style';
            document.head.appendChild(styleEl);
        }
        styleEl.textContent = scale < 1 ? `
            @media print {
                .page {
                    transform: scale(${scale}) !important;
                    transform-origin: top left !important;
                }
            }` : '';
    }

    // Attendre le chargement des images
    const imgs = document.querySelectorAll('img');
    let loaded = 0;
    if (!imgs.length) { apply(); return; }

    imgs.forEach(img => {
        if (img.complete) { if (++loaded === imgs.length) apply(); }
        else {
            img.addEventListener('load',  () => { if (++loaded === imgs.length) apply(); });
            img.addEventListener('error', () => { if (++loaded === imgs.length) apply(); });
        }
    });

    // Fallback
    setTimeout(apply, 1200);
})();

// Impression auto après rendu
window.addEventListener('load', () => setTimeout(() => window.print(), 1400));
</script>
</body>
</html>