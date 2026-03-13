<?php
/**
 * php/employee/print_renseignement.php
 * Fiche de renseignement employé — tout en une page A4
 */
require_once __DIR__ . '/../auth.php';
requireLogin();
require_once __DIR__ . '/../connect_db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { http_response_code(400); die('ID manquant'); }

$stmt = $conn->prepare("
    SELECT e.*, u.nom_prenom AS imprime_par
    FROM employee e
    LEFT JOIN users u ON u.id = :uid
    WHERE e.id = :id
");
$stmt->execute([':id' => $id, ':uid' => $_SESSION['user_id']]);
$e = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$e) { http_response_code(404); die('Employé introuvable'); }

$isFemme = in_array($e['civilite'], ['Mme','Mlle']);
$titre   = 'FICHE DE RENSEIGNEMENT ' . ($isFemme ? 'EMPLOYÉE' : 'EMPLOYÉ');
$today   = new DateTime();

function fd(?string $d): string {
    if (!$d) return '—';
    try { return (new DateTime($d))->format('d/m/Y'); }
    catch(Exception $ex){ return '—'; }
}
function s(?string $v, string $def='—'): string {
    return htmlspecialchars(trim($v ?? '') ?: $def, ENT_QUOTES,'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Fiche renseignement — <?= s($e['nom']) ?> <?= s($e['prenom']) ?></title>
<style>
/* ── Reset ── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

body{
    font-family:'Segoe UI',Tahoma,Arial,sans-serif;
    background:#e8edf4;
    display:flex; justify-content:center;
    padding:20px 0 70px;
    color:#1a202c;
}

/* ── Page A4 fixe ── */
.page{
    width:210mm; height:297mm;
    overflow:hidden;
    background:#fff;
    padding:7mm 11mm 6mm;
    display:flex; flex-direction:column;
    box-shadow:0 6px 40px rgba(0,0,0,.18);
    position:relative;
}

/* ── Bande décorative latérale ── */
.page::before{
    content:'';
    position:absolute;
    left:0; top:0; bottom:0;
    width:5px;
    background:linear-gradient(180deg,#1e3a8a,#3b82f6,#06b6d4);
}

/* ── Header ── */
.header{
    display:flex; align-items:center;
    border-bottom:2.5px solid #1e40af;
    padding-bottom:5px; margin-bottom:5px;
    flex-shrink:0; gap:10px;
}
.header img{width:100%;height:auto;max-height:50px;object-fit:contain;display:block;}

/* ── Titre ── */
.fiche-title{
    text-align:center;
    font-size:13.5pt; font-weight:900;
    color:#1e3a8a;
    letter-spacing:.13em;
    text-transform:uppercase;
    padding:4px 0 5px;
    border-bottom:1px solid #bfdbfe;
    margin-bottom:6px;
    flex-shrink:0;
}

/* ── Bande identité ── */
.identity{
    display:flex; gap:10px;
    align-items:flex-start;
    margin-bottom:6px;
    padding:8px 10px;
    background:linear-gradient(135deg,#f0f9ff,#eff6ff);
    border:1.5px solid #bfdbfe;
    border-radius:8px;
    flex-shrink:0;
}

.identity-info{ flex:1; display:flex; flex-direction:column; gap:3px; }

.emp-name{
    font-size:13.5pt; font-weight:900;
    color:#1e3a8a; text-transform:uppercase;
    letter-spacing:.03em; line-height:1.2;
}
.emp-sub{font-size:9pt; color:#374151; font-weight:600; margin-top:2px;}
.emp-sub-small{font-size:8.5pt; color:#6b7280; font-style:italic;}

.etat-dot{
    display:inline-flex; align-items:center; gap:5px;
    margin-top:5px;
    font-size:8pt; font-weight:800;
    text-transform:uppercase; letter-spacing:.05em;
    padding:2px 9px; border-radius:999px;
}
.etat-actif  {background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;}
.etat-inactif{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;}

/* Photo à droite */
.identity-photo{
    flex-shrink:0; order:2;
    display:flex; flex-direction:column; align-items:center; gap:4px;
}
.identity-photo img{
    width:5cm; height:6cm;
    object-fit:cover;
    border:2px solid #93c5fd;
    border-radius:6px;
}
.mat-box{
    width:5cm; text-align:center;
    background:#1e40af; color:#fff;
    font-size:8pt; font-weight:800;
    padding:3px 4px; border-radius:4px;
    font-family:monospace; letter-spacing:.04em;
}

/* ── Corps 2 colonnes ── */
.body-cols{
    display:grid; grid-template-columns:1fr 1fr;
    gap:5px; flex:1; min-height:0; overflow:hidden;
}

/* ── Sections ── */
.section{
    border:1px solid #e2e8f0;
    border-radius:7px; overflow:hidden;
    break-inside:avoid;
}
.section.full{grid-column:span 2;}

.sh{
    display:flex; align-items:center; gap:6px;
    background:linear-gradient(90deg,#1e3a8a,#2563eb);
    color:#fff; font-size:7pt; font-weight:800;
    text-transform:uppercase; letter-spacing:.08em;
    padding:3px 8px;
}
.sh .ic{
    width:16px; height:16px;
    background:rgba(255,255,255,.2); border-radius:4px;
    display:flex; align-items:center; justify-content:center;
    font-size:9px;
}

.sb{padding:5px 8px; background:#fff;}

/* ── Champs ── */
.g{display:grid; gap:3px 10px;}
.g2{grid-template-columns:1fr 1fr;}
.g3{grid-template-columns:1fr 1fr 1fr;}
.g1{grid-template-columns:1fr;}
.span2{grid-column:span 2;}
.span3{grid-column:span 3;}

.f{display:flex; flex-direction:column; gap:0;}
.fl{font-size:6.5pt; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; line-height:1.3;}
.fv{font-size:8.5pt; font-weight:500; color:#111827; border-bottom:.5px dotted #d1d5db; padding-bottom:1px; line-height:1.4;}
.fv.mn{font-family:'Courier New',monospace; font-size:8pt;}

/* Signature */
.sig-row{
    display:grid; grid-template-columns:1fr 1fr 1fr;
    gap:8px; margin-top:4px;
}
.sig-box{
    border-top:1px solid #cbd5e1;
    padding-top:4px;
    font-size:7pt; font-weight:700; color:#6b7280;
    text-transform:uppercase; letter-spacing:.05em;
    text-align:center;
    min-height:28px;
}

/* ── Footer ── */
.pfooter{
    flex-shrink:0; margin-top:4px;
    padding-top:4px; border-top:1px solid #e2e8f0;
    display:flex; justify-content:space-between;
    font-size:7pt; color:#9ca3af;
}
.pfooter .pu{font-weight:700; color:#374151;}

/* ── Boutons ── */
.btn-bar{
    position:fixed; bottom:20px; right:20px;
    display:flex; gap:10px; z-index:999;
}
.bprint{
    background:linear-gradient(135deg,#1e40af,#2563eb);
    color:#fff; border:none;
    padding:10px 22px; border-radius:10px;
    font-size:11pt; font-weight:700; cursor:pointer;
    box-shadow:0 4px 15px rgba(30,64,175,.4);
    display:flex; align-items:center; gap:8px;
}
.bclose{
    background:#fff; color:#374151;
    border:1.5px solid #d1d5db;
    padding:10px 18px; border-radius:10px;
    font-size:11pt; font-weight:600; cursor:pointer;
}

/* ── Print ── */
@media print{
    body{background:#fff !important;padding:0;}
    .page{box-shadow:none;width:100%;height:297mm;padding:7mm 11mm 6mm;}
    .btn-bar{display:none !important;}
    @page{size:A4 portrait;margin:0;}
}
</style>
</head>
<body>
<div class="page" id="fiche-page">

    <!-- EN-TÊTE -->
    <div class="header">
        <img src="../../img/logo/head.png" onerror="this.style.display='none'" alt="">
    </div>

    <!-- TITRE -->
    <div class="fiche-title"><?= $titre ?></div>

    <!-- IDENTITÉ -->
    <div class="identity">
        <div class="identity-info">
            <div class="emp-name"><?= s($e['civilite']) ?> <?= s($e['nom']) ?> <?= s($e['prenom']) ?></div>
            <div class="emp-sub">
                <?php if (!empty($e['no_assurance_cnas'])): ?>
                    <i>CNAS :</i> <?= s($e['no_assurance_cnas']) ?>
                <?php endif; ?>
            </div>
            <div class="emp-sub-small">
                <?php if (!empty($e['adresse'])): ?><?= s($e['adresse']) ?><?php endif; ?>
                <?php if (!empty($e['wilaya_residence'])): ?> — <?= s($e['wilaya_residence']) ?><?php endif; ?>
            </div>
            <span class="etat-dot <?= $e['etat']==='actif' ? 'etat-actif' : 'etat-inactif' ?>">
                <?= $e['etat']==='actif' ? '● Actif' : '● Inactif' ?>
            </span>
        </div>

        <!-- Photo (droite) -->
        <div class="identity-photo">
            <img src="../../img/employee/<?= s($e['photo'] ?: 'user.png') ?>"
                 onerror="this.src='../../img/employee/user.png'" alt="Photo">
            <div class="mat-box"><?= s($e['matricule'],'N/A') ?></div>
        </div>
    </div>

    <!-- CORPS -->
    <div class="body-cols">

        <!-- COL GAUCHE -->
        <div style="display:flex;flex-direction:column;gap:5px;">

            <!-- État civil -->
            <div class="section">
                <div class="sh"><span class="ic">◆</span> État Civil</div>
                <div class="sb">
                    <div class="g g2">
                        <div class="f">
                            <span class="fl">Date de naissance</span>
                            <span class="fv">
                                <?php if ($e['date_naissance']): echo fd($e['date_naissance']);
                                elseif ($e['presume']): echo 'Présumée : '.s($e['presume']);
                                else: echo '—'; endif; ?>
                            </span>
                        </div>
                        <div class="f">
                            <span class="fl">Lieu de naissance</span>
                            <span class="fv"><?= s($e['lieu_naissance']) ?></span>
                        </div>
                        <div class="f">
                            <span class="fl">N° Acte naissance</span>
                            <span class="fv mn"><?= s($e['no_acte_naissance']) ?></span>
                        </div>
                        <div class="f">
                            <span class="fl">Situation familiale</span>
                            <span class="fv"><?= s($e['situation_familiale']) ?></span>
                        </div>
                        <div class="f">
                            <span class="fl">Nombre d'enfants</span>
                            <span class="fv"><?= (int)($e['nombre_enfants']??0) ?></span>
                        </div>
                        <div class="f">
                            <span class="fl">Prénom du père</span>
                            <span class="fv"><?= s($e['prenom_pere']) ?></span>
                        </div>
                        <div class="f span2">
                            <span class="fl">Nom &amp; Prénom de la mère</span>
                            <span class="fv"><?= s($e['nom_prenom_mere']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coordonnées -->
            <div class="section">
                <div class="sh"><span class="ic">◆</span> Coordonnées</div>
                <div class="sb">
                    <div class="g g2">
                        <div class="f span2">
                            <span class="fl">Adresse complète</span>
                            <span class="fv"><?= s($e['adresse']) ?></span>
                        </div>
                        <div class="f">
                            <span class="fl">Wilaya de résidence</span>
                            <span class="fv"><?= s($e['wilaya_residence']) ?></span>
                        </div>
                        <div class="f">
                            <span class="fl">Téléphone</span>
                            <span class="fv mn"><?= s($e['telephone']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assurance & Paiement -->
            <div class="section">
                <div class="sh"><span class="ic">◆</span> Assurance &amp; Paiement</div>
                <div class="sb">
                    <div class="g g1">
                        <div class="f">
                            <span class="fl">N° Assurance CNAS</span>
                            <span class="fv mn"><?= s($e['no_assurance_cnas']) ?></span>
                        </div>
                        <div class="f">
                            <span class="fl">Compte à payer</span>
                            <span class="fv mn"><?= s($e['compte_a_paye']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COL DROITE : Renseignements complémentaires + Signatures -->
        <div style="display:flex;flex-direction:column;gap:5px;">

            <!-- Infos administratives -->
            <div class="section">
                <div class="sh"><span class="ic">◆</span> Informations administratives</div>
                <div class="sb">
                    <div class="g g2">
                        <div class="f">
                            <span class="fl">Matricule</span>
                            <span class="fv mn"><?= s($e['matricule']) ?></span>
                        </div>
                        <div class="f">
                            <span class="fl">Statut</span>
                            <span class="fv"><?= s($e['etat']) ?></span>
                        </div>
                        <div class="f">
                            <span class="fl">Date d'entrée</span>
                            <span class="fv"><?= fd($e['date_creation']) ?></span>
                        </div>
                        <div class="f">
                            <span class="fl">Saisi par</span>
                            <span class="fv"><?= s($e['user']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Zones renseignements libres (lignes vides à remplir manuellement) -->
            <div class="section" style="flex:1;">
                <div class="sh"><span class="ic">◆</span> Observations &amp; Renseignements complémentaires</div>
                <div class="sb" style="flex:1;">
                    <?php for($i=0;$i<8;$i++): ?>
                    <div style="border-bottom:.5px solid #e5e7eb;height:18px;margin-bottom:3px;"></div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Signatures -->
            <div class="section">
                <div class="sh"><span class="ic">◆</span> Signatures</div>
                <div class="sb">
                    <div class="sig-row">
                        <div class="sig-box">L'employé(e)</div>
                        <div class="sig-box">Le responsable RH</div>
                        <div class="sig-box">La Direction</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- FOOTER -->
    <div class="pfooter">
        <div>Imprimé par : <span class="pu"><?= s($e['imprime_par'] ?? ($_SESSION['user_name'] ?? 'Administrateur')) ?></span></div>
        <div>DNK — Gestion des Ressources Humaines &nbsp;|&nbsp; <em>Document confidentiel</em></div>
        <div>Le <?= $today->format('d/m/Y à H:i') ?></div>
    </div>

</div>

<div class="btn-bar">
    <button class="bclose" onclick="window.close()">✕ Fermer</button>
    <button class="bprint" onclick="window.print()">🖨 Imprimer</button>
</div>

<script>
(function fitOnePage() {
    const page = document.getElementById('fiche-page');
    const PH   = 297 * (96/25.4);
    const PW   = 210 * (96/25.4);

    function apply() {
        page.style.transform = '';
        const scale = Math.min(PH / page.scrollHeight, PW / page.scrollWidth, 1);
        if (scale < 1) {
            page.style.transform       = `scale(${scale})`;
            page.style.transformOrigin = 'top center';
            page.style.marginBottom    = `-${(1-scale)*PH}px`;
        }
        let s = document.getElementById('ps');
        if (!s) { s = document.createElement('style'); s.id='ps'; document.head.appendChild(s); }
        s.textContent = scale < 1 ? `@media print{.page{transform:scale(${scale}) !important;transform-origin:top left !important;}}` : '';
    }

    const imgs = document.querySelectorAll('img');
    let n = 0, total = imgs.length;
    if (!total) { apply(); return; }
    imgs.forEach(img => {
        const done = () => { if (++n >= total) apply(); };
        img.complete ? done() : (img.onload = img.onerror = done);
    });
    setTimeout(apply, 1200);
})();
window.addEventListener('load', () => setTimeout(() => window.print(), 1400));
</script>
</body>
</html>