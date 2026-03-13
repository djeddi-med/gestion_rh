<?php
/**
 * php/parametres/save_permissions.php
 * Enregistre (INSERT ou UPDATE) les permissions d'un utilisateur
 * Reçoit : user_id + permissions (JSON : { module: { view:0, add:1, ... } })
 */
require_once __DIR__ . '/../auth.php';
requireAdmin();
require_once __DIR__ . '/../connect_db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$userId      = (int)($_POST['user_id'] ?? 0);
$permJson    = $_POST['permissions'] ?? '';

if (!$userId || !$permJson) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

$permissions = json_decode($permJson, true);
if (!is_array($permissions)) {
    echo json_encode(['success' => false, 'message' => 'Format permissions invalide']);
    exit;
}

// Modules autorisés (whitelist)
$allowedModules = [
    'employee', 'contrat', 'cnas', 'certificat', 'fin_relation',
    'mise_en_demeure', 'titre_conge', 'reprise_travail', 'absences',
    'pointages', 'situation_effectifs', 'ordre_mission', 'decharge', 'global'
];

try {
    $conn->beginTransaction();

    // Supprimer les anciennes permissions de cet utilisateur
    $del = $conn->prepare("DELETE FROM permissions WHERE user_id = :user_id");
    $del->execute([':user_id' => $userId]);

    // Réinsérer toutes les permissions
    $ins = $conn->prepare("
        INSERT INTO permissions (user_id, module, can_view, can_add, can_edit, can_delete, can_print)
        VALUES (:user_id, :module, :view, :add, :edit, :delete, :print)
    ");

    foreach ($permissions as $module => $actions) {
        if (!in_array($module, $allowedModules, true)) continue;

        $ins->execute([
            ':user_id' => $userId,
            ':module'  => $module,
            ':view'    => (int)($actions['view']   ?? 0),
            ':add'     => (int)($actions['add']    ?? 0),
            ':edit'    => (int)($actions['edit']   ?? 0),
            ':delete'  => (int)($actions['delete'] ?? 0),
            ':print'   => (int)($actions['print']  ?? 0),
        ]);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Permissions enregistrées avec succès']);

} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erreur base de données : ' . $e->getMessage()]);
}