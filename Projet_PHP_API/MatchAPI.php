<?php
require_once 'librairie/BD.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Récupérer un match par ID
            $id = (int) $_GET['id'];
            $match = getMatchParId($id);
            if ($match) {
                echo json_encode($match);
            } else {
                echo json_encode(['success' => false, 'message' => 'Match introuvable.']);
            }
        } else {
            // Récupérer tous les matchs
            $matchs = getAllMatchs();
            echo json_encode($matchs);
        }
        break;

    case 'POST':
        // Ajouter un nouveau match
        $data = json_decode(file_get_contents('php://input'), true);
        $match_id = ajouterMatch($data['date_match'], $data['heure_match'], $data['equipe_adverse'], $data['lieu'], $data['resultat_equipe'], $data['resultat_adverse']);
        if ($match_id) {
            echo json_encode(['success' => true, 'message' => 'Match ajouté avec succès.', 'match_id' => $match_id]);
        } else {
            echo json_encode(['success' => false, 'message' => "Erreur lors de l'ajout du match."]);
        }
        break;

    case 'PUT':
        // Mettre à jour un match
        // Lire les données JSON du corps de la requête
    $data = json_decode(file_get_contents('php://input'), true);

    // Vérifier que toutes les clés nécessaires sont présentes
    if (isset($data['id'], $data['date_match'], $data['heure_match'], $data['equipe_adverse'], $data['lieu'])) {
        if (modifierMatch($data['id'], $data['date_match'], $data['heure_match'], $data['equipe_adverse'], $data['lieu'])) {
            echo json_encode(['success' => true, 'message' => 'Match modifié avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => "Erreur lors de la modification du match."]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Données manquantes ou incorrectes.']);
    }
    break;

    case 'DELETE':
        // Supprimer un match
        if (isset($_GET['id'])) {
            $id = (int) $_GET['id'];
            if (supprimerMatch($id)) {
                echo json_encode(['success' => true, 'message' => 'Match supprimé avec succès.']);
            } else {
                echo json_encode(['success' => false, 'message' => "Impossible de supprimer ce match, il a déjà eu lieu."]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID du match manquant.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
        break;
}
?>