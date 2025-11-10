<?php
include 'connexion.php';
if (
    !empty($_POST['libelle_categorie'])
    && !empty($_POST['id'])
) {
$sql1 = "UPDATE article SET categorie=? WHERE categorie=?";
    $req1 = $connexion->prepare($sql1);
    
    $req1->execute(array(
        $_POST['libelle_categorie'],
        $_POST['libelle_categorie']
    ));
$sql = "UPDATE categorie_article SET categorie=? WHERE id_categorie=?";
    $req = $connexion->prepare($sql);
    
    $req->execute(array(
        $_POST['libelle_categorie'],
        $_POST['id']
    ));
    
    if ( $req->rowCount()!=0) {
        $_SESSION['message']['text'] = "Catégorie modifié avec succès";
        $_SESSION['message']['type'] = "success";
    }else {
        $_SESSION['message']['text'] = "Rien a été modifié";
        $_SESSION['message']['type'] = "warning";
    }

} else {
    $_SESSION['message']['text'] ="Une information obligatoire non rensignée";
    $_SESSION['message']['type'] = "danger";
}

header('Location: ../vue/categorie.php');