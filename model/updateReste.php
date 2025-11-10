<?php
include 'function.php';
include 'connexion.php';
if (
    !empty($_GET['id'])
    && !empty($_GET['montant'])
) {
    $vente = getpanier($_GET['id']);
    $paiement = $vente['paiement'] + $_GET['montant'];
    $reste = $vente['reste'] - $_GET['montant'];
    if($paiement >= $vente['total']){
        $sql = "UPDATE panier SET paiement=?, reste=? WHERE id_panier=?";
        $req = $connexion->prepare($sql);
        
        $req->execute(array(
            $paiement,
            0,
            $_GET['id']
        ));
    }else{

        $sql = "UPDATE panier SET paiement=?, reste=? WHERE id_panier=?";
        $req = $connexion->prepare($sql);
        
        $req->execute(array(
            $paiement,
            $reste,
            $_GET['id']
        ));
    }

}
header('Location: ../vue/recuVente.php?id=' . $_GET['id']);