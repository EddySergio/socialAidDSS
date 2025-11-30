<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../../model/function.php';

$projectId = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);
$criterionId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($projectId && $criterionId) {
    $pdo = dbConnect();
    $pdo->beginTransaction();
    try {
        // 1. Récupérer le rang du critère à supprimer pour la mise à jour ultérieure.
        $stmtGetRank = $pdo->prepare("SELECT RANG FROM critere WHERE ID_CRITERE = ?");
        $stmtGetRank->execute([$criterionId]);
        $deletedRank = $stmtGetRank->fetchColumn();

        if ($deletedRank !== false) {
            // 2. Supprimer les valeurs qualitatives associées (si elles existent).
            $stmtDeleteVal = $pdo->prepare("DELETE FROM valeurqualitative WHERE ID_CRITERE = ?");
            $stmtDeleteVal->execute([$criterionId]);

            // 3. Supprimer le critère lui-même.
            $stmtDeleteCrit = $pdo->prepare("DELETE FROM critere WHERE ID_CRITERE = ?");
            $stmtDeleteCrit->execute([$criterionId]);

            // 4. Mettre à jour le rang des critères restants qui étaient en dessous.
            $stmtUpdateRanks = $pdo->prepare("UPDATE critere SET RANG = RANG - 1 WHERE ID_PROJET = ? AND RANG > ?");
            $stmtUpdateRanks->execute([$projectId, $deletedRank]);

            // Recalculer les poids pour les critères restants
            if (calculateAndSaveFuzzyWeights($pdo, $projectId)) {
                $pdo->commit();
                $_SESSION['message'] = ['text' => "Critère supprimé et poids mis à jour.", 'type' => 'info'];
            } else {
                $pdo->rollBack();
                $_SESSION['message'] = ['text' => "Erreur lors de la mise à jour des poids après suppression.", 'type' => 'danger'];
            }
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = ['text' => "Erreur lors de la suppression du critère : " . $e->getMessage(), 'type' => 'danger'];
    }
}

header("Location: ../../vue/gestion_critere.php?project_id=" . ($projectId ?: ''));
exit;