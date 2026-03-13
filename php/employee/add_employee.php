<?php
/**
 * php/employee/add_employee.php
 * — Matricule calculé ici 
 * — ob_start() pour éviter tout warning/notice avant le JSON
 * — Détection doublon (nom + prénom + date_naissance/presume)
 * — Photo nommée {matricule}.ext  →  img/employee/
 */
ob_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../connect_db.php';

/* ── Helpers ── */
function fail(string $msg): void {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}
function ok(array $data): void {
    ob_end_clean();
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

/* ── Matricule seul (appelé par GET pour l'affichage dans le modal) ── */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->prepare("SELECT mat FROM matricule LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        ob_end_clean();
        echo json_encode($row
            ? ['success' => true, 'next_matricule' => (int)$row['mat'] + 1]
            : ['success' => false, 'message' => 'Table matricule vide']);
    } catch (PDOException $e) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Erreur DB : ' . $e->getMessage()]);
    }
    exit;
}

/* ── POST uniquement ── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Méthode non autorisée');

/* ── Validation champs requis ── */
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

/* ── Normalisation ── */
$nom    = strtoupper(trim($_POST['nom']));
$prenom = ucfirst(strtolower(trim($_POST['prenom'])));
$date_naissance = !empty($_POST['date_naissance']) ? $_POST['date_naissance'] : null;
$presume        = !empty($_POST['presume'])        ? (int)$_POST['presume']   : null;

/* ── Vérification doublon ── */
try {
    $chk = $conn->prepare(
        "SELECT id, matricule FROM employee
         WHERE nom = :nom AND prenom = :prenom
           AND (
                 (:dn IS NOT NULL AND date_naissance = :dn2)
              OR (:pr IS NOT NULL AND presume = :pr2)
           )
         LIMIT 1"
    );
    $chk->execute([':nom'=>$nom,':prenom'=>$prenom,
                   ':dn'=>$date_naissance,':dn2'=>$date_naissance,
                   ':pr'=>$presume,':pr2'=>$presume]);
    $existing = $chk->fetch(PDO::FETCH_ASSOC);
    if ($existing) {
        fail("Cet employé existe déjà (matricule {$existing['matricule']}) — enregistrement annulé");
    }
} catch (PDOException $e) {
    fail('Erreur vérification doublon : ' . $e->getMessage());
}

/* ── Transaction ── */
try {
    $conn->beginTransaction();

    /* Matricule */
    $row_m = $conn->query("SELECT mat FROM matricule LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if (!$row_m) throw new Exception('Table matricule vide ou introuvable');
    $new_mat = (int)$row_m['mat'] + 1;
    $conn->prepare("UPDATE matricule SET mat = :m")->execute([':m' => $new_mat]);

    /* Photo */
    $photo_name = 'user.png';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif']))
            throw new Exception('Type de photo non autorisé (JPG, PNG, GIF)');
        if ($_FILES['photo']['size'] > 2 * 1024 * 1024)
            throw new Exception('Photo trop lourde (max 2 Mo)');
        $photo_name = $new_mat . '.' . $ext;
        $dir = __DIR__ . '/../../img/employee/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dir . $photo_name))
            throw new Exception("Erreur lors de l'enregistrement de la photo");
    }

    /* Insertion */
    $stmt = $conn->prepare("
        INSERT INTO employee (
            matricule, civilite, nom, prenom, date_naissance, presume,
            lieu_naissance, no_acte_naissance, situation_familiale, nombre_enfants,
            prenom_pere, nom_prenom_mere, adresse, wilaya_residence, telephone,
            no_assurance_cnas, compte_a_paye, photo, date_creation, user
        ) VALUES (
            :matricule, :civilite, :nom, :prenom, :date_naissance, :presume,
            :lieu_naissance, :no_acte_naissance, :situation_familiale, :nombre_enfants,
            :prenom_pere, :nom_prenom_mere, :adresse, :wilaya_residence, :telephone,
            :no_assurance_cnas, :compte_a_paye, :photo, NOW(), :user
        )
    ");
    $stmt->execute([
        ':matricule'           => $new_mat,
        ':civilite'            => trim($_POST['civilite']),
        ':nom'                 => $nom,
        ':prenom'              => $prenom,
        ':date_naissance'      => $date_naissance,
        ':presume'             => $presume,
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
        ':user'                => $_SESSION['user_name'] ?? 'admin',
    ]);

    $conn->commit();
    ok(['message'=>'Employé recruté avec succès','matricule'=>$new_mat,'employee_id'=>$conn->lastInsertId(),'photo'=>$photo_name]);

} catch (PDOException $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    fail('Erreur DB : ' . $e->getMessage());
} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    fail($e->getMessage());
}