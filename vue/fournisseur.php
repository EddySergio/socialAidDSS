<?php
$page_title = "FOURNISSEURS";
include 'header.php';

if (!empty($_GET['id'])) {
    $fournisseur = getFournisseur($_GET['id']);
}

?>
<div class="home-content">
    <div class="overview-boxes">
        <div class="box" style="max-width: 30%;">
            <form action="<?= !empty($_GET['id']) ?  "../model/modifFournisseur.php" : "../model/ajoutFournisseur.php" ?>" method="post">
            <label for="nom">Nom du fournisseur</label>
                <input value="<?= !empty($_GET['id']) ?  $fournisseur['nom_fournisseur'] : "" ?>" type="text" name="nom" id="nom" placeholder="Veuillez saisir le nom">
                <input value="<?= !empty($_GET['id']) ?  $fournisseur['id_fournisseur'] : "" ?>" type="hidden" name="id" id="id" >

                <label for="telephone">N° de téléphone</label>
                <input value="<?= !empty($_GET['id']) ?  $fournisseur['telephone'] : "" ?>" type="text" name="telephone" id="telephone" placeholder="Veuillez saisir le N° de téléphone">
                
                <label for="adresse">Adresse</label>
                <input style="margin-bottom: 10px;" value="<?= !empty($_GET['id']) ?  $fournisseur['adresse'] : "" ?>" type="text" name="adresse" id="adresse" placeholder="Veuillez saisir l'adresse">

                <button type="submit"><?=!empty($_GET['id']) ? "Modifier" : "Enregistrer" ?></button>

                <?php
                if (!empty($_SESSION['message']['text'])) {
                ?>
                    <div class="alert <?= $_SESSION['message']['type'] ?>">
                        <?= $_SESSION['message']['text'] ?>
                    </div>
                <?php
                unset($_SESSION['message']);
                }
                ?>
            </form>
        </div>
        <div class="box">
            <table class="mtable">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Téléphone</th>
                        <th>Adresse</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $fournisseurs = getFournisseur();

                    if (!empty($fournisseurs) && is_array($fournisseurs)) {
                        foreach ($fournisseurs as $key => $value) {
                    ?>
                            <tr>
                                <td><?= $value['nom_fournisseur'] ?></td>
                                <td><?= $value['telephone'] ?></td>
                                <td><?= $value['adresse'] ?></td>
                                <td>
                                    <div class="cote-a-cote">
                                        <a href="?id=<?= $value['id_fournisseur'] ?>"><img src="../public/img/edit.svg" alt=""></a>
                                        <a onclick="deleteFournisseur(<?= $value['id_fournisseur'] ?>)"><img src="../public/img/delete.svg" alt=""></a>
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
    function deleteFournisseur(id) {
        if (confirm("Voulez-vous vraiment effacer cette fournisseur ?")) {
            window.location.href = "../model/deleteFournisseur.php?id=" + id;
        }
    }
</script>
