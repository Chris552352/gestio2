<?php
// Fonctions utilitaires pour le système de gestion de présence

// Échapper les données pour éviter les injections HTML/JS
function echapper($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Fonction pour formater une date au format français
function formaterDate($date) {
    if (!$date) return '';
    $dateObj = new DateTime($date);
    return $dateObj->format('d/m/Y');
}

// Fonction pour formater une date avec l'heure au format français
function formaterDateHeure($dateHeure) {
    if (!$dateHeure) return '';
    $dateObj = new DateTime($dateHeure);
    return $dateObj->format('d/m/Y à H:i');
}

// Fonction pour obtenir le nombre total d'étudiants
function obtenirNombreEtudiants($pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM etudiants");
        $resultat = $stmt->fetch();
        return $resultat['total'];
    } catch (PDOException $e) {
        return 0;
    }
}

// Fonction pour obtenir le nombre total de cours
function obtenirNombreCours($pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM cours");
        $resultat = $stmt->fetch();
        return $resultat['total'];
    } catch (PDOException $e) {
        return 0;
    }
}

// Fonction pour obtenir le nombre de présences aujourd'hui
function obtenirPresencesAujourdhui($pdo) {
    try {
        // Adapté pour MySQL: CURDATE()
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM presences WHERE date_presence = CURDATE() AND statut = 'present'");
        $stmt->execute();
        $resultat = $stmt->fetch();
        return $resultat['total'];
    } catch (PDOException $e) {
        return 0;
    }
}

// Fonction pour obtenir le nombre d'absences aujourd'hui
function obtenirAbsencesAujourdhui($pdo) {
    try {
        // Adapté pour MySQL: CURDATE()
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM presences WHERE date_presence = CURDATE() AND statut = 'absent'");
        $stmt->execute();
        $resultat = $stmt->fetch();
        return $resultat['total'];
    } catch (PDOException $e) {
        return 0;
    }
}

// Fonction pour obtenir tous les cours
function obtenirTousLesCours($pdo) {
    try {
        // Adapté pour MySQL: CONCAT() pour la concaténation
        $stmt = $pdo->query("
            SELECT c.*, CONCAT(u.prenom, ' ', u.nom) as nom_enseignant 
            FROM cours c
            LEFT JOIN utilisateurs u ON c.enseignant_id = u.id
            ORDER BY c.nom ASC
        ");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Fonction pour obtenir un cours par ID
function obtenirCoursParId($pdo, $id) {
    try {
        // Adapté pour MySQL: CONCAT() pour la concaténation
        $stmt = $pdo->prepare("
            SELECT c.*, CONCAT(u.prenom, ' ', u.nom) as nom_enseignant 
            FROM cours c
            LEFT JOIN utilisateurs u ON c.enseignant_id = u.id
            WHERE c.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

// Fonction pour obtenir tous les étudiants
function obtenirTousLesEtudiants($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM etudiants ORDER BY nom, prenom");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Fonction pour obtenir un étudiant par ID
function obtenirEtudiantParId($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

// Fonction pour obtenir les cours d'un étudiant
function obtenirCoursEtudiant($pdo, $etudiantId) {
    try {
        $stmt = $pdo->prepare("
            SELECT c.* 
            FROM cours c
            JOIN inscriptions i ON c.id = i.cours_id
            WHERE i.etudiant_id = :etudiantId
        ");
        $stmt->execute(['etudiantId' => $etudiantId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Fonction pour obtenir les étudiants inscrits à un cours
function obtenirEtudiantsCours($pdo, $coursId) {
    try {
        $stmt = $pdo->prepare("
            SELECT e.* 
            FROM etudiants e
            JOIN inscriptions i ON e.id = i.etudiant_id
            WHERE i.cours_id = :coursId
            ORDER BY e.nom, e.prenom
        ");
        $stmt->execute(['coursId' => $coursId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Fonction pour vérifier si un étudiant est inscrit à un cours
function estInscritAuCours($pdo, $etudiantId, $coursId) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM inscriptions 
            WHERE etudiant_id = :etudiantId AND cours_id = :coursId
        ");
        $stmt->execute([
            'etudiantId' => $etudiantId,
            'coursId' => $coursId
        ]);
        $resultat = $stmt->fetch();
        return $resultat['total'] > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// Fonction pour obtenir les présences d'un cours à une date spécifique
function obtenirPresencesCours($pdo, $coursId, $date) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, e.matricule, e.nom, e.prenom 
            FROM presences p
            JOIN etudiants e ON p.etudiant_id = e.id
            WHERE p.cours_id = :coursId AND p.date_presence = :date
            ORDER BY e.nom, e.prenom
        ");
        $stmt->execute([
            'coursId' => $coursId,
            'date' => $date
        ]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Fonction pour obtenir les statistiques de présence pour un cours
function obtenirStatistiquesCours($pdo, $coursId) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN statut = 'present' THEN 1 ELSE 0 END) as total_present,
                SUM(CASE WHEN statut = 'absent' THEN 1 ELSE 0 END) as total_absent,
                COUNT(*) as total
            FROM presences
            WHERE cours_id = :coursId
        ");
        $stmt->execute(['coursId' => $coursId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return [
            'total_present' => 0,
            'total_absent' => 0,
            'total' => 0
        ];
    }
}

// Fonction pour obtenir les statistiques de présence pour un étudiant
function obtenirStatistiquesEtudiant($pdo, $etudiantId) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN statut = 'present' THEN 1 ELSE 0 END) as total_present,
                SUM(CASE WHEN statut = 'absent' THEN 1 ELSE 0 END) as total_absent,
                COUNT(*) as total
            FROM presences
            WHERE etudiant_id = :etudiantId
        ");
        $stmt->execute(['etudiantId' => $etudiantId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return [
            'total_present' => 0,
            'total_absent' => 0,
            'total' => 0
        ];
    }
}

// Fonction pour obtenir les statistiques globales de présence
function obtenirStatistiquesGlobales($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                SUM(CASE WHEN statut = 'present' THEN 1 ELSE 0 END) as total_present,
                SUM(CASE WHEN statut = 'absent' THEN 1 ELSE 0 END) as total_absent,
                COUNT(*) as total
            FROM presences
        ");
        return $stmt->fetch();
    } catch (PDOException $e) {
        return [
            'total_present' => 0,
            'total_absent' => 0,
            'total' => 0
        ];
    }
}

// Fonction pour obtenir les statistiques de présence par mois
function obtenirStatistiquesParMois($pdo, $annee = null) {
    if (!$annee) {
        $annee = date('Y');
    }
    
    try {
        // Adapté pour MySQL: utilisation de MONTH() et YEAR()
        $stmt = $pdo->prepare("
            SELECT 
                MONTH(date_presence) as mois,
                SUM(CASE WHEN statut = 'present' THEN 1 ELSE 0 END) as total_present,
                SUM(CASE WHEN statut = 'absent' THEN 1 ELSE 0 END) as total_absent
            FROM presences
            WHERE YEAR(date_presence) = :annee
            GROUP BY MONTH(date_presence)
            ORDER BY MONTH(date_presence)
        ");
        $stmt->execute(['annee' => $annee]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Fonction pour obtenir un utilisateur par son ID
function obtenirUtilisateurParId($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

// Fonction pour obtenir tous les enseignants
function obtenirTousLesEnseignants($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, nom, prenom FROM utilisateurs WHERE role = 'enseignant' ORDER BY nom, prenom");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Générer un message d'alerte (bootstrap)
function afficherAlerte($message, $type = 'success') {
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>';
}
?>
