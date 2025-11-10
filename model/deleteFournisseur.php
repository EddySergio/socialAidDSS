<?php
include 'connexion.php';
if (!empty($_GET['id'])) {
    $sql = "UPDATE fournisseur SET actif = 0 WHERE id_fournisseur = ?";
    $req = $connexion->prepare($sql);
    
    $req->execute(array(
        $_GET['id']
    ));
}

header('Location: ../vue/fournisseur.php');
