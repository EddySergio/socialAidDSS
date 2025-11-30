<?php
// Démarrage de la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclusion des fonctions du modèle

// Vérification de l'authentification (sauf pour les pages publiques)
$current_page = basename($_SERVER['PHP_SELF']);
$public_pages = ['login.php', 'register.php', 'index.php'];

if (!in_array($current_page, $public_pages) && (!isset($_SESSION['ID_USER']) || !isset($_SESSION['NOM_USER']))) {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas authentifié
    header("Location: login.php");
    exit;
}

// Le titre de la page est défini dans le script appelant (ex: $pageTitle = "Gestion de Projet")
$finalPageTitle = isset($pageTitle) ? "SocialAidDSS - " . $pageTitle : "SocialAidDSS";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($finalPageTitle) ?></title>
    <!-- Chargement de Bootstrap 5.3 pour l'esthétique et le dynamisme -->
    <link href="../public/css/bootstrap.min.css" rel="stylesheet">
    <!-- Importation de la police Inter depuis Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="../public/node_modules/bootstrap-icons/font/bootstrap-icons.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Style personnalisé -->
    <link href="../public/css/bootstrap.min.css" rel="stylesheet"> 
    <!-- Chargement des icônes Bootstrap en local -->
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="../public/css/dataTables.bootstrap4.min.css">

    <link href="../public/css/bootstrap-icons.css" rel="stylesheet">
    <!-- Chargement de la police Inter en local -->
    <!-- ========================================================================= -->
    <!-- INSPIRATION : On utilise maintenant uniquement les polices locales du projet. -->
    <!-- ========================================================================= -->
    <link href="../public/css/local-fonts.css" rel="stylesheet">
    <!-- Style personnalisé -->
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
    <style>
        /* Définition d'une palette de couleurs personnalisée */
        :root {
            --custom-primary: #5D87FF; /* Un bleu doux et moderne */
            --custom-primary-dark: #4a6fdd;
            --custom-light: #f0f2f5; /* Un fond gris très clair pour réduire la fatigue oculaire */
            --custom-light-bg: #f0f2f5; /* Fond général clair */
            --custom-dark-text: #343a40; /* Texte sombre par défaut */
            --card-bg: #ffffff; /* Fond de carte clair */
            --card-border-color: #e9ecef; /* Bordure de carte claire */
            --card-header-bg: #f6f8fa; /* Fond d'en-tête de carte clair */

            /* Custom shadows for light mode */
            --bs-box-shadow-sm: 0 .125rem .25rem rgba(0,0,0,.075);
            --custom-dark: #343a40;
            --card-header-bg: #f6f8fa;
        }

        [data-bs-theme="dark"] {
            --bs-body-bg: #1a1a1a;
            --bs-body-color: #dee2e6;
            --bs-border-color: #444;
            --bs-card-bg: #242424;
            --bs-tertiary-bg: #2b2b2b;
            --card-header-bg: #2b2b2b;

            --custom-primary: #7a9bff; /* Un bleu plus clair pour le mode sombre */
            --custom-primary-dark: #6282e6; /* Un bleu plus clair et foncé pour le mode sombre */
            --custom-light-bg: #1a1a1a; /* Le fond général en mode sombre */
            --custom-dark-text: #dee2e6; /* Texte clair en mode sombre */
            --card-bg: #242424; /* Fond de carte sombre */
            --card-border-color: #444; /* Bordure de carte sombre */
            --card-header-bg: #2b2b2b; /* Fond d'en-tête de carte sombre */

            /* Custom shadows for dark mode */
            --bs-box-shadow-sm: 0 .125rem .25rem rgba(0,0,0,.2);
            --bs-box-shadow: 0 .5rem 1rem rgba(0,0,0,.3);
            --bs-box-shadow-lg: 0 1rem 3rem rgba(0,0,0,.4);

            .table-light {
                --bs-table-bg: #2b2b2b;
                --bs-table-color: #dee2e6;
                --bs-table-bg: var(--card-header-bg); /* Use card header bg for light table header in dark mode */
                --bs-table-color: var(--bs-body-color);
            }
            .table-striped > tbody > tr:nth-of-type(odd) > * {
                --bs-table-accent-bg: var(--bs-tertiary-bg);
            }
            .table-success {
                --bs-table-bg: rgba(25, 135, 84, 0.3); /* Slightly darker success for dark mode */
            }
            .alert-success {
                --bs-alert-bg: #198754;
                --bs-alert-color: #ffffff;
                --bs-alert-border-color: #198754;
            }
            .alert-danger {
                --bs-alert-bg: #dc3545;
                --bs-alert-color: #ffffff;
                --bs-alert-border-color: #dc3545;
            }
            .alert-dismissible .btn-close {
                filter: invert(1) grayscale(100%) brightness(200%);
            }

            .alert-success {
                --bs-alert-bg: rgba(25, 135, 84, 0.3);
                --bs-alert-color: var(--bs-body-color);
                --bs-alert-border-color: var(--bs-success);
            }
            .alert-danger {
                --bs-alert-bg: rgba(220, 53, 69, 0.3);
                --bs-alert-color: var(--bs-body-color);
                --bs-alert-border-color: var(--bs-danger);
            }
            .table-hover > tbody > tr:hover > * {
                --bs-table-accent-bg: #363636;
            }
        }

        body {
            background-color: var(--custom-light-bg);
            font-family: 'Inter', sans-serif;
            color: var(--custom-dark-text);
        }
        /* Personnalisation de la barre de navigation */
        .navbar {
            box-shadow: 0 2px 8px rgba(0,0,0,.06); /* Une ombre encore plus douce */
        }
        .navbar-dark.bg-primary {
            background-color: var(--custom-primary) !important;
            box-shadow: var(--bs-box-shadow-sm); /* Utilise la variable d'ombre */
        }
        .btn-primary {
            background-color: var(--custom-primary);
            border-color: var(--custom-primary);
        }
        .btn-primary:hover {
            background-color: var(--custom-primary-dark); /* Utilise la variable custom-primary */
            border-color: var(--custom-primary-dark); /* Utilise la variable custom-primary-dark */
        }
        .card {
            border-radius: 12px; /* Bords légèrement plus arrondis */
            border: 1px solid #e9ecef; /* Bordure très subtile */
            box-shadow: 0 4px 12px rgba(0,0,0,0.08); /* Ombre plus visible mais douce */
            border: 1px solid var(--card-border-color);
            background-color: var(--card-bg);
            box-shadow: var(--bs-box-shadow); /* Use general shadow variable */
        }
        .card-header {
            background-color: var(--card-header-bg);
            border-bottom: 1px solid #e9ecef;
            border-bottom: 1px solid var(--card-border-color);
            color: var(--custom-dark-text);
        }
        /* Override Bootstrap shadow utility classes to use our custom variables */
        .shadow-sm { box-shadow: var(--bs-box-shadow-sm) !important; }
        .shadow { box-shadow: var(--bs-box-shadow) !important; }
        .shadow-lg { box-shadow: var(--bs-box-shadow-lg) !important; }

        /* Ensure text colors adapt */
        .navbar-brand {
            color: var(--bs-white);
        }
        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.75);
        }
        [data-bs-theme="dark"] .navbar-nav .nav-link {
            color: var(--bs-body-color);
        }
        .text-primary { color: var(--custom-primary) !important; }
        .text-muted { color: var(--bs-secondary-color) !important; } /* Ensure muted text adapts */

        /* Form elements */
        .form-control, .form-select, .form-text {
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            border-color: var(--bs-border-color);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--custom-primary);
            box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), .25);
        }
        .form-check-input {
            background-color: var(--bs-body-bg);
            border-color: var(--bs-border-color);
        }
        .form-check-input:checked {
            background-color: var(--custom-primary);
            border-color: var(--custom-primary);
        }

        /* List groups */
        .list-group-item {
            background-color: var(--card-bg);
            color: var(--bs-body-color);
            border-color: var(--card-border-color);
        }
        .list-group-item:hover {
            background-color: var(--bs-tertiary-bg);
        }

        /* Tables */
        .table {
            color: var(--bs-body-color);
            border-color: var(--bs-border-color);
        }
        .table-bordered {
            border-color: var(--bs-border-color);
        }
        .table-hover > tbody > tr:hover > * {
            background-color: var(--bs-tertiary-bg);
        }
        /* Alerts */
        .alert {
            background-color: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            border: none;
        }
        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
            border-color: #badbcc;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
            border-color: #f5c2c7;
        }
        .alert-info {
            background-color: #cff4fc;
            color: #055160;
        }
    </style>
