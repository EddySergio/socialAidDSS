<?php
$pageTitle = "Résultats du Classement";
require_once '../model/function.php';

// =========================================================================
// DÉBUT DE LA LOGIQUE DE TRAITEMENT (AVANT TOUT HTML)
// =========================================================================
// Récupération safe de project_id
$projectId = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);
if (!$projectId) {
    echo "<div class='alert alert-danger'>ID de projet non valide.</div>";
    include 'footer.php';
    exit;
}

$project = getProjectDetails($projectId);

// Helper local si formatTFN n'est pas défini ailleurs
if (!function_exists('formatTFN')) {
    function formatTFN(array $tfn): string {
        if (!is_array($tfn) || count($tfn) < 3) return "(0.00, 0.00, 0.00)";
        return sprintf("(%.2f, %.2f, %.2f)", $tfn[0], $tfn[1], $tfn[2]);
    }
}

// On récupère les alternatives AVANT de lancer le calcul.
$alternatives = getAlternativesForProject($projectId);

if (empty($alternatives)) {
    // S'il n'y a pas d'alternatives, on crée un message d'erreur personnalisé et on ne calcule rien.
    $codasResult = ['error' => "Veuillez ajouter au moins une cible pour pouvoir afficher les résultats."];
} else {
    // S'il y a des alternatives, on lance le calcul Fuzzy CODAS.
    $codasResult = calculateFuzzyCODAS($projectId);
}

// Si le calcul renvoie une erreur, on peut l'afficher plus tard.
$finalRanking = [];
if (!isset($codasResult['error'])) {
    $finalRanking = $codasResult['final_ranking'] ?? $codasResult['ranking'] ?? [];
    $steps = $codasResult['matrices'] ?? [];
    // On récupère les méta-données (critères, alternatives) pour l'affichage des tableaux
    $criteria = getCriteriaForProject($projectId);
}

/**
 * EXPORT CSV (Excel)
 */
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    // Nom fichier
    $filename = "ranking_project_{$projectId}_" . date('Ymd_His') . ".csv";

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $out = fopen('php://output', 'w');

    // En-têtes
    fputcsv($out, ['Rang', 'cible', 'Score']);

    // Utilise $finalRanking — accepte deux formats (associatif ou indexé)
    foreach ($finalRanking as $row) {
        // Si $finalRanking était indexé par id, $row peut contenir 'rank' etc.
        $rank = $row['rank'] ?? '';
        $name = $row['name'] ?? ($row['NOM_PERSONNE'] ?? '');
        $score = $row['score'] ?? ($row['fuzzy_score'] ?? '');
        // Si score est TFN, convertir en COG approximative
        if (is_array($score)) {
            $score = number_format((($score[0] ?? 0) + ($score[1] ?? 0) + ($score[2] ?? 0)) / 3, 6);
        }
        fputcsv($out, [$rank, $name, $score]);
    }

    fclose($out);
    exit;
}

