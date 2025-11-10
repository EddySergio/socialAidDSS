<?php 
$page_title = "VENDRE";
include 'header.php';
if(!empty($_GET['id'])){
    $article = getArticle($_GET['id']);
}
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = array();
}

?>
      <div class="home-content">
        <div class="overview-boxes">
            <div class="box" style="max-width: 30%;">
                <form action="../model/ajoutPanier.php"  method="post">
                    
                    <input value="<?=!empty($_GET['id']) ? $article['id_article'] : ""?>" type="hidden" name="id" id="id">

                    <label for="id_article">Article</label>
                    <select name="id_article" id="id_article">
                    <?php 
                            $article = getArticle();
                            if(!empty($article && is_array($article))){
                                foreach ($article as $key => $value) {
                                    ?>
                                        <option data-categorie="<?= $value['categorie'] ?>" data-prix-glace="<?= $value['prix_glace'] ?>" data-prix-gros="<?= $value['prix_gros'] ?>" data-prix="<?= $value['prix_uni'] ?>" value="<?= $value['id_article'] ?>"><?= $value['nom_article'] . " - " . $value['quantite'] . " disponible" ?></option>
                                    <?php 
                                }
                            }
                    ?>
                    </select>                  

                    <label for="quantite">Quantité</label>
                    <input onkeyup="setPrix()" value="<?= !empty($_GET['id']) ?  $article['quantite'] : "" ?>" type="number" name="quantite" id="quantite" placeholder="Veuillez saisir la quantité">

                    <div style="display:flex; align-items:center;">
                        <div style="margin-right: 50px;">
                            <label for="prix_gros"><i>Gros</i></label>
                            <input type="checkbox" name="prix_gros" id="prix_gros" >
                        </div>

                        
                        <div id="prix_glace_field" style="display: none;">
                            <label for="prix_glace"><i>Glace</i></label>
                            <input type="checkbox" name="prix_glace" id="prix_glace" >
                        </div>
   
                    </div>

                    <label for="prix" >Prix</label>
                    <input style="margin-bottom: 20px;" value="<?= !empty($_GET['id']) ?  $article['prix'] : "" ?>" type="number" name="prix" id="prix" placeholder="Veuillez saisir le prix" readonly>


                    <button type="submit"><?=!empty($_GET['id']) ? "Modifier" : "Ajouter au panier"?></button>
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
                </form>
            </div>
            <div class="box" style="min-width: 45%;">
                
                <div style="display: block; width : 100%">
                    <h2>PANIER</h2>
                    <form action="../model/ajoutVente.php" method="post">                        
                        <div style="margin-bottom: 20px;">
                                <table class="mtable">
                                <tr>
                                    <th>Nom article</th>
                                    <th>Quantite</th>
                                    <th>Prix</th>
                                    <th>Action</th>
                                </tr>
                                <?php   
                                        foreach ($_SESSION['panier'] as $articlePanier) {
                                    ?>
                                    <tr>
                                        <td><?=$articlePanier['nom_article']?></td>
                                        <td><?=$articlePanier['quantite']?></td>
                                        <td><?=formatPrix($articlePanier['prix'])?></td>
                                        <td>
                                            <a onclick = "annuleVente(<?=$articlePanier['id_article']?>, <?=$articlePanier['quantite']?>)"><img src="../public/img/delete.svg" alt=""></a>
                                        </td>
                                    </tr>
                                    <?php
                                        }
                                ?> 
                                <?php
                                    $total = 0;
                                    foreach ($_SESSION['panier'] as $articlePanier) {
                                        $total += $articlePanier['prix'];
                                    } 
                                ?>  
                                <tr>
                                    <td colspan="1"></td>
                                    <td><strong>Total a payer:</strong></td>
                                    <td id="totalPrix"><?=formatPrix($total)?></td>
                                    
                                </tr>
                                <tr>
                                    <td colspan="1"></td>
                                    <td><strong>Paiement:</strong></td>
                                    <td id="paymentValue">0</td>   
                                </tr>
                                </tr>
                                <tr>
                                    <td colspan="1"></td>
                                    <td><strong>A rendre:</strong></td>
                                    <td id="resteValue">0</td>   
                                </tr>
                                <tr>
                                    <td colspan="1"></td>
                                    <td><strong>Reste a payer:</strong></td>
                                    <td id="restePayment">0</td>   
                                </tr>
                            </table>
                        </div>
                        <label for="nom_client">Nom du client / Contact</label>
                        <input style="margin-bottom: 20px;" type="text" name="nom_client" id="nom_client" placeholder='exemple "rakoto / 032 69 666 69"'>

                        <label for="paymentAmount">Somme du paiement</label>
                        <input value="0" id="paymentAmount" style="margin-bottom: 20px;" type="text" name="paymentAmount" id="paymentAmount" placeholder="Veuillez saisir le montant du paiement">

                        <button type="submit" name="action" value="valider">Valider</button>
                        <button type="submit" name="action" value="annuler">Annuler la vente</button>
                        <?php 
                        if (!empty($_SESSION['message']['info'])) {
                        ?> 
                            <div class="alert  <?=$_SESSION['message']['type']?>">
                                <?=$_SESSION['message']['info']?>
                            </div>
                        <?php 
                        unset($_SESSION['message']);  
                        }
                        ?>
                    </form>
                </div>
                
            </div>
        </div>
      </div>
    </section>

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
    function togglePrixGlaceField() {
    var article = $('#id_article option:selected');
    var categorie = article.data('categorie');
    var prixGlaceField = document.getElementById("prix_glace_field");
    console.log(categorie);

    // Afficher/masquer en fonction de la catégorie
    if (categorie === "Boisson" || categorie === "Biere" || categorie === "Eau") {
        prixGlaceField.style.display = "block";
    } else {
        prixGlaceField.style.display = "none";
    }
 // Recalculer le prix après changement de catégorie
    }


    $('#id_article').on('select2:select', function (e) {
  togglePrixGlaceField();
  setPrix(); // Passage de la variable en argument
});
</script>
<script>
    function annuleVente(idArticle, quantite) {
        if (confirm("Voulez-vous vraiment annuler cette vente ?")) {
            window.location.href = "../model/annuleVente.php?idArticle=" + idArticle + "&quantite=" + quantite
        }
    }
    // Fonction pour mettre à jour le prix en fonction de la quantité et des options "Gros" et "Glace"
    function setPrix() {

        var article = $('#id_article option:selected');
        var quantite = document.querySelector('#quantite');
        var prix = document.querySelector('#prix');

        var prixGlace = article.data('prix-glace');
        var prixUnitaire = article.data('prix');
        var prixGros = article.data('prix-gros');

        // Vérifier l'état des cases à cocher "Gros" et "Glace"
        var isGrosChecked = document.getElementById("prix_gros").checked;
        var isGlaceChecked = document.getElementById("prix_glace").checked;

        // Calculer le prix en fonction de la quantité, du prix unitaire et des cases à cocher
        if (isGrosChecked) {
            prix.value = Number(quantite.value) * Number(prixGros);
        } else if (isGlaceChecked) {
            prix.value = Number(quantite.value) * Number(prixGlace);
        } else {
            prix.value = Number(quantite.value) * Number(prixUnitaire);
        }
    }

    // Ajouter des écouteurs d'événements pour détecter les changements dans la quantité et les cases à cocher
    document.getElementById("quantite").addEventListener("input", setPrix);
    document.getElementById("prix_gros").addEventListener("change", setPrix);
    document.getElementById("prix_glace").addEventListener("change", setPrix);
    document.addEventListener('DOMContentLoaded', function() {
        togglePrixGlaceField();
});
    // Appeler la fonction pour initialiser le prix lors du chargement de la page
    setPrix();
