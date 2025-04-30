<?php
// Page des rapports de présence
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
verifierConnexion();

// Récupération des paramètres de filtrage
$etudiant_id = isset($_GET['etudiant_id']) ? intval($_GET['etudiant_id']) : 0;
$cours_id = isset($_GET['cours_id']) ? intval($_GET['cours_id']) : 0;
$date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : date('Y-m-01'); // Premier jour du mois
$date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : date('Y-m-t'); // Dernier jour du mois
$type_rapport = isset($_GET['type']) ? $_GET['type'] : '';

// Récupérer tous les cours pour le filtre
try {
    $stmt = $pdo->query("SELECT id, code, nom FROM cours ORDER BY nom");
    $cours = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['message_erreur'] = "Erreur lors de la récupération des cours: " . $e->getMessage();
    $cours = [];
}

// Récupérer tous les étudiants pour le filtre
try {
    $stmt = $pdo->query("SELECT id, matricule, nom, prenom FROM etudiants ORDER BY nom, prenom");
    $etudiants = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['message_erreur'] = "Erreur lors de la récupération des étudiants: " . $e->getMessage();
    $etudiants = [];
}

// Construire la requête de base pour les rapports
$params = [];
$where_clauses = [];
$sql_base = "
    SELECT p.id, p.date_presence, p.statut, 
           e.id as etudiant_id, e.matricule, e.nom as etudiant_nom, e.prenom as etudiant_prenom,
           c.id as cours_id, c.code as cours_code, c.nom as cours_nom
    FROM presences p
    JOIN etudiants e ON p.etudiant_id = e.id
    JOIN cours c ON p.cours_id = c.id
";

// Ajouter les filtres à la requête
if ($etudiant_id > 0) {
    $where_clauses[] = "p.etudiant_id = :etudiant_id";
    $params['etudiant_id'] = $etudiant_id;
}

if ($cours_id > 0) {
    $where_clauses[] = "p.cours_id = :cours_id";
    $params['cours_id'] = $cours_id;
}

if (!empty($date_debut)) {
    $where_clauses[] = "p.date_presence >= :date_debut";
    $params['date_debut'] = $date_debut;
}

if (!empty($date_fin)) {
    $where_clauses[] = "p.date_presence <= :date_fin";
    $params['date_fin'] = $date_fin;
}

// Assembler la requête complète
if (!empty($where_clauses)) {
    $sql_base .= " WHERE " . implode(' AND ', $where_clauses);
}
$sql_base .= " ORDER BY p.date_presence DESC, e.nom, e.prenom";

// Exécuter la requête
try {
    $stmt = $pdo->prepare($sql_base);
    $stmt->execute($params);
    $presences = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['message_erreur'] = "Erreur lors de la récupération des données: " . $e->getMessage();
    $presences = [];
}

// Préparer des données pour les graphiques
$statsEtudiants = [];
$statsCours = [];

// Statistiques par étudiant
if (empty($type_rapport) || $type_rapport === 'etudiants') {
    try {
        $sql = "
            SELECT e.id, CONCAT(e.prenom, ' ', e.nom) as nom,
                  SUM(CASE WHEN p.statut = 'present' THEN 1 ELSE 0 END) as present,
                  SUM(CASE WHEN p.statut = 'absent' THEN 1 ELSE 0 END) as absent
            FROM etudiants e
            LEFT JOIN presences p ON e.id = p.etudiant_id
        ";
        
        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }
        
        $sql .= " GROUP BY e.id ORDER BY e.nom LIMIT 10";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $statsEtudiants = $stmt->fetchAll();
    } catch (PDOException $e) {
        $_SESSION['message_erreur'] = "Erreur lors de la récupération des statistiques: " . $e->getMessage();
    }
}

// Statistiques par cours
if (empty($type_rapport) || $type_rapport === 'cours') {
    try {
        $sql = "
            SELECT c.id, c.nom,
                  SUM(CASE WHEN p.statut = 'present' THEN 1 ELSE 0 END) as present,
                  SUM(CASE WHEN p.statut = 'absent' THEN 1 ELSE 0 END) as absent
            FROM cours c
            LEFT JOIN presences p ON c.id = p.cours_id
        ";
        
        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }
        
        $sql .= " GROUP BY c.id ORDER BY c.nom";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $statsCours = $stmt->fetchAll();
    } catch (PDOException $e) {
        $_SESSION['message_erreur'] = "Erreur lors de la récupération des statistiques: " . $e->getMessage();
    }
}

