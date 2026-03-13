<?php
/**
 * php/employee/edit_employee.php
 * Colonnes réelles de la table employee (sans date_modification, sans etat)
 */
ob_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../connect_db.php';

function fail(string $msg): void { ob_end_clean(); echo json_encode(['success'=>false,'message'=>$msg]); exit; }
function ok(array $d): void      { ob_end_clean(); echo json_encode(array_merge(['success'=>true],$d)); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Méthode non autorisée');

$id = (int)($_POST['id'] ?? 0);
if (!$id) fail('ID employé manquant');

/* ── Récupérer l'employé actuel (photo + matricule) ── */
$cur = $conn->prepare("SELECT matricule, photo FROM employee WHERE id = :id");
$cur->execute([':id' => $id]);
$current = $cur->fetch(PDO::FETCH_ASSOC);
if (!$current) fail('Employé introuvable');

/* ── Validation ── */
$required = [
    'civilite','nom','prenom','lieu_naissance','no_acte_naissance',
    'situation_familiale','prenom_pere','nom_prenom_mere',
    'adresse','wilaya_residence','telephone','no_assurance_cnas','compte_a_paye'
];
foreach ($required as $f) {
    if (empty(trim($_POST[$f] ?? ''))) fail("Le champ « $f » est obligatoire");
}
if (empty($_POST['date_naissance']) && empty($_POST['presume'])) {
    fail('La date de naissance ou l\'année présumée est requise');
}
if (!preg_match('/^0[0-9]{9}$/', $_POST['telephone'] ?? '')) {
    fail('Téléphone invalide — format attendu : 0XXXXXXXXX');
}

/* ── Photo ── */
$photo_name = $current['photo'];
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif'])) fail('Type de photo non autorisé');
    if ($_FILES['photo']['size'] > 2 * 1024 * 1024) fail('Photo trop lourde (max 2 Mo)');
    $photo_name = $current['matricule'] . '.' . $ext;
    $dir = __DIR__ . '/../../img/employee/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dir . $photo_name))
        fail("Erreur lors de l'enregistrement de la photo");
}

/* ── Mise à jour ── */
try {
    $stmt = $conn->prepare("
        UPDATE employee SET
            civilite            = :civilite,
            nom                 = :nom,
            prenom              = :prenom,
            date_naissance      = :date_naissance,
            presume             = :presume,
            lieu_naissance      = :lieu_naissance,
            no_acte_naissance   = :no_acte_naissance,
            situation_familiale = :situation_familiale,
            nombre_enfants      = :nombre_enfants,
            prenom_pere         = :prenom_pere,
            nom_prenom_mere     = :nom_prenom_mere,
            adresse             = :adresse,
            wilaya_residence    = :wilaya_residence,
            telephone           = :telephone,
            no_assurance_cnas   = :no_assurance_cnas,
            compte_a_paye       = :compte_a_paye,
            photo               = :photo
        WHERE id = :id
    ");
    $stmt->execute([
        ':civilite'            => trim($_POST['civilite']),
        ':nom'                 => strtoupper(trim($_POST['nom'])),
        ':prenom'              => ucfirst(strtolower(trim($_POST['prenom']))),
        ':date_naissance'      => !empty($_POST['date_naissance']) ? $_POST['date_naissance'] : null,
        ':presume'             => !empty($_POST['presume']) ? (int)$_POST['presume'] : null,
        ':lieu_naissance'      => trim($_POST['lieu_naissance']),
        ':no_acte_naissance'   => trim($_POST['no_acte_naissance']),
        ':situation_familiale' => trim($_POST['situation_familiale']),
        ':nombre_enfants'      => (int)($_POST['nombre_enfants'] ?? 0),
        ':prenom_pere'         => trim($_POST['prenom_pere']),
        ':nom_prenom_mere'     => trim($_POST['nom_prenom_mere']),
        ':adresse'             => trim($_POST['adresse']),
        ':wilaya_residence'    => trim($_POST['wilaya_residence']),
        ':telephone'           => trim($_POST['telephone']),
        ':no_assurance_cnas'   => trim($_POST['no_assurance_cnas']),
        ':compte_a_paye'       => trim($_POST['compte_a_paye']),
        ':photo'               => $photo_name,
        ':id'                  => $id,
    ]);
    ok(['message' => 'Employé modifié avec succès', 'photo' => $photo_name]);

} catch (PDOException $e) {
    fail('Erreur DB : ' . $e->getMessage());
}