<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Connexion Utilisateur";

// Inclusion du chargeur de modèles principal
require_once '../model/function.php';

$message = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $message = '<div class="alert alert-danger">Veuillez entrer votre nom d\'utilisateur et votre mot de passe.</div>';
    } else {
        $user = authenticateUser($username, $password); // Fonction du modèle

        if ($user) {
            // Succès de la connexion
            $_SESSION['ID_USER'] = $user['ID_USER'];
            $_SESSION['NOM_USER'] = $user['NOM_USER']; 
            $_SESSION['EMAIL'] = $user['EMAIL'];// Ajout du nom d'utilisateur en session
            header("Location: gestion_de_projet.php");
            exit;
        } else {
            $message = '<div class="alert alert-danger">Nom d\'utilisateur ou mot de passe incorrect.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - SocialAidDSS</title>
    <!-- Bootstrap CSS via CDN -->
    <link href="../public/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Applique le thème (clair/sombre) dès le chargement du HTML pour éviter le flash
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        })();
    </script>
</head>
<body>
    <div class="container">
        <div class="col-md-6 offset-md-3">
            <div class="card shadow-lg p-5 mt-5 rounded">
                <h3 class="card-title text-center text-primary fw-bold mb-4">Se Connecter à SocialAidDSS</h3>
                
                <?= $message ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label for="username" class="form-label">Nom d'utilisateur</label>
                        <input type="text" class="form-control" id="username" name="username" 
                            value="<?= htmlspecialchars($username) ?>" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">Connexion</button>
                    </div>
                </form>

                <p class="text-center mt-4">
                    Pas encore de compte ? <a href="register.php" class="text-primary fw-bold">S'inscrire ici</a>.
                </p>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS Bundle (optionnel, mais requis pour les composants JS comme les menus déroulants) -->
    <script src="../public/js/bootstrap.bundle.min.js"></script>
</body>
</html>
