<?php
/**
 * php/contrat/delete_contrat.php
 * Supprime un contrat de la table contrat — POST JSON
 */
ob_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../connect_db.php';

function fail(string $m): void { ob_end_clean(); echo json_encode(['success'=>false,'message'=>$m]); exit; }
function ok(array $d): void    { ob_end_clean(); echo json_encode(array_merge(['success'=>true],$d)); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Méthode non autorisée');

$data = json_decode(file_get_contents('php://input'), true);
$id   = (int)($data['id'] ?? 0);
if (!$id) fail('ID contrat manquant');

try {
    $chk = $conn->prepare("SELECT id FROM contrat WHERE id = :id LIMIT 1");
    $chk->execute([':id' => $id]);
    if (!$chk->fetch()) fail('Contrat introuvable');

    $conn->prepare("DELETE FROM contrat WHERE id = :id")->execute([':id' => $id]);
    ok(['message' => 'Contrat supprimé avec succès']);
} catch (PDOException $e) {
    fail('Erreur DB : ' . $e->getMessage());
}