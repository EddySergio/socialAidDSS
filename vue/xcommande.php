<?php
require_once 'escpos-php-development/autoload.php'; // Inclure l'autoloader de Composer pour charger les dépendances

use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

$page_title = "BON DE COMMANDE";
include 'header.php';

if (!empty($_GET['id'])) {
    $commande = getCommande($_GET['id']);
}

// Fonction pour formater le prix
function formatPrix($prix) {
    return number_format($prix, 2) . ' Ar'; // Formatage du prix avec "Ar" (Ariary)
}

// Connexion à l'imprimante ESC/POS (remplacez 'Printer_Name' par le nom de votre imprimante sur Windows)
$connector = new WindowsPrintConnector("Printer_Name");
$printer = new Printer($connector);

// En-tête du bon de commande
$printer->initialize(); // Initialisation de l'imprimante
$printer->setEmphasis(true); // Activer l'emphase (police en gras)
$printer->text("BON DE COMMANDE\n"); // Titre
$printer->setEmphasis(false); // Désactiver l'emphase

// Contenu du bon de commande
$printer->text("Bon de commande N°: " . $commande[0]['id_lscommande'] . "\n"); // Numéro du bon de commande
$printer->text("Date de commande  : " . date('d/m/Y H:i:s', strtotime($commande[0]['date_commande'])) . "\n"); // Date de commande

if ($commande[0]['date_livraison'] != "1111-11-11") {
    $printer->text("Date de livraison: " . date('d/m/Y', strtotime($commande[0]['date_livraison'])) . "\n"); // Date de livraison
}

$printer->text("De: TRANOMBAROTRA MANAMBINA\n"); // Expéditeur
$printer->text("A: " . $commande[0]['nom_fournisseur'] . "\n"); // Destinataire
$printer->text("\n");

  // Tableau des articles
  $printer->text("----------------------------------------\n");
  $printer->text("| Désignation | Quantité | Prix Unitaire | Prix Total |\n");
  $printer->text("----------------------------------------\n");
  foreach ($ventes as $vente) {
    $printer->text("| " . $vente['nom_article'] . " | " . $vente['quantite'] . " | " . formatPrix($vente['prix'] / $vente['quantite']) . " | " . formatPrix($vente['prix']) . " |\n");
  }
  $printer->text("----------------------------------------\n");

// Total à payer
$printer->text("Total à payer: " . formatPrix($commande[0]['total_commande']) . "\n");

// Commande pour couper le papier
$printer->cut();

// Fermeture de la connexion
$printer->close();

include 'footer.php';
?>