// Titre du rapport en fonction des filtres
$titre_rapport = "Rapport de présence";
if ($etudiant_id > 0) {
    foreach ($etudiants as $e) {
        if ($e['id'] == $etudiant_id) {
            $titre_rapport .= " - " . $e['prenom'] . " " . $e['nom'];
            break;
        }
    }
}
if ($cours_id > 0) {
    foreach ($cours as $c) {
        if ($c['id'] == $cours_id) {
            $titre_rapport .= " - " . $c['nom'];
            break;
        }
    }
}
$titre_rapport .= " (" . formaterDate($date_debut) . " - " . formaterDate($date_fin) . ")";

// Inclure l'en-tête
include 'includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Rapports de Présence</h1>
    <div>
        <button onclick="window.print()" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50 me-2"></i> Exporter PDF
        </button>
    </div>
</div>

<!-- Filtres de rapport -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filtrer les résultats</h6>
    </div>
    <div class="card-body">
        <form action="" method="GET" id="filter-form" class="row g-3">
            <!-- Type de rapport -->
            <div class="col-md-12 mb-3">
                <div class="btn-group w-100" role="group">
                    <a href="rapports.php" class="btn <?php echo empty($type_rapport) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-table me-2"></i>Rapport détaillé
                    </a>
                    <a href="rapports.php?type=etudiants" class="btn <?php echo $type_rapport === 'etudiants' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-user-graduate me-2"></i>Par étudiant
                    </a>
                    <a href="rapports.php?type=cours" class="btn <?php echo $type_rapport === 'cours' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-book me-2"></i>Par cours
                    </a>
                </div>
            </div>
            
            <!-- Étudiant -->
            <div class="col-md-6 col-lg-3">
                <label for="etudiant_id" class="form-label">Étudiant</label>
                <select class="form-select" id="etudiant_id" name="etudiant_id">
                    <option value="">Tous les étudiants</option>
                    <?php foreach ($etudiants as $e): ?>
                        <option value="<?php echo $e['id']; ?>" <?php echo ($etudiant_id == $e['id']) ? 'selected' : ''; ?>>
                            <?php echo echapper($e['prenom']) . ' ' . echapper($e['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Cours -->
            <div class="col-md-6 col-lg-3">
                <label for="cours_id" class="form-label">Cours</label>
                <select class="form-select" id="cours_id" name="cours_id">
                    <option value="">Tous les cours</option>
                    <?php foreach ($cours as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo ($cours_id == $c['id']) ? 'selected' : ''; ?>>
                            <?php echo echapper($c['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Date début -->
            <div class="col-md-6 col-lg-3">
                <label for="date_debut" class="form-label">Date début</label>
                <input type="date" class="form-control" id="date_debut" name="date_debut" value="<?php echo $date_debut; ?>">
            </div>
            
            <!-- Date fin -->
            <div class="col-md-6 col-lg-3">
                <label for="date_fin" class="form-label">Date fin</label>
                <input type="date" class="form-control" id="date_fin" name="date_fin" value="<?php echo $date_fin; ?>">
            </div>
            
            <!-- Boutons -->
            <div class="col-12 mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-2"></i> Filtrer
                </button>
                <a href="rapports.php" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-sync-alt me-2"></i> Réinitialiser
                </a>
                
                <!-- Conserver le type de rapport lors de la soumission du formulaire -->
                <input type="hidden" name="type" value="<?php echo $type_rapport; ?>">
            </div>
        </form>
    </div>
</div>

<!-- Affichage du rapport en fonction du type -->
<?php if (empty($type_rapport)): ?>
    <!-- Rapport détaillé -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo $titre_rapport; ?></h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Étudiant</th>
                            <th>Matricule</th>
                            <th>Cours</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($presences) > 0): ?>
                            <?php foreach ($presences as $presence): ?>
                                <tr>
                                    <td><?php echo formaterDate($presence['date_presence']); ?></td>
                                    <td><?php echo echapper($presence['etudiant_prenom']) . ' ' . echapper($presence['etudiant_nom']); ?></td>
                                    <td><?php echo echapper($presence['matricule']); ?></td>
                                    <td><?php echo echapper($presence['cours_nom']); ?></td>
                                    <td>
                                        <?php if ($presence['statut'] === 'present'): ?>
                                            <span class="badge bg-success text-white badge-presence">Présent</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-white badge-presence">Absent</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Aucune donnée de présence trouvée pour les critères sélectionnés.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php elseif ($type_rapport === 'etudiants'): ?>
    <!-- Rapport par étudiant -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Présences par étudiant</h6>
                </div>
                <div class="card-body">
                    <?php if (count($statsEtudiants) > 0): ?>
                        <div class="chart-container">
                            <canvas id="studentChart" data-students='<?php echo json_encode($statsEtudiants); ?>'></canvas>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Aucune donnée disponible pour générer le graphique.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 5 Présences</h6>
                </div>
                <div class="card-body">
                    <?php
                    // Tri des données par taux de présence
                    $etudiantsPresence = [];
                    foreach ($statsEtudiants as $stat) {
                        $total = ($stat['present'] + $stat['absent']);
                        $taux = $total > 0 ? round(($stat['present'] / $total) * 100) : 0;
                        $etudiantsPresence[] = [
                            'nom' => $stat['nom'],
                            'present' => $stat['present'],
                            'absent' => $stat['absent'],
                            'taux' => $taux
                        ];
                    }
                    
                    // Tri par taux de présence décroissant
                    usort($etudiantsPresence, function($a, $b) {
                        return $b['taux'] - $a['taux'];
                    });
                    
                    // Limiter à 5 entrées
                    $etudiantsPresence = array_slice($etudiantsPresence, 0, 5);
                    ?>
                    
                    <?php if (count($etudiantsPresence) > 0): ?>
                        <div class="list-group">
                            <?php foreach ($etudiantsPresence as $ep): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo echapper($ep['nom']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo $ep['present']; ?> présences, <?php echo $ep['absent']; ?> absences
                                        </small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill"><?php echo $ep['taux']; ?>%</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Aucune donnée disponible.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tableau détaillé -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Détails par étudiant</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Étudiant</th>
                            <th>Présences</th>
                            <th>Absences</th>
                            <th>Taux de présence</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statsEtudiants as $stat): ?>
                            <?php
                            $total = ($stat['present'] + $stat['absent']);
                            $taux = $total > 0 ? round(($stat['present'] / $total) * 100) : 0;
                            ?>
                            <tr>
                                <td><?php echo echapper($stat['nom']); ?></td>
                                <td><?php echo $stat['present']; ?></td>
                                <td><?php echo $stat['absent']; ?></td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $taux; ?>%" 
                                             aria-valuenow="<?php echo $taux; ?>" aria-valuemin="0" aria-valuemax="100">
                                            <?php echo $taux; ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php elseif ($type_rapport === 'cours'): ?>
    <!-- Rapport par cours -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Présences par cours</h6>
                </div>
                <div class="card-body">
                    <?php if (count($statsCours) > 0): ?>
                        <div class="chart-container">
                            <canvas id="courseChart" data-courses='<?php echo json_encode($statsCours); ?>'></canvas>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Aucune donnée disponible pour générer le graphique.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Résumé des cours</h6>
                </div>
                <div class="card-body">
                    <?php if (count($statsCours) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Cours</th>
                                        <th>Taux</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($statsCours as $sc): ?>
                                        <?php
                                        $total = ($sc['present'] + $sc['absent']);
                                        $taux = $total > 0 ? round(($sc['present'] / $total) * 100) : 0;
                                        ?>
                                        <tr>
                                            <td><?php echo echapper($sc['nom']); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $taux; ?>%" 
                                                             aria-valuenow="<?php echo $taux; ?>" aria-valuemin="0" aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <span class="small"><?php echo $taux; ?>%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Aucune donnée disponible.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tableau détaillé -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Détails par cours</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Cours</th>
                            <th>Présences</th>
                            <th>Absences</th>
                            <th>Taux de présence</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statsCours as $stat): ?>
                            <?php
                            $total = ($stat['present'] + $stat['absent']);
                            $taux = $total > 0 ? round(($stat['present'] / $total) * 100) : 0;
                            ?>
                            <tr>
                                <td><?php echo echapper($stat['nom']); ?></td>
                                <td><?php echo $stat['present']; ?></td>
                                <td><?php echo $stat['absent']; ?></td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $taux; ?>%" 
                                             aria-valuenow="<?php echo $taux; ?>" aria-valuemin="0" aria-valuemax="100">
                                            <?php echo $taux; ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
