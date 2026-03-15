<?php
require_once '../connect_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $contrat_id = $_GET['id'] ?? null;
    
    if (!$contrat_id) {
        echo json_encode(['success' => false, 'message' => 'ID du contrat manquant']);
        exit;
    }
    
    try {
        
        $query = "SELECT 
                    c.*,
                    e.matricule,
                    e.civilite,
                    e.nom,
                    e.prenom,
                    e.date_naissance,
                    e.lieu_naissance,
                    e.no_acte_naissance,
                    e.situation_familiale,
                    e.nombre_enfants,
                    e.prenom_pere,
                    e.nom_prenom_mere,
                    e.adresse,
                    e.wilaya_residence,
                    e.telephone,
                    e.no_assurance_cnas,
                    e.compte_a_paye,
                    e.photo
                  FROM contrat c
                  INNER JOIN employee e ON c.id_employee = e.id
                  WHERE c.id = :contrat_id";
                  
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':contrat_id', $contrat_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $contrat = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($contrat) {
            echo json_encode([
                'success' => true,
                'data' => $contrat
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Contrat non trouvé'
            ]);
        }
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur de base de données: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>