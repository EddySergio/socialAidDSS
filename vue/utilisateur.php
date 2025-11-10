<?php
$page_title = "UTILISATEURS";
include 'header.php';

if (!empty($_GET['id'])) {
    $user = getUser($_GET['id']);
}

?>
<div class="home-content">
    <div class="overview-boxes">
        <div class="box" style="max-width: 30%;">
            <form action="<?= !empty($_GET['id']) ?  "../model/modifUser.php" : "../model/ajoutUser.php" ?>" method="post">
            <label for="username">Nom d'utisateur</label>
                <input value="<?= !empty($_GET['id']) ?  $user['username'] : "" ?>" type="text" name="username" id="username" placeholder="Veuillez saisir le nom d'utilisateur">
                <input value="<?= !empty($_GET['id']) ?  $user['id'] : "" ?>" type="hidden" name="id" id="id" >

                <label for="mdp">Mot de passe</label>
                <input value="<?= !empty($_GET['id']) ?  $user['mdp'] : "" ?>" type="password" name="mdp" id="mdp" placeholder="Veuillez saisir votre mot de passe">
                
                <label for="type_user">Type</label>
                    <select style="margin-bottom: 10px;" name="type_user" id="type_user">
                        <option <?=!empty($_GET['id']) && $user['type_user']== "admin" ? "selected" : ""?> value="admin">admin</option>
                        <option <?=!empty($_GET['id']) && $user['type_user']== "caissier" ? "selected" : ""?> value="caissier">caissier</option>
                    </select>

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
        <div class="box" style="min-width: 50%;">
            <table class="mtable">
                <thead>
                    <tr>
                        <th>Nom d'utilisateur</th>
                        <th>Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $users = getuser();

                    if (!empty($users) && is_array($users)) {
                        foreach ($users as $key => $value) {
                    ?>
                            <tr>
                                <td><?= $value['username'] ?></td>
                                <td><?= $value['type_user'] ?></td>
                                <td>
                                    <div class="cote-a-cote">
                                        <a href="?id=<?= $value['id'] ?>"><img src="../public/img/edit.svg" alt=""></a>
                                        <a onclick="deleteuser(<?= $value['id'] ?>)"><img src="../public/img/delete.svg" alt=""></a>
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
    function deleteuser(idArticle) {
        if (confirm("Voulez-vous vraiment effacer cette user ?")) {
            window.location.href = "../model/deleteUser.php?id=" + idArticle;
        }
    }
</script>
