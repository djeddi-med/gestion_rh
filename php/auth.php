<?php
/**
 * auth.php — Garde de session centralisée
 *
 * Rôles : 'admin' (accès total) | 'user' (accès selon table permissions)
 * Table : users       (id, nom_prenom, email, password, statut, roles, photo)
 * Table : permissions (user_id, module, can_view, can_add, can_edit, can_delete, can_print)
 *
 * Toutes les fonctions sont protégées par function_exists()
 * pour éviter les erreurs de double déclaration.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ═══════════════════════════════════════════════
// 1. AUTHENTIFICATION
// ═══════════════════════════════════════════════

if (!function_exists('requireLogin')) {
    function requireLogin(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php');
            exit;
        }
        // Filet de sécurité : session ancienne sans rôle ni permissions
        if (!isset($_SESSION['user_role']) || !isset($_SESSION['permissions'])) {
            _refreshSessionFromDB();
        }
    }
}

if (!function_exists('_refreshSessionFromDB')) {
    function _refreshSessionFromDB(): void {
        try {
            require_once __DIR__ . '/connect_db.php';
            $stmt = $conn->prepare("SELECT roles, nom_prenom, photo FROM users WHERE id = :id AND statut = 'actif'");
            $stmt->execute([':id' => $_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) { logout(); }

            $_SESSION['user_role']  = $user['roles'];
            $_SESSION['user_name']  = $user['nom_prenom'];
            $_SESSION['user_photo'] = $user['photo'] ?? 'user.png';

            loadPermissions($conn, (int) $_SESSION['user_id']);

        } catch (PDOException $e) {
            error_log("Erreur _refreshSessionFromDB : " . $e->getMessage());
        }
    }
}

if (!function_exists('requireAdmin')) {
    function requireAdmin(): void {
        requireLogin();
        if (!isAdmin()) {
            header('Location: dnk.php?error=access_denied');
            exit;
        }
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin(): bool {
        return ($_SESSION['user_role'] ?? '') === 'admin';
    }
}

if (!function_exists('sessionGet')) {
    function sessionGet(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
}

// ═══════════════════════════════════════════════
// 2. PERMISSIONS PAR MODULE
// ═══════════════════════════════════════════════

if (!function_exists('loadPermissions')) {
    function loadPermissions(PDO $conn, int $userId): void {
        if (($_SESSION['user_role'] ?? '') === 'admin') {
            $_SESSION['permissions'] = ['__admin__' => true];
            return;
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
                    'view'   => (bool) $row['can_view'],
                    'add'    => (bool) $row['can_add'],
                    'edit'   => (bool) $row['can_edit'],
                    'delete' => (bool) $row['can_delete'],
                    'print'  => (bool) $row['can_print'],
                ];
            }
            $_SESSION['permissions'] = $permissions;

        } catch (PDOException $e) {
            error_log("Erreur loadPermissions user $userId : " . $e->getMessage());
            $_SESSION['permissions'] = [];
        }
    }
}

if (!function_exists('can')) {
    /**
     * @param string $module  employee|contrat|cnas|certificat|fin_relation|
     *                        mise_en_demeure|titre_conge|reprise_travail|absences|
     *                        pointages|situation_effectifs|ordre_mission|decharge|global
     * @param string $action  view|add|edit|delete|print
     */
    function can(string $module, string $action): bool {
        if (isAdmin()) return true;
        return $_SESSION['permissions'][$module][$action] ?? false;
    }
}

if (!function_exists('requirePermission')) {
    function requirePermission(string $module, string $action): void {
        requireLogin();
        if (!can($module, $action)) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé : permission insuffisante']);
            exit;
        }
    }
}

// ═══════════════════════════════════════════════
// 3. DÉCONNEXION
// ═══════════════════════════════════════════════

if (!function_exists('logout')) {
    function logout(): void {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        // Chemin absolu — fonctionne quel que soit le sous-dossier (ex: /dnk/)
        $root = str_replace('\\', '/', dirname(__DIR__));
        $base = str_replace(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '', $root);
        header('Location: ' . $base . '/index.php');
        exit;
    }
}