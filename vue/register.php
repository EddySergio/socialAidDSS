<?php
// Il est recommandé de démarrer la session au tout début du script.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclusion du chargeur de modèles principal
require_once '../model/function.php';

$pageTitle = "Inscription Utilisateur";

// Si l'utilisateur est déjà connecté, le rediriger vers le tableau de bord
if (isset($_SESSION['ID_USER'])) {
    header("Location: gestion_de_projet.php");
    exit;
}

$message = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $message = '<div class="alert alert-danger">Veuillez remplir tous les champs.</div>';
    } elseif ($password !== $confirmPassword) {
        $message = '<div class="alert alert-danger">Les mots de passe ne correspondent pas.</div>';
    } elseif (strlen($password) < 6) {
        $message = '<div class="alert alert-danger">Le mot de passe doit contenir au moins 6 caractères.</div>';
    } else {
        $userId = registerUser($username,$email, $password); // Fonction du modèle

        if ($userId) {
            // Succès de l'inscription : connecter automatiquement et rediriger
            $_SESSION['ID_USER'] = $userId;
            $_SESSION['NOM_USER'] = $username;
            $_SESSION['EMAIL'] = $email;
            $message = '<div class="alert alert-success">Inscription réussie ! Redirection...</div>';
            echo '<meta http-equiv="refresh" content="2;url=gestion_de_projet.php">';
            // Pour des raisons de clarté dans le terminal, nous n'appelons pas header("Location: ...") immédiatement après le message.

        } else {
            $message = '<div class="alert alert-danger">Ce nom d\'utilisateur existe déjà. Veuillez en choisir un autre.</div>';
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
                <h3 class="card-title text-center text-primary fw-bold mb-4">Créer un Compte SocialAidDSS</h3>

                <?= $message ?>

                <form method="POST" action="register.php">
                    <div class="mb-3">
                        <label for="username" class="form-label">Nom d'utilisateur</label>
                        <input type="text" class="form-control" id="username" name="username"
                            value="<?= htmlspecialchars($username) ?>" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Email</label>
                        <input type="mail" class="form-control" id="email" name="email"
                            value="<?= htmlspecialchars($username) ?>" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
                    </div>
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirmer le Mot de passe</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg shadow-sm">S'inscrire et Commencer</button>
                    </div>
                </form>

                <p class="text-center mt-4">
                    Déjà un compte ? <a href="login.php" class="text-primary fw-bold">Se connecter ici</a>.
                </p>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS Bundle (optionnel, mais requis pour les composants JS comme les menus déroulants) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
