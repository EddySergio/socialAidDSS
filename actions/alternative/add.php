<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../../model/function.php';

$projectId = filter_input(INPUT_POST, 'project_id', FILTER_VALIDATE_INT);
$alternativeId = filter_input(INPUT_POST, 'alternative_id', FILTER_VALIDATE_INT); // ID pour l'édition

if (!$projectId) {
    $_SESSION['message'] = ['text' => "ID de projet manquant ou invalide.", 'type' => 'danger'];
    header("Location: ../../vue/gestion_de_projet.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $personneName = trim($_POST['personne_name'] ?? '');
    $evaluations = $_POST['evaluations'] ?? [];

    if (empty($personneName) || empty($evaluations)) {
        $_SESSION['message'] = ['text' => "Le nom de la cible et toutes les évaluations sont requis.", 'type' => 'danger'];
        header("Location: ../../vue/gestion_alternatives.php?project_id=" . $projectId);
        exit;
    }

    $pdo = dbConnect();
    $pdo->beginTransaction();
 
    try {
        if ($alternativeId) {
            // --- MODE MISE À JOUR ---
            // 1. Mettre à jour le nom de la personne
            $stmtUpdatePersonne = $pdo->prepare("UPDATE personne SET NOM_PERSONNE = ? WHERE ID_PERSONNE = ? AND ID_PROJET = ?");
            $stmtUpdatePersonne->execute([$personneName, $alternativeId, $projectId]);
            
            // 2. Supprimer les anciennes performances pour cette personne
            $stmtDeletePerf = $pdo->prepare("DELETE FROM performance WHERE ID_PERSONNE = ?");
            $stmtDeletePerf->execute([$alternativeId]);
            
            $personneId = $alternativeId; // Utiliser l'ID existant pour la réinsertion
            $_SESSION['message'] = ['text' => "La cible '" . htmlspecialchars($personneName) . "' a été mise à jour.", 'type' => 'success'];
        } else {
            // --- MODE AJOUT (comportement original) ---
            // Vérifier si une personne avec le même nom existe déjà dans ce projet
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM personne WHERE NOM_PERSONNE = ? AND ID_PROJET = ?");
            $stmtCheck->execute([$personneName, $projectId]);
            if ($stmtCheck->fetchColumn() > 0) {
                $_SESSION['message'] = ['text' => "Une cible avec le nom '" . htmlspecialchars($personneName) . "' existe déjà.", 'type' => 'warning'];
                $pdo->rollBack();
                header("Location: ../../vue/gestion_alternatives.php?project_id=" . $projectId);
                exit;
            }
            // 1. Insérer la nouvelle personne
            $stmtPersonne = $pdo->prepare("INSERT INTO personne (ID_PROJET, NOM_PERSONNE) VALUES (?, ?)");
            $stmtPersonne->execute([$projectId, $personneName]);
            $personneId = $pdo->lastInsertId();
            $_SESSION['message'] = ['text' => "Nouvelle cible '" . htmlspecialchars($personneName) . "' ajoutée avec succès.", 'type' => 'success'];
        }

        // --- PARTIE COMMUNE : INSERTION DES PERFORMANCES ---
        // Cette logique est la même pour un ajout ou une mise à jour (après suppression)
        $stmtPerformance = $pdo->prepare(
            "INSERT INTO performance (ID_PERSONNE, ID_CRITERE, VALEURNUM, VALEURQUAL, valeur_l, valeur_m, valeur_u) 
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $criteria = getCriteriaForProject($projectId);
        foreach ($criteria as $criterion) {
            $critId = $criterion['ID_CRITERE'];
            if (!isset($evaluations[$critId]) || $evaluations[$critId] === '') continue;

            $value = $evaluations[$critId];
            $valeurNum = null;
            $valeurQual = null;
            $tfn = [0, 0, 0];

            if ($criterion['TYPE_CRITERE'] === 'quantitative') {
                $valeurNum = floatval($value);
                // Si la valeur est 0, on utilise une très petite valeur pour le TFN pour éviter les divisions par zéro
                if ($valeurNum == 0) {
                    $tfn = [0.00001, 0.00001, 0.00001];
                } else {
                    $tfn = [$valeurNum, $valeurNum, $valeurNum]; // TFN net pour les quantitatifs
                }
            } else { // Qualitative
                $valeurQual = $value;
                $stmtOption = $pdo->prepare("SELECT RANG FROM valeurqualitative WHERE ID_CRITERE = ? AND LIBELLE = ?");
                $stmtOption->execute([$critId, $valeurQual]);
                $rank = $stmtOption->fetchColumn();

                $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM valeurqualitative WHERE ID_CRITERE = ?");
                $stmtCount->execute([$critId]);
                $optionCount = $stmtCount->fetchColumn();

                if ($rank !== false && $optionCount > 0) {
                    $tfn = generateFuzzyValue((int)$rank, (int)$optionCount);
                }
            }

            $stmtPerformance->execute([$personneId, $critId, $valeurNum, $valeurQual, $tfn[0], $tfn[1], $tfn[2]]);
        }

        $pdo->commit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $errorMessage = $alternativeId ? "de la mise à jour" : "de l'ajout";
        $_SESSION['message'] = ['text' => "Erreur lors {$errorMessage} de la cible : " . $e->getMessage(), 'type' => 'danger'];
    }
}

header("Location: ../../vue/gestion_alternatives.php?project_id=" . $projectId);
exit;
?>