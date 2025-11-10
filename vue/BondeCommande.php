<?php
$page_title = "BON DE COMMANDE";
include 'header.php';

if (!empty($_GET['id'])) {
    $commande = getCommande($_GET['id']);
}

?>
    <div class="home-content">

      <button class="hidden-print" id="btnPrint" style="position: relative; left: 45%;"> <i class='bx bx-printer'></i> Imprimer</button>

        <div class="page">
            <div class="cote-a-cote">
                <h2 style="margin-bottom: 20px;">BON DE COMMANDE</h2>
                <div>
                    <p>Bon de commande N° : <?= $commande[0]['id_lscommande'] ?> </p>
                    <p>Date de commande : <?= date('d/m/Y H:i:s', strtotime($commande[0]['date_commande'])) ?> </p>
                    <?php 
                                    if ($commande[0]['date_livraison'] != "1111-11-11") {
                                    ?> 
                                        <div >
                                            <p>Date de livraison : <?= date('d/m/Y', strtotime($commande[0]['date_livraison'])) ?></p>
                                        </div>
                                    <?php  
                                    }
                        ?>
                </div>
            </div>

            <div class="cote-a-cote" style="width: 45%;">
                <p>De :</p>
                <p>TRANOMBAROTRA MANAMBINA</p>
            </div>
            <div class="cote-a-cote" style="width: 45%;">
                <p>A :</p>
                <p><?=$commande[0]['nom_fournisseur']?></p>
            </div>
            <br>
        
            <table class="mtable">
                <tr>
                <th>Designation</th>
                <th>Quantite</th>
                <th>Prix unitaire</th>
                <th>Prix Total</th>
                </tr>
                <?php   
                    foreach ($commande as $vente) { 
                        ?>
                        <tr>
                            <td><?=$vente['nom_article']?></td>
                            <td><?=$vente['quantite']?></td>
                            <td><?=formatPrix($vente['prix']/$vente['quantite'])?></td>
                            <td><?=formatPrix($vente['prix'])?></td>
                        </tr>
                        <?php
                            }
                    ?>  
                    <tr>
                        <td colspan="2"></td>
                         <td><strong>Total a payer:</strong></td>
                         <td><?=formatPrix($commande[0]['total_commande'])?></td>
                    </tr>
                </table>                
        </div>
    </div>

<?php 
include 'footer.php'
?>
<script>
    var btnPrint = document.querySelector('#btnPrint');
    btnPrint.addEventListener("click", () => {
        window.print();
    });
</script>

    