<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../../model/function.php';

$projectId = filter_input(INPUT_POST, 'project_id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $projectId) {
    $orderedCriteriaIds = $_POST['criteria_order'] ?? [];

    if (!empty($orderedCriteriaIds)) {
        $pdo = dbConnect();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE critere SET RANG = ? WHERE ID_CRITERE = ? AND ID_PROJET = ?");
            foreach ($orderedCriteriaIds as $rank => $criterionId) {
                $stmt->execute([$rank + 1, $criterionId, $projectId]);
            }

            // Après avoir mis à jour les rangs, on recalcule les poids flous.
            if (calculateAndSaveFuzzyWeights($pdo, $projectId)) {
                $pdo->commit();
                $_SESSION['message'] = ['text' => "Classement et poids des critères mis à jour avec succès.", 'type' => 'success'];
            } else {
                $pdo->rollBack();
                $_SESSION['message'] = ['text' => "Erreur lors du calcul des poids des critères.", 'type' => 'danger'];
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['message'] = ['text' => "Erreur lors de la sauvegarde du classement.", 'type' => 'danger'];
        }
    }
}

header("Location: ../../vue/gestion_critere.php?project_id=" . ($projectId ?: ''));
exit;