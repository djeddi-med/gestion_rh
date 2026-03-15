<?php
/**
 * php/parametres/manage_user.php
 * Gestion CRUD utilisateurs — réservé admin
 *
 * Actions : add | edit | delete | toggle_statut | reset_password
 */
require_once __DIR__ . '/../auth.php';
requireAdmin();
require_once __DIR__ . '/../connect_db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$action = trim($_POST['action'] ?? '');

try {
    switch ($action) {

        // ─────────────────────────────
        case 'add':
            $nom    = trim($_POST['nom_prenom'] ?? '');
            $email  = trim($_POST['email'] ?? '');
            $pwd    = $_POST['password'] ?? '';
            $roles  = in_array($_POST['roles'] ?? '', ['admin','user']) ? $_POST['roles'] : 'user';
            $statut = in_array($_POST['statut'] ?? '', ['actif','inactif']) ? $_POST['statut'] : 'actif';

            if (!$nom || !$email || !$pwd) {
                throw new Exception('Nom, email et mot de passe sont obligatoires');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Format email invalide');
            }
            if (strlen($pwd) < 6) {
                throw new Exception('Le mot de passe doit contenir au moins 6 caractères');
            }

            // Vérifier email unique
            $check = $conn->prepare("SELECT id FROM users WHERE email = :email");
            $check->execute([':email' => $email]);
            if ($check->fetch()) throw new Exception('Cet email est déjà utilisé');

            $photo = handlePhotoUpload(null);
            $hash  = password_hash($pwd, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO users (nom_prenom, email, password, roles, statut, photo, date_creation)
                VALUES (:nom, :email, :pwd, :roles, :statut, :photo, NOW())
            ");
            $stmt->execute([
                ':nom'    => $nom,
                ':email'  => $email,
                ':pwd'    => $hash,
                ':roles'  => $roles,
                ':statut' => $statut,
                ':photo'  => $photo,
            ]);

            echo json_encode(['success' => true, 'message' => 'Utilisateur créé avec succès']);
            break;

        // ─────────────────────────────
        case 'edit':
            $id     = (int)($_POST['id'] ?? 0);
            $nom    = trim($_POST['nom_prenom'] ?? '');
            $email  = trim($_POST['email'] ?? '');
            $pwd    = $_POST['password'] ?? '';
            $roles  = in_array($_POST['roles'] ?? '', ['admin','user']) ? $_POST['roles'] : 'user';
            $statut = in_array($_POST['statut'] ?? '', ['actif','inactif']) ? $_POST['statut'] : 'actif';

            if (!$id || !$nom || !$email) {
                throw new Exception('Données manquantes');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Format email invalide');
            }
            if ($pwd && strlen($pwd) < 6) {
                throw new Exception('Le mot de passe doit contenir au moins 6 caractères');
            }

            // Vérifier email unique (hors l'utilisateur lui-même)
            $check = $conn->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
            $check->execute([':email' => $email, ':id' => $id]);
            if ($check->fetch()) throw new Exception('Cet email est déjà utilisé');

            // Récupérer la photo actuelle
            $current = $conn->prepare("SELECT photo FROM users WHERE id = :id");
            $current->execute([':id' => $id]);
            $currentPhoto = $current->fetchColumn();

            $photo = handlePhotoUpload($currentPhoto);

            // Construire la requête dynamiquement
            $sets   = ['nom_prenom = :nom', 'email = :email', 'roles = :roles', 'statut = :statut', 'photo = :photo', 'date_modification = NOW()'];
            $params = [':nom' => $nom, ':email' => $email, ':roles' => $roles, ':statut' => $statut, ':photo' => $photo, ':id' => $id];

            if ($pwd) {
                $sets[]           = 'password = :pwd';
                $params[':pwd']   = password_hash($pwd, PASSWORD_DEFAULT);
            }

            $stmt = $conn->prepare("UPDATE users SET " . implode(', ', $sets) . " WHERE id = :id");
            $stmt->execute($params);

            echo json_encode(['success' => true, 'message' => 'Utilisateur modifié avec succès']);
            break;

        // ─────────────────────────────
        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) throw new Exception('ID manquant');

            // Interdire de supprimer un admin
            $check = $conn->prepare("SELECT roles FROM users WHERE id = :id");
            $check->execute([':id' => $id]);
            $user = $check->fetch();
            if (!$user) throw new Exception('Utilisateur introuvable');
            if ($user['roles'] === 'admin') throw new Exception('Impossible de supprimer un administrateur');

            // Supprimer la photo
            $photoRow = $conn->prepare("SELECT photo FROM users WHERE id = :id");
            $photoRow->execute([':id' => $id]);
            $photo = $photoRow->fetchColumn();
            if ($photo && $photo !== 'user.png') {
                $path = __DIR__ . '/../../img/user/' . $photo;
                if (file_exists($path)) unlink($path);
            }

            $stmt = $conn->prepare("DELETE FROM users WHERE id = :id AND roles != 'admin'");
            $stmt->execute([':id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé']);
            break;

        // ─────────────────────────────
        case 'toggle_statut':
            $id     = (int)($_POST['id'] ?? 0);
            $statut = in_array($_POST['statut'] ?? '', ['actif','inactif']) ? $_POST['statut'] : null;
            if (!$id || !$statut) throw new Exception('Données manquantes');

            $stmt = $conn->prepare("UPDATE users SET statut = :statut, date_modification = NOW() WHERE id = :id");
            $stmt->execute([':statut' => $statut, ':id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Statut mis à jour']);
            break;

        // ─────────────────────────────
        case 'reset_password':
            $id  = (int)($_POST['id'] ?? 0);
            $pwd = $_POST['password'] ?? '';
            if (!$id || !$pwd) throw new Exception('Données manquantes');
            if (strlen($pwd) < 6) throw new Exception('Le mot de passe doit contenir au moins 6 caractères');

            $hash = password_hash($pwd, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = :pwd, date_modification = NOW() WHERE id = :id");
            $stmt->execute([':pwd' => $hash, ':id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Mot de passe réinitialisé avec succès']);
            break;

        default:
            throw new Exception('Action inconnue');
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// ─────────────────────────────────────────────
// Helper : upload photo
// ─────────────────────────────────────────────
function handlePhotoUpload(?string $currentPhoto): string {
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        return $currentPhoto ?? 'user.png';
    }

    $file      = $_FILES['photo'];
    $finfo     = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mimeType, $allowedMimes)) {
        throw new Exception('Type de fichier non autorisé (JPG, PNG, GIF, WEBP uniquement)');
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        throw new Exception('La photo ne doit pas dépasser 2 Mo');
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'user_' . uniqid() . '.' . strtolower($ext);
    $dest     = __DIR__ . '/../../img/user/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new Exception('Erreur lors de l\'upload de la photo');
    }

    // Supprimer l'ancienne photo si ce n'est pas la photo par défaut
    if ($currentPhoto && $currentPhoto !== 'user.png') {
        $old = __DIR__ . '/../../img/user/' . $currentPhoto;
        if (file_exists($old)) unlink($old);
    }

    return $filename;
}