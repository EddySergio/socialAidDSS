<?php
// index.php - Point d'entrée de l'application

// Démarre la session PHP
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// -------------------------------------------------------------------------
// LOGIQUE DE REDIRECTION
// -------------------------------------------------------------------------

// Vérifie si l'utilisateur est connecté via l'existence de l'ID utilisateur en session
if (isset($_SESSION['ID_USER'])) {
    // CAS 1 : Utilisateur connecté
    // Le rediriger vers le tableau de bord (page sécurisée)
    header("Location: gestion_de_projet.php");
    exit;
} else {
    // CAS 2 : Utilisateur non connecté
    // Le rediriger vers la page de connexion (pour l'authentification)
    header("Location: login.php");
    exit;
}
?>