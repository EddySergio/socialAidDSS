<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../../model/function.php';

// Utilisation des classes de la bibliothèque PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;

$projectId = filter_input(INPUT_POST, 'project_id', FILTER_VALIDATE_INT);
if (!$projectId) {
    $_SESSION['message'] = ['text' => "ID de projet manquant ou invalide.", 'type' => 'danger'];
    header("Location: ../../vue/gestion_de_projet.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['message'] = ['text' => "Erreur lors de l'envoi du fichier.", 'type' => 'danger'];
        header("Location: ../../vue/gestion_alternatives.php?project_id=" . $projectId);
        exit;
    }

    $pdo = dbConnect();

    try {
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Récupérer les critères du projet pour faire correspondre les colonnes
        $criteria = getCriteriaForProject($projectId);
        if (empty($criteria)) {
            throw new Exception("Aucun critère n'est défini pour ce projet. Import impossible.");
        }

        // Pré-charger les options qualitatives valides pour la validation
        $validQualitativeOptions = [];
        foreach ($criteria as $criterion) {
            if ($criterion['TYPE_CRITERE'] === 'qualitative') {
                $options = getQualitativeValuesForCriterion($criterion['ID_CRITERE']);
                // On stocke uniquement les libellés pour une recherche facile
                $validQualitativeOptions[$criterion['ID_CRITERE']] = array_column($options, 'LIBELLE');
            }
        }

        $errors = [];
        $validRowsData = [];
        $headerSkipped = false;

        // Étape 1: Valider toutes les lignes avant d'insérer
        foreach ($rows as $rowIndex => $row) {
            if (!$headerSkipped) {
                $headerSkipped = true;
                continue; // Sauter la ligne d'en-tête
            }

            $personneName = trim($row[0] ?? '');
            if (empty($personneName)) continue; // Ignorer les lignes où le nom est vide

            $rowData = ['name' => $personneName, 'evaluations' => []];
            $isRowValid = true;

            foreach ($criteria as $colIndex => $criterion) {
                $critId = $criterion['ID_CRITERE'];
                $critName = $criterion['NOM_CRITERE'];
                $value = trim($row[$colIndex + 1] ?? '');

                if ($criterion['TYPE_CRITERE'] === 'quantitative') {
                    if (!is_numeric($value)) {
                        $errors[] = "Ligne " . ($rowIndex + 1) . " ('$personneName'), critère '$critName': La valeur '$value' n'est pas un nombre valide.";
                        $isRowValid = false;
                    }
                } else { // Qualitative
                    if (!in_array($value, $validQualitativeOptions[$critId])) {
                        $errors[] = "Ligne " . ($rowIndex + 1) . " ('$personneName'), critère '$critName': La valeur '$value' n'est pas une option valide. Options possibles : " . implode(', ', $validQualitativeOptions[$critId]);
                        $isRowValid = false;
                    }
                }
                $rowData['evaluations'][$critId] = $value;
            }

            if ($isRowValid) {
                $validRowsData[] = $rowData;
            }
        }

        // Étape 2: Si des erreurs ont été trouvées, annuler l'importation et les afficher
        if (!empty($errors)) {
            $errorHtml = '<h6 class="alert-heading fw-bold">Échec de l\'importation</h6>';
            $errorHtml .= "<p>L'opération a été annulée car des erreurs ont été détectées dans votre fichier. Veuillez corriger les points suivants et réessayer :</p><hr>";
            $errorHtml .= "<ul class='mb-0' style='max-height: 200px; overflow-y: auto;'>";
            foreach ($errors as $error) {
                $errorHtml .= "<li>" . htmlspecialchars($error) . "</li>";
            }
            $errorHtml .= "</ul>";

            $_SESSION['message'] = ['text' => $errorHtml, 'type' => 'danger'];
            header("Location: ../../vue/gestion_alternatives.php?project_id=" . $projectId);
            exit;
        }

        // Étape 3: Si tout est valide, procéder à l'insertion
        $pdo->beginTransaction();
        $stmtPersonne = $pdo->prepare("INSERT INTO personne (ID_PROJET, NOM_PERSONNE) VALUES (?, ?)");
        $stmtPerformance = $pdo->prepare(
            "INSERT INTO performance (ID_PERSONNE, ID_CRITERE, VALEURNUM, VALEURQUAL, valeur_l, valeur_m, valeur_u) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        foreach ($validRowsData as $data) {
            $stmtPersonne->execute([$projectId, $data['name']]);
            $personneId = $pdo->lastInsertId();

            foreach ($data['evaluations'] as $critId => $value) {
                $criterion = current(array_filter($criteria, fn($c) => $c['ID_CRITERE'] == $critId));
                
                $critId = $criterion['ID_CRITERE'];
                $valeurNum = null;
                $valeurQual = null;
                $tfn = [0, 0, 0];

                if ($criterion['TYPE_CRITERE'] === 'quantitative') {
                    $valeurNum = floatval($value);
                    $tfn = ($valeurNum == 0) ? [0.00001, 0.00001, 0.00001] : [$valeurNum, $valeurNum, $valeurNum];
                } else { // Qualitative
                    $valeurQual = $value;
                    $stmtOption = $pdo->prepare("SELECT RANG FROM valeurqualitative WHERE ID_CRITERE = ? AND LIBELLE = ?");
                    $stmtOption->execute([$critId, $valeurQual]);
                    $rank = $stmtOption->fetchColumn();
                    $tfn = generateFuzzyValue((int)$rank, count($validQualitativeOptions[$critId]));
                }
                $stmtPerformance->execute([$personneId, $critId, $valeurNum, $valeurQual, $tfn[0], $tfn[1], $tfn[2]]);
            }
        }

        $pdo->commit();
        $_SESSION['message'] = ['text' => count($validRowsData) . " cible(s) importé(s) avec succès.", 'type' => 'success'];
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = ['text' => "Erreur lors de l'importation : " . $e->getMessage(), 'type' => 'danger'];
    }
}

header("Location: ../../vue/gestion_alternatives.php?project_id=" . $projectId);
exit;

?>
```

Avec ces ajouts, votre application est maintenant capable d'importer en masse des cibles et leurs évaluations, ce qui devrait grandement accélérer votre flux de travail.

<!--
[PROMPT_SUGGESTION]Comment puis-je créer un modèle Excel à télécharger pour guider l'utilisateur ?[/PROMPT_SUGGESTION]
[PROMPT_SUGGESTION]Améliore la gestion des erreurs pour indiquer quelle ligne du fichier Excel a posé problème.[/PROMPT_SUGGESTION]
