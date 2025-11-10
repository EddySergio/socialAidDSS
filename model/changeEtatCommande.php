<?php
include 'connexion.php';
include 'function.php';
if (
    $commande = getCommande($_GET['id'])
) {
    
    $sql = "UPDATE lscommande SET estLivre = 'livre', date_livraison = NOW() WHERE id_lscommande = ?";
    $req = $connexion->prepare($sql);
    
    $req->execute(array($_GET['id']));
    if ($req->rowCount() !=0) {
        foreach ($commande as $vente) {
            $sql = "UPDATE article SET quantite=quantite+? WHERE id_article=?";
                $req = $connexion->prepare($sql);
            
                $req->execute(array(
                    $vente['quantite'],
                    $vente['id_article'], 
                ));
        }
    }
    
}

header('Location: ../vue/tousCommande.php');