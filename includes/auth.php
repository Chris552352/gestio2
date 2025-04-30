<?php
session_start();

// Fonction pour vérifier si l'utilisateur est connecté
function estConnecte() {
    return isset($_SESSION['utilisateur_id']);
}

// Fonction pour vérifier si l'utilisateur est un admin
function estAdmin() {
    return estConnecte() && $_SESSION['role'] === 'admin';
}

// Fonction pour vérifier si l'utilisateur est un enseignant
function estEnseignant() {
    return estConnecte() && $_SESSION['role'] === 'enseignant';
}

// Fonction pour rediriger vers la page de connexion si l'utilisateur n'est pas connecté
function verifierConnexion() {
    if (!estConnecte()) {
        $_SESSION['message_erreur'] = "Veuillez vous connecter pour accéder à cette page.";
        header('Location: login.php');
        exit();
    }
}

// Fonction d'authentification utilisateur
function authentifier($email, $mot_de_passe, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id, nom, prenom, email, mot_de_passe, role FROM utilisateurs WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $utilisateur = $stmt->fetch();
        
        if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
            // Mise à jour de la dernière connexion (adapté pour PostgreSQL)
            $stmt = $pdo->prepare("UPDATE utilisateurs SET derniere_connexion = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->execute(['id' => $utilisateur['id']]);
            
            // Création des variables de session
            $_SESSION['utilisateur_id'] = $utilisateur['id'];
            $_SESSION['nom'] = $utilisateur['nom'];
            $_SESSION['prenom'] = $utilisateur['prenom'];
            $_SESSION['email'] = $utilisateur['email'];
            $_SESSION['role'] = $utilisateur['role'];
            
            return true;
        }
        return false;
    } catch (PDOException $e) {
        // En cas d'erreur, on retourne false
        return false;
    }
}

// Fonction de déconnexion
function deconnecter() {
    // Supprimer toutes les variables de session
    $_SESSION = array();
    
    // Détruire la session
    session_destroy();
}
?>
