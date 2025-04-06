<?php
require_once 'librairie/BD.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['match_id'])) {
            // Récupérer la feuille de match par ID de match
            $match_id = (int) $_GET['match_id'];
            $joueurs = getJoueursDeFeuilleMatchComplet($match_id);

            if (!$joueurs) {
                // Si aucune feuille de match n'est trouvée, renvoyer une feuille vide
                $joueurs = [];
            } else {
                // Ajouter les noms et prénoms des joueurs
                foreach ($joueurs as &$joueur) {
                    $joueur_info = getJoueurParId($joueur['joueur_id']);
                    $joueur['nom'] = $joueur_info['nom'];
                    $joueur['prenom'] = $joueur_info['prenom'];
                }
            }

            echo json_encode($joueurs);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID du match manquant.']);
        }
        break;

    case 'POST':
        // Ajouter un joueur à la feuille de match
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['match_id'], $data['joueur_id'], $data['statut'], $data['poste_prefere'])) {
            ajouterJoueurFeuilleMatch($data['match_id'], $data['joueur_id'], $data['statut'], $data['poste_prefere']);
            echo json_encode(['success' => true, 'message' => 'Joueur ajouté à la feuille de match.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Données manquantes ou incorrectes.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
        break;
}
?>