<?php
require_once 'librairie/BD.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Récupérer un joueur par ID
            $id = (int) $_GET['id'];
            $joueur = getJoueurParId($id);
            if ($joueur) {
                echo json_encode($joueur);
            } else {
                echo json_encode(['success' => false, 'message' => 'Joueur introuvable.']);
            }
        } else {
            // Récupérer tous les joueurs
            $joueurs = getTousLesJoueurs();
            echo json_encode($joueurs);
        }
        break;

    case 'POST':
        // Ajouter un nouveau joueur
        $data = json_decode(file_get_contents('php://input'), true);
        if (ajouterJoueur($data['nom'], $data['prenom'], $data['numero_licence'], $data['date_naissance'], $data['taille'], $data['poids'], $data['statut'])) {
            echo json_encode(['success' => true, 'message' => 'Joueur ajouté avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => "Erreur lors de l'ajout du joueur."]);
        }
        break;

    case 'PUT':
        // Mettre à jour un joueur
        // Lire les données JSON du corps de la requête
    $data = json_decode(file_get_contents('php://input'), true);

    // Vérifier que toutes les clés nécessaires sont présentes
    if (isset($data['id'], $data['nom'], $data['prenom'], $data['numero_licence'], $data['date_naissance'], $data['taille'], $data['poids'], $data['statut'], $data['commentaires'])) {
        if (modifierJoueur($data['id'], $data['nom'], $data['prenom'], $data['numero_licence'], $data['date_naissance'], $data['taille'], $data['poids'], $data['statut'], $data['commentaires'])) {
            echo json_encode(['success' => true, 'message' => 'Joueur modifié avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => "Erreur lors de la modification du joueur."]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Données manquantes ou incorrectes.']);
    }
    break;

    case 'DELETE':
        // Supprimer un joueur
        if (isset($_GET['id'])) {
            $id = (int) $_GET['id'];
            if (supprimerJoueur($id)) {
                echo json_encode(['success' => true, 'message' => 'Joueur supprimé avec succès.']);
            } else {
                echo json_encode(['success' => false, 'message' => "Impossible de supprimer ce joueur, il a déjà participé à un match."]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID du joueur manquant.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
        break;
}
?>