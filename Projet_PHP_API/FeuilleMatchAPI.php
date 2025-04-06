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
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['action'])) {
            $match_id = (int)($data['match_id'] ?? 0);
            $joueur_id = (int)($data['joueur_id'] ?? 0);
            $statut = $data['statut'] ?? '';
            $poste_prefere = $data['poste_prefere'] ?? '';
    
            if ($data['action'] === 'ajouter') {
                // Ajouter un joueur à la feuille de match
                ajouterJoueurFeuilleMatch($match_id, $joueur_id, $statut, $poste_prefere);
                echo json_encode(['success' => true, 'message' => 'Joueur ajouté avec succès.']);
            } elseif ($data['action'] === 'supprimer') {
                // Supprimer un joueur de la feuille de match
                supprimerJoueurDeFeuilleMatch($match_id, $joueur_id);
                echo json_encode(['success' => true, 'message' => 'Joueur supprimé avec succès.']);
            } elseif ($data['action'] === 'valider') {
                // Valider la feuille de match
                $total_joueurs = count(getJoueursDeFeuilleMatchComplet($match_id));
                if ($total_joueurs !== 8) {
                    echo json_encode(['success' => false, 'message' => 'Vous devez sélectionner exactement 5 titulaires et 3 remplaçants (8 joueurs au total).']);
                } else {
                    echo json_encode(['success' => true, 'message' => 'Sélection validée avec succès.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Action non reconnue.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
        }
        break;
    
    default:
    echo json_encode(['success' => false, 'message' => 'Méthode non supportée.']);
        break;
}
?>