<?php
$page_title = "DETAILS DE VENTE";
include 'header.php';

if (!empty($_GET['id'])) {
    $ventes = getVente($_GET['id']);
}
?>
<div class="home-content">
    <div class="overview-boxes" style="display: flex; flex-direction: column;">
    <?php 
            if (!empty($_SESSION['message']['text'])) {
        ?> 
            <div class="box alert  <?=$_SESSION['message']['type']?>">
                <?=$_SESSION['message']['text']?>
            </div>
        <?php 
            unset($_SESSION['message']);  
            }
        ?>
        <div class="box">
        <table id="mtable" class="mtable">
            <thead>
                <tr>
                    <th>Designation</th>
                    <th>Quantite</th>
                    <th>Prix unitaire</th>
                    <th>Prix Total</th>
                    <th>Article a rendre</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php   
                    foreach ($ventes as $vente) { 
                        ?>
                        <tr>
                            <td><?=$vente['nom_article']?></td>
                            <td><?=$vente['quantite']?></td>
                            <td><?=formatPrix($vente['prix']/$vente['quantite'])?></td>
                            <td><?=formatPrix($vente['prix'])?></td>
                            <td><?=$vente['arendre']?></td>
                            <td>
                                <?php if ($vente['arendre'] != 0) { ?>
                                                <!-- Lien pour changer l'état de la commande -->
                                                <a onclick="updateRendu(<?=$vente['id_vente']?>,<?=$vente['id_panier'][0]?>)" class="btn btn-success btn-sm" style="margin:0 10px;">Rendre</a>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php
                            }
                    ?>  
            </tbody>
        </table>

        </div>
    </div>

</div>
</section>

<?php
include 'footer.php';
?>
<script>
    $(document).ready(function() {
        // Initialisation de DataTables avec les fonctionnalités de tri, pagination et recherche
        $('.mtable').DataTable({
            "paging": true,
            "ordering": true,
            "searching": true,
            "language": {
                "url": "french.json"
            }
        });

    });
</script>
<script>
    function updateRendu(idVente,idPanier) {
    var nbreRendu = prompt("Entrez le nombre d'article rendu :");
    if (nbreRendu !== null) { // Si l'utilisateur clique sur "OK"
        console.log(nbreRendu);
        // Redirection vers PHP avec le montant comme paramètre
        window.location.href = "../model/updateRendu.php?idVente=" + idVente + "&idPanier=" + idPanier + "&nbreRendu=" + nbreRendu;
    }
}

</script>