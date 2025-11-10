<?php
include_once '../model/function.php';
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
};
?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <title>
        <?php
            echo"MANAMBINA | CONNEXION";
        ?>
    </title>
    <link rel="stylesheet" href="../public/css/connexion.css" />
    <!-- Boxicons CDN Link -->
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>

<body>
<div class="home-content">
    <div class="overview-boxes">
        <div class="box">
            <h1>TRANOMBAROTRA MANAMBINA</h1>
            <form action="../model/login.php" method="post">
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" name="username" id="username" placeholder="Veuillez saisir le nom d'utilisateur">

                <label for="mdp">Mot de passe :</label>
                <input type="password" name="mdp" id="mdp" placeholder="Veuillez entrer votre mot de passe">

                <button type="submit">Se connecter</button>

                <?php
                if (!empty($_SESSION['message']['text'])) {
                ?>
                    <div class="alert <?= $_SESSION['message']['type'] ?>">
                        <?= $_SESSION['message']['text'] ?>
                    </div>
                <?php
                unset($_SESSION['message']);
                }
                ?>
            </form>

        </div>
    </div>

</div>
</section>
