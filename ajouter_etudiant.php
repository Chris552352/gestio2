<?php
// Page pour ajouter ou modifier un étudiant
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
verifierConnexion();

// Variables pour le formulaire
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$etudiant = [
    'matricule' => '',
    'nom' => '',
    'prenom' => '',
    'email' => ''
];
$titre = "Ajouter un nouvel étudiant";
$bouton = "Ajouter";
$mode = "ajout";

// Si mode édition, récupérer les données de l'étudiant
if ($id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $etudiantExistant = $stmt->fetch();
        
        if ($etudiantExistant) {
            $etudiant = $etudiantExistant;
            $titre = "Modifier l'étudiant";
            $bouton = "Mettre à jour";
            $mode = "edition";
        } else {
            $_SESSION['message_erreur'] = "Étudiant non trouvé.";
            header('Location: etudiants.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['message_erreur'] = "Erreur lors de la récupération des données: " . $e->getMessage();
        header('Location: etudiants.php');
        exit();
    }
}

// Récupérer la liste des cours pour les inscriptions
try {
    $stmt = $pdo->query("SELECT * FROM cours ORDER BY nom");
    $cours = $stmt->fetchAll();
    
    // Si en mode édition, récupérer les cours auxquels l'étudiant est inscrit
    $coursInscrit = [];
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT cours_id FROM inscriptions WHERE etudiant_id = :etudiant_id");
        $stmt->execute(['etudiant_id' => $id]);
        $inscriptions = $stmt->fetchAll();
        
        foreach ($inscriptions as $inscription) {
            $coursInscrit[] = $inscription['cours_id'];
        }
    }
} catch (PDOException $e) {
    $_SESSION['message_erreur'] = "Erreur lors de la récupération des cours: " . $e->getMessage();
    $cours = [];
    $coursInscrit = [];
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et validation des données
    $matricule = filter_input(INPUT_POST, 'matricule', FILTER_SANITIZE_STRING);
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $prenom = filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $coursSelectionnes = isset($_POST['cours']) ? $_POST['cours'] : [];
    
    // Validation basique
    $erreurs = [];
    
    if (empty($matricule)) {
        $erreurs[] = "Le matricule est obligatoire.";
    }
    
    if (empty($nom)) {
        $erreurs[] = "Le nom est obligatoire.";
    }
    
    if (empty($prenom)) {
        $erreurs[] = "Le prénom est obligatoire.";
    }
    
    if (empty($email)) {
        $erreurs[] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'email n'est pas valide.";
    }
    
    // Vérifier si le matricule ou l'email existe déjà
    try {
        $stmt = $pdo->prepare("SELECT id FROM etudiants WHERE (matricule = :matricule OR email = :email) AND id != :id");
        $stmt->execute([
            'matricule' => $matricule,
            'email' => $email,
            'id' => $id
        ]);
        
        $existant = $stmt->fetch();
        if ($existant) {
            $erreurs[] = "Un étudiant avec ce matricule ou cet email existe déjà.";
        }
    } catch (PDOException $e) {
        $erreurs[] = "Erreur lors de la vérification des données: " . $e->getMessage();
    }
    
    // Si pas d'erreurs, enregistrer
    if (empty($erreurs)) {
        try {
            $pdo->beginTransaction();
            
            if ($mode === "ajout") {
                // Insertion d'un nouvel étudiant
                $stmt = $pdo->prepare("
                    INSERT INTO etudiants (matricule, nom, prenom, email) 
                    VALUES (:matricule, :nom, :prenom, :email)
                ");
                $stmt->execute([
                    'matricule' => $matricule,
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email
                ]);
                
                $nouvelId = $pdo->lastInsertId();
                
                // Inscriptions aux cours
                foreach ($coursSelectionnes as $coursId) {
                    $stmt = $pdo->prepare("
                        INSERT INTO inscriptions (etudiant_id, cours_id) 
                        VALUES (:etudiant_id, :cours_id)
                    ");
                    $stmt->execute([
                        'etudiant_id' => $nouvelId,
                        'cours_id' => $coursId
                    ]);
                }
                
                $_SESSION['message_succes'] = "L'étudiant a été ajouté avec succès.";
            } else {
                // Mise à jour d'un étudiant existant
                $stmt = $pdo->prepare("
                    UPDATE etudiants 
                    SET matricule = :matricule, nom = :nom, prenom = :prenom, email = :email
                    WHERE id = :id
                ");
                $stmt->execute([
                    'matricule' => $matricule,
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'id' => $id
                ]);
                
                // Supprimer toutes les inscriptions
                $stmt = $pdo->prepare("DELETE FROM inscriptions WHERE etudiant_id = :etudiant_id");
                $stmt->execute(['etudiant_id' => $id]);
                
                // Ajouter les nouvelles inscriptions
                foreach ($coursSelectionnes as $coursId) {
                    $stmt = $pdo->prepare("
                        INSERT INTO inscriptions (etudiant_id, cours_id) 
                        VALUES (:etudiant_id, :cours_id)
                    ");
                    $stmt->execute([
                        'etudiant_id' => $id,
                        'cours_id' => $coursId
                    ]);
                }
                
                $_SESSION['message_succes'] = "L'étudiant a été mis à jour avec succès.";
            }
            
            $pdo->commit();
            
            // Redirection
            header('Location: etudiants.php');
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
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
    <a href="etudiants.php" class="d-none d-sm-inline-block btn btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50 me-2"></i> Retour à la liste
    </a>
</div>

<!-- Formulaire d'ajout/modification -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Informations de l'étudiant</h6>
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
                    <label for="matricule" class="form-label">Matricule <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="matricule" name="matricule" value="<?php echo echapper($etudiant['matricule']); ?>" required>
                    <div class="invalid-feedback">
                        Veuillez saisir le matricule de l'étudiant.
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo echapper($etudiant['email']); ?>" required>
                    <div class="invalid-feedback">
                        Veuillez saisir une adresse email valide.
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo echapper($etudiant['nom']); ?>" required>
                    <div class="invalid-feedback">
                        Veuillez saisir le nom de l'étudiant.
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo echapper($etudiant['prenom']); ?>" required>
                    <div class="invalid-feedback">
                        Veuillez saisir le prénom de l'étudiant.
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Inscription aux cours</label>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                    <?php foreach ($cours as $c): ?>
                        <div class="col">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="<?php echo $c['id']; ?>" id="cours<?php echo $c['id']; ?>" name="cours[]" 
                                    <?php echo ($mode === "edition" && in_array($c['id'], $coursInscrit)) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="cours<?php echo $c['id']; ?>">
                                    <?php echo echapper($c['nom']); ?> (<?php echo echapper($c['code']); ?>)
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (empty($cours)): ?>
                    <div class="alert alert-warning mt-2">Aucun cours disponible. <a href="ajouter_cours.php">Ajouter un cours</a></div>
                <?php endif; ?>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i> <?php echo $bouton; ?>
                </button>
                <a href="etudiants.php" class="btn btn-secondary ms-2">
                    <i class="fas fa-times me-2"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
