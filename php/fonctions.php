<?php
// Inclure le fichier de connexion
require_once 'connect_db.php';


function countRecords(PDO $conn, string $tableName): int {
    try {
        // Préparer la requête
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM $tableName");
        $stmt->execute();
        $result = $stmt->fetch();

        // Retourner le nombre total
        return (int)$result['total'];

    } catch (PDOException $e) {
        error_log("Erreur lors du comptage des enregistrements de $tableName : " . $e->getMessage());
        return 0; // Retourne 0 en cas d'erreur
    }
}


//Fonction pour compter le nombre d'employés actifs
function countActiveEmployees(PDO $conn): int {
    try {
        // Préparer la requête SQL sécurisée
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM employee WHERE etat = :etat");
        $stmt->execute(['etat' => 'actif']);
        
        // Récupérer le résultat
        $result = $stmt->fetch();

        return (int)$result['total'];

    } catch (PDOException $e) {
        error_log("Erreur lors du comptage des employés actifs : " . $e->getMessage());
        return 0;
    }
}

//Fonction pour compter le nombre de contrats actifs
function countActiveContrats(PDO $conn): int {
    try {
        // Requête SQL
        $sql = "
            SELECT COUNT(*) AS total
            FROM contrat
            WHERE etat = 'actif'
              AND (
                    type_contrat = 'CDI'
                    OR (date_fin IS NOT NULL AND date_fin >= CURDATE())
                  )
        ";

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetch();
        return (int)$result['total'];

    } catch (PDOException $e) {
        error_log("Erreur lors du comptage des contrats actifs : " . $e->getMessage());
        return 0;
    }
}

/**
 * Fonction spécifique pour compter les employés
 */
function countEmployees(PDO $conn): int {
    return countRecords($conn, 'employee');
}

/**
 * Fonction spécifique pour compter les contrats
 */
function countContrats(PDO $conn): int {
    return countRecords($conn, 'contrat');
}

/**
 * Convertit un nombre en lettres (pour les salaires)
 */
function nombreEnLettres($nombre) {
    $unites = array('', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf');
    $dizaines = array('', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix');
    $centaines = array('', 'cent', 'deux cents', 'trois cents', 'quatre cents', 'cinq cents', 'six cents', 'sept cents', 'huit cents', 'neuf cents');
    
    if ($nombre == 0) {
        return 'zéro';
    }
    
    // Gestion des millions
    if ($nombre >= 1000000) {
        $millions = floor($nombre / 1000000);
        $reste = $nombre % 1000000;
        $texte = nombreEnLettresPetit($millions) . ' million' . ($millions > 1 ? 's' : '');
        if ($reste > 0) {
            $texte .= ' ' . nombreEnLettres($reste);
        }
        return $texte;
    }
    
    // Gestion des milliers
    if ($nombre >= 1000) {
        $milliers = floor($nombre / 1000);
        $reste = $nombre % 1000;
        $texte = ($milliers == 1 ? 'mille' : nombreEnLettresPetit($milliers) . ' mille');
        if ($reste > 0) {
            $texte .= ' ' . nombreEnLettresPetit($reste);
        }
        return $texte;
    }
    
    return nombreEnLettresPetit($nombre);
}

function nombreEnLettresPetit($nombre) {
    $unites = array('', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf');
    $dizaines = array('', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix');
    $centaines = array('', 'cent', 'deux cents', 'trois cents', 'quatre cents', 'cinq cents', 'six cents', 'sept cents', 'huit cents', 'neuf cents');
    
    if ($nombre < 10) return $unites[$nombre];
    
    if ($nombre < 100) {
        if ($nombre < 20) {
            switch($nombre) {
                case 10: return 'dix';
                case 11: return 'onze';
                case 12: return 'douze';
                case 13: return 'treize';
                case 14: return 'quatorze';
                case 15: return 'quinze';
                case 16: return 'seize';
                case 17: return 'dix-sept';
                case 18: return 'dix-huit';
                case 19: return 'dix-neuf';
            }
        }
        
        $d = floor($nombre / 10);
        $u = $nombre % 10;
        
        if ($u == 0) {
            return $dizaines[$d];
        } elseif ($d == 1) {
            return $dizaines[1] . '-' . $unites[$u];
        } elseif ($d == 7 || $d == 9) {
            return $dizaines[$d - 1] . '-' . ($u == 1 ? 'et-' : '') . nombreEnLettresPetit(10 + $u);
        } else {
            return $dizaines[$d] . ($u == 1 ? ' et ' : '-') . $unites[$u];
        }
    }
    
    if ($nombre < 1000) {
        $c = floor($nombre / 100);
        $reste = $nombre % 100;
        
        $texte = $centaines[$c];
        if ($reste > 0) {
            $texte .= ' ' . nombreEnLettresPetit($reste);
        }
        return $texte;
    }
    
    return '';
}

/**
 * Formate une date en français complet
 */
function formatDateComplete($date) {
    if (empty($date)) return '';
    
    $mois = array(
        '01' => 'janvier', '02' => 'février', '03' => 'mars',
        '04' => 'avril', '05' => 'mai', '06' => 'juin',
        '07' => 'juillet', '08' => 'août', '09' => 'septembre',
        '10' => 'octobre', '11' => 'novembre', '12' => 'décembre'
    );
    
    list($annee, $mois_num, $jour) = explode('-', $date);
    return $jour . ' ' . $mois[$mois_num] . ' ' . $annee;
}
?>
