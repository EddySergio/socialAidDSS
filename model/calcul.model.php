<?php

/**
 * Calcule la distance Euclidienne floue entre deux nombres flous triangulaires.
 */
function fuzzyDistanceE($A, $B) {
    return sqrt((pow($A[0] - $B[0], 2) + pow($A[1] - $B[1], 2) + pow($A[2] - $B[2], 2)) / 3);
}

/**
 * Calcule la distance Taxicab (Manhattan) floue entre deux nombres flous triangulaires.
 */
function fuzzyDistanceT($A, $B) {
    return (abs($A[0] - $B[0]) + abs($A[1] - $B[1]) + abs($A[2] - $B[2])) / 3;
}

/**
 * Fonction seuil t(x) pour la comparaison relative.
 */
function threshold($delta_ED, $theta = 0.02): int {
    return (abs($delta_ED) >= $theta) ? 1 : 0;
}


/**
 * Triangular Fuzzy CODAS complet avec Normalisation Réciproque et Débogage
 */
function calculateFuzzyCODAS(int $projectId): array {

    // Simuler la récupération des données (assumer que ces fonctions existent)
    $criteria = getCriteriaForProject($projectId);
    $alternatives = getAlternativesForProject($projectId);
    $performances = getEvaluationsForProject($projectId, $alternatives, $criteria);

    if (empty($criteria) || empty($alternatives) || empty($performances)) {
        return ['error' => "Données manquantes."];
    }

    //---------------------------------------------------------
    // 1. Construction de la matrice décision floue (X)
    //---------------------------------------------------------
    $X = [];
    foreach ($alternatives as $alt) {
        foreach ($criteria as $crit) {
            $X[$alt['ID_PERSONNE']][$crit['ID_CRITERE']]
                = $performances[$alt['ID_PERSONNE']][$crit['ID_CRITERE']]['tfn'];
        }
    }

    //---------------------------------------------------------
    // 2. Normalisation floue (N) - Normalisation Réciproque pour Coût
    //---------------------------------------------------------
    $N = [];
    $epsilon = 1e-9; 

    foreach ($criteria as $crit) {
        $j = $crit['ID_CRITERE'];
        $type = $crit['OBJECTIF']; 

        // --- PHASE 1 : Calcul des bornes de normalisation ---
        $max_u = 0;             
        $max_recip_u = 0;       

        foreach ($alternatives as $a) {
            $t = $X[$a['ID_PERSONNE']][$j]; 
            
            $max_u = max($max_u, $t[2]);
            
            // Utiliser epsilon si t[0] est proche de zéro pour éviter la division par zéro
            $l_for_reciprocal = ($t[0] < $epsilon) ? $epsilon : $t[0];
            $max_recip_u = max($max_recip_u, 1 / $l_for_reciprocal);
        }
        
        $div_max_u = ($max_u < $epsilon) ? $epsilon : $max_u;
        $div_max_recip_u = ($max_recip_u < $epsilon) ? $epsilon : $max_recip_u;


        // --- PHASE 2 : Application de la normalisation ---
        foreach ($alternatives as $a) {
            $id = $a['ID_PERSONNE'];
            $l_t = $X[$id][$j][0]; $m_t = $X[$id][$j][1]; $u_t = $X[$id][$j][2];

            if ($type === 'max') {
                $N[$id][$j] = [
                    $l_t / $div_max_u, 
                    $m_t / $div_max_u, 
                    $u_t / $div_max_u
                ];
            } else {
                $l_div = ($l_t < $epsilon) ? $epsilon : $l_t;
                $m_div = ($m_t < $epsilon) ? $epsilon : $m_t;
                $u_div = ($u_t < $epsilon) ? $epsilon : $u_t;
                
                $recip_l = 1 / $u_div;
                $recip_m = 1 / $m_div;
                $recip_u = 1 / $l_div;
                
                $N[$id][$j] = [
                    $recip_l / $div_max_recip_u,  
                    $recip_m / $div_max_recip_u,  
                    $recip_u / $div_max_recip_u   
                ];
            }
        }
    }

    //---------------------------------------------------------
    // 3. Pondération floue (V)
    //---------------------------------------------------------
    $W = [];
    foreach ($criteria as $crit) {
        $W[$crit['ID_CRITERE']] = [$crit['poids_l'], $crit['poids_m'], $crit['poids_u']];
    }

    $V = [];
    foreach ($alternatives as $a) {
        foreach ($criteria as $crit) {
            $idA = $a['ID_PERSONNE'];
            $idC = $crit['ID_CRITERE'];

            $n = $N[$idA][$idC];
            $w = $W[$idC];

            $V[$idA][$idC] = [$n[0] * $w[0], $n[1] * $w[1], $n[2] * $w[2]];
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
            $id = $a['ID_PERSONNE'];
            if (!isset($V[$id][$j])) {
                continue; 
            }
            $t = $V[$id][$j];
            
            $min_tfn[0] = min($min_tfn[0], $t[0]);
            $min_tfn[1] = min($min_tfn[1], $t[1]);
            $min_tfn[2] = min($min_tfn[2], $t[2]);
        }
        $NI[$j] = $min_tfn;
    }

    //---------------------------------------------------------
    // 5. Distances floues Euclidienne (dE) et Taxicab (dT)
    //---------------------------------------------------------
    $dE = [];
    $dT = [];

    foreach ($alternatives as $a) {
        $id = $a['ID_PERSONNE'];
        $sumE = 0.0;
        $sumT = 0.0;

        foreach ($criteria as $crit) {
            $j = $crit['ID_CRITERE'];
            
            if (isset($V[$id][$j]) && isset($NI[$j])) {
                $sumE += fuzzyDistanceE($V[$id][$j], $NI[$j]);
                $sumT += fuzzyDistanceT($V[$id][$j], $NI[$j]);
            }
        }

        $dE[$id] = $sumE;
        $dT[$id] = $sumT;
    }

    //---------------------------------------------------------
    // 6. Score d'Évaluation (AS) et Débogage de la matrice P_ik
    //---------------------------------------------------------
    $theta = 0.02; 
    $AS = []; 
    $P_debug = []; // Nouvelle matrice pour le débogage de p_ik

    foreach ($alternatives as $a_i) {
        $i = $a_i['ID_PERSONNE'];
        $sum_p_ik = 0.0;

        foreach ($alternatives as $a_k) {
            $k = $a_k['ID_PERSONNE'];
            
            // Initialisation de la ligne pour le débogage, y compris la case i=k
            $P_debug[$i][$k] = [
                'delta_ED' => 0.0,
                'delta_HD' => 0.0,
                't_val' => 0,
                'p_ik' => 0.0
            ];

            if ($i == $k) continue;

            $delta_ED = $dE[$i] - $dE[$k];
            $delta_HD = $dT[$i] - $dT[$k];
            
            $t_val = threshold($delta_ED, $theta);
            
            $p_ik = $delta_ED + ($t_val * $delta_HD);

            $sum_p_ik += $p_ik;

            // Enregistrement des valeurs pour le débogage
            $P_debug[$i][$k]['delta_ED'] = $delta_ED;
            $P_debug[$i][$k]['delta_HD'] = $delta_HD;
            $P_debug[$i][$k]['t_val'] = $t_val;
            $P_debug[$i][$k]['p_ik'] = $p_ik;
        }

        $AS[$i] = [
            'name' => $a_i['NOM_PERSONNE'],
            'score' => $sum_p_ik 
        ];
    }

    //---------------------------------------------------------
    // 7. Classement final
    //---------------------------------------------------------
    uasort($AS, fn($a, $b) => $b['score'] <=> $a['score']);

    $r = 1;
    foreach ($AS as $id => &$row) {
        $row['rank'] = $r++;
    }

    return [
        'ranking' => $AS,
        'matrices' => [
            'X' => $X,
            'N' => $N,
            'V' => $V,
            'NI' => $NI,
            'dE' => $dE,
            'dT' => $dT,
            'P_Relative_Evaluation_Debug' => $P_debug,
            'AS' => array_column($AS, 'score', 'id') 
        ]
    ];
}