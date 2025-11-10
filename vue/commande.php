<?php 
$page_title = "COMMANDER";
include 'header.php';

// Vérifiez si la liste des commandes contient des articles
$fournisseurDesactive = !empty($_SESSION['lscommande']);

if (!isset($_SESSION['lscommande'])) {
    $_SESSION['lscommande'] = array();
}

if (!empty($_GET['id'])) {
    $article = getArticle($_GET['id']);
}
?>
<div class="home-content">
    <div class="overview-boxes">
        <div class="box" style="max-width: 30%;">
            <form action="../model/ajoutLscommande.php" method="post">
                <label for="id_fournisseur">Fournisseur</label>
                <select 
                    style="margin-bottom: 20px;" 
                    name="id_fournisseur" 
                    id="id_fournisseur" 
                    onchange="updateArticles()"
                    <?= $fournisseurDesactive ? 'disabled' : '' // Désactive si des articles existent ?>
                >
                    <?php
                    $clients = getFournisseur();
                    if (!empty($clients) && is_array($clients)) {
                        foreach ($clients as $key => $value) {
                            // Marquez le fournisseur comme sélectionné si des articles existent
                            $selected = $fournisseurDesactive && $_SESSION['lscommande'][0]['id_fournisseur'] == $value['id_fournisseur'] ? 'selected' : '';
                            echo "<option value='" . $value['id_fournisseur'] . "' $selected>" . $value['nom_fournisseur'] . "</option>";
                        }
                    }
                    ?>
                </select>

                <input value="<?= !empty($_GET['id']) ? $article['id_article'] : "" ?>" type="hidden" name="id" id="id">

                <label for="id_article">Article</label>
                <select name="id_article" id="id_article">
                    <?php 
                        $article = getArticle();
                        if (!empty($article && is_array($article))) {
                            foreach ($article as $key => $value) {
                                echo "<option data-prix='{$value['prix_commande']}' value='{$value['id_article']}'>" . 
                                    "{$value['nom_article']} - {$value['quantite']} disponible</option>";
                            }
                        }
                    ?>
                </select>                  

                <label for="quantite">Quantité</label>
                <input 
                    onkeyup="setPrix()" 
                    value="<?= !empty($_GET['id']) ? $article['quantite'] : "" ?>" 
                    type="number" 
                    name="quantite" 
                    id="quantite" 
                    placeholder="Veuillez saisir la quantité"
                >

                <label for="prix">Prix</label>
                <input 
                    style="margin-bottom: 20px;" 
                    value="<?= !empty($_GET['id']) ? $article['prix'] : "" ?>" 
                    type="number" 
                    name="prix" 
                    id="prix" 
                    placeholder="Veuillez saisir le prix" 
                    readonly
                >

                <button type="submit"><?= !empty($_GET['id']) ? "Modifier" : "Ajouter au liste" ?></button>
                <?php 
                if (!empty($_SESSION['message']['text'])) {
                ?> 
                    <div class="alert  <?= $_SESSION['message']['type'] ?>">
                        <?= $_SESSION['message']['text'] ?>
                    </div>
                <?php 
                unset($_SESSION['message']);  
                }
                ?>  
            </form>
        </div>

        <div class="box" style="min-width: 50%;">
            <div style="display: block;width : 100%">
                <h2 style="margin-bottom: 10px;">Liste des commandes</h2>
                <form action="../model/ajoutCommande.php" method="post">
                    <div style="margin-bottom: 20px;">
                        <table class="mtable">
                            <tr>
                                <th>Nom article</th>
                                <th>Quantite</th>
                                <th>Prix</th>
                                <th>Action</th>
                            </tr>
                            <?php   
                                foreach ($_SESSION['lscommande'] as $articlePanier) {
                            ?>
                            <tr>
                                <td><?= $articlePanier['nom_article'] ?></td>
                                <td><?= $articlePanier['quantite'] ?></td>
                                <td><?= formatPrix($articlePanier['prix']) ?></td>
                                <td>
                                    <a onclick="annuleCommande(<?= $articlePanier['id_article'] ?>, <?= $articlePanier['quantite'] ?>)">
                                        <img src="../public/img/delete.svg" alt="">
                                    </a>
                                </td>
                            </tr>
                            <?php
                                }
                            ?> 
                            <?php
                                $total = 0;
                                foreach ($_SESSION['lscommande'] as $articlePanier) {
                                    $total += $articlePanier['prix'];
                                } 
                            ?>  
                            <tr>
                                <td colspan="1"></td>
                                <td><strong>Total:</strong></td>
                                <td><?= formatPrix($total) ?></td>
                            </tr>
                        </table>
                    </div>

                    <button type="submit" name="action" value="valider">Valider</button>
                    <button type="submit" name="action" value="annuler" id="annulerBcBtn">Annuler la BC</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php 
include 'footer.php'
?>

<script>
    // Initialiser Select2 sur votre select
    $(document).ready(function() {
        $('#id_article').select2();
    });
</script>
<script>
    function updateArticles() {
    var fournisseurId = document.getElementById('id_fournisseur').value;

    fetch("../model/articleParFournisseur.php?id_fournisseur=" + fournisseurId)
        .then(response => response.json())
        .then(data => {
            var articleSelect = document.getElementById('id_article');
            articleSelect.innerHTML = ""; // Effacer les articles actuels

            data.forEach(article => {
                var option = document.createElement('option');
                option.value = article.id_article;
                option.textContent = `${article.nom_article} - ${article.quantite} disponible`;
                option.setAttribute('data-prix', article.prix_commande);
                articleSelect.appendChild(option);
            });

        })
        .catch(error => {
            console.error('Erreur lors de la récupération des articles :', error);
        });
}

    function annuleCommande(idArticle) {
        if (confirm("Voulez-vous vraiment annuler cette commande?")) {
            window.location.href = "../model/annulecommande.php?idArticle=" + idArticle
        }
    }

</script>
<script>
        function setPrix() {

            var article = $('#id_article option:selected');
            var quantite = document.querySelector('#quantite');
            var prix = document.querySelector('#prix');

            var prixUnitaire = article.data('prix');

                prix.value = Number(quantite.value) * Number(prixUnitaire);
            }
            document.getElementById('annulerBcBtn').addEventListener('click', function() {
            if (confirm("Voulez-vous vraiment annuler cette Bon de commande?")) {
                    // Vous pouvez rediriger vers un script PHP ou faire d'autres actions
                    window.location.href = "../model/ajoutCommande.php"; // Passer l'action pour annuler la commande
                }
            });

            // Ajouter des écouteurs d'événements pour détecter les changements dans la quantité et les cases à cocher
            document.getElementById("quantite").addEventListener("input", setPrix);
            $('#id_article').on('select2:select', function (e) {
            setPrix(); // Passage de la variable en argument
            });
            document.addEventListener('DOMContentLoaded', function () {
    updateArticles(); // Mise à jour des articles lors du chargement de la page
});
// Appeler la fonction pour initialiser le prix lors du chargement de la page
setPrix();
</script>

