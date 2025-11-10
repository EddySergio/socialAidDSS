<?php
include 'connexion.php';
include_once "function.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'valider') {
            if (!empty($_SESSION['panier'])) {
                    $total = 0;
                    $id_user = $_SESSION['user_id'];
                    foreach ($_SESSION['panier'] as $articlePanier) {
                        $total += $articlePanier['prix'];
                    }
                    $idPanier = updateIdPanier();
                    if (
                        !empty($_POST['nom_client'])
                    ) {
                    if ($_POST['paymentAmount'] >= $total) {
                                
                                $sql1 = "INSERT INTO panier(id_panier, nom_client, total, id_user, paiement, reste)
                                VALUES(?, ?, ?, ?, ?, ?)";
                                $req1 = $connexion->prepare($sql1);
                            
                                $req1->execute(array(
                                    $idPanier,  
                                    $_POST['nom_client'],
                                    $total,
                                    $id_user,
                                    $_POST['paymentAmount'],
                                    0
                                ));
            
                                foreach ($_SESSION['panier'] as $articlePanier) {
                                    $arendre=0;
                                    if($articlePanier['estArendre']==="A rendre"){
                                        $arendre = $articlePanier['quantite'];
                                    }else{
                                        $arendre = 0;
                                    }
                                    $sql = "INSERT INTO vente(id_article, quantite, prix, id_panier, arendre)
                                        VALUES(?, ?, ?, ?, ?)";
                                    $req = $connexion->prepare($sql);
                                
                                    $req->execute(array(
                                        $articlePanier['id_article'],  
                                        $articlePanier['quantite'],
                                        $articlePanier['prix'],
                                        $idPanier,
                                        $arendre
                                    ));

                                    }

            
                                    $_SESSION['panier'] = array() ;      
                                    header('Location: ../vue/recuVente.php?id=' . $idPanier);
                                    exit();
                        }else {
                            
                            $sql1 = "INSERT INTO panier(id_panier, nom_client, total, id_user, paiement, reste)
                            VALUES(?, ?, ?, ?, ?, ?)";
                            $req1 = $connexion->prepare($sql1);
                        
                            $req1->execute(array(
                                $idPanier,  
                                $_POST['nom_client'],
                                $total,
                                $id_user,
                                $_POST['paymentAmount'],
                                $total - $_POST['paymentAmount'],
                            ));
        
                            foreach ($_SESSION['panier'] as $articlePanier) {
                                $arendre=0;
                                    if($articlePanier['estArendre']==="A rendre"){
                                        $arendre = $articlePanier['quantite'];
                                    }else{
                                        $arendre = 0;
                                    }
                                $sql = "INSERT INTO vente(id_article, quantite, prix, id_panier, arendre)
                                    VALUES(?, ?, ?, ?, ?)";
                                $req = $connexion->prepare($sql);
                            
                                $req->execute(array(
                                    $articlePanier['id_article'],  
                                    $articlePanier['quantite'],
                                    $articlePanier['prix'],
                                    $idPanier,
                                    $arendre
                                ));

                                }

        
                                $_SESSION['panier'] = array() ;      
                                header('Location: ../vue/recuVente.php?id=' . $idPanier);
                                exit();
                        }

                }else {
                    $_SESSION['message']['info'] ="Une information obligatoire non renseignée dans le panier";
                    $_SESSION['message']['type'] = "danger";

                }
                header('Location: ../vue/vente.php');
                exit();
            }else{
                $_SESSION['message']['info'] ="le panier est vide";
                $_SESSION['message']['type'] = "warning";
                header('Location: ../vue/vente.php');
                exit();
            }


        } elseif ($_POST['action'] === 'annuler') {
            foreach ($_SESSION['panier'] as $articlePanier) {
                $id_article = $articlePanier['id_article'];
                $quantite = $articlePanier['quantite'];

                // Mettre à jour la quantité de chaque article dans la base de données
                $sql2 = "UPDATE article SET quantite=quantite+? WHERE id_article=?";
                $req2 = $connexion->prepare($sql2);
                $req2->execute(array($quantite, $id_article));
            }

            // Après avoir mis à jour les quantités, vider le panier
            $_SESSION['panier'] = array();
            header('Location: ../vue/vente.php');
            exit(); // Assurez-vous de quitter le script après la redirection
        }
    }
}


