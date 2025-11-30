<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../../model/function.php';
require_once '../../model/optionCritere.php';

$projectId = filter_input(INPUT_POST, 'project_id', FILTER_VALIDATE_INT);
// =========================================================================
// CORRECTION : S'assurer que project_id est valide avant de continuer.
// =========================================================================
if (!$projectId) {
    $_SESSION['message'] = ['text' => "ID de projet manquant ou invalide.", 'type' => 'danger'];
    header("Location: ../../vue/gestion_projet.php"); // Rediriger vers une page appropriée
    exit;
}// =========================================================================
// CORRECTION : On s'assure que le script est bien déclenché par le clic
// sur le bouton "Enregistrer" du formulaire d'ajout de critère.
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_criterion']) && $projectId) {
    // TEST : Vérifier si la condition est remplie et arrêter le script
    $criterionName = trim($_POST['name'] ?? '');
    $criterionType = trim($_POST['type']);
    $direction = $_POST['direction'] ?? 'max';

    if (!empty($criterionName)) {
        $pdo = dbConnect();
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM critere WHERE NOM_CRITERE = ? AND ID_PROJET = ?");
        $stmtCheck->execute([$criterionName, $projectId]);

        if ($stmtCheck->fetchColumn() > 0) {
            $_SESSION['message'] = ['text' => "Un critère avec ce nom existe déjà dans ce projet.", 'type' => 'danger'];
        } else {
            $pdo->beginTransaction();
            try {
                // =========================================================================
                // CORRECTION : Logique de calcul du rang simplifiée et rendue plus robuste.
                // On compte simplement le nombre de critères existants pour déterminer le nouveau rang.
                // =========================================================================
                $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM critere WHERE ID_PROJET = ?");
                $stmtCount->execute([$projectId]);
                $newRank = $stmtCount->fetchColumn() + 1;

                // Correction de la requête pour correspondre à la nouvelle structure de la table
                // On insère les poids flous à 0, ils seront recalculés juste après.
                $sqlCritere = "INSERT INTO critere (ID_PROJET, NOM_CRITERE, TYPE_CRITERE, RANG, OBJECTIF, poids_l, poids_m, poids_u) VALUES (?, ?, ?, ?, ?, 0, 0, 0)";
                $stmtCritere = $pdo->prepare($sqlCritere);
                $stmtCritere->execute([$projectId, $criterionName, $criterionType, $newRank, $direction]);
                $criterionId = $pdo->lastInsertId();   
                // Ajout des options qualitatives si le type est qualitative
                if ($criterionType === 'qualitative' && !empty($_POST['opt_labels'])) {
                        $sqlValeur = "INSERT INTO valeurqualitative (ID_CRITERE, LIBELLE, RANG) VALUES (?, ?, ?)";
                        $stmtValeur = $pdo->prepare($sqlValeur);

                        foreach ($_POST['opt_labels'] as $index => $label) {
                            $score = $_POST['opt_scores'][$index];
                            $stmtValeur->execute([$criterionId, $label, $score]); // Le score est utilisé comme RANG
                        }
                }

                // Gérer les alternatives existantes pour ce nouveau critère
                $alternatives = getAlternativesForProject($projectId);
                if (!empty($alternatives)) {
                    // La table correcte est 'performance', pas 'evaluation'.
                    $stmtPerf = $pdo->prepare(
                        "INSERT INTO performance (ID_PERSONNE, ID_CRITERE, VALEURNUM, VALEURQUAL, valeur_l, valeur_m, valeur_u) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );

                    foreach ($alternatives as $alt) {
                        $valeurNum = null;
                        $valeurQual = null;
                        $tfn = [0, 0, 0];

                        if ($criterionType === 'quantitative') {
                            $valeurNum = 0; // Valeur par défaut pour un critère quantitatif
                            // On utilise une très petite valeur pour le TFN pour éviter les divisions par zéro
                            $tfn = [0.00001, 0.00001, 0.00001];
                        } else { // Qualitative
                            // On prend la première option (la meilleure) comme valeur par défaut
                            $stmtOption = $pdo->prepare("SELECT LIBELLE, RANG FROM valeurqualitative WHERE ID_CRITERE = ? ORDER BY RANG ASC LIMIT 1");
                            $stmtOption->execute([$criterionId]);
                            $defaultOption = $stmtOption->fetch();

                            if ($defaultOption) {
                                $valeurQual = $defaultOption['LIBELLE'];
                                $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM valeurqualitative WHERE ID_CRITERE = ?");
                                $stmtCount->execute([$criterionId]);
                                $optionCount = $stmtCount->fetchColumn();
                                $tfn = generateFuzzyValue((int)$defaultOption['RANG'], (int)$optionCount);
                            }
                        }

                        if ($valeurNum !== null || $valeurQual !== null) {
                            $stmtPerf->execute([$alt['ID_PERSONNE'], $criterionId, $valeurNum, $valeurQual, $tfn[0], $tfn[1], $tfn[2]]);
                        }
                    }
                }

                // Recalculer les poids pour tous les critères du projet
                if (calculateAndSaveFuzzyWeights($pdo, $projectId)) {
                    $pdo->commit();
                    $_SESSION['message'] = ['text' => "Nouveau critère ajouté et poids mis à jour.", 'type' => 'success'];
                } else {
                    $pdo->rollBack();
                    $_SESSION['message'] = ['text' => "Erreur lors de la mise à jour des poids après ajout.", 'type' => 'danger'];
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['message'] = ['text' => "Erreur lors de l'ajout du critère : " . $e->getMessage(), 'type' => 'danger'];
            }
        }
    }
}

header("Location: ../../vue/gestion_critere.php?project_id=" . $projectId);
exit;