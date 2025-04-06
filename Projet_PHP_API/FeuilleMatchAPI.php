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
        
        case 'PUT':
            $data = json_decode(file_get_contents("php://input"), true);
            if (isset($data['match_id'])) {
                $match_id = (int)$data['match_id'];
                // Supprimer tous les joueurs existants de la feuille de match
                supprimerTousJoueursDeFeuilleMatch($match_id);
    
                // Ajouter les nouveaux joueurs
                foreach ($data['joueurs'] as $joueur) {
                    ajouterJoueurFeuilleMatch($match_id, $joueur['joueur_id'], $joueur['statut'], $joueur['poste_prefere']);
                }
    
                echo json_encode(['success' => true, 'message' => 'Feuille de match mise à jour avec succès.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'ID du match manquant.']);
            }
            break;
    
        default:
            echo json_encode(['success' => false, 'message' => 'Méthode non supportée.']);
            break;
}
?>