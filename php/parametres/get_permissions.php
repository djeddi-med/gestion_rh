<?php
/**
 * php/parametres/get_permissions.php
 * Retourne les permissions d'un utilisateur sous forme de tableau indexé par module
 */
require_once __DIR__ . '/../auth.php';
requireAdmin();
require_once __DIR__ . '/../connect_db.php';

header('Content-Type: application/json');

$userId = (int)($_GET['user_id'] ?? 0);
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'user_id manquant']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT module, can_view, can_add, can_edit, can_delete, can_print
        FROM permissions
        WHERE user_id = :user_id
    ");
    $stmt->execute([':user_id' => $userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $permissions = [];
    foreach ($rows as $row) {
        $permissions[$row['module']] = [
            'view'   => (bool)$row['can_view'],
            'add'    => (bool)$row['can_add'],
            'edit'   => (bool)$row['can_edit'],
            'delete' => (bool)$row['can_delete'],
            'print'  => (bool)$row['can_print'],
        ];
    }

    echo json_encode(['success' => true, 'permissions' => $permissions]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}