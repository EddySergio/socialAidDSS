<?php

include_once '../model/function.php';
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Récupérer l'ID de l'utilisateur connecté
?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <title>
        <?php
        echo $page_title;
        ?>
    </title>
    <link rel="stylesheet" href="../public/css/style.css" />
    <!-- Boxicons CDN Link -->
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="../public/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css" href="../public/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../public/css/select2.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>

<body>

    <div class="sidebar hidden-print">
        <div class="logo-details">
            <div style="scale: 0.4;">
                        <img src="../public/img/logo.png" alt="">
                    </div>
            <span class="logo_name">MANAMBINA</span>
        </div>
        <ul class="nav-links">
        <?php 
                    $user = getUser($_SESSION['user_id']);
                    if ($user['type_user'] == 'admin') {
        ?> 
                                <li>
                <a href="dashboard.php" >
                    <div style="scale: 0.4;">
                        <img src="../public/img/icon/browser.png" alt="">
                    </div>
                    <span class="links_name">   DASHBOARD</span>
                </a>
            </li>
        <?php   
                    }
        ?>
            
            <li>
                <a href="article.php"  >
                    <div style="scale: 0.4;">
                        <img src="../public/img/icon/add-product.png" alt="">
                    </div>
                    <span class="links_name">   ARTICLE</span>
                </a>
            </li>
            <li>
                <a href="vente.php" >
                    <div style="scale: 0.4;">
                        <img src="../public/img/icon/price-tag.png" alt="">
                    </div>
                    <span class="links_name">   VENDRE</span>
                </a>
            </li>

            <li>
              <a href="tousVente.php" >
                    <div style="scale: 0.4;">
                        <img src="../public/img/icon/price-list.png" alt="">
                    </div>
                    <span class="links_name">   LES VENTES</span>
                </a>
            </li>
            
            <?php 
                    $user = getUser($_SESSION['user_id']);
                    if ($user['type_user'] == 'admin') {
                    ?> 
                        <div >
                        <li>
                                <a href="commande.php" >
                                    <div style="scale: 0.4;">
                                        <img src="../public/img/icon/cart.png" alt="">
                                    </div>
                                    <span class="links_name">   COMMANDER</span>
                                </a>
                            </li>
                            <li>
                                <a href="fournisseur.php" >
                                <div style="scale: 0.4;">
                                    <img src="../public/img/icon/supplier.png" alt="">
                                </div>
                                    <span class="links_name">   FOURNISSEUR</span>
                                </a>
                            </li>
                            
                            <li>
                            <a href="tousCommande.php" >
                                <div style="scale: 0.4;">
                                    <img src="../public/img/icon/invoice.png" alt="">
                                </div>
                                    <span class="links_name">   LES COMMANDES</span>
                                </a>
                            </li>
                            <li>
                            <a href="categorie.php" >
                                <div style="scale: 0.4;">
                                    <img src="../public/img/icon/categorise.png" alt="">
                                </div>
                                    <span class="links_name">   CATEGORIE</span>
                                </a>
                            </li>
                            <li>
                                <a href="utilisateur.php" >
                                <div style="scale: 0.4;">
                                    <img src="../public/img/icon/user.png" alt="">
                                </div>
                                    <span class="links_name">   UTILISATEUR</span>
                                </a>
                        </li>
                        </div>
                    <?php   
                    }
        ?>


            <!-- <li>
          <a href="#">
            <i class="bx bx-message" ></i>
            <span class="links_name">Messages</span>
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bx bx-heart" ></i>
            <span class="links_name">Favrorites</span>
          </a>
        </li> -->
            
            <li class="log_out">
                <a href="logout.php">
                    <div style="scale: 0.4;">
                        <img src="../public/img/icon/logout.png" alt="">
                    </div>
                    <span class="links_name">Déconnexion</span>
                </a>
            </li>
        </ul>
    </div>
    <section class="home-section">
        <nav class="hidden-print">
            <div class="sidebar-button">
                <div style="scale: 0.5;">
                    <img src="../public/img/icon/menu-bar.png" alt="">
                </div>
                <span class="dashboard">
                    <?php
                        echo $page_title;
                    ?>
                </span>
            </div>
            <div class="profile-details" style="display: flex; justify-content: space-between;">
                <span class="admin_name"><?=getUser($_SESSION['user_id'])['username'] ?></span>
                <div style="scale: 0.5;">
                    <img src="../public/img/user.png" alt="">
                </div>
            </div>
        </nav>