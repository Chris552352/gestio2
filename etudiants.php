<?php
// Page de gestion des étudiants
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
verifierConnexion();

// Gestion des actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    // Suppression d'un étudiant
    if ($action === 'supprimer' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM etudiants WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            $_SESSION['message_succes'] = "L'étudiant a été supprimé avec succès.";
        } catch (PDOException $e) {
            $_SESSION['message_erreur'] = "Erreur lors de la suppression de l'étudiant.";
        }
        
        // Redirection pour éviter la réexécution du code en cas de rafraîchissement
        header('Location: etudiants.php');
        exit();
    }
}

// Récupération des étudiants
try {
    $recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';
    
    if (!empty($recherche)) {
        $stmt = $pdo->prepare("
            SELECT e.*, 
                   (SELECT COUNT(*) FROM inscriptions WHERE etudiant_id = e.id) as nb_cours,
                   (SELECT COUNT(*) FROM presences WHERE etudiant_id = e.id AND statut = 'present') as nb_presences,
                   (SELECT COUNT(*) FROM presences WHERE etudiant_id = e.id AND statut = 'absent') as nb_absences
            FROM etudiants e 
            WHERE e.nom LIKE :recherche OR e.prenom LIKE :recherche OR e.matricule LIKE :recherche OR e.email LIKE :recherche
            ORDER BY e.nom, e.prenom
        ");
        $stmt->execute(['recherche' => "%$recherche%"]);
    } else {
        $stmt = $pdo->query("
            SELECT e.*, 
                   (SELECT COUNT(*) FROM inscriptions WHERE etudiant_id = e.id) as nb_cours,
                   (SELECT COUNT(*) FROM presences WHERE etudiant_id = e.id AND statut = 'present') as nb_presences,
                   (SELECT COUNT(*) FROM presences WHERE etudiant_id = e.id AND statut = 'absent') as nb_absences
            FROM etudiants e
            ORDER BY e.nom, e.prenom
        ");
    }
    
    $etudiants = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['message_erreur'] = "Erreur lors de la récupération des étudiants: " . $e->getMessage();
    $etudiants = [];
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Gestion des Étudiants</h1>
    <a href="ajouter_etudiant.php" class="d-none d-sm-inline-block btn btn-primary shadow-sm">
        <i class="fas fa-user-plus fa-sm text-white-50 me-2"></i> Ajouter un étudiant
    </a>
</div>

<!-- Filtres et recherche -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Recherche d'étudiants</h6>
    </div>
    <div class="card-body">
        <form action="" method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" name="recherche" placeholder="Rechercher par nom, prénom, matricule ou email..." value="<?php echo echapper($recherche); ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <?php if (!empty($recherche)): ?>
                    <a href="etudiants.php" class="btn btn-outline-secondary">Réinitialiser</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Liste des étudiants -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Liste des étudiants</h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownMenuLink">
                <li><a class="dropdown-item" href="rapports.php?type=etudiants">Voir le rapport</a></li>
                <li><a class="dropdown-item" href="#" onclick="window.print()">Imprimer la liste</a></li>
            </ul>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Nom & Prénom</th>
                        <th>Email</th>
                        <th>Cours inscrits</th>
                        <th>Présences/Absences</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($etudiants) > 0): ?>
                        <?php foreach ($etudiants as $etudiant): ?>
                            <tr>
                                <td><?php echo echapper($etudiant['matricule']); ?></td>
                                <td><?php echo echapper($etudiant['prenom']) . ' ' . echapper($etudiant['nom']); ?></td>
                                <td><?php echo echapper($etudiant['email']); ?></td>
                                <td>
                                    <span class="badge bg-primary text-white"><?php echo $etudiant['nb_cours']; ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-success text-white me-2"><?php echo $etudiant['nb_presences']; ?> P</span>
                                        <span class="badge bg-danger text-white"><?php echo $etudiant['nb_absences']; ?> A</span>
                                    </div>
                                </td>
                                <td>
                                    <a href="ajouter_etudiant.php?id=<?php echo $etudiant['id']; ?>" class="btn btn-sm btn-info btn-action" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="etudiants.php?action=supprimer&id=<?php echo $etudiant['id']; ?>" class="btn btn-sm btn-danger btn-action btn-delete" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <a href="rapports.php?etudiant_id=<?php echo $etudiant['id']; ?>" class="btn btn-sm btn-primary btn-action" title="Rapport de présence">
                                        <i class="fas fa-clipboard-list"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Aucun étudiant trouvé.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
