<?php
require_once '../connect_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID manquant']);
        exit;
    }
    
    try {
        
        // Désactiver l'employé
        $query_employee = "UPDATE employee SET etat = 'inactif' WHERE id = :id";
        $stmt_employee = $conn->prepare($query_employee);
        $stmt_employee->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_employee->execute();
        
        // Désactiver tous les contrats de l'employé
        $query_contrats = "UPDATE contrat SET etat = 'inactif' WHERE id_employee = :id_employee";
        $stmt_contrats = $conn->prepare($query_contrats);
        $stmt_contrats->bindParam(':id_employee', $id, PDO::PARAM_INT);
        $stmt_contrats->execute();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Employé et ses contrats désactivés avec succès'
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur de suppression: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>