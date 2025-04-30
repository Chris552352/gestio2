<?php 
// Démarrage de la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Présence Étudiants</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    
    <!-- Font Awesome CSS pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Style personnalisé -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php if(isset($_SESSION['utilisateur_id'])): ?>
                <!-- Sidebar (visible seulement si connecté) -->
                <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                    <?php include_once 'includes/sidebar.php'; ?>
                </div>
                
                <!-- Contenu principal -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                    <!-- En-tête avec barre de navigation -->
                    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
                        <div class="container-fluid">
                            <button class="navbar-toggler d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target=".sidebar" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            <span class="navbar-brand mb-0 h1">Système de Gestion de Présence</span>
                            <div class="d-flex">
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-user me-1"></i> <?php echo $_SESSION['prenom'] . ' ' . $_SESSION['nom']; ?>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                        <li><a class="dropdown-item" href="profil.php"><i class="fas fa-id-card me-2"></i>Profil</a></li>
                                        <li><a class="dropdown-item" href="changer_mdp.php"><i class="fas fa-key me-2"></i>Changer Mot de Passe</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="deconnexion.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </nav>
                    
                    <!-- Affichage des messages d'alerte -->
                    <?php if(isset($_SESSION['message_succes'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['message_succes']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                        </div>
                        <?php unset($_SESSION['message_succes']); ?>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['message_erreur'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['message_erreur']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                        </div>
                        <?php unset($_SESSION['message_erreur']); ?>
                    <?php endif; ?>
            <?php else: ?>
                <!-- Si non connecté, utiliser une mise en page différente -->
                <main class="col-12 px-md-4 py-4">
            <?php endif; ?>
