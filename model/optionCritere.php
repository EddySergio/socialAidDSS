<?php

/**
 * Ajoute plusieurs options qualitatives à un critère.
 * @param int $criterionId L'ID du critère.
 * @param array $labels Les libellés des options.
 * @param array $scores Les scores des options.
 */


/**
 * Ajoute une nouvelle option qualitative à un critère.
 * @param int $criterionId L'ID du critère parent.
 * @param string $label Le libellé de la nouvelle option.
 * @return bool True en cas de succès.
 */


/**
 * Déplace une option qualitative vers le haut ou vers le bas dans le classement.
 * @param int $optionId L'ID de l'option à déplacer.
 * @param string $direction 'up' pour monter, 'down' pour descendre.
 * @return bool True en cas de succès.
 */
function rankOption(int $optionId, string $direction): bool {
    $pdo = dbConnect();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT ID_VALQUAL, ID_CRITERE, RANG FROM valeurqualitative WHERE ID_VALQUAL = ?");
        $stmt->execute([$optionId]);
        $current = $stmt->fetch();

        if (!$current) return false;

        $criterionId = $current['ID_CRITERE'];
        $currentRank = $current['RANG'];
        $targetRank = ($direction === 'up') ? $currentRank - 1 : $currentRank + 1;

        $stmt = $pdo->prepare("SELECT ID_VALQUAL, RANG FROM valeurqualitative WHERE ID_CRITERE = ? AND RANG = ?");
        $stmt->execute([$criterionId, $targetRank]);
        $neighbor = $stmt->fetch();

        if ($neighbor) {
            $stmtUpdateCurrent = $pdo->prepare("UPDATE valeurqualitative SET RANG = ? WHERE ID_VALQUAL = ?");
            $stmtUpdateCurrent->execute([$neighbor['RANG'], $current['ID_VALQUAL']]);

            $stmtUpdateNeighbor = $pdo->prepare("UPDATE valeurqualitative SET RANG = ? WHERE ID_VALQUAL = ?");
            $stmtUpdateNeighbor->execute([$currentRank, $neighbor['ID_VALQUAL']]);
        }
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Erreur de classement d'option: " . $e->getMessage());
        return false;
    }
}

/**
 * Supprime une option qualitative.
 * @param int $optionId L'ID de l'option à supprimer.
 * @return bool True en cas de succès.
 */
function deleteCriterionOption(int $optionId): bool {
    $pdo = dbConnect();
    try {
        $stmt = $pdo->prepare("DELETE FROM valeurqualitative WHERE ID_VALQUAL = ?");
        return $stmt->execute([$optionId]);
    } catch (Exception $e) {
        error_log("Erreur de suppression d'option qualitative: " . $e->getMessage());
        return false;
    }
}