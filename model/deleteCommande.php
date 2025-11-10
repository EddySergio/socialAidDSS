<?php
include 'connexion.php';
if (
    !empty($_GET['id'])
) {

$sql = "DELETE FROM commande WHERE id_lscommande=? ";
    $req = $connexion->prepare($sql);
    
    $req->execute(array($_GET['id']));
$sql1 = "DELETE FROM lscommande WHERE id_lscommande=? ";
    $req1 = $connexion->prepare($sql1);
    
    $req1->execute(array($_GET['id']));   
}
header('Location: ../vue/tousCommande.php');