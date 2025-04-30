<?php
// Page pour marquer les présences
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
verifierConnexion();

// Récupération des paramètres
$cours_id = isset($_GET['cours_id']) ? intval($_GET['cours_id']) : (isset($_POST['cours_id']) ? intval($_POST['cours_id']) : 0);
$date_presence = isset($_GET['date']) ? $_GET['date'] : (isset($_POST['date']) ? $_POST['date'] : date('Y-m-d'));

// Vérifier si la date est valide
$date_obj = DateTime::createFromFormat('Y-m-d', $date_presence);
if (!$date_obj || $date_obj->format('Y-m-d') !== $date_presence) {
    $date_presence = date('Y-m-d');
}

// Récupérer tous les cours
try {
    $stmt = $pdo->query("SELECT id, code, nom FROM cours ORDER BY nom");
    $cours = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['message_erreur'] = "Erreur lors de la récupération des cours: " . $e->getMessage();
    $cours = [];
}

// Si un cours est sélectionné, récupérer les étudiants inscrits
$etudiants = [];
$coursSelectionne = null;

if ($cours_id > 0) {
    try {
        // Récupérer les informations du cours
        $stmt = $pdo->prepare("
            SELECT c.*, CONCAT(u.prenom, ' ', u.nom) as nom_enseignant
            FROM cours c
            LEFT JOIN utilisateurs u ON c.enseignant_id = u.id
            WHERE c.id = :id
        ");
        $stmt->execute(['id' => $cours_id]);
        $coursSelectionne = $stmt->fetch();
        
        if (!$coursSelectionne) {
            $_SESSION['message_erreur'] = "Cours non trouvé.";
            header('Location: presence.php');
            exit();
        }
        
        // Récupérer les étudiants inscrits à ce cours
        $stmt = $pdo->prepare("
            SELECT e.*, 
                  (SELECT statut FROM presences WHERE etudiant_id = e.id AND cours_id = :cours_id AND date_presence = :date_presence) as statut
            FROM etudiants e
            JOIN inscriptions i ON e.id = i.etudiant_id
            WHERE i.cours_id = :cours_id
            ORDER BY e.nom, e.prenom
        ");
        $stmt->execute([
            'cours_id' => $cours_id,
            'date_presence' => $date_presence
        ]);
        $etudiants = $stmt->fetchAll();
    } catch (PDOException $e) {
        $_SESSION['message_erreur'] = "Erreur lors de la récupération des données: " . $e->getMessage();
    }
}

// Traitement du formulaire d'enregistrement des présences
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enregistrer'])) {
    $statuts = $_POST['statut'] ?? [];
    $cours_id = intval($_POST['cours_id']);
    $date_presence = $_POST['date'];
    
    // Validation
    if (empty($cours_id)) {
        $_SESSION['message_erreur'] = "Veuillez sélectionner un cours.";
    } else if (empty($date_presence)) {
        $_SESSION['message_erreur'] = "Veuillez sélectionner une date.";
    } else {
        try {
            $pdo->beginTransaction();
            
            // Supprimer d'abord toutes les présences existantes pour ce cours et cette date
            $stmt = $pdo->prepare("
                DELETE FROM presences 
                WHERE cours_id = :cours_id AND date_presence = :date_presence
            ");
            $stmt->execute([
                'cours_id' => $cours_id,
                'date_presence' => $date_presence
            ]);
            
            // Puis insérer les nouvelles présences
            $compte_present = 0;
            $compte_absent = 0;
            
            $stmt = $pdo->prepare("
                INSERT INTO presences (etudiant_id, cours_id, date_presence, statut, enregistre_par)
                VALUES (:etudiant_id, :cours_id, :date_presence, :statut, :enregistre_par)
            ");
            
            foreach ($statuts as $etudiant_id => $statut) {
                $stmt->execute([
                    'etudiant_id' => $etudiant_id,
                    'cours_id' => $cours_id,
                    'date_presence' => $date_presence,
                    'statut' => $statut,
                    'enregistre_par' => $_SESSION['utilisateur_id']
                ]);
                
                if ($statut === 'present') {
                    $compte_present++;
                } else {
                    $compte_absent++;
                }
            }
            
            $pdo->commit();
            
            $_SESSION['message_succes'] = "Présences enregistrées avec succès: $compte_present présent(s), $compte_absent absent(s).";
            
            // Redirection pour éviter la réexécution du code en cas de rafraîchissement
            header("Location: presence.php?cours_id=$cours_id&date=$date_presence");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['message_erreur'] = "Erreur lors de l'enregistrement des présences: " . $e->getMessage();
        }
    }
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Gestion des Présences</h1>
    <div>
        <a href="rapports.php" class="d-none d-sm-inline-block btn btn-outline-primary shadow-sm me-2">
            <i class="fas fa-chart-bar fa-sm text-primary-50 me-2"></i> Voir les rapports
        </a>
        <a href="cours.php" class="d-none d-sm-inline-block btn btn-outline-secondary shadow-sm">
            <i class="fas fa-book fa-sm text-secondary-50 me-2"></i> Gérer les cours
        </a>
    </div>
</div>

<!-- Sélection du cours et de la date -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Sélectionner un cours et une date</h6>
    </div>
    <div class="card-body">
        <form action="" method="GET" id="filter-presence-form" class="row g-3">
            <div class="col-md-5">
                <label for="cours-select" class="form-label">Cours</label>
                <select class="form-select" id="cours-select" name="cours_id" required>
                    <option value="">-- Sélectionner un cours --</option>
                    <?php foreach ($cours as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo ($cours_id == $c['id']) ? 'selected' : ''; ?>>
                            <?php echo echapper($c['nom']) . ' (' . echapper($c['code']) . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label for="date-presence" class="form-label">Date</label>
                <input type="date" class="form-control" id="date-presence" name="date" value="<?php echo $date_presence; ?>" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i> Filtrer
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($cours_id > 0 && $coursSelectionne): ?>
    <!-- Formulaire de présence -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                Marquer la présence - <?php echo echapper($coursSelectionne['nom']); ?> (<?php echo echapper($coursSelectionne['code']); ?>)
                <br>
                <small class="text-muted">Date: <?php echo formaterDate($date_presence); ?></small>
            </h6>
            <div>
                <button type="button" id="select-all-present" class="btn btn-sm btn-success">Tous présents</button>
                <button type="button" id="select-all-absent" class="btn btn-sm btn-danger">Tous absents</button>
            </div>
        </div>
        <div class="card-body">
            <?php if (count($etudiants) > 0): ?>
                <form action="" method="POST">
                    <input type="hidden" name="cours_id" value="<?php echo $cours_id; ?>">
                    <input type="hidden" name="date" value="<?php echo $date_presence; ?>">
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Matricule</th>
                                    <th>Nom & Prénom</th>
                                    <th class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($etudiants as $etudiant): ?>
                                    <tr>
                                        <td><?php echo echapper($etudiant['matricule']); ?></td>
                                        <td><?php echo echapper($etudiant['prenom']) . ' ' . echapper($etudiant['nom']); ?></td>
                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" 
                                                           name="statut[<?php echo $etudiant['id']; ?>]" 
                                                           id="present-<?php echo $etudiant['id']; ?>" 
                                                           value="present" 
                                                           <?php echo ($etudiant['statut'] === 'present' || $etudiant['statut'] === null) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label text-success" for="present-<?php echo $etudiant['id']; ?>">
                                                        <i class="fas fa-check-circle me-1"></i> Présent
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" 
                                                           name="statut[<?php echo $etudiant['id']; ?>]" 
                                                           id="absent-<?php echo $etudiant['id']; ?>" 
                                                           value="absent" 
                                                           <?php echo ($etudiant['statut'] === 'absent') ? 'checked' : ''; ?>>
                                                    <label class="form-check-label text-danger" for="absent-<?php echo $etudiant['id']; ?>">
                                                        <i class="fas fa-times-circle me-1"></i> Absent
                                                    </label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3 text-center">
                        <button type="submit" name="enregistrer" value="1" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i> Enregistrer la présence
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Aucun étudiant n'est inscrit à ce cours. 
                    <a href="ajouter_etudiant.php" class="alert-link">Ajouter des étudiants au cours</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php elseif ($cours_id > 0): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle me-2"></i>
        Le cours sélectionné n'existe pas ou a été supprimé.
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        Veuillez sélectionner un cours et une date pour marquer les présences.
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
