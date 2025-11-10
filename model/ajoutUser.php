<?php
include 'connexion.php';
if (
    !empty($_POST['username'])
    && !empty($_POST['mdp'])
    && !empty($_POST['type_user'])
) {

$sql = "INSERT INTO user(username, mdp, type_user)
        VALUES(?, ?, ?)";
    $req = $connexion->prepare($sql);
    
    $req->execute(array(
        $_POST['username'],
        $_POST['mdp'],
        $_POST['type_user']
    ));
    
    if ( $req->rowCount()!=0) {
        $_SESSION['message']['text'] = "Utilisateur ajouté avec succès";
        $_SESSION['message']['type'] = "success";
    }else {
        $_SESSION['message']['text'] = "Une erreur s'est produite lors de l'ajout de l'utilisateur";
        $_SESSION['message']['type'] = "danger";
    }

} else {
    $_SESSION['message']['text'] ="Une information obligatoire non rensignée";
    $_SESSION['message']['type'] = "danger";
}

header('Location: ../vue/utilisateur.php');