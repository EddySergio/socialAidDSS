<?php 
$page_title = "RECU";
include 'header.php';

if (!empty($_GET['id'])) {
    $ventes = getVente($_GET['id']);
}

?>
    <div class="home-content">
    <!--onclick="Imprimer(<?= $ventes[0]['id_panier'] ?>)"-->

      <button class="hidden-print" id="btnPrintA4" style="position: relative; left: 30%;"> <i class='bx bx-printer'></i> Imprimer A4</button>
      <a class="hidden-print" style="position: relative; left: 45%;" href="xrecu.php?id=<?= $ventes[0]['id_panier']?>"><button class="hidden-print" id="btnPrintXpriter"> <i class='bx bx-printer'></i> Imprimer Xpriter</button></a>
        <div class="page">
            <div class="cote-a-cote">
                <h2 style="margin-bottom: 20px;">TRANOMBAROTRA MANAMBINA</h2>
                <div>
                    <p>Reçu N°        : <?= $ventes[0]['id_panier'] ?> </p>
                    <p>Date : <?= date('d/m/Y H:i:s', strtotime($ventes[0]['date_vente'])) ?> </p>
                </div>
            </div>

            <div class="cote-a-cote" style="width: 45%;">
                <p>Nom du client :</p>
                <p><?=$ventes[0]['nom']?></p>
            </div>
            <div class="cote-a-cote" style="width: 45%;">
                <p>Caissier(e) :</p>
                <p><?= $ventes[0]['username']?></p>
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
                    foreach ($ventes as $vente) { 
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
                        <td><?=formatPrix($ventes[0]['total'])?></td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td><strong>Paiement:</strong></td>
                        <td><?=formatPrix($ventes[0]['paiement'])?></td>   
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td><strong>A rendre:</strong></td>
                        <?php 
                            $rendu = 0;
                            if($ventes[0]['paiement'] >= $ventes[0]['total']){
                                $rendu = $ventes[0]['paiement'] - $ventes[0]['total'] ;
                            }else{
                                $rendu = 0;
                            }
                        ?>
                        <td><?=formatPrix($rendu)?></td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td><strong>Reste a payer:</strong></td>
                        <td><?=formatPrix($ventes[0]['reste'])?></td>
                    </tr>
                </table>                
        </div>
    </div>

<?php 
include 'footer.php'
?>
<script>
    var btnPrint = document.querySelector('#btnPrintA4');
    btnPrint.addEventListener("click", () => {
        window.print();
        //Imprimer(<?= $ventes[0]['id_panier'] ?>);
    });
    function Imprimer(idRecu){
        window.location.href = "xrecu.php?id=" + idArticle;
    };
</script>

    