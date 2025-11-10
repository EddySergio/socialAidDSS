<?php
include ("connexion.php");
function getArticle($id=null)
{
    if(!empty($id)){
        $sql = "SELECT * FROM article WHERE id_article=?";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute(array($id));

        return $req->fetch();
    } else{
        $sql = "SELECT a.id_article, a.nom_article, a.quantite, a.prix_uni, a.prix_gros, a.prix_glace, a.prix_commande, a.categorie, a.estArendre, f.nom_fournisseur FROM article as a, fournisseur as f WHERE f.id_fournisseur = a.id_fournisseur ORDER BY nom_article ASC";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute();

    return $req->fetchAll();
    }
}
function getDetailVente($id=null)
{
    if(!empty($id)){
        $sql = "SELECT * FROM vente WHERE id_vente=?";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute(array($id));

        return $req->fetch();
    } else{
        $sql = "SELECT * FROM vente";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute();

    return $req->fetchAll();
    }
}

function getVente($id = null)
{
    if (!empty($id)) {
        $sql = "SELECT v.id_vente, v.arendre,p.reste, p.paiement, a.nom_article, p.nom_client as nom, u.username ,v.quantite, v.prix, p.total, p.date_vente, p.id_panier
        FROM vente AS v, article AS a, panier AS p, user AS u WHERE p.id_panier=? AND u.id=p.id_user AND v.id_panier = p.id_panier AND a.id_article = v.id_article";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute(array($id));

        return $req->fetchAll();
    } else {
        $sql = "SELECT v.id_vente, v.arendre ,p.reste, p.paiement, a.nom_article, p.nom_client,u.username ,v.quantite, v.prix, p.total, p.date_vente, p.id_panier
        FROM vente AS v, article AS a, panier AS p, user AS u WHERE u.id=p.id_user AND v.id_panier = p.id_panier AND a.id_article = v.id_article";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute(array());

        return $req->fetchAll();
    }
}
function getArendre($id = null){
    if(!empty($id)){
        $sql = "SELECT * FROM vente WHERE id_panier=?";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute(array($id));

        return $req->fetchAll();
    } else{
        $sql = "SELECT * FROM vente";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute();

    return $req->fetchAll();
    }
}
function getCommande($id = null)
{
    if (!empty($id)) {
        $sql = "SELECT c.id_commande, a.id_article, a.nom_article, f.nom_fournisseur , c.quantite, l.date_commande, l.id_lscommande, l.date_livraison, l.estLivre, l.total_commande,c.prix FROM commande AS c, article AS a, lscommande AS l, fournisseur AS f WHERE l.id_lscommande=? AND f.id_fournisseur=l.id_fournisseur AND c.id_lscommande = l.id_lscommande AND a.id_article = c.id_article";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute(array($id));

        return $req->fetchAll();
    } else {
        $sql = "SELECT c.id_commande, a.id_article, a.nom_article, f.nom_fournisseur , c.quantite, l.date_commande, l.id_lscommande, l.date_livraison, l.estLivre, l.total_commande,c.prix FROM commande AS c, article AS a, lscommande AS l, fournisseur AS f WHERE f.id_fournisseur=l.id_fournisseur AND c.id_lscommande = l.id_lscommande AND a.id_article = c.id_article";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute(array());

        return $req->fetchAll();
    }
}
function updateIdPanier(){
    $sql = "SELECT MAX(panier.id_panier) AS count FROM panier";

    $req = $GLOBALS['connexion']->prepare($sql);

    $req->execute();
    
    $result = $req->fetch(PDO::FETCH_ASSOC);

    return $result['count'] + 1;
}
function updateIdlsCommande(){
    $sql = "SELECT MAX(lscommande.id_lscommande) AS count FROM lscommande";

    $req = $GLOBALS['connexion']->prepare($sql);

    $req->execute();
    
    $result = $req->fetch(PDO::FETCH_ASSOC);

    return $result['count'] + 1;
}
function formatPrix($prix) {
    // Formater le prix en utilisant number_format avec des milliers séparés et deux décimales
    return number_format($prix, 2, ',', ' ') . ' Ar'; // Utilisation de la virgule comme séparateur décimal et de l'euro (€) comme symbole de devise
}
function getFournisseur($id = null)
{
    if (!empty($id)) {
        $sql = "SELECT * FROM fournisseur WHERE id_fournisseur=? AND actif=1";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute(array($id));

        return $req->fetch();
    } else {
        $sql = "SELECT * FROM fournisseur WHERE actif=1";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute();

        return $req->fetchAll();
    }
}
function getUser($id = null)
{
    if (!empty($id)) {
        $sql = "SELECT * FROM user WHERE id=?";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute(array($id));

        return $req->fetch();
    } else {
        $sql = "SELECT * FROM user";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute();

        return $req->fetchAll();
    }
}
function getLscommande()
{

        $sql = "SELECT 
                    lscommande.id_lscommande,
                    lscommande.id_fournisseur,
                    fournisseur.nom_fournisseur,
                    lscommande.estLivre,
                    lscommande.date_commande,
                    lscommande.date_livraison,
                    (SELECT COUNT(commande.id_article) 
                        FROM commande 
                        WHERE commande.id_lscommande = lscommande.id_lscommande) as nbr_article
                FROM 
                    lscommande,fournisseur
                WHERE fournisseur.id_fournisseur =lscommande.id_fournisseur ";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute();

        return $req->fetchAll();

}
function getpanier($id = null)
{
    if(!empty($id)){
        $sql = "SELECT 
        panier.id_panier,
        panier.id_user,
        user.username,
        panier.nom_client,
        panier.date_vente,
        panier.total,
        panier.paiement,
        panier.reste,
        (SELECT COUNT(vente.id_article) 
            FROM vente 
            WHERE vente.id_panier = panier.id_panier) as nbr_article,
        (SELECT SUM(vente.arendre) 
            FROM vente 
            WHERE vente.id_panier = panier.id_panier) as nbr_arendre
    FROM 
    panier, user
    WHERE panier.id_panier = ? AND user.id = panier.id_user";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute(array($id));

        return $req->fetch();
    } else{
        $sql = "SELECT 
        panier.id_panier,
        panier.id_user,
        user.username,
        panier.nom_client,
        panier.date_vente,
        panier.total,
        panier.reste,
        (SELECT COUNT(vente.id_article) 
            FROM vente 
            WHERE vente.id_panier = panier.id_panier) as nbr_article,
        (SELECT SUM(vente.arendre) 
            FROM vente 
            WHERE vente.id_panier = panier.id_panier) as nbr_arendre
    FROM 
    panier, user
    WHERE user.id = panier.id_user";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute();

        return $req->fetchAll();
    }


}
function getAllCommande()
{
    $sql = "SELECT COUNT(*) AS nbre FROM commande";

    $req = $GLOBALS['connexion']->prepare($sql);

    $req->execute();

    return $req->fetch();
}

