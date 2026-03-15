<?php
/**
 * php/contrat/add_contrat.php
 * Crée un nouveau contrat — POST
 */
ob_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../connect_db.php';

function fail(string $m): void { ob_end_clean(); echo json_encode(['success'=>false,'message'=>$m]); exit; }
function ok(array $d): void    { ob_end_clean(); echo json_encode(array_merge(['success'=>true],$d)); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Méthode non autorisée');

$id_employee   = (int)($_POST['id_employee']   ?? 0);
$type_contrat  = trim($_POST['type_contrat']   ?? '');
$date_debut    = trim($_POST['date_debut']     ?? '');
$affectation   = trim($_POST['affectation']    ?? '');
$poste         = trim($_POST['poste']          ?? '');
$periode_essai = trim($_POST['periode_essai']  ?? '6 mois');
$ref           = trim($_POST['ref']            ?? '');
$salaire       = !empty($_POST['salaire'])      ? (float)$_POST['salaire'] : null;
$date_fin      = (!empty($_POST['date_fin']) && $type_contrat === 'CDD') ? $_POST['date_fin'] : null;

if (!$id_employee)                            fail('Employé non sélectionné');
if (!in_array($type_contrat, ['CDD','CDI']))  fail('Type de contrat invalide');
if (!$date_debut)                             fail('La date de début est obligatoire');
if (!$affectation)                            fail("L'affectation est obligatoire");
if (!$poste)                                  fail('Le poste est obligatoire');
if ($type_contrat === 'CDD' && !$date_fin)    fail('La date de fin est obligatoire pour un CDD');
if ($date_fin && $date_fin <= $date_debut)    fail('La date de fin doit être après la date de début');

try {
    $chk = $conn->prepare("SELECT id FROM employee WHERE id = :id LIMIT 1");
    $chk->execute([':id' => $id_employee]);
    if (!$chk->fetch()) fail('Employé introuvable');

    $stmt = $conn->prepare("
        INSERT INTO contrat
            (id_employee, ref, type_contrat, periode_essai, date_debut, date_fin,
             salaire, affectation, poste, date_creation, user, etat)
        VALUES
            (:id_employee, :ref, :type_contrat, :periode_essai, :date_debut, :date_fin,
             :salaire, :affectation, :poste, NOW(), :user, 'actif')
    ");
    $stmt->execute([
        ':id_employee'   => $id_employee,
        ':ref'           => $ref ?: null,
        ':type_contrat'  => $type_contrat,
        ':periode_essai' => $periode_essai,
        ':date_debut'    => $date_debut,
        ':date_fin'      => $date_fin,
        ':salaire'       => $salaire,
        ':affectation'   => $affectation,
        ':poste'         => $poste,
        ':user'          => $_SESSION['user_name'] ?? 'admin',
    ]);
    ok(['message' => 'Contrat créé avec succès', 'id' => (int)$conn->lastInsertId()]);
} catch (PDOException $e) {
    fail('Erreur DB : ' . $e->getMessage());
}