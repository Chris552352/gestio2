<?php
// Connexion à la base de données
// Remarque: Utilisation temporaire de PostgreSQL pour ce déploiement
// Le projet final devra utiliser MySQL conformément aux exigences

// Récupération des variables d'environnement
$host = getenv('PGHOST') ?: 'localhost';
$port = getenv('PGPORT') ?: '5432';
$dbname = getenv('PGDATABASE') ?: 'gestion_presence';
$username = getenv('PGUSER') ?: 'root';
$password = getenv('PGPASSWORD') ?: '';

// Options de connexion PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Création de l'instance PDO avec PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // En cas d'erreur de connexion, afficher un message
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}
?>
