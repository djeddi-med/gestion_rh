<?php
/**
 * php/parametres/show_users.php
 * Retourne la liste des utilisateurs (pour l'onglet Users et le select Permissions)
 */
require_once __DIR__ . '/../auth.php';
requireAdmin();
require_once __DIR__ . '/../connect_db.php';

header('Content-Type: application/json');

$search      = trim($_GET['search'] ?? '');
$rolesFilter = trim($_GET['roles_filter'] ?? '');

try {
    $where  = ['1'];
    $params = [];

    if ($search) {
        $where[]           = '(nom_prenom LIKE :search OR email LIKE :search)';
        $params[':search'] = "%$search%";
    }
    if ($rolesFilter) {
        $where[]           = 'roles = :roles';
        $params[':roles']  = $rolesFilter;
    }

    $sql  = "SELECT id, nom_prenom, email, roles, statut, photo, date_creation
             FROM users
             WHERE " . implode(' AND ', $where) . "
             ORDER BY roles ASC, nom_prenom ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'users' => $users]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur base de données : ' . $e->getMessage()]);
}