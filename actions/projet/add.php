<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../../model/function.php';

$userId = $_SESSION['ID_USER'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId) {
    $projectName = trim($_POST['project_name'] ?? '');
    $projectDescription = trim($_POST['project_description'] ?? '');

    if (!empty($projectName)) {
        $pdo = dbConnect();

        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM projet WHERE NOM_PROJET = ? AND ID_USER = ?");
        $stmtCheck->execute([$projectName, $userId]);

        if ($stmtCheck->fetchColumn() > 0) {
            $_SESSION['message'] = ['text' => "Un projet avec ce nom existe déjà.", 'type' => 'danger'];
        } else {
            $sql = "INSERT INTO projet (NOM_PROJET, DESCRIPTION_PROJET, ID_USER, DATE_CREATION) VALUES (?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$projectName, $projectDescription, $userId])) {
                $_SESSION['message'] = ['text' => "Projet créé avec succès !", 'type' => 'success'];
            }
        }
    }
}

header("Location: ../../vue/gestion_de_projet.php");
exit;