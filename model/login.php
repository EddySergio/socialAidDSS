<?php 
session_start();
include 'connexion.php';
include 'function.php';
if( !empty($_POST['username'])
    && !empty($_POST['mdp']))
{
        // Mettre mail et mot de passe dans des variables
        $email = $_POST['username'];
        $mdp = $_POST['mdp'];

        $sql = "SELECT * FROM user WHERE BINARY username=? AND BINARY mdp=?";
        $req = $GLOBALS['connexion']->prepare($sql);
        $req->execute(array($email, $mdp));
        $result = $req->fetch();

        if(!empty($result)) {
            // Redirection si l'utilisateur est trouvé
                $_SESSION['user_id'] = $result['id'];
                $user = getUser($_SESSION['user_id']);
                if ($user['type_user'] == 'admin') {
                    header("Location: ../vue/dashboard.php");
                    exit();
                }else{
                    header("Location: ../vue/article.php");
                    exit();
                }

        } else {
                $_SESSION['message']['text'] = "Nom d'utilisateur ou mot de passe incorecte";
                $_SESSION['message']['type'] = "danger";

                }
}                        
 else {
    $_SESSION['message']['text'] ="Une champ obligatoire n'est pas remplie";
    $_SESSION['message']['type'] = "danger";
}
header('Location: ../vue/index.php');



