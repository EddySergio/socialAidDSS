<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../../model/function.php';

$projectId = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);
$alternativeIdToDelete = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($projectId && $alternativeIdToDelete) {
    $pdo = dbConnect();
    $stmt = $pdo->prepare("DELETE FROM personne WHERE ID_PERSONNE = ?");
    if ($stmt->execute([$alternativeIdToDelete])) {
        $_SESSION['message'] = ['text' => "cible supprimé avec succès.", 'type' => 'success'];
    }
}

header("Location: ../../vue/gestion_alternatives.php?project_id=" . ($projectId ?: ''));
exit;