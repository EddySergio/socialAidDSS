<?php

/**
 * Calcule et sauvegarde les poids flous selon la méthode
 * Fuzzy Rank Reciprocal (FRR) - Logique Wang & Elhag (Même logique que le Python).
 *
 * @param PDO $pdo
 * @param int $projectId
 * @return bool
 */
function calculateAndSaveFuzzyWeights(PDO $pdo, int $projectId): bool {
    
    // 1. Récupérer les critères triés par rang
    $stmt = $pdo->prepare("SELECT ID_CRITERE, RANG FROM critere WHERE ID_PROJET = ? ORDER BY RANG ASC");
    $stmt->execute([$projectId]);
    $criteria = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $n = count($criteria);
    if ($n === 0) return true;

    // -----------------------------------------------------------
    // 2. Calcul des Poids Individuels Flous (r_k)
    //    r_kl = 1/(k+0.5), r_km = 1/k, r_ku = 1/(k-0.5)
    // -----------------------------------------------------------
    $fuzzyIndividual = [];
    
    // On initialise les sommes pour l'étape suivante
    $sum_r_l = 0.0;
    $sum_r_m = 0.0;
    $sum_r_u = 0.0;

    foreach ($criteria as $c) {
        $k = floatval($c['RANG']); // Rang crisp
        $id = $c['ID_CRITERE'];

        // Calcul des composantes r (inverse du rang flou)
        // Note: Si k=1, k-0.5 = 0.5. Pas de division par zéro car k >= 1.
        $r_l = 1 / ($k + 0.5);
        $r_m = 1 / $k;
        $r_u = 1 / ($k - 0.5);

        $fuzzyIndividual[$id] = [
            'l' => $r_l,
            'm' => $r_m,
            'u' => $r_u
        ];

        // Accumulation des sommes globales
        $sum_r_l += $r_l;
        $sum_r_m += $r_m;
        $sum_r_u += $r_u;
    }

    // -----------------------------------------------------------
    // 3. Normalisation Floue (Formule Wang et Elhag adaptée)
    //    C'est ici que la logique diffère de l'arithmétique floue classique.
    // -----------------------------------------------------------
    $fuzzyWeights = [];

    foreach ($criteria as $c) {
        $id = $c['ID_CRITERE'];
        $r = $fuzzyIndividual[$id]; // r_kl, r_km, r_ku du critère courant

        // A. Calcul de w_m (Modal)
        // w_km = r_km / Σ r_jm
        $w_m = $r['m'] / $sum_r_m;

        // B. Calcul de w_l (Minimum)
        // Somme des u de tous les *autres* critères
        $sum_others_u = $sum_r_u - $r['u'];
        // w_kl = r_kl / (r_kl + Σ_{j!=k} r_ju)
        $w_l = $r['l'] / ($r['l'] + $sum_others_u);

        // C. Calcul de w_u (Maximum)
        // Somme des l de tous les *autres* critères
        $sum_others_l = $sum_r_l - $r['l'];
        // w_ku = r_ku / (r_ku + Σ_{j!=k} r_jl)
        $w_u = $r['u'] / ($r['u'] + $sum_others_l);

        $fuzzyWeights[$id] = [
            'l' => $w_l,
            'm' => $w_m,
            'u' => $w_u
        ];
    }

    // -----------------------------------------------------------
    // 4. Sauvegarde en base
    // -----------------------------------------------------------
    try {
        $stmtUpdate = $pdo->prepare("
            UPDATE critere SET poids_l=?, poids_m=?, poids_u=? 
            WHERE ID_CRITERE=?
        ");

        foreach ($criteria as $c) {
            $id = $c['ID_CRITERE'];
            $w = $fuzzyWeights[$id];

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