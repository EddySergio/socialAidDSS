<?php
include 'connexion.php';
include_once "function.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'valider') {
            $idLscommande = updateIdlsCommande();
            echo $idLscommande;
            if (!empty($_SESSION['lscommande'])) {
                $total = 0;
                foreach ($_SESSION['lscommande'] as $articlePanier) {
                    $total += $articlePanier['prix'];
                    $idFournisseur += $articlePanier['id_fournisseur'];
                }
                    $sql1 = "INSERT INTO lscommande(id_lscommande, id_fournisseur, total_commande)
                    VALUES(?, ?, ?)";
                    $req1 = $connexion->prepare($sql1);
                
                    $req1->execute(array(
                        $idLscommande,  
                        $idFournisseur,
                        $total
                    ));

                foreach ($_SESSION['lscommande'] as $articleLscommande) {

                    $sql = "INSERT INTO commande(id_article, quantite, id_lscommande, prix)
                        VALUES(?, ?, ?, ?)";
                    $req = $connexion->prepare($sql);
                
                    $req->execute(array(
                        $articleLscommande['id_article'],  
                        $articleLscommande['quantite'],
                        $idLscommande,
                        $articleLscommande['prix'],
                    ));
                }

                $_SESSION['lscommande'] = array() ;      
                header('Location: ../vue/BondeCommande.php?id=' . $idLscommande);                
            }else{
                $_SESSION['message']['info'] ="la liste de commande est vide";
                $_SESSION['message']['type'] = "warning";
                header('Location: ../vue/commande.php');
                exit();
            }


        } elseif ($_POST['action'] === 'annuler') {
            $_SESSION['lscommande'] = array();
            header('Location: ../vue/commande.php');
            exit(); // Assurez-vous de quitter le script après la redirection
        }
    }
}