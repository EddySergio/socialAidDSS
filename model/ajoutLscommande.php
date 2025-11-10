<?php 
include 'connexion.php';
include_once "function.php";

if (
    !empty($_POST['id_article'])
    && !empty($_POST['quantite'])
    && !empty($_POST['prix'])
) {
    $article = getArticle($_POST['id_article']);

    if (!empty($article) && is_array($article)) {
        $articleLscommande = array(
            'id_article' => $article['id_article'],
            'nom_article' => $article['nom_article'],
            'quantite' => $_POST['quantite'], // Utilisez la quantité fournie par l'utilisateur, pas celle de l'article
            'prix' => $_POST['prix'], // Utilisez le prix fourni par l'utilisateur, pas celui de l'article
            'id_fournisseur' => $_POST['id_fournisseur']
        );

        // Ajoutez l'article au panier
        $_SESSION['lscommande'][] = $articleLscommande;
        // Vérifiez si l'article a été ajouté au panier
        if (!empty($_SESSION['lscommande'])) {
    
                $_SESSION['message']['text'] = "Commande ajoutée dans le panier avec succès";
                $_SESSION['message']['type'] = "success";
        } 
        else {
            $_SESSION['message']['text'] = "Impossible de faire cette commande";
            $_SESSION['message']['type'] = "danger";
        }
                
    }
} else {
    $_SESSION['message']['text'] ="Une information obligatoire non renseignée";
    $_SESSION['message']['type'] = "danger";
}

header('Location: ../vue/commande.php');

