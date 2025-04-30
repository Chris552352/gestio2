<?php
// Page d'accueil (redirection intelligente)
session_start();
require_once 'includes/auth.php';

// Si l'utilisateur est déjà connecté, rediriger vers le tableau de bord
if (estConnecte()) {
    header('Location: dashboard.php');
    exit();
} else {
    // Sinon, rediriger vers la page de connexion
    header('Location: login.php');
    exit();
}
?>
