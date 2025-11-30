<?php

/**
 * Récupère toutes les alternatives (personnes cibles) pour un projet.
 * @param int $projectId L'ID du projet.
 * @return array La liste des personnes.
 */
function getAlternativesForProject(int $projectId): array {
    $pdo = dbConnect();
    $stmt = $pdo->prepare("SELECT ID_PERSONNE, NOM_PERSONNE FROM personne WHERE ID_PROJET = ? ORDER BY ID_PERSONNE");
    $stmt->execute([$projectId]);
    return $stmt->fetchAll();
}

/**
 * Récupère toutes les évaluations existantes pour un projet.
 * @param int $projectId L'ID du projet.
 * @return array Un tableau multidimensionnel [id_personne][id_critere] => valeur.
 */
function getEvaluationsForProject(int $projectId): array {
    $pdo = dbConnect();
    // CORRECTION : On joint `performance` avec `personne` pour pouvoir filtrer par `ID_PROJET`
    $sql = "SELECT perf.ID_PERSONNE, perf.ID_CRITERE, perf.VALEURNUM, perf.VALEURQUAL, c.TYPE_CRITERE,
                   perf.valeur_l, perf.valeur_m, perf.valeur_u
            FROM performance perf
            JOIN personne pers ON perf.ID_PERSONNE = pers.ID_PERSONNE
            JOIN critere c ON perf.ID_CRITERE = c.ID_CRITERE
            WHERE pers.ID_PROJET = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$projectId]);
    $evaluations = [];
    foreach ($stmt->fetchAll() as $eval) {
        // On stocke la valeur dans un format facile à utiliser dans la vue.
        $displayValue = '';
        if ($eval['TYPE_CRITERE'] === 'qualitative') {
            $displayValue = $eval['VALEURQUAL'];
        } else { // quantitative
            $displayValue = $eval['VALEURNUM'];
        }
        $evaluations[$eval['ID_PERSONNE']][$eval['ID_CRITERE']] = [
            'raw'     => $displayValue, // Ajout de la valeur brute pour l'édition
            'display' => $displayValue,
            'tfn' => [$eval['valeur_l'], $eval['valeur_m'], $eval['valeur_u']]
        ];
    }
    return $evaluations;
}

/**
 * Sauvegarde la matrice d'évaluation complète pour un projet. (Actuellement, ne fait que supprimer).
 * NOTE : Cette fonction semble incomplète ou son usage a changé. Elle ne fait que supprimer les performances.
 * La logique d'ajout est maintenant dans `addPersonAndEvaluations`.
 */
function saveEvaluations(int $projectId, array $evaluations): bool {
    $pdo = dbConnect();
    $pdo->beginTransaction();

    try {
        // 1. On supprime les anciennes performances pour ce projet en se basant sur les personnes du projet
        $stmtDelete = $pdo->prepare("DELETE FROM performance WHERE ID_PERSONNE IN (SELECT ID_PERSONNE FROM personne WHERE ID_PROJET = ?)");
        $stmtDelete->execute([$projectId]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}