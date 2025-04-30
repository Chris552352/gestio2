<?php
// Tableau de bord
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
verifierConnexion();

// Récupérer les statistiques
$nbEtudiants = obtenirNombreEtudiants($pdo);
$nbCours = obtenirNombreCours($pdo);
$nbPresencesAujourdhui = obtenirPresencesAujourdhui($pdo);
$nbAbsencesAujourdhui = obtenirAbsencesAujourdhui($pdo);

// Récupérer les statistiques globales pour le graphique
$statsGlobales = obtenirStatistiquesGlobales($pdo);
$totalPresent = $statsGlobales['total_present'] ?? 0;
$totalAbsent = $statsGlobales['total_absent'] ?? 0;

// Récupérer les statistiques par mois pour le graphique
$anneeActuelle = date('Y');
$statsMensuelles = obtenirStatistiquesParMois($pdo, $anneeActuelle);
$statsMensuellesJson = json_encode($statsMensuelles);

// Récupérer les 5 dernières entrées de présence
$stmt = $pdo->query("
    SELECT p.date_presence, p.statut, e.nom as etudiant_nom, e.prenom as etudiant_prenom, c.nom as cours_nom
    FROM presences p
    JOIN etudiants e ON p.etudiant_id = e.id
    JOIN cours c ON p.cours_id = c.id
    ORDER BY p.date_enregistrement DESC
    LIMIT 5
");
$dernieresPresences = $stmt->fetchAll();

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tableau de Bord</h1>
        <div class="d-none d-sm-inline-block">
            <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                <?php echo formaterDate(date('Y-m-d')); ?>
            </span>
        </div>
    </div>

    <!-- Cartes statistiques -->
    <div class="row">
        <!-- Présences aujourd'hui -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stats border-0 shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Présences aujourd'hui</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $nbPresencesAujourdhui; ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="card-icon bg-success-light text-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Absences aujourd'hui -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stats border-0 shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Absences aujourd'hui</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $nbAbsencesAujourdhui; ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="card-icon bg-warning-light text-danger">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nombre total d'étudiants -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stats border-0 shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Nombre d'étudiants</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $nbEtudiants; ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="card-icon bg-primary-light text-primary">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nombre total de cours -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stats border-0 shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Nombre de cours</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $nbCours; ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="card-icon bg-info-light text-info">
                                <i class="fas fa-book"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques et Tableaux -->
    <div class="row">
        <!-- Graphique Présence vs Absence (Global) -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Présence vs Absence (Global)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="presenceChart" data-present="<?php echo $totalPresent; ?>" data-absent="<?php echo $totalAbsent; ?>"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphique mensuel de présence -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Tendance mensuelle de présence (<?php echo $anneeActuelle; ?>)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyChart" data-monthly='<?php echo $statsMensuellesJson; ?>'></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des dernières présences enregistrées -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dernières présences enregistrées</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Étudiant</th>
                                    <th>Cours</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($dernieresPresences) > 0): ?>
                                    <?php foreach ($dernieresPresences as $presence): ?>
                                        <tr>
                                            <td><?php echo formaterDate($presence['date_presence']); ?></td>
                                            <td><?php echo echapper($presence['etudiant_prenom']) . ' ' . echapper($presence['etudiant_nom']); ?></td>
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
                                        <td colspan="4" class="text-center">Aucune présence enregistrée récemment.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="presence.php" class="btn btn-primary">
                            <i class="fas fa-clipboard-check me-2"></i> Marquer les présences
                        </a>
                        <a href="rapports.php" class="btn btn-outline-primary ms-2">
                            <i class="fas fa-chart-bar me-2"></i> Voir tous les rapports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
