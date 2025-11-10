
<?php
include 'connexion.php';

if (
    !empty($_POST['nom_article'])
    && !empty($_POST['categorie'])
    && !empty($_POST['prix_uni'])
    && !empty($_POST['prix_gros'])
    && !empty($_POST['prix_glace'])
    && !empty($_POST['prix_commande'])
    && !empty($_POST['estArendre'])
    && !empty($_POST['id_fournisseur'])
){
    // Vérifier si le nom d'article est unique
    $sql_check = "SELECT COUNT(*) AS count FROM article WHERE nom_article = ?";
    $req_check = $connexion->prepare($sql_check);
    $req_check->execute(array($_POST['nom_article']));
    $result = $req_check->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] > 0) {
        // Si le nom d'article existe déjà, afficher une erreur
        $_SESSION['message']['text'] = "Le nom de l'article existe déjà. Veuillez en choisir un autre.";
        $_SESSION['message']['type'] = 'danger';
    } else {
        // Insérer l'article si le nom est unique
        $sql_insert = "INSERT INTO article(nom_article, categorie, quantite, prix_uni, prix_gros, prix_glace, prix_commande, estArendre, id_fournisseur)
                       VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $req_insert = $connexion->prepare($sql_insert);
        $success = $req_insert->execute(array( 
            $_POST['nom_article'],
            $_POST['categorie'],
            $_POST['quantite'],
            $_POST['prix_uni'],
            $_POST['prix_gros'],
            $_POST['prix_glace'],
            $_POST['prix_commande'],
            $_POST['estArendre'],
            $_POST['id_fournisseur'],
        ));
        
        if ($success) {
            $_SESSION['message']['text'] = 'Article ajouté avec succès';
            $_SESSION['message']['type'] = 'success';
        } else {
            $_SESSION['message']['text'] = "Une erreur s'est produite lors de l'ajout de l'article";
            $_SESSION['message']['type'] = 'danger';
        }
    }

} else {
    $_SESSION['message']['text'] = "Une information obligatoire non renseignée";
    $_SESSION['message']['type'] = 'danger';
}

header('Location: ../vue/article.php');

