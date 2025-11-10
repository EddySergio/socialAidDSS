<?php
include 'connexion.php';
if (
    !empty($_POST['nom_article'])
    && !empty($_POST['categorie'])
    && !empty($_POST['prix_uni'])
    && !empty($_POST['prix_gros'])
    && !empty($_POST['prix_glace'])
    && !empty($_POST['prix_commande'])
    && !empty($_POST['estArendre'])
    && !empty($_POST['id_fournisseur'])
){
    {
        // Modifier l'article si le nom est unique
            $sql = "UPDATE article SET nom_article=?, categorie=?, quantite=?, prix_uni=?,
            prix_gros=?, prix_glace=?, prix_commande=?, estArendre=?, id_fournisseur=? WHERE id_article=?
            ";
            $req = $connexion->prepare($sql);

            $req->execute(array( 
                $_POST['nom_article'],
                $_POST['categorie'],
                $_POST['quantite'],
                $_POST['prix_uni'],
                $_POST['prix_gros'],
                $_POST['prix_glace'],
                $_POST['prix_commande'],
                $_POST['estArendre'],
                $_POST['id_fournisseur'],
                $_POST['id']
                )
            );
            if( $req->rowCount() > 0){
                $_SESSION['message']['text'] = 'article modifier avec succes';
                $_SESSION['message']['type'] = 'success';
            } else{
                $_SESSION['message']['text'] = "rien n'a ete modifier";
                $_SESSION['message']['type'] = 'warning';
            }

    }

} else {
    $_SESSION['message']['text'] = "Une information obligatoire non renseignée";
    $_SESSION['message']['type'] = 'danger';
}

header('Location: ../vue/article.php');

