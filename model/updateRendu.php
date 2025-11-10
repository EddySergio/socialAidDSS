<?php
include 'function.php';
include 'connexion.php';
$detVent = getDetailVente($_GET['idVente']);
if (
    !empty($_GET['idVente'])
    && !empty($_GET['idPanier'])
    && !empty($_GET['nbreRendu'])
) {
    
    if($detVent['arendre'] >= $_GET['nbreRendu']){
        $arendre = $detVent['arendre'] - $_GET['nbreRendu'];
        $sql = "UPDATE vente SET arendre=? WHERE id_vente=?";
        $req = $connexion->prepare($sql);
        
        $success = $req->execute(array(
            $arendre,
            $_GET['idVente']
        ));
        if ($success) {
            $_SESSION['message']['text'] = 'Rendu effectue avec succes';
            $_SESSION['message']['type'] = 'success';
        } else {
            $_SESSION['message']['text'] = "Une erreur s'est produite lors de la rendu";
            $_SESSION['message']['type'] = 'danger';
        }
    }else{

        $_SESSION['message']['text'] = "le nombre a rendre est superieur qu'a celle qu'il faut rendre";
        $_SESSION['message']['type'] = 'danger';
    }

}
header('Location: ../vue/detailvente.php?id=' . $_GET['idPanier']);