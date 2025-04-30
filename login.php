<?php
// Page de connexion
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

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
<body class="bg-light">
    <div class="container">
        <div class="login-container">
            <div class="login-logo text-center mb-4">
                <i class="fas fa-user-check mb-2"></i>
                <h2>Gestion de Présence</h2>
                <p class="text-muted">Connectez-vous pour accéder au système</p>
            </div>
            
            <?php if (!empty($erreur)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $erreur; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['message_succes'])): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $_SESSION['message_succes']; ?>
                </div>
                <?php unset($_SESSION['message_succes']); ?>
            <?php endif; ?>
            
            <form method="post" action="" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="votre@email.com" required>
                        <div class="invalid-feedback">
                            Veuillez saisir une adresse email valide.
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="invalid-feedback">
                            Veuillez saisir votre mot de passe.
                        </div>
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Se souvenir de moi</label>
                </div>
                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i> Se connecter
                    </button>
                </div>
                <div class="text-center">
                    <a href="#" class="text-decoration-none">Mot de passe oublié ?</a>
                </div>
            </form>
        </div>
        
        <div class="text-center mt-4 text-muted">
            <p>&copy; <?php echo date('Y'); ?> Système de Gestion de Présence. Tous droits réservés.</p>
        </div>
    </div>
    
    <!-- jQuery et Bootstrap JS -->
    <script src="vendor/jquery/jquery-3.6.0.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Validation du formulaire
        (function() {
            'use strict';
            
            var forms = document.querySelectorAll('.needs-validation');
            
            Array.from(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>
