<?php
$pageTitle = "Gestion des Critères";
// =========================================================================
// RESTRUCTURATION : PARTIE 1 - PRÉPARATION DE LA VUE
// Ce bloc ne fait que préparer les données pour l'affichage.
// Toute la logique de traitement (ajout, suppression, etc.) a été retirée.
require_once '../model/function.php';

$projectId = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);
if (!$projectId) {
    echo "<div class='alert alert-danger'>ID de projet non valide.</div>";
    include 'footer.php';
    exit;
}

$project = getProjectDetails($projectId);
if (!$project) {
    echo "<div class='alert alert-danger'>Projet non trouvé.</div>";
    include 'footer.php';
    exit;
}

// =========================================================================
// INSPIRATION ajoutArticle.php : GESTION DES MESSAGES VIA SESSION
// =========================================================================
// =========================================================================
// RESTRUCTURATION : PARTIE 3 - RÉCUPÉRATION DES DONNÉES (GET)
// La page récupère les données à jour depuis la base de données pour les afficher.
// C'est l'étape "Get" du modèle Post/Redirect/Get.
$projectCriteria = getCriteriaForProject($projectId);
include 'header.php';
?>

<div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted">Projet : <strong class="text-primary"><?= htmlspecialchars($project['NOM_PROJET']) ?></strong></p>
        </div>
        <div>
            <a href="gestion_de_projet.php" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill me-2" viewBox="0 0 16 16"><path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0zm3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"/></svg>
                Retour aux projets
            </a>
        </div>
    </div>
</div>

<nav aria-label="breadcrumb">
  <ol class="breadcrumb bg-white p-3 rounded shadow-sm">
    <li class="breadcrumb-item"><a href="gestion_de_projet.php" class="text-decoration-none">Projets</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page"><?= htmlspecialchars($project['NOM_PROJET']) ?> : Gestion des Critères</li>
  </ol>
</nav>

<h2 class="fw-bold text-dark mb-4"><i class="bi bi-card-checklist text-primary me-2"></i> Gestion des Critères - <?= htmlspecialchars($project['NOM_PROJET']) ?></h2>

<!-- Navigation d'étape -->
<div class="d-flex justify-content-center mb-5">
    <div class="btn-group shadow-sm" role="group">
        <button class="btn btn-primary disabled"><i class="bi bi-card-checklist me-2"></i> Critères</button>
        <a href="gestion_alternatives.php?project_id=<?= $projectId ?>" class="btn btn-outline-primary"><i class="bi bi-people me-2"></i> Cibles</a>
        <a href="resultats.php?project_id=<?= $projectId ?>" class="btn btn-outline-primary"><i class="bi bi-graph-up me-2"></i> Résultats</a>
    </div>
</div>

