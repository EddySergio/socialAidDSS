<?php

/**
 * Calcule et sauvegarde les poids flous selon la méthode
 * Fuzzy Reciprocal Weights (FRR) de Roszkowska (2020).
 *
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

    // -------------------------------
    // 2. Construire les rangs flous k̃ = (k-0.5, k, k+0.5)
    // -------------------------------
    $fuzzyRanks = [];
    foreach ($criteria as $c) {
        $k = floatval($c['RANG']);
        $fuzzyRanks[$c['ID_CRITERE']] = [
            'l' => max(0.00001, $k - 0.5),
            'm' => $k,
            'u' => $k + 0.5
        ];
    }

    // -------------------------------
    // 3. Calcul des inverses flous 1/k̃
    // -------------------------------
    $fuzzyRecip = [];
    foreach ($fuzzyRanks as $id => $r) {
        $fuzzyRecip[$id] = [
            'l' => 1 / $r['u'],  // 1/(k+0.5)
            'm' => 1 / $r['m'],  // 1/k
            'u' => 1 / $r['l']   // 1/(k-0.5)
        ];
    }

    // -------------------------------
    // 4. Somme floue Σ 1/k̃ (composante par composante)
    // -------------------------------
    $sum = ['l' => 0, 'm' => 0, 'u' => 0];
    foreach ($fuzzyRecip as $r) {
        $sum['l'] += $r['l'];
        $sum['m'] += $r['m'];
        $sum['u'] += $r['u'];
    }

    // -------------------------------
    // 5. Normalisation floue (division composante par composante)
    //    w_k = r_k / Σ r_j
    // -------------------------------
    $fuzzyWeights = [];
    foreach ($criteria as $c) {
        $id = $c['ID_CRITERE'];
        $rk = $fuzzyRecip[$id];

        $fuzzyWeights[$id] = [
            'l' => $rk['l'] / $sum['u'],  // min / max
            'm' => $rk['m'] / $sum['m'],  // centre / centre
            'u' => $rk['u'] / $sum['l']   // max / min
        ];
    }

    // -------------------------------
    // 6. Sauvegarde en base
    // -------------------------------
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
        error_log("Erreur FRR Roszkowska : " . $e->getMessage());
        return false;
    }
}
