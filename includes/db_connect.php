<?php
// Connexion à la base de données MySQL pour WAMP
// Configuration pour environnement local WAMP

// Configuration MySQL
$host = 'localhost';
$port = '3306'; // Port par défaut de MySQL
$dbname = 'gestion_presence';
$username = 'root'; // Utilisateur par défaut de WAMP
$password = ''; // Mot de passe par défaut (vide) pour WAMP

// Options de connexion PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Création de l'instance PDO avec MySQL
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // En cas d'erreur de connexion, afficher un message
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}
?>