<!-- ========================================================================= -->
<!-- INSPIRATION : Le formulaire est maintenant dans une modale (pop-up). -->
<!-- Un bouton est ajouté pour déclencher l'ouverture de cette modale. -->
<!-- ========================================================================= -->
<div class="d-flex justify-content-end mb-4">
    <button type="button" class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#addCriterionModal">
        <i class="bi bi-plus-lg me-1"></i> Nouveau Critère
    </button>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold pt-3">
                <i class="bi bi-list-ol text-primary me-2"></i> Classement des Critères
            </div>
            <div class="card-body p-0">
                <form action="../actions/critere/save_criteria.php" method="POST">
                    <input type="hidden" name="project_id" value="<?= $projectId ?>">
                    <div class="p-3 border-bottom">
                        <h6 class="fw-bold">Méthode de Pondération</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="weight_method" id="auto_weights" value="auto" <?= ($project['methode_poids'] ?? 'auto') === 'auto' ? 'checked' : '' ?> onchange="toggleWeightInputs()">
                            <label class="form-check-label" for="auto_weights">
                                <strong>Automatique</strong> - Les poids sont calculés selon le rang (Fuzzy Reciprocal Weights).
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="weight_method" id="manual_weights" value="manual" <?= ($project['methode_poids'] ?? 'auto') === 'manual' ? 'checked' : '' ?> onchange="toggleWeightInputs()">
                            <label class="form-check-label" for="manual_weights">
                                <strong>Manuelle</strong> - Saisir les poids flous (l, m, u) pour chaque critère.
                            </label>
                        </div>
                    </div>
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;"></th>
                                <th class="ps-3">Rang</th>
                                <th>Critère</th>
                                <th>Détail</th>
                                <th class="manual-weight-col" style="display: none;">Poids l</th>
                                <th class="manual-weight-col" style="display: none;">Poids m</th>
                                <th class="manual-weight-col" style="display: none;">Poids u</th>
                                <th>Options</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody id="criteria-list-body">
                            <?php if(empty($projectCriteria)): ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted">Aucun critère.</td></tr>
                            <?php endif; ?>
                            <?php foreach($projectCriteria as $index => $c): ?>
                            <tr data-id="<?= $c['ID_CRITERE'] ?>">
                                <td class="text-center"><i class="bi bi-grip-vertical drag-handle" style="cursor: move;"></i></td>
                                <td class="ps-3">
                                    <input type="hidden" name="criteria_order[]" value="<?= $c['ID_CRITERE'] ?>">
                                    <span class="badge bg-dark rounded-pill rank-display"><?= $c['RANG'] ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($c['NOM_CRITERE']) ?></span>
                                </td>
                                <td>
                                    <?php if($c['TYPE_CRITERE']=='quantitative'): ?>
                                        <span class="badge bg-info bg-opacity-10 text-info me-2">Numérique</span>
                                        <?php if ($c['OBJECTIF'] === 'max'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success">Maximiser</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger">Minimiser</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-warning bg-opacity-10 text-warning mb-1">Qualitative</span>
                                        <?php if ($c['OBJECTIF'] === 'max'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success">Maximiser</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger">Minimiser</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="manual-weight-col" style="display: none;">
                                    <input type="number" step="0.001" name="poids_l[<?= $c['ID_CRITERE'] ?>]" class="form-control form-control-sm" value="<?= number_format($c['poids_l'], 3, '.', '') ?>">
                                </td>
                                <td class="manual-weight-col" style="display: none;">
                                    <input type="number" step="0.001" name="poids_m[<?= $c['ID_CRITERE'] ?>]" class="form-control form-control-sm" value="<?= number_format($c['poids_m'], 3, '.', '') ?>">
                                </td>
                                <td class="manual-weight-col" style="display: none;">
                                    <input type="number" step="0.001" name="poids_u[<?= $c['ID_CRITERE'] ?>]" class="form-control form-control-sm" value="<?= number_format($c['poids_u'], 3, '.', '') ?>">
                                </td>
                                <td>
                                    <?php if($c['TYPE_CRITERE']=='qualitative' && !empty($c['options'])): ?>
                                        <ul class="list-unstyled mb-0">
                                            <?php foreach($c['options'] as $opt): ?>
                                                <li>
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                        <?= htmlspecialchars($opt['LIBELLE']) ?>
                                                    </span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="../actions/critere/delete.php?project_id=<?= $projectId ?>&id=<?= $c['ID_CRITERE'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce critère ?');" class="btn btn-sm text-danger"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if(!empty($projectCriteria)): ?>
                        <div class="card-footer text-end border-0">
                            <button type="submit" id="save-ranking-btn" class="btn btn-success">
                                <i class="bi bi-check-circle me-2"></i>Valider les Modifications
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Fenêtre Modale (Pop-up) pour l'Ajout de Critère -->
<div class="modal fade" id="addCriterionModal" tabindex="-1" aria-labelledby="addCriterionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addCriterionModalLabel"><i class="bi bi-plus-circle me-2"></i> Ajouter un nouveau critère</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="../actions/critere/add.php" method="POST" id="criterionForm">
        <div class="modal-body">
            <input type="hidden" name="project_id" value="<?= $projectId ?>">
            
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">Intitulé</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted">Type</label>
                    <select name="type" id="typeSelect" class="form-select" onchange="toggleQualitativeOptions()">
                        <option value="quantitative">Chiffre (Quantitative)</option>
                        <option value="qualitative">Choix (Qualitative)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted">Objectif</label>
                    <select name="direction" class="form-select">
                        <option value="max">Maximiser</option>
                        <option value="min">Minimiser</option>
                    </select>
                </div>
            </div>

            <div id="qualitative-options-container" class="mb-3 p-3 bg-light border rounded" style="display: none;">
                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">Options Qualitatives</h6>
                <p class="small text-muted">Classez les options par ordre de préférence (la première est la meilleure). Utilisez <i class="bi bi-grip-vertical"></i> pour réorganiser.</p>
                <div id="options-inputs-list" class="mb-2">
                    <!-- Les options ajoutées dynamiquement apparaîtront ici -->
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="addOptionRow()">
                    <i class="bi bi-plus me-1"></i> Ajouter une ligne
                </button>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" name="add_criterion" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Nouveau container pour afficher la liste des critères (exemple) -->
<div class="col-12 mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold pt-3">
            <i class="bi bi-info-circle text-primary me-2"></i> Données Actuelles des Critères
        </div>
        <div class="card-body">
            <p class="text-muted small">Cette section affiche les données telles qu'elles sont actuellement enregistrées dans la base de données. Le classement ci-dessus doit être validé pour que les changements soient pris en compte ici.</p>
            
            <?php 
            if(empty($projectCriteria)): ?>
                <div class="text-center text-muted py-3">Aucune donnée de critère à afficher.</div>
            <?php 
            else: 
                // Calculer la somme des poids
                // On somme les poids modaux (m) pour avoir un aperçu
                $totalWeight = array_sum(array_column($projectCriteria, 'poids_m'));
            ?>
                <ul class="list-group list-group-flush">
                    <?php foreach($projectCriteria as $c): ?>
                        <li class="list-group-item px-1 py-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center" style="flex-basis: 50%;">
                                <span class="badge bg-dark rounded-pill me-3 px-2 py-1">Rang <?= $c['RANG'] ?></span>
                                <span class="fw-bold text-dark"><?= htmlspecialchars($c['NOM_CRITERE']) ?></span>
                            </div>
                            <div style="flex-basis: 30%;">
                                <?php if($c['TYPE_CRITERE']=='quantitative'): ?>
                                    <span class="badge bg-info bg-opacity-10 text-info me-1"><i class="bi bi-hash me-1"></i>Numérique</span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning me-1"><i class="bi bi-chat-dots me-1"></i>Qualitatif</span>
                                <?php endif; ?>
                                <?php if ($c['OBJECTIF'] === 'max'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-arrow-up-right me-1"></i>Maximiser</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger"><i class="bi bi-arrow-down-right me-1"></i>Minimiser</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-end" style="flex-basis: 20%;">
                                <small class="text-muted d-block">Poids</small>
                                <div class="fw-bold fs-6 text-dark" title="Lower, Middle, Upper bounds">(<?= number_format($c['poids_l'], 3) ?>, <?= number_format($c['poids_m'], 3) ?>, <?= number_format($c['poids_u'], 3) ?>)</div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <li class="list-group-item px-1 py-3 d-flex justify-content-end align-items-center bg-light mt-2">
                        <div class="text-end">
                            <small class="text-muted fw-bold">POIDS TOTAL</small>
                            <div class="fw-bolder fs-4 text-primary" title="La somme des poids devrait être proche de 1"><?= number_format($totalWeight, 3) ?></div>
                        </div>
                    </li>
                </ul>
                <?php
                // Afficher un avertissement si la méthode est manuelle et que la somme des poids n'est pas 1
                if (($project['methode_poids'] ?? 'auto') === 'manual' && abs($totalWeight - 1.0) > 0.0001): // On utilise une tolérance pour les erreurs de flottants
                ?>
                    <div class="alert alert-warning mt-3 d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><strong>Attention :</strong> La somme des poids des critères n'est pas égale à 1. La cohérence des résultats n'est pas garantie.</div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Gestion du classement des options qualitatives ---
    const optionsList = document.getElementById('options-inputs-list');
    new Sortable(optionsList, {
        animation: 150,
        handle: '.drag-handle',
        onEnd: updateRanks
    });
    toggleQualitativeOptions();

    // --- Gestion du classement des critères ---
    const criteriaListBody = document.getElementById('criteria-list-body');
    const saveRankingBtn = document.getElementById('save-ranking-btn');
    if (criteriaListBody) {
        // Initialiser l'état de l'interface au chargement
        // pour afficher/masquer les champs de poids manuels
        // en fonction de l'option cochée par défaut.
        toggleWeightInputs();

        // Activer le glisser-déposer
        new Sortable(criteriaListBody, {
            animation: 150,
            handle: '.drag-handle',
            onEnd: updateCriteriaRanks
        });
    }
});

function toggleQualitativeOptions() {
    const typeSelect = document.getElementById('typeSelect');
    const container = document.getElementById('qualitative-options-container');
    const isQualitative = typeSelect.value === 'qualitative';

    container.style.display = isQualitative ? 'block' : 'none';
    const inputs = container.querySelectorAll('input[type="text"]');
    inputs.forEach(input => input.required = isQualitative);
}

function addOptionRow() {
    const list = document.getElementById('options-inputs-list');
    const newRow = document.createElement('div');
    newRow.className = 'input-group mb-2';
    newRow.innerHTML = `
        <span class="input-group-text drag-handle" style="cursor: move;"><i class="bi bi-grip-vertical"></i></span>
        <span class="input-group-text rank-number fw-bold" style="width: 50px; text-align: center; justify-content: center;"></span>
        <input type="text" name="opt_labels[]" class="form-control" placeholder="Label" required>
        <input type="hidden" name="opt_scores[]">
        <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)"><i class="bi bi-x"></i></button>
    `;
    list.appendChild(newRow);
    updateRanks();
}

function updateRanks() {
    const rows = document.querySelectorAll('#options-inputs-list .input-group');
    rows.forEach((row, index) => {
        const rank = index + 1;
        row.querySelector('.rank-number').textContent = rank;
        row.querySelector('input[name="opt_scores[]"]').value = rank;
    });
}

function removeOption(button) {
    button.parentElement.remove();
    updateRanks();
}

function updateCriteriaRanks() {
    const rows = document.querySelectorAll('#criteria-list-body tr');
    rows.forEach((row, index) => {
        const rankDisplay = row.querySelector('.rank-display');
        if (rankDisplay) {
            rankDisplay.textContent = index + 1;
        }
    });
}

function toggleWeightInputs() {
    const isManual = document.getElementById('manual_weights').checked;
    const manualCols = document.querySelectorAll('.manual-weight-col');
    const dragHandles = document.querySelectorAll('.drag-handle');

    manualCols.forEach(col => {
        col.style.display = isManual ? '' : 'none';
        col.querySelectorAll('input').forEach(input => input.required = isManual);
    });

    // Le glisser-déposer reste actif dans les deux modes pour permettre de réorganiser l'affichage.
    dragHandles.forEach(handle => {
        handle.style.cursor = 'move';
    });
}

</script>

<?php include 'footer.php'; ?>