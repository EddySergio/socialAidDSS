<?php
$page_title= "CATEGORIE";
include 'header.php';

if (!empty($_GET['id'])) {
    $categorie = getCategorie($_GET['id']);
}

?>
<div class="home-content">
    <div class="overview-boxes">
        <div class="box">
            <form action=" <?= !empty($_GET['id']) ?  "../model/modifCategorie.php" : "../model/ajoutCategorie.php" ?>" method="post">
                <label for="libelle_categorie">Libelle</label>
                <input style="margin-bottom: 10px;" value="<?= !empty($_GET['id']) ?  $categorie['categorie'] : "" ?>" type="text" name="libelle_categorie" id="libelle_categorie" placeholder="Veuillez saisir le libéllé">
                <input value="<?= !empty($_GET['id']) ?  $categorie['id_categorie'] : "" ?>" type="hidden" name="id" id="id" >

                <button type="submit"><?=!empty($_GET['id']) ? "Modifier" : "Enregistrer" ?></button>

                <?php
                if (!empty($_SESSION['message']['text'])) {
                ?>
                    <div class="alert <?= $_SESSION['message']['type'] ?>">
                        <?= $_SESSION['message']['text'] ?>
                    </div>
                <?php
                unset($_SESSION['message']['text']);
                }
                ?>
            </form>

        </div>
        <div class="box" style="min-width: 40%;">
            <table class="mtable">
                <thead>
                <tr>
                    <th>Libelle categorie</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                    <?php
                    $categories = getCategorie();

                    if (!empty($categories) && is_array($categories)) {
                        foreach ($categories as $key => $value) {
                            
                    ?>
                            <tr>
                                <td><?= $value['categorie'] ?></td>
                                <?php
                                if ($value['id_categorie'] != 70) {
                                    if ($value['id_categorie'] != 71) {
                                        if ($value['id_categorie'] != 72) {

                                ?>
                                <td>
                                    <div>
                                        <a href="categorie.php?id=<?= $value['id_categorie']?>"><img src="../public/img/edit.svg" alt=""></a>
                                    </div>
                                </td>
                                <?php
                                    }
                                }
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
include 'footer.php';
?>