/**
 * EXPORT PDF (via Dompdf si présent)
 */
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    // Vérifier si Dompdf est disponible
    if (!class_exists('\Dompdf\Dompdf')) {
        // Message d'erreur instructif (on ne peut pas créer le pdf sans la lib)
        echo "<div class='container py-5'>";
        echo "<div class='alert alert-warning'>La génération PDF nécessite la bibliothèque <strong>Dompdf</strong> (composer require dompdf/dompdf).";
        echo " Téléchargez et installez Dompdf, puis réessayez. En attendant, vous pouvez exporter en <a href='?project_id={$projectId}&export=csv' class='fw-bold'>CSV (Excel)</a>.</div>";
        echo "<a class='btn btn-secondary' href='?project_id={$projectId}'>&larr; Retour</a>";
        echo "</div>";
        include 'footer.php';
        exit;
    }

    // Construire le HTML du tableau (simple et propre pour la conversion)
    ob_start();
    ?>
    <html>
    <head>
        <meta charset="utf-8"/>
        <style>
            body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
            th { background: #f2f2f2; }
            .title { text-align:center; margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <div class="title">
            <h2>Résultats du Classement (Fuzzy CODAS)</h2>
            <p>Projet : <strong><?= htmlspecialchars($project['NOM_PROJET']) ?></strong></p>
            <p>Exporté le <?= date('Y-m-d H:i:s') ?></p>
        </div>

        <table>
            <thead>
                <tr><th>Rang</th><th>cible</th><th>Score (COG)</th></tr>
            </thead>
            <tbody>
                <?php foreach ($finalRanking as $row): 
                    $rank = $row['rank'] ?? '';
                    $name = $row['name'] ?? ($row['NOM_PERSONNE'] ?? '');
                    $score = $row['score'] ?? null;
                    if (is_array($score)) {
                        $score = number_format((($score[0] ?? 0) + ($score[1] ?? 0) + ($score[2] ?? 0)) / 3, 6);
                    } elseif (is_numeric($score)) {
                        $score = number_format($score, 6);
                    } else {
                        $score = '';
                    }
                ?>
                    <tr>
                        <td><?= htmlspecialchars($rank) ?></td>
                        <td><?= htmlspecialchars($name) ?></td>
                        <td><?= htmlspecialchars($score) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    $html = ob_get_clean();

    // Générer le PDF via Dompdf
    // Assure-toi d'avoir fait : composer require dompdf/dompdf
    require_once __DIR__ . '/../vendor/autoload.php';
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->loadHtml($html);
    $dompdf->render();

    $pdfOutput = $dompdf->output();
    $filename = "ranking_project_{$projectId}_" . date('Ymd_His') . ".pdf";

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo $pdfOutput;
    exit;
}

// =========================================================================
// FIN DE LA LOGIQUE DE TRAITEMENT. DÉBUT DE L'AFFICHAGE HTML.
// =========================================================================
include 'header.php';

// Affichage de l'erreur de calcul si elle existe
if (isset($codasResult['error'])) {
    // Cas spécifique où il n'y a pas de cibles
    if (empty($alternatives)) {
        echo '<div class="text-center py-5 text-muted bg-light rounded shadow-sm">';
        echo '    <i class="bi bi-people-fill fs-1"></i>';
        echo '    <h5 class="mt-3">Aucune cible à évaluer.</h5>';
        echo '    <p>Pour obtenir des résultats, vous devez d\'abord ajouter des cibles à votre projet.</p>';
        echo '    <a href="gestion_alternatives.php?project_id=' . $projectId . '" class="btn btn-primary mt-2">';
        echo '        <i class="bi bi-plus-circle me-2"></i>Ajouter des cibles';
        echo '    </a>';
        echo '</div>';
    } else {
        echo "<div class='alert alert-danger'>Erreur de calcul : " . htmlspecialchars($codasResult['error']) . "</div>";
    }
    include 'footer.php'; // Inclure le pied de page pour une structure HTML correcte
    exit; // Arrêter le script ici car il n'y a rien d'autre à afficher
}
?>

<div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 fw-bold">Résultats du Classement (Fuzzy CODAS)</h2>
            <p class="text-muted">Projet : <strong class="text-primary"><?= htmlspecialchars($project['NOM_PROJET']) ?></strong></p>
        </div>

        <!-- Boutons d'export -->
        <div class="d-flex gap-2">
            <a href="?project_id=<?= $projectId ?>&export=csv" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel me-2"></i> Exporter CSV (Excel)
            </a>
            <a href="?project_id=<?= $projectId ?>&export=pdf" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf me-2"></i> Exporter PDF
            </a>
            <a href="gestion_alternatives.php?project_id=<?= $projectId ?>" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill me-2" viewBox="0 0 16 16"><path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0zm3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"/></svg>
                Retour à l'évaluation
            </a>
        </div>
    </div>

    <!-- Navigation d'étape -->
    <div class="d-flex justify-content-center mb-5">
        <div class="btn-group shadow-sm" role="group">
            <a href="gestion_critere.php?project_id=<?= $projectId ?>" class="btn btn-outline-primary"><i class="bi bi-card-checklist me-2"></i> Critères</a>
            <a href="gestion_alternatives.php?project_id=<?= $projectId ?>" class="btn btn-outline-primary"><i class="bi bi-people me-2"></i> Cibles</a>
            <button class="btn btn-primary disabled"><i class="bi bi-graph-up me-2"></i> Résultats</button>
        </div>
    </div>

    <!-- (le reste de ton HTML existant: accordéon, tableaux, classement) -->
    <!-- J'utilise les mêmes variables $steps et $meta que tu avais. -->
    <!-- Étape 1: Matrice de Décision Floue -->
    <div class="accordion mb-5" id="calculationStepsAccordion">
        <!-- ... (garde le HTML que tu avais) ... -->
        <!-- Pour éviter de répéter tout le code long, je réinsère ici exactement les sections principales -->
        <!-- Étape 1 -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                    <strong>Étape 1 :</strong>&nbsp;Matrice de Décision Floue
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#calculationStepsAccordion">
                <div class="accordion-body table-responsive">
                    <p class="small text-muted">Cette matrice montre les performances de chaque cible pour chaque critère, représentées par des Nombres Flous Triangulaires (l, m, u).</p>
                    <table class="table table-bordered table-sm text-center">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">cible</th>
                                <?php foreach ($criteria as $crit): ?>
                                    <th><?= htmlspecialchars($crit['NOM_CRITERE']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alternatives as $alt): ?>
                                <tr>
                                    <th class="text-start"><?= htmlspecialchars($alt['NOM_PERSONNE']) ?></th>
                                    <?php foreach ($criteria as $crit): ?>
                                        <td><?= formatTFN($steps['X'][$alt['ID_PERSONNE']][$crit['ID_CRITERE']] ?? [0,0,0]) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Étape 2 -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    <strong>Étape 2 :</strong>&nbsp;Matrice de Décision Normalisée
                </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#calculationStepsAccordion">
                <div class="accordion-body table-responsive">
                    <p class="small text-muted">La matrice normalisée.</p>
                    <table class="table table-bordered table-sm text-center">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">cible</th>
                                <?php foreach ($criteria as $crit): ?>
                                    <th><?= htmlspecialchars($crit['NOM_CRITERE']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alternatives as $alt): ?>
                                <tr>
                                    <th class="text-start"><?= htmlspecialchars($alt['NOM_PERSONNE']) ?></th>
                                    <?php foreach ($criteria as $crit): ?>
                                        <td><?= formatTFN($steps['N'][$alt['ID_PERSONNE']][$crit['ID_CRITERE']] ?? [0,0,0]) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Étape 3 -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    <strong>Étape 3 :</strong>&nbsp;Matrice Normalisée et Pondérée
                </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#calculationStepsAccordion">
                <div class="accordion-body table-responsive">
                    <p class="small text-muted">Matrice normalisée et pondérée.</p>
                    <table class="table table-bordered table-sm text-center">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">cible</th>
                                <?php foreach ($criteria as $crit): ?>
                                    <th><?= htmlspecialchars($crit['NOM_CRITERE']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alternatives as $alt): ?>
                                <tr>
                                    <th class="text-start"><?= htmlspecialchars($alt['NOM_PERSONNE']) ?></th>
                                    <?php foreach ($criteria as $crit): ?>
                                        <td><?= formatTFN($steps['V'][$alt['ID_PERSONNE']][$crit['ID_CRITERE']] ?? [0,0,0]) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Étape 4: NI -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingFour">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                    <strong>Étape 4 :</strong>&nbsp;Solution Négative-Idéale (NI)
                </button>
            </h2>
            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#calculationStepsAccordion">
                <div class="accordion-body table-responsive">
                    <p class="small text-muted">Solution Négative-Idéale.</p>
                    <table class="table table-bordered table-sm text-center">
                        <thead class="table-light">
                            <tr>
                                <?php foreach ($criteria as $crit): ?>
                                    <th><?= htmlspecialchars($crit['NOM_CRITERE']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="align-middle">
                                <?php foreach ($criteria as $crit): ?>
                                    <td><?= formatTFN($steps['NI'][$crit['ID_CRITERE']] ?? [0,0,0]) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Étape 5: Distances -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingFive">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                    <strong>Étape 5 :</strong>&nbsp;Distances Euclidienne (dE) et Taxicab (dT)
                </button>
            </h2>
            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#calculationStepsAccordion">
                <div class="accordion-body table-responsive">
                    <p class="small text-muted">Distances.</p>
                    <table class="table table-bordered table-sm text-center">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">cible</th>
                                <th>Distance Euclidienne (dE)</th>
                                <th>Distance Taxicab (dT)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alternatives as $alt): ?>
                                <tr>
                                    <th class="text-start"><?= htmlspecialchars($alt['NOM_PERSONNE']) ?></th>
                                    <td><?= number_format($steps['dE'][$alt['ID_PERSONNE']] ?? 0, 6) ?></td>
                                    <td><?= number_format($steps['dT'][$alt['ID_PERSONNE']] ?? 0, 6) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Étape 6: Matrice H -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingSix">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                    <strong>Étape 6 :</strong>&nbsp;Matrice d'Évaluation Relative (RA)
                </button>
            </h2>
            <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#calculationStepsAccordion">
                <div class="accordion-body table-responsive">
                    <p class="small text-muted">Matrice d'évaluation relative (RA). Les valeurs sont calculées pour chaque paire de cibles (i, k).</p>
                    <table class="table table-bordered table-sm text-center">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">↓ vs →</th>
                                <?php foreach ($alternatives as $alt): ?>
                                    <th><?= htmlspecialchars($alt['NOM_PERSONNE']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alternatives as $alt_i): ?>
                                <tr>
                                    <th class="text-start"><?= htmlspecialchars($alt_i['NOM_PERSONNE']) ?></th>
                                    <?php foreach ($alternatives as $alt_k): ?>
                                        <?php
                                        $id_i = $alt_i['ID_PERSONNE'];
                                        $id_k = $alt_k['ID_PERSONNE'];
                                        $p_ik = $steps['P_Relative_Evaluation_Debug'][$id_i][$id_k]['p_ik'] ?? 0;
                                        ?>
                                        <td><?= ($id_i == $id_k) ? '—' : number_format($p_ik, 6) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Classement final -->
    <div class="card shadow-lg border-0 overflow-hidden">
        <div class="card-header bg-success text-white fw-bold">
            <i class="bi bi-trophy-fill me-2"></i>Classement Final des cibles
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="py-3 ps-4">Rang</th>
                        <th>Denomination</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($finalRanking)): ?>
                        <tr>
                            <td colspan="4" class="text-center p-5 text-muted">Pas assez de données pour le calcul. Veuillez ajouter des critères et des personnes, puis les évaluer.</td>
                        </tr>
                    <?php else: foreach ($finalRanking as $data): ?>
                        <tr class="<?= ($data['rank'] ?? 999) <= 3 ? 'table-success bg-opacity-10' : '' ?>">
                            <td class="ps-4 fw-bold fs-5">#<?= htmlspecialchars($data['rank'] ?? '') ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($data['name'] ?? '') ?></td>
                            <td><?= number_format($data['score'] ?? 0, 6) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
