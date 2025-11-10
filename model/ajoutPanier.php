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
        if ($_POST['quantite'] > $article['quantite']) {
            $_SESSION['message']['text'] = "La quantité à vendre n'est pas disponible";
            $_SESSION['message']['type'] = "danger";
        } else {
            $articlePanier = array(
                'id_article' => $article['id_article'],
                'nom_article' => $article['nom_article'],
                'categorie' => $article['categorie'],
                'estArendre' => $article['estArendre'],
                'quantite' => $_POST['quantite'], // Utilisez la quantité fournie par l'utilisateur, pas celle de l'article
                'prix' => $_POST['prix'] // Utilisez le prix fourni par l'utilisateur, pas celui de l'article
            );

            // Ajoutez l'article au panier
            $_SESSION['panier'][] = $articlePanier;
            // Vérifiez si l'article a été ajouté au panier
            if (!empty($_SESSION['panier'])) {
        
                $sql = "UPDATE article SET quantite=quantite-? WHERE id_article=?";
                $req = $connexion->prepare($sql);
            
                $req->execute(array(
                    $_POST['quantite'],
                    $_POST['id_article'], 
                ));
                
                if ($req->rowCount()!=0) {
                    $_SESSION['message']['text'] = "Vente ajoutée dans le panier avec succès";
                    $_SESSION['message']['type'] = "success";
                } else {
                    $_SESSION['message']['text'] = "Impossible de faire cette vente";
                    $_SESSION['message']['type'] = "danger";
                }
                    
            }

        }
    }
} else {
    $_SESSION['message']['text'] ="Une information obligatoire non renseignée";
    $_SESSION['message']['type'] = "danger";
}

header('Location: ../vue/vente.php');

