<?php

/**
 * Récupère tous les critères pour un projet spécifique, ordonnés par leur rang.
 * @param int $projectId L'ID du projet.
 * @return array La liste des critères.
 */
function getCriteriaForProject(int $projectId): array {
    $pdo = dbConnect();
    // On sélectionne les 3 nouvelles colonnes de poids flou.
    $sql = "SELECT c.ID_CRITERE, c.NOM_CRITERE, c.TYPE_CRITERE, c.RANG, c.poids_l, c.poids_m, c.poids_u, c.OBJECTIF FROM critere c
            WHERE c.ID_PROJET = ? ORDER BY c.RANG ASC, c.ID_CRITERE ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$projectId]);
    $criteria = $stmt->fetchAll();

    foreach ($criteria as $key => $criterion) {
        if ($criterion['TYPE_CRITERE'] === 'qualitative') {
            $criteria[$key]['options'] = getQualitativeValuesForCriterion($criterion['ID_CRITERE']);
        }
    }

    return $criteria;
}

/**
 * Récupère les valeurs linguistiques (qualitatives) pour un critère spécifique, ordonnées par importance.
 * @param int $criterionId L'ID du critère.
 * @return array La liste des options qualitatives.
 */
function getQualitativeValuesForCriterion(int $criterionId): array {
    $pdo = dbConnect();
    $stmt = $pdo->prepare("SELECT ID_VALQUAL, LIBELLE, RANG FROM valeurqualitative WHERE ID_CRITERE = ? ORDER BY RANG DESC");
    $stmt->execute([$criterionId]);
    return $stmt->fetchAll();
}