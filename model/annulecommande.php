<?php
include 'connexion.php';

if (
    !empty($_GET['idArticle']) 
) {
    foreach ($_SESSION['lscommande'] as $index => $article) {
        if ($article['id_article'] == $_GET['idArticle']) {
            unset($_SESSION['lscommande'][$index]); // Supprimer l'élément du panier
            break; // Sortir de la boucle une fois que l'article est supprimé
        }
    }
}
header('Location: ../vue/commande.php');