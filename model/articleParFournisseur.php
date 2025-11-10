<?php
include 'connexion.php';

if (!empty($_GET['id_fournisseur'])) {
    $id_fournisseur = $_GET['id_fournisseur'];

    $sql = "SELECT * FROM article WHERE id_fournisseur = ?";
    $req = $connexion->prepare($sql);
    $req->execute([$id_fournisseur]);

    $articles = $req->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($articles);
}
?>
