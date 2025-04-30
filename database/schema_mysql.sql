-- Schéma pour MySQL (WAMP) 
-- À utiliser avec phpMyAdmin dans l'environnement WAMP

-- Table des utilisateurs (enseignants et administrateurs)
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'enseignant') NOT NULL DEFAULT 'enseignant',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des étudiants
CREATE TABLE IF NOT EXISTS etudiants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricule VARCHAR(20) NOT NULL UNIQUE,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des cours
CREATE TABLE IF NOT EXISTS cours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    nom VARCHAR(150) NOT NULL,
    enseignant_id INT,
    description TEXT NULL,
    FOREIGN KEY (enseignant_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table de liaison entre étudiants et cours (inscriptions)
CREATE TABLE IF NOT EXISTS inscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    cours_id INT NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (etudiant_id, cours_id),
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE,
    FOREIGN KEY (cours_id) REFERENCES cours(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des présences
CREATE TABLE IF NOT EXISTS presences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    cours_id INT NOT NULL,
    date_presence DATE NOT NULL,
    statut ENUM('present', 'absent') NOT NULL DEFAULT 'present',
    enregistre_par INT NOT NULL,
    date_enregistrement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (etudiant_id, cours_id, date_presence),
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE,
    FOREIGN KEY (cours_id) REFERENCES cours(id) ON DELETE CASCADE,
    FOREIGN KEY (enregistre_par) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données initiales pour démo (utilisateur admin)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role)
VALUES ('Admin', 'System', 'admin@example.com', '$2y$10$lHj7gQX1Kd2x1RCBQKL0T.0d/dQFXnJL81U5aOrCQpBfCsqSg3U/m', 'admin');
-- Mot de passe: admin123

-- Utilisateur enseignant (avec les identifiants spécifiés)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role)
VALUES ('Dupont', 'Chris', 'chris552352@gmail.com', '$2y$10$.Gltio/0jwGtIIpIUXBOp./5QwJQe7FQ1ZIXCGCAFSOV8f3WXTLeu', 'enseignant');
-- Mot de passe: 552352

-- Enseignants
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role)
VALUES ('Dupont', 'Jean', 'jean.dupont@example.com', '$2y$10$lHj7gQX1Kd2x1RCBQKL0T.0d/dQFXnJL81U5aOrCQpBfCsqSg3U/m', 'enseignant');

-- Étudiants
INSERT INTO etudiants (matricule, nom, prenom, email)
VALUES 
('ET001', 'Martin', 'Sophie', 'sophie.martin@example.com'),
('ET002', 'Lefebvre', 'Thomas', 'thomas.lefebvre@example.com'),
('ET003', 'Dubois', 'Emma', 'emma.dubois@example.com'),
('ET004', 'Leroy', 'Lucas', 'lucas.leroy@example.com'),
('ET005', 'Moreau', 'Chloé', 'chloe.moreau@example.com');

-- Cours
INSERT INTO cours (code, nom, enseignant_id, description)
VALUES 
('INFO101', 'Introduction à l''informatique', 3, 'Cours d''introduction aux concepts fondamentaux de l''informatique'),
('MATH201', 'Mathématiques avancées', 3, 'Cours de mathématiques pour les sciences informatiques');

-- Inscriptions
INSERT INTO inscriptions (etudiant_id, cours_id)
VALUES 
(1, 1), (2, 1), (3, 1), (4, 1), (5, 1),  -- Tous les étudiants inscrits au cours INFO101
(1, 2), (3, 2), (5, 2);                  -- Quelques étudiants inscrits au cours MATH201

-- Présences (quelques exemples)
INSERT INTO presences (etudiant_id, cours_id, date_presence, statut, enregistre_par)
VALUES 
(1, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'present', 3),
(2, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'present', 3),
(3, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'absent', 3),
(4, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'present', 3),
(5, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'present', 3),

(1, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'present', 3),
(2, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'absent', 3),
(3, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'present', 3),
(4, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'present', 3),
(5, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'absent', 3),

(1, 2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'present', 3),
(3, 2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'present', 3),
(5, 2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'absent', 3);