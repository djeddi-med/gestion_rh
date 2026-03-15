<?php
/**
 * php/contrat/get_employee_history.php
 * Retourne : infos employé + tous ses contrats du plus récent au plus ancien
 * GET ?employee_id=X
 */
ob_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../connect_db.php';

$employee_id = (int)($_GET['employee_id'] ?? 0);
if (!$employee_id) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'ID employé manquant']);
    exit;
}

try {
    /* ── Infos employé ── */
    $stmt_emp = $conn->prepare("
        SELECT id, matricule, civilite, nom, prenom, photo, etat
        FROM employee WHERE id = :id LIMIT 1
    ");
    $stmt_emp->execute([':id' => $employee_id]);
    $employee = $stmt_emp->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Employé introuvable']);
        exit;
    }

    /* ── Tous ses contrats — du plus récent au plus ancien ── */
    $stmt_ct = $conn->prepare("
        SELECT id, ref, type_contrat, periode_essai,
               date_debut, date_fin, salaire,
               affectation, poste, etat, date_creation, user
        FROM contrat
        WHERE id_employee = :id
        ORDER BY date_debut DESC, id DESC
    ");
    $stmt_ct->execute([':id' => $employee_id]);
    $contrats = $stmt_ct->fetchAll(PDO::FETCH_ASSOC);

    /* ── Poste/affectation courant ── */
    $today              = date('Y-m-d');
    $poste_actuel       = '';
    $affectation_actuel = '';

    foreach ($contrats as $c) {
        if ($c['etat'] === 'actif' && (!$c['date_fin'] || $c['date_fin'] >= $today)) {
            $poste_actuel       = $c['poste'];
            $affectation_actuel = $c['affectation'];
            break;
        }
    }
    if (!$poste_actuel && count($contrats) > 0) {
        $poste_actuel       = $contrats[0]['poste'];
        $affectation_actuel = $contrats[0]['affectation'];
    }

    ob_end_clean();
    echo json_encode([
        'success'  => true,
        'employee' => array_merge($employee, [
            'poste_actuel'         => $poste_actuel,
            'affectation_actuelle' => $affectation_actuel,
        ]),
        'contrats' => $contrats,
        'total'    => count($contrats),
    ]);

} catch (PDOException $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Erreur DB : ' . $e->getMessage()]);
}