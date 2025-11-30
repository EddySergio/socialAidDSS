<?php

/**
 * Triangular Fuzzy CODAS complet :
 * - distances floues
 * - comparaison floue
 * - défuzzification finale par COG
 */
function calculateFuzzyCODAS(int $projectId): array {

    $criteria = getCriteriaForProject($projectId);
    $alternatives = getAlternativesForProject($projectId);
    $performances = getEvaluationsForProject($projectId);

    if (empty($criteria) || empty($alternatives) || empty($performances)) {
        return ['error' => "Données manquantes."];
    }

    //---------------------------------------------------------
    // 1. Construction de la matrice décision floue
    //---------------------------------------------------------
    $X = [];
    foreach ($alternatives as $alt) {
        foreach ($criteria as $crit) {
            $X[$alt['ID_PERSONNE']][$crit['ID_CRITERE']]
                = $performances[$alt['ID_PERSONNE']][$crit['ID_CRITERE']]['tfn'];
        }
    }

    //---------------------------------------------------------
    // 2. Normalisation floue
    //---------------------------------------------------------
    $N = [];
    foreach ($criteria as $crit) {
        $j = $crit['ID_CRITERE'];
        $type = $crit['OBJECTIF'];

        $max_u = 0;
        $min_l = PHP_FLOAT_MAX;

        foreach ($alternatives as $a) {
            $t = $X[$a['ID_PERSONNE']][$j];
            $max_u = max($max_u, $t[2]);
            $min_l = min($min_l, $t[0]);
        }

        foreach ($alternatives as $a) {
            $id = $a['ID_PERSONNE'];
            $t = $X[$id][$j];

            // Sécurité pour éviter la division par zéro
            $safe_max_u = ($max_u == 0) ? 1.0e-9 : $max_u;
            $safe_t0 = ($t[0] == 0) ? 1.0e-9 : $t[0];
            $safe_t1 = ($t[1] == 0) ? 1.0e-9 : $t[1];
            $safe_t2 = ($t[2] == 0) ? 1.0e-9 : $t[2];

            if ($type === 'max') {
                $N[$id][$j] = [
                    $t[0] / $safe_max_u,
                    $t[1] / $safe_max_u,
                    $t[2] / $safe_max_u
                ];
            } else {
                $N[$id][$j] = [
                    $min_l / $safe_t2,
                    $min_l / $safe_t1,
                    $min_l / $safe_t0
                ];
            }
        }
    }

    //---------------------------------------------------------
    // 3. Pondération floue : TFN * TFN
    //---------------------------------------------------------
    $W = [];
    foreach ($criteria as $crit) {
        $W[$crit['ID_CRITERE']] = [
            $crit['poids_l'],
            $crit['poids_m'],
            $crit['poids_u']
        ];
    }

    $V = [];
    foreach ($alternatives as $a) {
        foreach ($criteria as $crit) {
            $idA = $a['ID_PERSONNE'];
            $idC = $crit['ID_CRITERE'];

            $n = $N[$idA][$idC];
            $w = $W[$idC];

            $V[$idA][$idC] = [
                $n[0] * $w[0],
                $n[1] * $w[1],
                $n[2] * $w[2]
            ];
        }
    }

    //---------------------------------------------------------
    // 4. Solution négative-idéale (NI)
    //---------------------------------------------------------
    $NI = [];
    foreach ($criteria as $crit) {
        $j = $crit['ID_CRITERE'];
        $min_tfn = [PHP_FLOAT_MAX, PHP_FLOAT_MAX, PHP_FLOAT_MAX];
        
        foreach ($alternatives as $a) {
            $t = $V[$a['ID_PERSONNE']][$j];
            if ($t[1] < $min_tfn[1]) {
                $min_tfn = $t;
            }
        }
        $NI[$j] = $min_tfn;
    }

    //---------------------------------------------------------
    // 5. Distances floues Euclidienne et Taxicab
    //---------------------------------------------------------
    function fuzzyDistanceE($A, $B) {
        return [
            sqrt(pow($A[0]-$B[0], 2)/3),
            sqrt(pow($A[1]-$B[1], 2)/3),
            sqrt(pow($A[2]-$B[2], 2)/3)
        ];
    }

    function fuzzyDistanceT($A, $B) {
        return [
            (abs($A[0]-$B[0]))/3,
            (abs($A[1]-$B[1]))/3,
            (abs($A[2]-$B[2]))/3
        ];
    }

    $dE = [];
    $dT = [];

    foreach ($alternatives as $a) {
        $id = $a['ID_PERSONNE'];

        // initialisation TFN distance totale
        $sumE = [0,0,0];
        $sumT = [0,0,0];

        foreach ($criteria as $crit) {
            $j = $crit['ID_CRITERE'];

            $de = fuzzyDistanceE($V[$id][$j], $NI[$j]);
            $dt = fuzzyDistanceT($V[$id][$j], $NI[$j]);

            $sumE = [$sumE[0]+$de[0], $sumE[1]+$de[1], $sumE[2]+$de[2]];
            $sumT = [$sumT[0]+$dt[0], $sumT[1]+$dt[1], $sumT[2]+$dt[2]];
        }

        $dE[$id] = $sumE;
        $dT[$id] = $sumT;
    }

    //---------------------------------------------------------
    // 6. Comparaison floue CODAS
    //---------------------------------------------------------
    $tau = 0.2;
    $H = [];

    foreach ($alternatives as $a_i) {
        foreach ($alternatives as $a_k) {
            if ($a_i['ID_PERSONNE'] == $a_k['ID_PERSONNE']) continue;

            $i = $a_i['ID_PERSONNE'];
            $k = $a_k['ID_PERSONNE'];

            $H[$i][$k] = [
                ($dE[$i][0] - $dE[$k][0]) + $tau*($dT[$i][0] - $dT[$k][0]),
                ($dE[$i][1] - $dE[$k][1]) + $tau*($dT[$i][1] - $dT[$k][1]),
                ($dE[$i][2] - $dE[$k][2]) + $tau*($dT[$i][2] - $dT[$k][2])
            ];
        }
    }

    //---------------------------------------------------------
    // 7. Score final (somme floue)
    //---------------------------------------------------------
    $S = [];
    foreach ($alternatives as $a) {
        $id = $a['ID_PERSONNE'];
        $sum = [0,0,0];

        if (!empty($H[$id])) {
            foreach ($H[$id] as $h) {
                $sum = [$sum[0]+$h[0], $sum[1]+$h[1], $sum[2]+$h[2]];
            }
        }

        // Defuzzification COG
        $S[$id] = [
            'name' => $a['NOM_PERSONNE'],
            'fuzzy_score' => $sum,
            'score' => ($sum[0] + $sum[1] + $sum[2]) / 3
        ];
    }

    //---------------------------------------------------------
    // 8. Classement final
    //---------------------------------------------------------
    uasort($S, fn($a, $b) => $b['score'] <=> $a['score']);

    $r = 1;
    foreach ($S as &$row) {
        $row['rank'] = $r++;
    }

    return [
        'ranking' => $S,
        'matrices' => [
            'X' => $X,
            'N' => $N,
            'V' => $V,
            'NI' => $NI,
            'dE' => $dE,
            'dT' => $dT,
            'H' => $H
        ]
    ];
}