</head>
<body>

<!-- Barre de navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="gestion_de_projet.php">
            <!-- Logo SVG simple et moderne -->
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-shield-check me-2" viewBox="0 0 16 16">
                <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.06.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.523zM13.292 2.788A60.473 60.473 0 0 1 8 2.067a60.473 60.473 0 0 1-5.292.721c.28.658.486 1.425.688 2.276.632 2.786.83 5.857-.2 8.475A9.72 9.72 0 0 1 8 14.56a9.72 9.72 0 0 1-4.78-1.298c-1.03-2.618-.828-5.69-.2-8.475A59.75 59.75 0 0 1 2.708 2.788z"/>
                <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
            </svg>
            SocialAid<span class="fw-light">DSS</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <!-- Les liens de navigation sont maintenant générés dynamiquement ci-dessous -->
            </ul>
            <!-- Bouton de bascule de thème -->
            <!-- <ul class="navbar-nav me-3">
                <li class="nav-item d-flex align-items-center">
                    <div class="form-check form-switch nav-link">
                        <input class="form-check-input" type="checkbox" role="switch" id="themeSwitch">
                        <label class="form-check-label" for="themeSwitch"><i class="bi bi-moon-stars-fill"></i></label>
                    </div>
                </li>
            </ul>
                -->
            <?php if (isset($_SESSION['ID_USER'])): ?>
                <!-- Menu utilisateur connecté -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i> 
                            <?= htmlspecialchars($_SESSION['NOM_USER']) ?>
                            <?= htmlspecialchars($_SESSION['EMAIL']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                            <li><a class="dropdown-item" href="gestion_de_projet.php">Mes Projets</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Déconnexion</a></li>
                        </ul>
                    </li>
                </ul>
            <?php else: ?>
                <!-- Boutons pour utilisateur non connecté -->
                <div class="d-flex">
                    <a href="login.php" class="btn btn-outline-secondary me-2">Connexion</a>
                    <a href="register.php" class="btn btn-secondary">Inscription</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Conteneur principal du contenu de la page -->
<main class="container py-5">
    <?php
    // =========================================================================
    // AFFICHAGE CENTRALISÉ DES MESSAGES DE SESSION
    // Affiche temporairement tous les messages (succès, erreur, exception)
    // =========================================================================
    if (isset($_SESSION['message'])) {
        $msg_type = $_SESSION['message']['type'] ?? 'info';
        $msg_text = $_SESSION['message']['text'] ?? 'Message non défini.';

        // Associer un type de message à une icône Bootstrap
        $icon_map = [
            'success' => 'bi-check-circle-fill',
            'danger'  => 'bi-exclamation-triangle-fill',
            'warning' => 'bi-exclamation-triangle-fill',
            'info'    => 'bi-info-circle-fill'
        ];
        $icon = $icon_map[$msg_type] ?? 'bi-info-circle-fill';

        echo '<div id="session-alert" class="alert alert-' . htmlspecialchars($msg_type) . ' alert-dismissible fade show d-flex align-items-center shadow-lg" role="alert">';
        echo '  <i class="bi ' . $icon . ' me-3 fs-4"></i>';
        echo '  <div class="w-100">' . $msg_text . '</div>';
        echo '  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';

        unset($_SESSION['message']); // Le message est affiché une seule fois
    }
    ?>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="row">
            <!-- Le contenu de la page spécifique sera inséré ici -->