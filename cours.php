<?php
// Page de gestion des cours
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
verifierConnexion();

// Gestion des actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    // Suppression d'un cours
    if ($action === 'supprimer' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM cours WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            $_SESSION['message_succes'] = "Le cours a été supprimé avec succès.";
        } catch (PDOException $e) {
            $_SESSION['message_erreur'] = "Erreur lors de la suppression du cours.";
        }
        
        // Redirection pour éviter la réexécution du code en cas de rafraîchissement
        header('Location: cours.php');
        exit();
    }
}

// Récupération des cours
try {
    $recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';
    
    if (!empty($recherche)) {
        $stmt = $pdo->prepare("
            SELECT c.*, 
                  CONCAT(u.prenom, ' ', u.nom) as nom_enseignant,
                  (SELECT COUNT(*) FROM inscriptions WHERE cours_id = c.id) as nb_etudiants,
                  (SELECT COUNT(DISTINCT date_presence) FROM presences WHERE cours_id = c.id) as nb_seances
            FROM cours c
            LEFT JOIN utilisateurs u ON c.enseignant_id = u.id
            WHERE c.nom LIKE :recherche OR c.code LIKE :recherche OR CONCAT(u.prenom, ' ', u.nom) LIKE :recherche
            ORDER BY c.nom
        ");
        $stmt->execute(['recherche' => "%$recherche%"]);
    } else {
        $stmt = $pdo->query("
            SELECT c.*, 
                  CONCAT(u.prenom, ' ', u.nom) as nom_enseignant,
                  (SELECT COUNT(*) FROM inscriptions WHERE cours_id = c.id) as nb_etudiants,
                  (SELECT COUNT(DISTINCT date_presence) FROM presences WHERE cours_id = c.id) as nb_seances
            FROM cours c
            LEFT JOIN utilisateurs u ON c.enseignant_id = u.id
            ORDER BY c.nom
        ");
    }
    
    $cours = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['message_erreur'] = "Erreur lors de la récupération des cours: " . $e->getMessage();
    $cours = [];
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Gestion des Cours</h1>
    <a href="ajouter_cours.php" class="d-none d-sm-inline-block btn btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50 me-2"></i> Ajouter un cours
    </a>
</div>

<!-- Filtres et recherche -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Recherche de cours</h6>
    </div>
    <div class="card-body">
        <form action="" method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" name="recherche" placeholder="Rechercher par nom, code ou enseignant..." value="<?php echo echapper($recherche); ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <?php if (!empty($recherche)): ?>
                    <a href="cours.php" class="btn btn-outline-secondary">Réinitialiser</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Liste des cours -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Liste des cours</h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownMenuLink">
                <li><a class="dropdown-item" href="rapports.php?type=cours">Voir le rapport</a></li>
                <li><a class="dropdown-item" href="#" onclick="window.print()">Imprimer la liste</a></li>
            </ul>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom du cours</th>
                        <th>Enseignant</th>
                        <th>Étudiants</th>
                        <th>Séances</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($cours) > 0): ?>
                        <?php foreach ($cours as $c): ?>
                            <tr>
                                <td><?php echo echapper($c['code']); ?></td>
                                <td><?php echo echapper($c['nom']); ?></td>
                                <td>
                                    <?php if (!empty($c['nom_enseignant'])): ?>
                                        <?php echo echapper($c['nom_enseignant']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Non assigné</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary text-white"><?php echo $c['nb_etudiants']; ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-info text-white"><?php echo $c['nb_seances']; ?></span>
                                </td>
                                <td>
                                    <a href="ajouter_cours.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-info btn-action" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="cours.php?action=supprimer&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-danger btn-action btn-delete" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <a href="presence.php?cours_id=<?php echo $c['id']; ?>" class="btn btn-sm btn-success btn-action" title="Marquer présence">
                                        <i class="fas fa-clipboard-check"></i>
                                    </a>
                                    <a href="rapports.php?cours_id=<?php echo $c['id']; ?>" class="btn btn-sm btn-primary btn-action" title="Rapport de présence">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Aucun cours trouvé.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
