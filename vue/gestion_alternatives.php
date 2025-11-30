<?php
$pageTitle = "Gestion des Alternatives";
require_once '../model/function.php';

// Valider que l'ID du projet est bien passé en paramètre
$projectId = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);
if (!$projectId) {
    echo "<div class='alert alert-danger'>ID de projet non valide.</div>";
    include 'footer.php';
    exit;
}

// Récupérer les détails du projet pour l'affichage
$project = getProjectDetails($projectId);
if (!$project) {
    echo "<div class='alert alert-danger'>Projet non trouvé.</div>";
    include 'footer.php';
    exit;
}

// Récupérer les données nécessaires pour la page
$criteria = getCriteriaForProject($projectId);
$alternatives = getAlternativesForProject($projectId);
$evaluations = getEvaluationsForProject($projectId); // Récupérer les évaluations

include 'header.php';
?>
<div class="col-12">
    <!-- Navigation d'étape -->
    <div class="d-flex justify-content-center mb-5">
        <div class="btn-group shadow-sm" role="group">
            <a href="gestion_critere.php?project_id=<?= $projectId ?>" class="btn btn-outline-primary"><i class="bi bi-card-checklist me-2"></i> Critères</a>
            <button class="btn btn-primary disabled"><i class="bi bi-people me-2"></i> Cibles</button>
            <a href="resultats.php?project_id=<?= $projectId ?>" class="btn btn-outline-primary"><i class="bi bi-graph-up me-2"></i> Résultats</a>
        </div>
    </div>
    <div class="row">
        <!-- Formulaire d'ajout -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                </div>
                <div class="card-header bg-white fw-bold py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-person-plus-fill text-primary me-2"></i> Ajouter un cible
                    </span>
                    <!-- Bouton d'import Excel -->
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                        <i class="bi bi-file-earmark-excel me-1"></i> Importer depuis Excel
                    </button>
                </div>
                <div class="card-body p-4">
                    <form action="../actions/alternative/add.php" method="POST" class="row g-3">
                        <input type="hidden" name="project_id" value="<?= $projectId ?>">
                        <input type="hidden" name="alternative_id" id="alternative_id" value="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Nom de la Personne</label>
                                <input type="text" name="personne_name" class="form-control" placeholder="Ex: Jean Dupont" required>
                            </div>
                            <div class="col-12 my-2"><hr class="text-muted opacity-25"></div>
                            
                            <div class="col-12 mb-2"><h6 class="text-primary"><i class="bi bi-clipboard-check me-2"></i>Évaluation des Critères (Selon l'ordre de priorité défini)</h6></div>

                            <?php if(empty($criteria)): ?>
                                <div class="alert alert-warning col-12">Veuillez d'abord définir des critères pour ce projet.</div>
                            <?php endif; ?>

                            <?php foreach($criteria as $c): ?>
                            <div class="col-md-3 mb-3">
                                <label class="form-label small fw-bold"><?= htmlspecialchars($c['NOM_CRITERE']) ?></label>
                                <?php if($c['TYPE_CRITERE'] === 'quantitative'): ?>
                                    <input type="number" step="0.01" name="evaluations[<?= $c['ID_CRITERE'] ?>]" class="form-control" required>
                                <?php else: ?>
                                    <select name="evaluations[<?= $c['ID_CRITERE'] ?>]" class="form-select" required>
                                        <option value="" disabled selected>Choisir...</option>
                                        <?php $options = getQualitativeValuesForCriterion($c['ID_CRITERE']); ?>
                                        <?php if(!empty($options)): foreach($options as $opt): ?>
                                            <option value="<?= htmlspecialchars($opt['LIBELLE']) ?>"><?= htmlspecialchars($opt['LIBELLE']) ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>

                            <div class="col-12 text-end">
                                <button type="submit" name="add_candidate" id="submit-btn" class="btn btn-primary rounded-pill px-5" <?= empty($criteria) ? 'disabled' : '' ?>>Enregistrer</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Liste des Personnes -->
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="bi bi-people-fill text-primary me-2"></i> Liste des cibles (<?= count($alternatives) ?>)
                <div class="card-body p-0">
                    <?php if (empty($alternatives)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-person-x fs-1"></i>
                            <h5 class="mt-3">Aucun cible enregistré.</h5>
                            <p>Utilisez le formulaire ci-dessus pour commencer.</p>
                        </div>
                    <?php else: ?>
                        <table id="beneficiairesTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">cible</th>
                                    <?php foreach ($criteria as $criterion): ?>
                                        <th class="text-center"><?= htmlspecialchars($criterion['NOM_CRITERE']) ?></th>
                                    <?php endforeach; ?>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alternatives as $alternative): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= htmlspecialchars($alternative['NOM_PERSONNE']) ?></td>
                                        <?php foreach ($criteria as $criterion): ?>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border">
                                                    <?= htmlspecialchars($evaluations[$alternative['ID_PERSONNE']][$criterion['ID_CRITERE']]['display'] ?? 'N/A') ?>
                                                </span>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="text-end pe-4">
                                            <div class="btn-group" role="group" aria-label="Actions pour la cible">
                                                <button type="button" class="btn btn-sm btn-outline-primary edit-btn"
                                                        data-id="<?= $alternative['ID_PERSONNE'] ?>"
                                                        data-name="<?= htmlspecialchars($alternative['NOM_PERSONNE']) ?>"
                                                        data-evaluations='<?= json_encode($evaluations[$alternative['ID_PERSONNE']] ?? []) ?>'
                                                        title="Modifier cette cible">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <a href="../actions/alternative/delete.php?project_id=<?= $projectId ?>&id=<?= $alternative['ID_PERSONNE'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette cible et toutes ses évaluations ?')" title="Supprimer cette cible">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fenêtre Modale pour l'Import Excel -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importExcelModalLabel"><i class="bi bi-file-earmark-excel-fill me-2 text-success"></i> Importer des cibles</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="../actions/alternative/import.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
            <input type="hidden" name="project_id" value="<?= $projectId ?>">
            <div class="alert alert-info small">
                <h6 class="alert-heading fw-bold">Format du fichier Excel</h6>
                <p>Assurez-vous que votre fichier respecte le format suivant :</p>
                <ul>
                    <li>La première ligne doit contenir les en-têtes.</li>
                    <li>La première colonne (A) doit être le **Nom du cible**.</li>
                    <li>Les colonnes suivantes doivent correspondre **exactement et dans l'ordre** à vos critères :
                        <ol class="mt-2">
                            <?php foreach($criteria as $c): ?>
                                <li><?= htmlspecialchars($c['NOM_CRITERE']) ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </li>
                </ul>
            </div>
            <label for="excel_file" class="form-label">Fichier Excel (.xlsx, .xls, .csv)</label>
            <input class="form-control" type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Lancer l'Importation</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-btn');
    const form = document.querySelector('form[action="../actions/alternative/add.php"]');
    const alternativeIdInput = document.getElementById('alternative_id');
    const nameInput = document.querySelector('input[name="personne_name"]');
    const submitBtn = document.getElementById('submit-btn');
    const originalBtnText = submitBtn.textContent;

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const evaluations = JSON.parse(this.dataset.evaluations);

            // Remplir le formulaire
            alternativeIdInput.value = id;
            nameInput.value = name;

            for (const critId in evaluations) {
                const input = form.querySelector(`[name="evaluations[${critId}]"]`);
                if (input) {
                    input.value = evaluations[critId].raw ?? '';
                }
            }

            // Changer le bouton et scroller en haut
            submitBtn.textContent = 'Modifier';
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-warning');
            window.scrollTo({ top: form.offsetTop - 20, behavior: 'smooth' });
        });
    });
});
</script>
<script>
$(document).ready(function() {
    $('#beneficiairesTable').DataTable({
        language: {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
        },
        "columnDefs": [
            { "orderable": false, "targets": -1 } // Désactiver le tri sur la dernière colonne (Actions)
        ]
    });
});
</script>