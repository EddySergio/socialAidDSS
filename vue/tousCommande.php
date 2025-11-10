<?php
$page_title = "TOUT LES COMMANDES";
include 'header.php';
?>
<div class="home-content">
    <div class="overview-boxes">
        <div class="box">
            <table class="mtable" style="width:100%">
                <thead>
                    <tr>
                        <th data-sortable>Fournisseur</th>
                        <th data-sortable>Commande numero</th>                     
                        <th data-sortable>nombre article</th>
                        <th data-sortable>Date de commande</th>
                        <th data-sortable>Date de livraison</th>
                        <th data-sortable>Etat</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $ventes = getLscommande();

                    if (!empty($ventes) && is_array($ventes)) {
                        foreach ($ventes as $vente) {
                    ?>
                            <tr>
                                <td><?= $vente['nom_fournisseur']?></td>
                                <td><?= $vente['id_lscommande']?></td>                              
                                <td><?= $vente['nbr_article']?></td>
                                <td><?= date('d/m/Y H:i:s', strtotime($vente['date_commande'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($vente['date_livraison'])) ?></td>
                                <td><?= $vente['estLivre'] ?></td>
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        <a href="Bondecommande.php?id=<?= $vente['id_lscommande'][0] ?>"><img src="../public/img/eye.svg" alt=""></a>
                                        <?php if ($vente['estLivre'] == 'non livre') { ?>
                                                <!-- Lien pour changer l'état de la commande -->
                                                <a href="../model/changeEtatCommande.php?id=<?=$vente['id_lscommande'][0] ?>" class="btn btn-success btn-sm" style="margin:0 10px;">Livre</a>
                                                <a onclick="deleteFournisseur(<?=$vente['id_lscommande'][0] ?>)"><img src="../public/img/delete.svg" alt=""></a>
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
    function deleteFournisseur(idArticle) {
        if (confirm("Voulez-vous vraiment annule cette bon de commande ?")) {
            window.location.href = "../model/deleteCommande.php?id=" + idArticle;
        }
    }
</script>
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
