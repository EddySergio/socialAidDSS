<?php
include 'connexion.php';
if (
    !empty($_POST['username'])
    && !empty($_POST['mdp'])
    && !empty($_POST['type_user'])
    && !empty($_POST['id'])
) {

$sql = "UPDATE user SET username=?, mdp=?, type_user=? WHERE id=?";
    $req = $connexion->prepare($sql);
    
    $req->execute(array(
        $_POST['username'],
        $_POST['mdp'],
        $_POST['type_user'],
        $_POST['id']
    ));
    
    if ( $req->rowCount()!=0) {
        $_SESSION['message']['text'] = "Utilisateur modifié avec succès";
        $_SESSION['message']['type'] = "success";
    }else {
        $_SESSION['message']['text'] = "Rien a été modifié";
        $_SESSION['message']['type'] = "warning";
    }

} else {
    $_SESSION['message']['text'] ="Une information obligatoire non rensignée";
    $_SESSION['message']['type'] = "danger";
}

header('Location: ../vue/utilisateur.php');