</script>
<script>
    // Fonction pour mettre à jour le montant du paiement et le reste à payer dans la table
    function updatePaymentAndRemainingAmount() {
        var paymentInput = document.getElementById("paymentAmount");
        var totalAmount = <?= $total ?>; // Récupérer le montant total à payer (vous devez définir cette variable dans votre code PHP)
        var paymentAmount = parseFloat(paymentInput.value);

        // Calculer le reste à payer
        var remainingAmount;
        var resteAmount;
        if(paymentAmount >= totalAmount){
             remainingAmount = paymentAmount - totalAmount;
             resteAmount = 0;
        } else{
             resteAmount = totalAmount - paymentAmount;
             remainingAmount = 0;
        }


        // Formater le montant du paiement et le montant restant avec le séparateur de milliers et le séparateur décimal
        var paymentFormatted = paymentAmount.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        var remainingFormatted = remainingAmount.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        var resteFormatted = resteAmount.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Ajouter le symbole de la devise "Ar"
        var paymentCell = document.getElementById("paymentValue");
        var remainingCell = document.getElementById("resteValue");
        var resteCell = document.getElementById("restePayment");
        paymentCell.textContent = paymentFormatted + " Ar"; 
        remainingCell.textContent = remainingFormatted + " Ar";
        resteCell.textContent = resteFormatted + " Ar";
        
    }


    // Ajouter un écouteur d'événement pour détecter les changements dans l'entrée de paiement
    document.getElementById("paymentAmount").addEventListener("input", updatePaymentAndRemainingAmount);

    // Appeler la fonction initiale pour s'assurer que les valeurs sont correctement définies au chargement de la page
    updatePaymentAndRemainingAmount();
</script>




