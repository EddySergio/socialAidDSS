<?php
include 'connexion.php';
if (
    !empty($_POST['libelle_categorie'])
) {
    $sql_check = "SELECT COUNT(*) AS count FROM categorie_article WHERE categorie = ?";
    $req_check = $connexion->prepare($sql_check);
    $req_check->execute(array($_POST['libelle_categorie']));
    $result = $req_check->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] > 0) {
        // Si le nom d'article existe déjà, afficher une erreur
        $_SESSION['message']['text'] = "cette categorie existe déjà. Veuillez en choisir un autre.";
        $_SESSION['message']['type'] = 'danger';
    } else {
        $sql = "INSERT INTO categorie_article(categorie)
                VALUES(?)";
            $req = $connexion->prepare($sql);
            
            $req->execute(array(
                $_POST['libelle_categorie'],  
            ));
            
            if ( $req->rowCount()!=0) {
                $_SESSION['message']['text'] = "Catégorie ajouté avec succès";
                $_SESSION['message']['type'] = "success";
            }else {
                $_SESSION['message']['text'] = "Une erreur s'est produite lors de l'ajout du catégorie";
                $_SESSION['message']['type'] = "danger";
            }

    }

} else {
    $_SESSION['message']['text'] ="Une information obligatoire non rensignée";
    $_SESSION['message']['type'] = "danger";
}

header('Location: ../vue/categorie.php');