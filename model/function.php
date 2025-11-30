<?php
// =========================================================================
// CHARGEUR PRINCIPAL DES MODÈLES
// =========================================================================
// Ce fichier centralise la configuration de la base de données et l'inclusion
// de tous les fichiers de modèle nécessaires à l'application.

// =========================================================================
// CHARGEMENT DE L'AUTOLOADER COMPOSER
// =========================================================================
require_once __DIR__ . '/../vendor/autoload.php';

// Définition des constantes pour la connexion à la base de données.
define('DB_HOST', 'localhost');
define('DB_NAME', 'prioritycare'); // Le nom de votre base de données
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Établit et retourne une connexion PDO à la base de données.
 * @return PDO L'objet de connexion PDO.
 */
function dbConnect(): PDO {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    try {
        // Crée une nouvelle instance de PDO pour la connexion.
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Active le mode d'erreur pour lancer des exceptions.
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Définit le mode de récupération par défaut en tableau associatif.
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // En cas d'erreur de connexion, affiche l'erreur et arrête le script.
        // En production, on utiliserait un message d'erreur générique.
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}

// Inclusion des différents fichiers de modèle pour organiser le code par fonctionnalité.
require_once 'auth.model.php';     // Fonctions pour l'authentification des utilisateurs
require_once 'projet.model.php';   // Fonctions pour la gestion des projets
require_once 'critere.model.php';  // Fonctions pour la gestion des critères
require_once 'personne.model.php'; // Fonctions pour la gestion des personnes et de leurs performances
require_once 'calcul.model.php';   // Fonctions pour les calculs (Fuzzy CODAS)
require_once 'optionCritere.php'; // Fonctions pour la gestion des options de critères qualitatives
require_once 'poids.model.php'; // Fonctions pour le calcul des poids

/**
 * Génère dynamiquement une échelle de nombres flous triangulaires (TFN)
 * en fonction du nombre d'options et d'un rang.
 *
 * @param int $rank Le rang de l'option (1 = meilleur).
 * @param int $optionCount Le nombre total d'options pour le critère.
 * @return array Le TFN correspondant [l, m, u].
 */
function generateFuzzyValue(int $rank, int $optionCount): array {
    if ($optionCount <= 0) {
        return [0, 0, 0];
    }
    if ($optionCount == 1) {
        return [1, 1, 3]; // Cas où il n'y a qu'une seule option
    }

    // Le rang le plus mauvais (ex: 5 sur 5)
    if ($rank == $optionCount) {
        return [1, 1, 3];
    }

    // Le meilleur rang (rang 1)
    if ($rank == 1) {
        $l = 1 + (2 * ($optionCount - 2));
        $m = $l + 2;
        return [$l, $m, $m]; // ex: pour 5 options -> [7, 9, 9]
    }

    // Pour tous les rangs intermédiaires
    $stepsFromWorst = $optionCount - $rank;
    $l = 1 + (2 * ($stepsFromWorst - 1));
    $m = $l + 2;
    $u = $m + 2;
    return [$l, $m, $u];
}

?>