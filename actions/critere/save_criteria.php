<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../../model/function.php';

$projectId = filter_input(INPUT_POST, 'project_id', FILTER_VALIDATE_INT);
if (!$projectId) {
    $_SESSION['message'] = ['text' => "ID de projet manquant ou invalide.", 'type' => 'danger'];
    header("Location: ../../vue/gestion_de_projet.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $weightMethod = $_POST['weight_method'] ?? 'auto';

    $pdo = dbConnect();
    $pdo->beginTransaction();

    try {
        // 1. Mettre à jour la méthode de pondération pour le projet
        $stmtUpdateProject = $pdo->prepare("UPDATE projet SET methode_poids = ? WHERE ID_PROJET = ?");
        $stmtUpdateProject->execute([$weightMethod, $projectId]);

        // 2. Mettre à jour les rangs des critères dans tous les cas pour garder un ordre d'affichage cohérent
        $criteriaOrder = $_POST['criteria_order'] ?? [];
        if (!empty($criteriaOrder)) {
            $stmtUpdateRank = $pdo->prepare("UPDATE critere SET RANG = ? WHERE ID_CRITERE = ? AND ID_PROJET = ?");
            foreach ($criteriaOrder as $index => $criterionId) {
                $rank = $index + 1;
                $stmtUpdateRank->execute([$rank, $criterionId, $projectId]);
            }
        }

        // 3. Traiter les poids en fonction de la méthode choisie
        if ($weightMethod === 'manual') {
            // --- PONDÉRATION MANUELLE ---
            $poids_l = $_POST['poids_l'] ?? [];
            $poids_m = $_POST['poids_m'] ?? [];
            $poids_u = $_POST['poids_u'] ?? [];

            $stmtUpdateWeights = $pdo->prepare("UPDATE critere SET poids_l = ?, poids_m = ?, poids_u = ? WHERE ID_CRITERE = ?");

            foreach ($criteriaOrder as $criterionId) {
                // Utiliser 0 comme valeur par défaut si non fourni
                $l = !empty($poids_l[$criterionId]) ? floatval($poids_l[$criterionId]) : 0;
                $m = !empty($poids_m[$criterionId]) ? floatval($poids_m[$criterionId]) : 0;
                $u = !empty($poids_u[$criterionId]) ? floatval($poids_u[$criterionId]) : 0;

                $stmtUpdateWeights->execute([$l, $m, $u, $criterionId]);
            }
            $_SESSION['message'] = ['text' => "Les poids manuels ont été sauvegardés avec succès.", 'type' => 'success'];

        } else {
            // --- PONDÉRATION AUTOMATIQUE ---
            // Recalculer les poids flous en se basant sur les nouveaux rangs
            $success = calculateAndSaveFuzzyWeights($pdo, $projectId);
            if (!$success) {
                throw new Exception("Le calcul automatique des poids a échoué.");
            }
            $_SESSION['message'] = ['text' => "Le classement des critères a été mis à jour et les poids ont été recalculés.", 'type' => 'success'];
        }

        $pdo->commit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = ['text' => "Erreur lors de la sauvegarde : " . $e->getMessage(), 'type' => 'danger'];
    }
}

// Rediriger vers la page de gestion des critères
header("Location: ../../vue/gestion_critere.php?project_id=" . $projectId);
exit;

?>