<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$nom_serveur = "localhost";
$nom_base = "gestion_stock";
$utilisateur = "root";
$mot_de_passe = "";
try {
    $connexion = new PDO("mysql:host=$nom_serveur;dbname=$nom_base", $utilisateur, $mot_de_passe);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    return $connexion;
} catch (Exception $e) {
    die("Erreur de connexion a la base: ". $e->getMessage());
}
