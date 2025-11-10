<?php 
$page_title = "ARTICLE";
include 'header.php';
if(!empty($_GET['id'])){
    $article = getArticle($_GET['id']);
}
?>
<div class="home-content">
    <div class="overview-boxes" style="flex-direction: column;">
    <?php 
                    $user = getUser($_SESSION['user_id']);
                    if ($user['type_user'] == 'admin') {
                    ?> 
                        <div class="box" style="min-width: 100%;">
                            <form action="<?=!empty($_GET['id']) ? "../model/modifArticle.php" : "../model/ajoutArticle.php"?>" method="post">
                                    <div style="display: flex;">
                                        <div style="display: flex; flex-direction:column; align-items: flex-start;min-width: 25%;">
                                            <label for="nom_article">Nom de l'article</label>
                                            <input style="max-width: 80%;" value="<?=!empty($_GET['id']) ? $article['nom_article'] : ""?>" type="text" name="nom_article" id="nom_article" placeholder="Veuillez saisir le nom">
                                            <input value="<?=!empty($_GET['id']) ? $article['id_article'] : ""?>" type="hidden" name="id" id="id">

                                            <label for="nom_article">Categorie</label>
                                            <select style="max-width: 80%;" name="categorie" id="categorie">
                                                <?php

                                                    $categories = getCategorie();
                                                    if (is_array($categories) && !empty($categories)) {
                                                        foreach ($categories as $key => $value) {
                                                ?>
                                                        <option <?= !empty($_GET['id']) && $article['categorie'] == $value['categorie'] ?  "selected" : "" ?> value="<?= $value['categorie'] ?>"><?= $value['categorie'] ?></option>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </select>
             
                                        </div>
                                        <div style="display: flex; flex-direction:column; align-items: flex-start;min-width: 25%;">
                                            <label for="quantite">Quantite</label>
                                            <input style="max-width: 80%;" value="<?=!empty($_GET['id']) ? $article['quantite'] : 0?>" type="number" name="quantite" id="quantite" placeholder="Veuillez saisir la quantite">
                                            
                                            <label for="prix_uni">Prix unitaire</label>
                                            <input style="max-width: 80%;" value="<?=!empty($_GET['id']) ? $article['prix_uni'] : ""?>" type="number" name="prix_uni" id="prix_uni" placeholder="Veuillez saisir le prix unitaire">
 
                                        </div>
                                        <div style="display: flex; flex-direction:column; align-items: flex-start;min-width: 25%;">

                                            <label for="prix_gros">Prix de gros</label>
                                            <input style="max-width: 80%;" value="<?=!empty($_GET['id']) ? $article['prix_gros'] : ""?>" type="number" name="prix_gros" id="prix_gros" placeholder="Veuillez saisir le prix de gros">
                                               
                                            <div id="prix_glace_field" style="max-width: 80%;">
                                                <label for="prix_glace">Prix glace</label>
                                                <input value="<?=!empty($_GET['id']) ? $article['prix_glace'] : ""?>" type="number" name="prix_glace" id="prix_glace" placeholder="Veuillez saisir le prix de glacee">
                                            </div>
                                        </div>
                                        <div style="display: flex; flex-direction:column; align-items: flex-start;min-width: 25%;">

                                            <label for="prix_commande">Prix de commande</label>
                                            <input style="max-width: 80%;" value="<?=!empty($_GET['id']) ? $article['prix_commande'] : ""?>" type="number" name="prix_commande" id="prix_commande" placeholder="Veuillez saisir le prix de commande">
                                            
                                            <label for="nom_article">A rendre ou pas ?</label>
                                            <select style="max-width: 80%;margin-bottom: 10px;" name="estArendre" id="estArendre">
                                                <option <?=!empty($_GET['id']) && $article['estArendre']== "A rendre" ? "selected" : ""?> value="A rendre">A rendre</option>
                                                <option <?=!empty($_GET['id']) && $article['estArendre']== "Pas a rendre" ? "selected" : ""?> value="Pas a rendre">Pas a rendre</option>
                                            </select>
                                            <label for="id_fournisseur">Fournisseur</label>
                                            <select style="margin-bottom: 20px;" name="id_fournisseur" id="id_fournisseur">
                                                <?php
                                                $clients = getFournisseur();
                                                if (!empty($clients) && is_array($clients)) {
                                                    foreach ($clients as $key => $value) {
                                                ?>
                                                        <option <?= !empty($_GET['id']) && $article['id_fournisseur'] == $value['id_fournisseur'] ?  "selected" : "" ?> value="<?= $value['id_fournisseur'] ?>"><?= $value['nom_fournisseur'] ?></option>
                                                <?php

                                                    }
                                                }

                                                ?>
                                            </select> 
                                            
                                            <button type="submit"><?=!empty($_GET['id']) ? "Modifier" : "Enregistrer" ?></button>
                                            <?php 
                                            if (!empty($_SESSION['message']['text'])) {
                                            ?> 
                                                <div class="alert  <?=$_SESSION['message']['type']?>">
                                                    <?=$_SESSION['message']['text']?>
                                                </div>
                                            <?php 
                                            unset($_SESSION['message']);  
                                            }
                                            ?>
                                        </div>
                                    </div>

  
                            </form>
                        </div>
                    <?php ;  
                    }
        ?>
        <div class="box">
            <table class="mtable">
                <thead>
                    <tr>
                        <th>Nom article</th>
                        <th>Categorie</th>
                        <th>Quantite</th>
                        <th>Prix unitaire</th>
                        <th>Prix de gros</th>
                        <th>Prix glace</th>
                        <th>Consignation</th>
                        <th>Fournisseur</th>
                        <?php 
                                    $user = getUser($_SESSION['user_id']);
                                    if ($user['type_user'] == 'admin') {
                                    ?> 
                                        <div >
                                            <th>Prix de commande</th>
                                            <th>Action</th>
                                        </div>
                                    <?php  
                                    }
                        ?> 
                    </tr>
                </thead>
                <tbody>
                    <?php   

                    $article = getArticle();
                    if(!empty($article) && is_array($article)) {
                        foreach($article as $key => $value) {
                    ?>
                    <tr>
                        <td><?=$value['nom_article']?></td>
                        <td><?=$value['categorie']?></td>
                        <td><?=$value['quantite']?></td>
                        <td><?=formatPrix($value['prix_uni'])?></td>
                        <td><?=formatPrix($value['prix_gros'])?></td>
                        <td><?=formatPrix($value['prix_glace'])?></td>
                        <td><?=$value['estArendre']?></td>
                        <td><?=$value['nom_fournisseur']?></td>
                        <?php 
                                    $user = getUser($_SESSION['user_id']);
                                    if ($user['type_user'] == 'admin') {
                                    ?> 
                                        <div >
                                            <td><?=formatPrix($value['prix_commande'])?></td>
                                            <td><a href="?id=<?=$value['id_article']?>"><img src="../public/img/edit.svg" alt=""></a></td>
                                        </div>
                                    <?php  
                                    }
                        ?>    
                    </tr>
                    <?php
                        }
                    }
                    ?>  
                </tbody>
            </table>
        </div>
    </div>
</div>
</section>

<?php 
include 'footer.php'
?>

<script>
    // Fonction pour gérer l'affichage du champ "Prix glace" en fonction de la catégorie sélectionnée
    function togglePrixGlaceField() {
        var categorie = document.getElementById("categorie").value;
        var prixGlaceField = document.getElementById("prix_glace_field");

        // Afficher le champ "Prix glace" uniquement si la catégorie est "boisson" ou "biere"
        if (categorie === "Boisson" || categorie === "Biere" || categorie === "Eau") {
            prixGlaceField.style.display = "block";
        } else {
            prixGlaceField.style.display = "none";
        }
    }

    // Ajouter un écouteur d'événement pour détecter les changements dans la sélection de catégorie
    document.getElementById("categorie").addEventListener("change", togglePrixGlaceField);

    // Appeler la fonction pour s'assurer que le champ est initialisé correctement au chargement de la page
    togglePrixGlaceField();
</script>
<script>
    // Récupérez le champ "Prix unitaire" et le champ "Prix glace"
    var prixUnitaireField = document.getElementById("prix_uni");
    var prixGlaceField = document.getElementById("prix_glace");
    var prixGrosceField = document.getElementById("prix_gros");
    var prixCommandeField = document.getElementById("prix_commande");

    // Ajoutez un écouteur d'événement sur le champ "Prix unitaire" pour détecter les changements
    prixUnitaireField.addEventListener("input", function() {
        // Définissez la valeur du champ "Prix glace" égale à celle du champ "Prix unitaire"
        prixGlaceField.value = prixUnitaireField.value;
        prixGrosceField.value = prixUnitaireField.value;
        prixCommandeField.value = prixUnitaireField.value;
    });
</script>
<script>
    $(document).ready(function() {
        // Initialisation de DataTables avec les fonctionnalités de tri, pagination et recherche
        $('.mtable').DataTable({
            "paging": true,
            "ordering": true,
            "searching": true,
            "lengthMenu": [ [5, 10, 25, 50, -1], [5, 10, 25, 50, "Tout"] ],
            "language": {
                "url": "french.json"
            }
        });

    });
</script>
