<?php
$pageTitle = "Matrice d'Évaluation";
include 'header.php';

// Valider l'ID du projet
$projectId = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);
if (!$projectId) {
    echo "<div class='alert alert-danger'>ID de projet non valide.</div>";
    include 'footer.php';
    exit;
}

// Récupérer les données nécessaires pour ce projet
$project = getProjectDetails($projectId);
$criteria = getCriteriaForProject($projectId); // Critères en colonnes
$alternatives = getAlternativesForProject($projectId); // Alternatives (personnes) en lignes

if (!$project) {
    echo "<div class='alert alert-danger'>Projet non trouvé.</div>";
    include 'footer.php';
    exit;
}

// Logique de sauvegarde de la matrice
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $evaluations = $_POST['evaluations'] ?? [];
    if (saveEvaluations($projectId, $evaluations)) { // La fonction saveEvaluations est dans function.php
        // Redirection pour actualiser la page et éviter le renvoi du formulaire
        header("Location: evaluation_matrice.php?project_id=$projectId&success=1");
        exit;
    } else {
        $message = '<div class="alert alert-danger">Erreur lors de la sauvegarde de la matrice.</div>';
    }
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = '<div class="alert alert-success">Matrice d\'évaluation sauvegardée avec succès.</div>';
}

// Récupérer les évaluations existantes pour pré-remplir le formulaire
$existingEvaluations = getEvaluationsForProject($projectId);

?>

<div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 fw-bold">Évaluation des cibles</h2>
            <p class="text-muted">Projet : <strong class="text-primary"><?= htmlspecialchars($project['NOM_PROJET']) ?></strong></p>
        </div>
        <a href="gestion_critere.php?project_id=<?= $projectId ?>" class="btn btn-secondary">
             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill me-2" viewBox="0 0 16 16"><path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0zm3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"/></svg>
            Retour à la configuration
        </a>
    </div>

    <?= $message ?>

    <?php if (empty($criteria) || empty($alternatives)): ?>
        <div class="alert alert-warning">
            Veuillez d'abord définir au moins un <a href="gestion_critere.php?project_id=<?= $projectId ?>">critère</a> et une <a href="gestion_alternatives.php?project_id=<?= $projectId ?>">personne cible</a> avant de procéder à l'évaluation.
        </div>
    <?php else: ?>
        <div class="card shadow-lg">
            <div class="card-header fw-bold">Matrice de Décision</div>
            <div class="card-body">
                <p class="text-muted">Évaluez chaque personne (ligne) en fonction de chaque critère (colonne). Pour les méthodes floues, vous utiliserez des échelles linguistiques (Ex: "Faible", "Moyen", "Élevé").</p>
                
                <form method="POST" action="evaluation_matrice.php?project_id=<?= $projectId ?>">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-center">
                            <thead class="table-light">
                                <tr>
                                    <th class="align-middle">Personnes Cibles</th>
                                    <?php foreach ($criteria as $criterion): ?>
                                        <th class="align-middle" title="<?= htmlspecialchars($criterion['NOM_CRITERE']) ?>">
                                            <?= htmlspecialchars($criterion['NOM_CRITERE']) ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alternatives as $alternative): ?>
                                    <tr>
                                        <th class="text-start align-middle"><?= htmlspecialchars($alternative['NOM_PERSONNE']) ?></th>
                                        <?php foreach ($criteria as $criterion): ?>
                                            <td>
                                                <?php 
                                                $currentValue = $existingEvaluations[$alternative['ID_PERSONNE']][$criterion['ID_CRITERE']] ?? '';
                                                
                                                if ($criterion['TYPE_CRITERE'] === 'qualitative'): 
                                                    // Récupérer les valeurs pour ce critère qualitative
                                                    $qualitativeValues = getQualitativeValuesForCriterion($criterion['ID_CRITERE']);
                                                ?>
                                                    <select name="evaluations[<?= $alternative['ID_PERSONNE'] ?>][<?= $criterion['ID_CRITERE'] ?>]" class="form-select form-select-sm">
                                                        <option value="">Choisir...</option>
                                                        <?php foreach ($qualitativeValues as $val): ?>
                                                            <option value="<?= htmlspecialchars($val['LIBELLE']) ?>" <?= ($currentValue == $val['LIBELLE']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($val['LIBELLE']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                <?php else: // Critère quantitative ?>
                                                    <input type="number" step="any" name="evaluations[<?= $alternative['ID_PERSONNE'] ?>][<?= $criterion['ID_CRITERE'] ?>]" 
                                                           class="form-control form-control-sm" placeholder="Note..." value="<?= htmlspecialchars($currentValue) ?>">
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-success me-2">Sauvegarder la Matrice</button>
                        <a href="resultats.php?project_id=<?= $projectId ?>" class="btn btn-primary">Lancer le Calcul et Voir les Résultats</a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>