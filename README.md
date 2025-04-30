# Système de Gestion de Présence Étudiante

Ce système de gestion de présence a été développé pour les établissements d'enseignement, permettant aux enseignants de suivre l'assiduité des étudiants.

## Technologies utilisées

- HTML, CSS (Bootstrap 5)
- JavaScript, jQuery
- PHP
- MySQL
- Chart.js (pour les graphiques)

## Instructions d'installation sur WAMP

1. **Cloner ou télécharger le projet**
   - Téléchargez le code sous forme d'archive ZIP et extrayez-le
   - Placez le dossier dans le répertoire `www` de votre installation WAMP (généralement `C:\wamp64\www\`)

2. **Configurer la base de données**
   - Lancez WAMP et assurez-vous que le service MySQL est démarré
   - Ouvrez phpMyAdmin (http://localhost/phpmyadmin)
   - Créez une nouvelle base de données nommée `gestion_presence`
   - Sélectionnez la base de données et importez le fichier `database/schema_mysql.sql` fourni avec le projet

3. **Vérifier la configuration de connexion à la base de données**
   - Ouvrez le fichier `includes/db_connect.php` et vérifiez que les paramètres de connexion correspondent à votre environnement :
   ```php
   $host = 'localhost';
   $port = '3306'; // Port par défaut de MySQL
   $dbname = 'gestion_presence';
   $username = 'root'; // Utilisateur par défaut de WAMP
   $password = ''; // Mot de passe par défaut (vide) pour WAMP
   ```
   - Modifiez ces valeurs si nécessaire (notamment le mot de passe si vous avez défini un mot de passe pour l'utilisateur MySQL root)

4. **Accéder à l'application**
   - Ouvrez votre navigateur web et accédez à l'URL suivante : `http://localhost/gestion_presence/`
   - Vous serez redirigé vers la page de connexion

## Comptes par défaut

Deux comptes utilisateurs sont créés par défaut :

1. **Administrateur**
   - Email : admin@example.com
   - Mot de passe : admin123

2. **Enseignant**
   - Email : chris552352@gmail.com
   - Mot de passe : 552352

## Fonctionnalités principales

- **Gestion des étudiants** : Ajouter, modifier et supprimer des étudiants
- **Gestion des cours** : Créer et gérer des cours
- **Enregistrement des présences** : Marquer la présence des étudiants pour chaque cours et date
- **Rapports détaillés** : Générer des rapports statistiques de présence
- **Tableau de bord** : Visualiser les statistiques à l'aide de graphiques

## Structure du projet

- `assets/` : Fichiers CSS, JavaScript et images
- `database/` : Scripts SQL pour la création de la base de données
- `includes/` : Fichiers PHP d'inclusion (connexion BDD, fonctions utilitaires, authentification)
- `vendor/` : Bibliothèques externes (Bootstrap, jQuery, Chart.js)
- Fichiers PHP principaux : `login.php`, `dashboard.php`, `etudiants.php`, etc.

## Remarques importantes

- Ce système a été développé et testé avec PHP 8.x et MySQL 8.x
- Assurez-vous que les extensions PDO et pdo_mysql sont activées dans votre configuration PHP