function getAllVente()
{
    $sql = "SELECT COUNT(*) AS nbre FROM vente";

    $req = $GLOBALS['connexion']->prepare($sql);

    $req->execute();

    return $req->fetch();
}
function getAllArticle()
{
    $sql = "SELECT COUNT(*) AS nbre FROM article";

    $req = $GLOBALS['connexion']->prepare($sql);

    $req->execute();

    return $req->fetch();
}
function getLastVente()
{

    $sql = "SELECT a.nom_article, p.nom_client, p.date_vente, v.prix, v.id_vente, a.id_article FROM panier as p, vente AS v, article AS a WHERE v.id_article=a.id_article AND v.id_panier=p.id_panier ORDER BY `p`.`date_vente` DESC LIMIT 6";

    $req = $GLOBALS['connexion']->prepare($sql);

    $req->execute();

    return $req->fetchAll();
}
function getMostVente()
{

    $sql = "SELECT nom_article, SUM(prix) AS prix FROM vente AS v, article AS a WHERE v.id_article=a.id_article GROUP BY a.id_article ORDER BY SUM(prix) DESC LIMIT 6;";

    $req = $GLOBALS['connexion']->prepare($sql);

    $req->execute();

    return $req->fetchAll();
}
function getCategorie($id = null)
{
    if (!empty($id)) {
        $sql = "SELECT * FROM categorie_article WHERE id_categorie=?";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute(array($id));

        return $req->fetch();
    } else {
        $sql = "SELECT * FROM categorie_article";

        $req = $GLOBALS['connexion']->prepare($sql);

        $req->execute();

        return $req->fetchAll();
    }
}
function getTotalCommande()
{

    $sql = "SELECT SUM(total_commande) AS prix FROM lscommande";

    $req = $GLOBALS['connexion']->prepare($sql);

    $req->execute();

    $result = $req->fetch(PDO::FETCH_ASSOC);

    return $result['prix'];
}
function getTotalVente()
{

    $sql = "SELECT SUM(total) AS prix FROM panier";

    $req = $GLOBALS['connexion']->prepare($sql);

    $req->execute();

    $result = $req->fetch(PDO::FETCH_ASSOC);

    return $result['prix'];
}
