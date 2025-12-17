<?php

/**
 * Calcule et sauvegarde les poids flous selon la méthode
 * Fuzzy Rank Reciprocal (FRR) – Logique Wang & Elhag
 * AVEC normalisation floue finale : ΣW = (1,1,1)
 *
 * @param PDO $pdo
 * @param int $projectId
 * @return bool
 */
function calculateAndSaveFuzzyWeights(PDO $pdo, int $projectId): bool {

    // -----------------------------------------------------------
    // 1. Récupération des critères ordonnés par rang
    // -----------------------------------------------------------
    $stmt = $pdo->prepare("
        SELECT ID_CRITERE, RANG 
        FROM critere 
        WHERE ID_PROJET = ? 
        ORDER BY RANG ASC
    ");
    $stmt->execute([$projectId]);
    $criteria = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $n = count($criteria);
    if ($n === 0) {
        return true;
    }

    // -----------------------------------------------------------
    // 2. Calcul des poids individuels flous (r_k)
    //    r_l = 1/(k+0.5), r_m = 1/k, r_u = 1/(k-0.5)
    // -----------------------------------------------------------
    $fuzzyIndividual = [];

    $sum_r_l = 0.0;
    $sum_r_m = 0.0;
    $sum_r_u = 0.0;

    foreach ($criteria as $c) {
        $k  = floatval($c['RANG']);
        $id = $c['ID_CRITERE'];

        if ($k < 1) {
            throw new Exception("Le rang doit être >= 1");
        }

        $r_l = 1 / ($k + 0.5);
        $r_m = 1 / $k;
        $r_u = 1 / ($k - 0.5); // valide pour k >= 1

        $fuzzyIndividual[$id] = [
            'l' => $r_l,
            'm' => $r_m,
            'u' => $r_u
        ];

        $sum_r_l += $r_l;
        $sum_r_m += $r_m;
        $sum_r_u += $r_u;
    }

    // -----------------------------------------------------------
    // 3. Normalisation floue Wang & Elhag
    // -----------------------------------------------------------
    $fuzzyWeights = [];

    foreach ($criteria as $c) {
        $id = $c['ID_CRITERE'];
        $r  = $fuzzyIndividual[$id];

        // Modal
        $w_m = $r['m'] / $sum_r_m;

        // Minimum
        $sum_others_u = $sum_r_u - $r['u'];
        $w_l = $r['l'] / ($r['l'] + $sum_others_u);

        // Maximum
        $sum_others_l = $sum_r_l - $r['l'];
        $w_u = $r['u'] / ($r['u'] + $sum_others_l);

        $fuzzyWeights[$id] = [
            'l' => $w_l,
            'm' => $w_m,
            'u' => $w_u
        ];
    }

    // -----------------------------------------------------------
    // 4. NORMALISATION FLOUE FINALE
    //    Garantit : Σ l = Σ m = Σ u = 1
    // -----------------------------------------------------------
    $sum_w_l = 0.0;
    $sum_w_m = 0.0;
    $sum_w_u = 0.0;

    foreach ($fuzzyWeights as $w) {
        $sum_w_l += $w['l'];
        $sum_w_m += $w['m'];
        $sum_w_u += $w['u'];
    }

    foreach ($fuzzyWeights as $id => $w) {
        $fuzzyWeights[$id]['l'] = $w['l'] / $sum_w_l;
        $fuzzyWeights[$id]['m'] = $w['m'] / $sum_w_m;
        $fuzzyWeights[$id]['u'] = $w['u'] / $sum_w_u;
    }

    // -----------------------------------------------------------
    // 5. Sauvegarde en base de données
    // -----------------------------------------------------------
    try {
        $stmtUpdate = $pdo->prepare("
            UPDATE critere 
            SET poids_l = ?, poids_m = ?, poids_u = ?
            WHERE ID_CRITERE = ?
        ");

        foreach ($criteria as $c) {
            $id = $c['ID_CRITERE'];
            $w  = $fuzzyWeights[$id];

            $stmtUpdate->execute([
                $w['l'],
                $w['m'],
                $w['u'],
                $id
            ]);
        }

        return true;

    } catch (Exception $e) {
        error_log("Erreur calcul poids FRR : " . $e->getMessage());
        return false;
    }
}
?>
