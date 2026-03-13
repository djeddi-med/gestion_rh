<?php
require_once '../connect_db.php';

try {
    
    $query = "SELECT mat FROM matricule LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $next_matricule = $result['mat'] + 1;
        echo json_encode([
            'success' => true,
            'next_matricule' => $next_matricule
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Matricule non trouvé'
        ]);
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
}
?>