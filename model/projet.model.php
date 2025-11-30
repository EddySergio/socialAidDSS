<?php

/**
 * Récupère tous les projets appartenant à un utilisateur spécifique.
 * @param int $userId L'ID de l'utilisateur connecté.
 * @return array La liste des projets de l'utilisateur.
 */
function getProjectsForUser(int $userId): array {
    $pdo = dbConnect();
    // La requête sélectionne les projets en filtrant par ID_USER pour assurer que l'utilisateur ne voit que ses propres projets.
    $stmt = $pdo->prepare("SELECT ID_PROJET, NOM_PROJET, DESCRIPTION_PROJET, DATE_CREATION FROM projet WHERE ID_USER = ? ORDER BY DATE_CREATION DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Récupère les détails (nom, description) d'un projet spécifique.
 * @param int $projectId L'ID du projet.
 * @return array|false Les détails du projet ou false s'il n'est pas trouvé.
 */
function getProjectDetails(int $projectId) {
    $pdo = dbConnect();
    $sql = "SELECT ID_PROJET, NOM_PROJET, DESCRIPTION_PROJET, methode_poids FROM projet WHERE ID_PROJET = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$projectId]);
    return $stmt->fetch();
}