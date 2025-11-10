<?php
include "../model/function.php";
require_once '../escpos-php-development/autoload.php'; // Assuming Composer autoloader is in 'vendor' directory

$ventes = getVente($_GET['id']); // Assuming you have a function to get vente details
  // Définissez le connecteur d'impression en mode "Texte brut" (à adapter)
  use Mike42\Escpos\Printer;
  use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
  $connector = new WindowsPrintConnector("LPT1"); // Remplacez "LPT1" par le port de votre Xprinter

  // Créez une instance de l'imprimante
  $printer = new Printer($connector);

  // En-tête du reçu
  
  $printer->text("             TRANOMBAROTRA MANAMBINA\n");
  $printer->setEmphasis(true);
  $printer->setUnderline(true);
  $printer->setTextSize(2,2);
  $printer->setLineSpacing(2);
  $printer->text("\n");

  // Détails du reçu
  $printer->text("Recu num : " . $ventes[0]['id_panier'] . "\n");
  $printer->text("Date : " . date('d/m/Y H:i:s', strtotime($ventes[0]['date_vente'])) . "\n\n");
  $printer->text("Nom du client : " . $ventes[0]['nom'] . "\n");
  $printer->text("Caissier(e) : " . $ventes[0]['username'] . "\n\n");

  // Tableau des articles
  $printer->text("-----------------------------------------------------------------------\n");
  $printer->text(" Designation          Qtt   Prix Unitaire              Prix Total |\n");
  $printer->text("-----------------------------------------------------------------------\n");
  foreach ($ventes as $vente) {
    $printer->text($vente['nom_article'] . "        " . $vente['quantite'] . " X   " . formatPrix($vente['prix'] / $vente['quantite']) . "               " . formatPrix($vente['prix']) . "   \n");
  }
  $printer->text("-----------------------------------------------------------------------\n");

  // Totaux
  $printer->text("Total a payer = " . formatPrix($ventes[0]['total']) . "\n");
  $printer->text("Paiement      = " . formatPrix($ventes[0]['paiement']) . "\n");
  $rendu = 0;
  if($ventes[0]['paiement'] >= $ventes[0]['total']){
    $rendu = $ventes[0]['paiement'] - $ventes[0]['total'] ;
    }else{
        $rendu = 0;
    }
  $printer->text("A rendre      = " . formatPrix($rendu) . "\n");
  $printer->text("Reste a payer = " . formatPrix($ventes[0]['reste']) . "\n\n");

  // Pied de page
  $printer->text("----------------------------------------\n");
  $printer->text("             Merci pour votre achat !\n");
  $printer->feed(3); // Coupe le papier

  // Ferme l'imprimante
  $printer->close();
  header('Location: ../vue/recuVente.php?id=' . $_GET['id']);

