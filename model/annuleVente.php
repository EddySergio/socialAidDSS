<?php
include 'connexion.php';

if (
    !empty($_GET['idArticle']) &&
    !empty($_GET['quantite'])
) {

    $sql = "UPDATE article SET quantite=quantite+? WHERE id_article=?";
    $req = $connexion->prepare($sql);
    $req->execute(array($_GET['quantite'],$_GET['idArticle']));
    foreach ($_SESSION['panier'] as $index => $article) {
        if ($article['id_article'] == $_GET['idArticle']) {
            unset($_SESSION['panier'][$index]); // Supprimer l'élément du panier
            break; // Sortir de la boucle une fois que l'article est supprimé
        }
    }
}
header('Location: ../vue/vente.php');