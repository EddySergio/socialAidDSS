<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../../model/function.php';

$projectId = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);
$criterionId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$direction = $_GET['direction'] ?? ''; // 'up' or 'down'

if ($projectId && $criterionId && in_array($direction, ['up', 'down'])) {
    $pdo = dbConnect();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT ID_CRITERE, ID_PROJET, RANG FROM critere WHERE ID_CRITERE = ?");
        $stmt->execute([$criterionId]);
        $current = $stmt->fetch();

        if ($current) {
            $currentRank = $current['RANG'];
            $targetRank = ($direction === 'up') ? $currentRank - 1 : $currentRank + 1;

            $stmt = $pdo->prepare("SELECT ID_CRITERE, RANG FROM critere WHERE ID_PROJET = ? AND RANG = ?");
            $stmt->execute([$projectId, $targetRank]);
            $neighbor = $stmt->fetch();

            if ($neighbor) {
                $stmtUpdateCurrent = $pdo->prepare("UPDATE critere SET RANG = ? WHERE ID_CRITERE = ?");
                $stmtUpdateCurrent->execute([$neighbor['RANG'], $current['ID_CRITERE']]);
                $stmtUpdateNeighbor = $pdo->prepare("UPDATE critere SET RANG = ? WHERE ID_CRITERE = ?");
                $stmtUpdateNeighbor->execute([$currentRank, $neighbor['ID_CRITERE']]);
            }
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }
}

header("Location: ../../vue/gestion_critere.php?project_id=" . ($projectId ?: ''));
exit;