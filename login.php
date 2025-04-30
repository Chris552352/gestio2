<?php
// Page de connexion
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Affichage du message de mode démo si nécessaire
$mode_demo = isset($_SESSION['mode_demo']) && $_SESSION['mode_demo'] === true;

// Si l'utilisateur est déjà connecté, rediriger vers le tableau de bord
if (estConnecte()) {
    header('Location: dashboard.php');
    exit();
}

$erreur = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et validation des données
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $erreur = "Veuillez remplir tous les champs";
    } else {
        // Tentative d'authentification
        if (authentifier($email, $password, $pdo)) {
            // Redirection vers le tableau de bord
            header('Location: dashboard.php');
            exit();
        } else {
            $erreur = "Identifiants incorrects. Veuillez réessayer.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Système de Gestion de Présence</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Style personnalisé -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="container">
        <!-- Bannière d'images décorative en haut -->
        <div class="image-banner">
            <img src="assets/images/students.svg" alt="Étudiants" width="150">
            <img src="assets/images/courses.svg" alt="Cours" width="150">
            <img src="assets/images/attendance.svg" alt="Présence" width="150">
        </div>
        
        <div class="login-container">
            <div class="login-logo text-center">
                <img src="assets/images/logo.svg" alt="Logo" class="mb-3">
                <h2 class="text-bleu">Gestion de Présence</h2>
                <p class="text-muted">Connectez-vous pour accéder au système</p>
            </div>
            
            <?php if ($mode_demo): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-info-circle me-2"></i>Mode démonstration activé - Connexion sans base de données (uniquement pour visualiser l'interface)
                </div>
            <?php endif; ?>
            
            <?php if (!empty($erreur)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $erreur; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['message_succes'])): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['message_succes']; ?>
                </div>
                <?php unset($_SESSION['message_succes']); ?>
            <?php endif; ?>
            
            <form method="post" action="" class="needs-validation" novalidate>
                <!-- Champ Email -->
                <div class="mb-4">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-bleu text-white"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="exemple@email.com" required>
                    </div>
                    <small class="text-muted">Entrez l'adresse email de votre compte.</small>
                </div>
                
                <!-- Champ Mot de passe -->
                <div class="mb-4">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text bg-bleu text-white"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <small class="text-muted">Entrez votre mot de passe personnel.</small>
                </div>
                
                <!-- Option Se souvenir de moi -->
                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Se souvenir de moi</label>
                </div>
                
                <!-- Bouton de connexion -->
                <div class="d-grid gap-2 mb-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i> Se connecter
                    </button>
                </div>
                
                <!-- Lien mot de passe oublié -->
                <div class="text-center mb-3">
                    <a href="#" class="text-decoration-none text-bleu">Mot de passe oublié ?</a>
                </div>
            </form>
            
            <!-- Aide à la connexion -->
            <div class="mt-4 p-3 bg-light rounded">
                <h5 class="text-center text-bleu mb-2">Besoin d'aide ?</h5>
                <p class="small text-center">
                    Pour une démonstration, utilisez:<br>
                    <strong>Email:</strong> chris552352@gmail.com<br>
                    <strong>Mot de passe:</strong> 552352
                </p>
            </div>
        </div>
        
        <!-- Pied de page -->
        <div class="text-center mt-4 text-white">
            <p>&copy; <?php echo date('Y'); ?> Système de Gestion de Présence. Tous droits réservés.</p>
        </div>
    </div>
    
    <!-- jQuery et Bootstrap JS -->
    <script src="vendor/jquery/jquery-3.6.0.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
