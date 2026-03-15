<?php
require_once '../connect_db.php';

// Récupérer les paramètres de pagination et recherche
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 100;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // Requête de base avec JOIN optimisé
    $query = "SELECT 
                c.id,
                c.ref,
                c.id_employee,
                c.type_contrat,
                c.periode_essai,
                c.date_debut,
                c.date_fin,
                c.salaire,
                c.affectation,
                c.poste,
                c.date_creation,
                c.user,
                c.etat,
                e.matricule,
                e.civilite,
                e.nom,
                e.prenom,
                e.photo,
                e.date_naissance,
                e.etat as employee_etat
              FROM contrat c
              INNER JOIN employee e ON c.id_employee = e.id
              WHERE 1";
    
    // Ajouter les conditions de recherche
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $query .= " AND (
                    c.ref LIKE :search OR
                    e.matricule LIKE :search OR
                    e.nom LIKE :search OR
                    e.prenom LIKE :search OR
                    c.poste LIKE :search OR
                    c.type_contrat LIKE :search OR
                    c.affectation LIKE :search
                )";
    }
    
    // Compter le nombre total d'enregistrements
    $countQuery = "SELECT COUNT(*) as total FROM ($query) as filtered";
    $countStmt = $conn->prepare(str_replace('SELECT c.id, c.ref, c.id_employee, c.type_contrat, c.periode_essai, c.date_debut, c.date_fin, c.salaire, c.affectation, c.poste, c.date_creation, c.user, c.etat, e.matricule, e.civilite, e.nom, e.prenom, e.etat as employee_etat', 'SELECT COUNT(*) as total', $countQuery));
    
    if (!empty($search)) {
        $countStmt->bindParam(':search', $searchTerm);
    }
    
    $countStmt->execute();
    $totalCount = $countStmt->fetchColumn();
    
    // Ajouter l'ordre et la pagination
    $query .= " ORDER BY c.date_creation DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    
    if (!empty($search)) {
        $stmt->bindParam(':search', $searchTerm);
    }
    
    $stmt->execute();
    $contrats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculer le nombre total de pages
    $totalPages = ceil($totalCount / $limit);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $contrats,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $totalCount,
            'totalPages' => $totalPages,
            'hasNext' => $page < $totalPages,
            'hasPrev' => $page > 1
        ],
        'currentCount' => count($contrats)
    ]);
    
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
}
?>