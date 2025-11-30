<?php
// Démarrage de la session et inclusion des modèles
// Cette partie doit être la toute première chose dans le script.
// Elle assure que la session est active et que toutes les fonctions du modèle sont disponibles.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../model/function.php'; // Charge la connexion et les fonctions de base

// L'ID utilisateur est requis pour toutes les opérations sur cette page
// On le récupère de la session pour s'assurer que l'utilisateur ne manipule que ses propres données.
$userId = $_SESSION['ID_USER'] ?? null;

// Récupérer UNIQUEMENT les projets de l'utilisateur connecté pour les afficher.
$projects = getProjectsForUser($userId);
?>
<?php
// dashboard.php - Page principale sécurisée pour la gestion de projet

$pageTitle = "Gestion de Projet DSS";
include 'header.php'; 
// Le header.php contient le début de la structure HTML, le CSS, et la barre de navigation.
// Le nom d'utilisateur et l'ID sont dans la session
$username = $_SESSION['NOM_USER'] ?? 'Utilisateur';
?>

<!-- Début du contenu spécifique au tableau de bord (Gestion de Projet) -->

<div class="col-12 mb-4">
    <div class="card border-0 bg-light">
        <div class="card-body p-4">
            <h2 class="h4 fw-bold">Bienvenue, <?= htmlspecialchars($username) ?> !</h2>
            <p class="text-muted mb-0">Voici un aperçu de vos projets en cours. Prêt à prendre des décisions éclairées ?</p>
        </div>
    </div>
</div>

<!-- Zone principale (Liste des Projets) -->
<div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="card-title text-primary fw-bold mb-0">Mes Projets (<?= count($projects) ?>)</h3>
        <!-- Bouton qui ouvre la fenêtre modale de création -->
        <button type="button" class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#createProjectModal"><i class="bi bi-plus-lg me-1"></i>
            Nouveau Projet
        </button>
    </div>

    <!-- Affichage des projets sous forme de cartes -->
    <!-- La boucle `foreach` parcourt les projets récupérés de la base de données. -->
    <div class="row gy-4">
        <?php if (!empty($projects)): ?>
            <?php foreach ($projects as $project): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold text-primary"><?= htmlspecialchars($project['NOM_PROJET']) ?></h5>
                            <p class="card-text text-muted small flex-grow-1">
                                <?= nl2br(htmlspecialchars($project['DESCRIPTION_PROJET'] ?: 'Aucune description.')) ?>
                            </p>
                            <p class="card-text"><small class="text-muted">Créé le : <?= htmlspecialchars(date('d/m/Y', strtotime($project['DATE_CREATION']))) ?></small></p>
                        </div>
                        <div class="card-footer bg-white border-top-0 d-flex justify-content-end gap-2">
                            <div class="btn-group w-100" role="group">
                                <!-- Liens vers les autres pages de gestion, passant l'ID du projet en paramètre GET. -->
                                <a href="gestion_critere.php?project_id=<?= $project['ID_PROJET'] ?>" class="btn btn-sm btn-outline-primary" title="Gérer les critères"><i class="bi bi-card-checklist"></i> Critères</a>
                                <a href="gestion_alternatives.php?project_id=<?= $project['ID_PROJET'] ?>" class="btn btn-sm btn-outline-primary" title="Gérer les cibles"><i class="bi bi-people"></i> Cibles</a>
                            </div>
                            <div class="btn-group w-100" role="group">
                                <a href="resultats.php?project_id=<?= $project['ID_PROJET'] ?>" class="btn btn-sm btn-warning text-dark" title="Voir les résultats"><i class="bi bi-graph-up"></i> Résultats</a>
                                <!-- Le lien de suppression inclut une confirmation JavaScript. -->
                                <a href="../actions/projet/delete.php?id=<?= $project['ID_PROJET'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce projet ?');" class="btn btn-sm btn-danger" title="Supprimer"><i class="bi bi-trash"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Message affiché si l'utilisateur n'a aucun projet. -->
            <div class="col-12 text-center">
                <div class="text-center p-5 bg-light rounded">
                    <h4 class="text-muted"><i class="bi bi-info-circle me-2"></i>Aucun projet trouvé.</h4>
                    <p>Créez-en un nouveau pour commencer à prendre des décisions !</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Fenêtre Modale (Pop-up) pour la Création de Projet -->
<!-- C'est un composant Bootstrap qui est caché par défaut et affiché via le bouton "Nouveau Projet". -->
<div class="modal fade" id="createProjectModal" tabindex="-1" aria-labelledby="createProjectModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createProjectModalLabel">Créer un nouveau projet</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="../actions/projet/add.php">
        <div class="modal-body">
            <div class="mb-3">
                <label for="project_name" class="form-label">Nom du Projet</label>
                <input type="text" class="form-control" id="project_name" name="project_name" placeholder="Ex: Choix d'un nouveau local" required autofocus>
                <div class="form-text">Donnez un nom clair et descriptif à votre problème de décision.</div>
            </div>
            <div class="mb-3">
                <label for="project_description" class="form-label">Description (Optionnel)</label>
                <textarea class="form-control" id="project_description" name="project_description" rows="3" placeholder="Décrivez brièvement le contexte ou l'objectif de ce projet..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Créer le Projet</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php 
// Le footer.php contient la fin de la structure HTML et les scripts JS globaux.
include 'footer.php'; 
?>