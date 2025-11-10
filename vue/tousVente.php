<?php
$page_title = "TOUT LES VENTES";
include 'header.php';

?>
<div class="home-content">
    <div class="overview-boxes">
        <div class="box">
        <table id="mtable" class="mtable">
            <thead>
                <tr>
                    <th>numero</th>
                    <th>Client</th>
                    <th>Caissier(e)</th>
                    <th>Nbr article</th>
                    <th>Date </th>
                    <th>Total</th>
                    <th>Reste a payer</th>
                    <th>Rendre</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $ventes = getpanier();

                if (!empty($ventes) && is_array($ventes)) {
                    foreach ($ventes as $vente) {
                ?>
                        <tr>
                            <td><?= $vente['id_panier']?></td>
                            <td><?= $vente['nom_client']?></td>
                            <td><?= $vente['username']?></td>
                            <td><?= $vente['nbr_article']?></td>
                            <td><?= date('d/m/Y H:i:s', strtotime($vente['date_vente'])) ?></td>
                            <td><?= formatPrix($vente['total'])?></td>
                            <td><?= formatPrix($vente['reste'])?></td>
                            <td><?= $vente['nbr_arendre']?></td>
                            <td>
                                    <div style="display: flex; align-items: center;">
                                        <a href="recuvente.php?id=<?= $vente['id_panier'][0] ?>"><img src="../public/img/eye.svg" alt=""></i></a>
                                        <?php if ($vente['reste'] != 0) { ?>
                                                <!-- Lien pour changer l'état de la commande -->
                                                <a onclick="updateReste(<?=$vente['id_panier'][0] ?>)" class="btn btn-success btn-sm" style="margin:0 10px;">Payer</a>
                                         <?php } ?>

                                         <?php if ($vente['nbr_arendre'] != 0) { ?>
                                                <!-- Lien pour changer l'état de la commande -->
                                                <a href="detailVente.php?id=<?=$vente['id_panier'][0] ?>" class="btn btn-danger btn-sm" style="margin:0 10px;">A Rendre</a>
                                         <?php } ?>
                                    </div>
                                </td>
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
    function updateReste(idArticle) {
    var montant = prompt("Entrez le montant du paiement :");
    if (montant !== null) { // Si l'utilisateur clique sur "OK"
        console.log(montant);
        // Redirection vers PHP avec le montant comme paramètre
        window.location.href = "../model/updateReste.php?id=" + idArticle + "&montant=" + montant;
    }
}

</script>