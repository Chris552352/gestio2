<?php
// Page de gestion du profil utilisateur
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
verifierConnexion();

// Récupérer les informations de l'utilisateur connecté
try {
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['utilisateur_id']]);
    $utilisateur = $stmt->fetch();
    
    if (!$utilisateur) {
        $_SESSION['message_erreur'] = "Utilisateur non trouvé.";
        header('Location: dashboard.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['message_erreur'] = "Erreur lors de la récupération des données: " . $e->getMessage();
    header('Location: dashboard.php');
    exit();
}

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et validation des données
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $prenom = filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    // Validation basique
    $erreurs = [];
    
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
    
    // Vérifier si l'email existe déjà (pour un autre utilisateur)
    try {
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = :email AND id != :id");
        $stmt->execute([
            'email' => $email,
            'id' => $_SESSION['utilisateur_id']
        ]);
        
        $existant = $stmt->fetch();
        if ($existant) {
            $erreurs[] = "Un utilisateur avec cet email existe déjà.";
        }
    } catch (PDOException $e) {
        $erreurs[] = "Erreur lors de la vérification des données: " . $e->getMessage();
    }
    
    // Si pas d'erreurs, mettre à jour le profil
    if (empty($erreurs)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE utilisateurs 
                SET nom = :nom, prenom = :prenom, email = :email
                WHERE id = :id
            ");
            
            $stmt->execute([
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'id' => $_SESSION['utilisateur_id']
            ]);
            
            // Mise à jour des variables de session
            $_SESSION['nom'] = $nom;
            $_SESSION['prenom'] = $prenom;
            $_SESSION['email'] = $email;
            
            $_SESSION['message_succes'] = "Votre profil a été mis à jour avec succès.";
            
            // Redirection pour éviter la réexécution du code en cas de rafraîchissement
            header('Location: profil.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['message_erreur'] = "Erreur lors de la mise à jour du profil: " . $e->getMessage();
        }
    }
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Mon Profil</h1>
    <a href="changer_mdp.php" class="d-none d-sm-inline-block btn btn-primary shadow-sm">
        <i class="fas fa-key fa-sm text-white-50 me-2"></i> Changer le mot de passe
    </a>
</div>

<!-- Profil Utilisateur -->
<div class="row">
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Information Utilisateur</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="profile-avatar mx-auto">
                        <i class="fas fa-user"></i>
                    </div>
                    <h5 class="mt-3"><?php echo echapper($utilisateur['prenom']) . ' ' . echapper($utilisateur['nom']); ?></h5>
                    <p class="text-muted">
                        <i class="fas fa-user-tag me-2"></i>
                        <?php echo ($utilisateur['role'] === 'admin') ? 'Administrateur' : 'Enseignant'; ?>
                    </p>
                </div>
                
                <div class="mb-3">
                    <h6 class="font-weight-bold">Email</h6>
                    <p><?php echo echapper($utilisateur['email']); ?></p>
                </div>
                <div class="mb-3">
                    <h6 class="font-weight-bold">Date d'inscription</h6>
                    <p><?php echo formaterDateHeure($utilisateur['date_creation']); ?></p>
                </div>
                <div class="mb-3">
                    <h6 class="font-weight-bold">Dernière connexion</h6>
                    <p><?php echo formaterDateHeure($utilisateur['derniere_connexion']); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Modifier mon profil</h6>
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
                            <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo echapper($utilisateur['prenom']); ?>" required>
                            <div class="invalid-feedback">
                                Veuillez saisir votre prénom.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nom" name="nom" value="<?php echo echapper($utilisateur['nom']); ?>" required>
                            <div class="invalid-feedback">
                                Veuillez saisir votre nom.
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo echapper($utilisateur['email']); ?>" required>
                        <div class="invalid-feedback">
                            Veuillez saisir une adresse email valide.
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Mettre à jour mon profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if (estEnseignant()): ?>
            <!-- Statistiques des cours enseignés -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Mes Cours</h6>
                </div>
                <div class="card-body">
                    <?php
                    // Récupérer les cours enseignés par l'utilisateur
                    try {
                        $stmt = $pdo->prepare("
                            SELECT c.*, 
                                  (SELECT COUNT(*) FROM inscriptions WHERE cours_id = c.id) as nb_etudiants,
                                  (SELECT COUNT(DISTINCT date_presence) FROM presences WHERE cours_id = c.id) as nb_seances
                            FROM cours c
                            WHERE c.enseignant_id = :enseignant_id
                            ORDER BY c.nom
                        ");
                        $stmt->execute(['enseignant_id' => $_SESSION['utilisateur_id']]);
                        $cours_enseignes = $stmt->fetchAll();
                    } catch (PDOException $e) {
                        $_SESSION['message_erreur'] = "Erreur lors de la récupération des cours: " . $e->getMessage();
                        $cours_enseignes = [];
                    }
                    ?>
                    
                    <?php if (count($cours_enseignes) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Nom du cours</th>
                                        <th>Étudiants</th>
                                        <th>Séances</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cours_enseignes as $cours): ?>
                                        <tr>
                                            <td><?php echo echapper($cours['code']); ?></td>
                                            <td><?php echo echapper($cours['nom']); ?></td>
                                            <td>
                                                <span class="badge bg-primary text-white"><?php echo $cours['nb_etudiants']; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-white"><?php echo $cours['nb_seances']; ?></span>
                                            </td>
                                            <td>
                                                <a href="presence.php?cours_id=<?php echo $cours['id']; ?>" class="btn btn-sm btn-success btn-action" title="Marquer présence">
                                                    <i class="fas fa-clipboard-check"></i>
                                                </a>
                                                <a href="rapports.php?cours_id=<?php echo $cours['id']; ?>" class="btn btn-sm btn-primary btn-action" title="Rapport de présence">
                                                    <i class="fas fa-chart-bar"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Vous n'enseignez actuellement aucun cours.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
