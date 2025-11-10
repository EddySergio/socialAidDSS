<?php
include 'connexion.php';
if (
    !empty($_GET['id'])
) {

$sql = "DELETE FROM user WHERE id=? ";
    $req = $connexion->prepare($sql);
    
    $req->execute(array($_GET['id']));
    
}

header('Location: ../vue/utilisateur.php');