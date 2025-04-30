<?php
// Page pour ajouter ou modifier un cours
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
verifierConnexion();

// Variables pour le formulaire
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$cours = [
    'code' => '',
    'nom' => '',
    'enseignant_id' => '',
    'description' => ''
];
$titre = "Ajouter un nouveau cours";
$bouton = "Ajouter";
$mode = "ajout";

// Si mode édition, récupérer les données du cours
if ($id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM cours WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $coursExistant = $stmt->fetch();
        
        if ($coursExistant) {
            $cours = $coursExistant;
            $titre = "Modifier le cours";
            $bouton = "Mettre à jour";
            $mode = "edition";
        } else {
            $_SESSION['message_erreur'] = "Cours non trouvé.";
            header('Location: cours.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['message_erreur'] = "Erreur lors de la récupération des données: " . $e->getMessage();
        header('Location: cours.php');
        exit();
    }
}

// Récupérer la liste des enseignants
try {
    $stmt = $pdo->query("
        SELECT id, CONCAT(prenom, ' ', nom) as nom_complet 
        FROM utilisateurs 
        WHERE role = 'enseignant' OR role = 'admin'
        ORDER BY nom, prenom
    ");
    $enseignants = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['message_erreur'] = "Erreur lors de la récupération des enseignants: " . $e->getMessage();
    $enseignants = [];
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et validation des données
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $enseignant_id = filter_input(INPUT_POST, 'enseignant_id', FILTER_SANITIZE_NUMBER_INT);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    
    // Si enseignant_id est vide, le définir à NULL
    if (empty($enseignant_id)) {
        $enseignant_id = null;
    }
    
    // Validation basique
    $erreurs = [];
    
    if (empty($code)) {
        $erreurs[] = "Le code du cours est obligatoire.";
    }
    
    if (empty($nom)) {
        $erreurs[] = "Le nom du cours est obligatoire.";
    }
    
    // Vérifier si le code existe déjà
    try {
        $stmt = $pdo->prepare("SELECT id FROM cours WHERE code = :code AND id != :id");
        $stmt->execute([
            'code' => $code,
            'id' => $id
        ]);
        
        $existant = $stmt->fetch();
        if ($existant) {
            $erreurs[] = "Un cours avec ce code existe déjà.";
        }
    } catch (PDOException $e) {
        $erreurs[] = "Erreur lors de la vérification des données: " . $e->getMessage();
    }
    
    // Si pas d'erreurs, enregistrer
    if (empty($erreurs)) {
        try {
            if ($mode === "ajout") {
                // Insertion d'un nouveau cours
                $stmt = $pdo->prepare("
                    INSERT INTO cours (code, nom, enseignant_id, description) 
                    VALUES (:code, :nom, :enseignant_id, :description)
                ");
                
                $stmt->execute([
                    'code' => $code,
                    'nom' => $nom,
                    'enseignant_id' => $enseignant_id,
                    'description' => $description
                ]);
                
                $_SESSION['message_succes'] = "Le cours a été ajouté avec succès.";
            } else {
                // Mise à jour d'un cours existant
                $stmt = $pdo->prepare("
                    UPDATE cours 
                    SET code = :code, nom = :nom, enseignant_id = :enseignant_id, description = :description
                    WHERE id = :id
                ");
                
                $stmt->execute([
                    'code' => $code,
                    'nom' => $nom,
                    'enseignant_id' => $enseignant_id,
                    'description' => $description,
                    'id' => $id
                ]);
                
                $_SESSION['message_succes'] = "Le cours a été mis à jour avec succès.";
            }
            
            // Redirection
            header('Location: cours.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['message_erreur'] = "Erreur lors de l'enregistrement: " . $e->getMessage();
        }
    }
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?php echo $titre; ?></h1>
    <a href="cours.php" class="d-none d-sm-inline-block btn btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50 me-2"></i> Retour à la liste
    </a>
</div>

<!-- Formulaire d'ajout/modification -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Informations du cours</h6>
    </div>
    <div class="card-body">
        <?php if (isset($erreurs) && !empty($erreurs)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($erreurs as $erreur): ?>
                        <li><?php echo $erreur; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="" method="post" class="needs-validation" novalidate>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="code" class="form-label">Code du cours <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="code" name="code" value="<?php echo echapper($cours['code']); ?>" required>
                    <div class="invalid-feedback">
                        Veuillez saisir le code du cours.
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="nom" class="form-label">Nom du cours <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo echapper($cours['nom']); ?>" required>
                    <div class="invalid-feedback">
                        Veuillez saisir le nom du cours.
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="enseignant_id" class="form-label">Enseignant responsable</label>
                <select class="form-select" id="enseignant_id" name="enseignant_id">
                    <option value="">-- Sélectionner un enseignant --</option>
                    <?php foreach ($enseignants as $enseignant): ?>
                        <option value="<?php echo $enseignant['id']; ?>" <?php echo ($cours['enseignant_id'] == $enseignant['id']) ? 'selected' : ''; ?>>
                            <?php echo echapper($enseignant['nom_complet']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description du cours</label>
                <textarea class="form-control" id="description" name="description" rows="4"><?php echo echapper($cours['description']); ?></textarea>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i> <?php echo $bouton; ?>
                </button>
                <a href="cours.php" class="btn btn-secondary ms-2">
                    <i class="fas fa-times me-2"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
