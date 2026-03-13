<?php
require_once '../connect_db.php';

// Récupérer les paramètres de pagination et recherche
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 100;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // Requête de base avec filtres
    $query = "SELECT 
                e.id,
                e.matricule,
                e.civilite,
                e.nom,
                e.prenom,
                e.date_naissance,
                e.telephone,
                e.etat,
                e.photo,
                c.poste,
                c.type_contrat,
                c.date_fin,
                e.date_creation
              FROM employee e
              LEFT JOIN contrat c ON e.id = c.id_employee 
                AND c.date_creation = (
                    SELECT MAX(date_creation) 
                    FROM contrat c2 
                    WHERE c2.id_employee = e.id
                )
              WHERE 1";
    
    // Ajouter les conditions de recherche
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $query .= " AND (
                    e.matricule LIKE :search OR
                    e.nom LIKE :search OR
                    e.prenom LIKE :search OR
                    e.telephone LIKE :search OR
                    c.poste LIKE :search OR
                    c.type_contrat LIKE :search
                )";
    }
    
    // Compter le nombre total d'enregistrements
    $countQuery = "SELECT COUNT(*) as total FROM ($query) as filtered";
    $countStmt = $conn->prepare(str_replace('SELECT e.id, e.matricule, e.civilite, e.nom, e.prenom, e.date_naissance, e.telephone, e.etat, c.poste, c.type_contrat, c.date_fin, e.date_creation', 'SELECT COUNT(*) as total', $countQuery));
    
    if (!empty($search)) {
        $countStmt->bindParam(':search', $searchTerm);
    }
    
    $countStmt->execute();
    $totalCount = $countStmt->fetchColumn();
    
    // Ajouter l'ordre et la pagination
    $query .= " ORDER BY e.date_creation DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    
    if (!empty($search)) {
        $stmt->bindParam(':search', $searchTerm);
    }
    
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculer le nombre total de pages
    $totalPages = ceil($totalCount / $limit);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $employees,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $totalCount,
            'totalPages' => $totalPages,
            'hasNext' => $page < $totalPages,
            'hasPrev' => $page > 1
        ],
        'currentCount' => count($employees)
    ]);
    
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
}
?>