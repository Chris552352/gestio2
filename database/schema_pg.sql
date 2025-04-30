-- Adaptation du schéma pour PostgreSQL
-- Remarque: Ce schéma est une adaptation temporaire pour PostgreSQL
-- Le projet final devra utiliser MySQL conformément aux exigences

-- Table des utilisateurs (enseignants et administrateurs)
CREATE TABLE IF NOT EXISTS utilisateurs (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role VARCHAR(10) NOT NULL DEFAULT 'enseignant' CHECK (role IN ('admin', 'enseignant')),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP NULL
);

-- Table des étudiants
CREATE TABLE IF NOT EXISTS etudiants (
    id SERIAL PRIMARY KEY,
    matricule VARCHAR(20) NOT NULL UNIQUE,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des cours
CREATE TABLE IF NOT EXISTS cours (
    id SERIAL PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    nom VARCHAR(150) NOT NULL,
    enseignant_id INTEGER REFERENCES utilisateurs(id) ON DELETE SET NULL,
    description TEXT NULL
);

-- Table de liaison entre étudiants et cours (inscriptions)
CREATE TABLE IF NOT EXISTS inscriptions (
    id SERIAL PRIMARY KEY,
    etudiant_id INTEGER NOT NULL REFERENCES etudiants(id) ON DELETE CASCADE,
    cours_id INTEGER NOT NULL REFERENCES cours(id) ON DELETE CASCADE,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (etudiant_id, cours_id)
);

-- Table des présences
CREATE TABLE IF NOT EXISTS presences (
    id SERIAL PRIMARY KEY,
    etudiant_id INTEGER NOT NULL REFERENCES etudiants(id) ON DELETE CASCADE,
    cours_id INTEGER NOT NULL REFERENCES cours(id) ON DELETE CASCADE,
    date_presence DATE NOT NULL,
    statut VARCHAR(10) NOT NULL DEFAULT 'present' CHECK (statut IN ('present', 'absent')),
    enregistre_par INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    date_enregistrement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (etudiant_id, cours_id, date_presence)
);

-- Données initiales pour démo (utilisateur admin)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role)
VALUES ('Admin', 'System', 'admin@example.com', '$2y$10$lHj7gQX1Kd2x1RCBQKL0T.0d/dQFXnJL81U5aOrCQpBfCsqSg3U/m', 'admin');
-- Mot de passe: admin123

-- Données de test supplémentaires
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
('INFO101', 'Introduction à l''informatique', 2, 'Cours d''introduction aux concepts fondamentaux de l''informatique'),
('MATH201', 'Mathématiques avancées', 2, 'Cours de mathématiques pour les sciences informatiques');

-- Inscriptions
INSERT INTO inscriptions (etudiant_id, cours_id)
VALUES 
(1, 1), (2, 1), (3, 1), (4, 1), (5, 1),  -- Tous les étudiants inscrits au cours INFO101
(1, 2), (3, 2), (5, 2);                  -- Quelques étudiants inscrits au cours MATH201

-- Présences (quelques exemples)
INSERT INTO presences (etudiant_id, cours_id, date_presence, statut, enregistre_par)
VALUES 
(1, 1, CURRENT_DATE - INTERVAL '3 days', 'present', 2),
(2, 1, CURRENT_DATE - INTERVAL '3 days', 'present', 2),
(3, 1, CURRENT_DATE - INTERVAL '3 days', 'absent', 2),
(4, 1, CURRENT_DATE - INTERVAL '3 days', 'present', 2),
(5, 1, CURRENT_DATE - INTERVAL '3 days', 'present', 2),

(1, 1, CURRENT_DATE - INTERVAL '2 days', 'present', 2),
(2, 1, CURRENT_DATE - INTERVAL '2 days', 'absent', 2),
(3, 1, CURRENT_DATE - INTERVAL '2 days', 'present', 2),
(4, 1, CURRENT_DATE - INTERVAL '2 days', 'present', 2),
(5, 1, CURRENT_DATE - INTERVAL '2 days', 'absent', 2),

(1, 2, CURRENT_DATE - INTERVAL '1 day', 'present', 2),
(3, 2, CURRENT_DATE - INTERVAL '1 day', 'present', 2),
(5, 2, CURRENT_DATE - INTERVAL '1 day', 'absent', 2);