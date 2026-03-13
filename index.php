<?php
// 1. On inclut la sécurité de session en premier
require_once 'php/auth.php'; 
require_once 'php/connect_db.php';

$error_message = '';
$success_message = '';

// Si l'utilisateur est déjà connecté, on le redirige directement
if (isset($_SESSION['user_id'])) {
    header('Location: dnk.php');
    exit;
}

// 2. Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error_message = "Veuillez remplir tous les champs";
    } else {
        try {
            // Requête SQL optimisée pour correspondre à votre structure 'users'
            $query = "SELECT id, nom_prenom, email, password, statut, roles, photo FROM users WHERE email = :email AND statut = 'actif' LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 3. Vérification du mot de passe
            if ($user && password_verify($password, $user['password'])) {
                
                // Sécurité : on régénère l'ID de session après un login réussi
                session_regenerate_id(true);

                // 4. On remplit la SESSION (Indispensable pour auth.php)
                $_SESSION['user_id']     = $user['id'];
                $_SESSION['user_name']   = $user['nom_prenom'];
                $_SESSION['user_email']  = $user['email'];
                $_SESSION['user_role']   = $user['roles']; // 'admin' ou 'user'
                $_SESSION['user_status'] = $user['statut'];
                $_SESSION['user_photo']  = $user['photo'] ?? 'user.png';

                // 5. Chargement des permissions (Bypass automatique si admin)
                loadPermissions($conn, (int)$user['id']);

                $success_message = "Connexion réussie ! Redirection...";
                
                // Redirection via JavaScript pour laisser le temps d'afficher le message de succès
                echo "<script>
                        setTimeout(function() {
                            window.location.href = 'dnk.php';
                        }, 1200);
                      </script>";
            } else {
                $error_message = "Email ou mot de passe incorrect";
            }
        } catch (PDOException $e) {
            $error_message = "Erreur système. Veuillez réessayer plus tard.";
            // Optionnel : error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Plateforme RH</title>
    <link rel="stylesheet" href="css/connexion.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="img/logo/logo.png" type="image/png">
</head>
<body>
    <div class="notification-container" id="notificationContainer"></div>
    
    <div class="login-container">
        <div class="login-header">
            <div class="logo-container">
                <img src="img/logo/logo.png" alt="Logo" class="logo">
            </div>
            <h1>PLATEFORME DE GESTION RH</h1>
            <h2>EPE EL DJAMIAYA LINAKL OUA EL KHADAMAT SPA</h2>
            <p class="welcome-text">Connectez-vous pour accéder à votre espace</p>
        </div>
        
        <form class="login-form" method="POST" action="">
            <div class="form-group">
                <div class="input-container">
                    <i class="fas fa-envelope icon"></i>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required autofocus>
                    <label for="email" class="input-label">Adresse Email</label>
                </div>
            </div>
            
            <div class="form-group">
                <div class="input-container">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" name="password" id="password" required>
                    <label for="password" class="input-label">Mot de passe</label>
                    <i class="fas fa-eye eye-icon" id="togglePassword" style="cursor:pointer;"></i>
                </div>
            </div>
            
            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember">
                    <span>Se souvenir de moi</span>
                </label>
                <a href="#" class="forgot-password">Mot de passe oublié ?</a>
            </div>
            
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i>
                <span>Se connecter</span>
            </button>
        </form>
        
        <div class="login-footer">
            <p>&copy; <?php echo date('Y'); ?> EPE EL DJAMIAYA LINAKL OUA EL KHADAMAT SPA.</p>
            <p class="version">Version 2.0.0</p>
        </div>
    </div>
    
    <script src="js/connexion.js"></script>
    <script>
        // Affichage des notifications PHP via votre fonction JS existante
        function showNotification(msg, type) {
            const container = document.getElementById('notificationContainer');
            if(container) {
                const notif = document.createElement('div');
                notif.className = `notification ${type}`;
                notif.innerHTML = msg;
                container.appendChild(notif);
                setTimeout(() => notif.remove(), 4000);
            }
        }

        <?php if ($error_message): ?>
            showNotification("<?php echo $error_message; ?>", 'error');
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            showNotification("<?php echo $success_message; ?>", 'success');
        <?php endif; ?>

        // Toggle Password Visibility
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>