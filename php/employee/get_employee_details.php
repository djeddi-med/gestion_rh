<?php
/**
 * php/employee/get_employee_details.php
 * Retourne toutes les infos d'un employé + ses contrats (ordre décroissant)
 */
require_once __DIR__ . '/../auth.php';
requireLogin();
require_once __DIR__ . '/../connect_db.php';

header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit;
}

try {
    // ── Infos employé ──
    $stmt = $conn->prepare("
        SELECT id, matricule, civilite, nom, prenom,
               date_naissance, presume, lieu_naissance, no_acte_naissance,
               situation_familiale, nombre_enfants,
               prenom_pere, nom_prenom_mere,
               adresse, wilaya_residence,
               telephone, no_assurance_cnas, compte_a_paye,
               photo, etat, date_creation, user
        FROM employee
        WHERE id = :id
    ");
    $stmt->execute([':id' => $id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        echo json_encode(['success' => false, 'message' => 'Employé introuvable']);
        exit;
    }

    // ── Contrats triés par date_creation DESC ──
    $stmtC = $conn->prepare("
        SELECT id, ref, type_contrat, periode_essai,
               date_debut, date_fin, salaire,
               affectation, poste, etat, date_creation
        FROM contrat
        WHERE id_employee = :id
        ORDER BY date_creation DESC
    ");
    $stmtC->execute([':id' => $id]);
    $contrats = $stmtC->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'  => true,
        'employee' => $employee,
        'contrats' => $contrats,
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur DB : ' . $e->getMessage()]);
}