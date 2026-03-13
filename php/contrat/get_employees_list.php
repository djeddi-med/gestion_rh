<?php
/**
 * php/contrat/get_employees_list.php
 * Retourne les employés actifs pour la recherche dans le modal contrat
 * GET ?search=xxx
 */
ob_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../connect_db.php';

$search = trim($_GET['search'] ?? '');
$params = [];

try {
    $sql = "SELECT id, matricule, nom, prenom, civilite, photo
            FROM employee
            WHERE etat = 'actif'";

    if ($search !== '') {
        $like = "%$search%";
        $sql .= " AND (nom LIKE :s OR prenom LIKE :s2 OR matricule LIKE :s3)";
        $params = [':s' => $like, ':s2' => $like, ':s3' => $like];
    }
    $sql .= " ORDER BY nom, prenom LIMIT 50";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    ob_end_clean();
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}