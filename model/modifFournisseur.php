<?php
include 'connexion.php';
if (
    !empty($_POST['nom'])
    && !empty($_POST['telephone'])
    && !empty($_POST['adresse'])
    && !empty($_POST['id'])
) {

$sql = "UPDATE fournisseur SET nom_fournisseur=?, telephone=?, adresse=? WHERE id_fournisseur=?";
    $req = $connexion->prepare($sql);
    
    $req->execute(array(
        $_POST['nom'],
        $_POST['telephone'],
        $_POST['adresse'],
        $_POST['id']
    ));
    
    if ( $req->rowCount()!=0) {
        $_SESSION['message']['text'] = "fournisseur modifié avec succès";
        $_SESSION['message']['type'] = "success";
    }else {
        $_SESSION['message']['text'] = "Rien a été modifié";
        $_SESSION['message']['type'] = "warning";
    }

} else {
    $_SESSION['message']['text'] ="Une information obligatoire non rensignée";
    $_SESSION['message']['type'] = "danger";
}

header('Location: ../vue/fournisseur.php');