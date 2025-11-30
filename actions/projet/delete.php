<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../../model/function.php';

$userId = $_SESSION['ID_USER'] ?? null;
$projectIdToDelete = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($projectIdToDelete && $userId) {
    $pdo = dbConnect();
    $stmt = $pdo->prepare("DELETE FROM projet WHERE ID_PROJET = ? AND ID_USER = ?");
    if ($stmt->execute([$projectIdToDelete, $userId])) {
        $_SESSION['message'] = ['text' => "Projet supprimé avec succès.", 'type' => 'info'];
    }
}

header("Location: ../../vue/gestion_de_projet.php");
exit;