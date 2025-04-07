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
                $joueurs = [];
            } else {
                // Récupérer les évaluations pour ce match
                $evaluations = getEvaluations($match_id);
                $evaluationsParJoueur = [];
                foreach ($evaluations as $eval) {
                    $evaluationsParJoueur[$eval['joueur_id']] = $eval['evaluation'];
                }

                // Ajouter les noms, prénoms et évaluations des joueurs
                foreach ($joueurs as &$joueur) {
                    $joueur_info = getJoueurParId($joueur['joueur_id']);
                    $joueur['nom'] = $joueur_info['nom'];
                    $joueur['prenom'] = $joueur_info['prenom'];
                    $joueur['evaluation'] = $evaluationsParJoueur[$joueur['joueur_id']] ?? null;
                }
            }

            echo json_encode(['success' => true, 'joueurs' => $joueurs]);
        } elseif (isset($_GET['action']) && $_GET['action'] === 'getActifs') {
            // Récupérer les joueurs actifs
            $joueurs_actifs = getJoueursActifs();
            echo json_encode(['success' => true, 'joueurs' => $joueurs_actifs]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID du match manquant.']);
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Si c'est une mise à jour des évaluations
        if (isset($data['match_id']) && isset($data['evaluations'])) {
            $match_id = (int)$data['match_id'];
            $erreurs = [];

            foreach ($data['evaluations'] as $evaluation) {
                if (!isset($evaluation['joueur_id']) || !isset($evaluation['evaluation'])) {
                    continue;
                }

                $joueur_id = (int)$evaluation['joueur_id'];
                $note = (int)$evaluation['evaluation'];

                if ($note < 1 || $note > 5) {
                    $erreurs[] = "L'évaluation doit être entre 1 et 5 pour le joueur $joueur_id";
                    continue;
                }

                setEvaluation($match_id, $joueur_id, $note);
            }

            if (empty($erreurs)) {
                echo json_encode(['success' => true, 'message' => 'Évaluations mises à jour avec succès.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreurs lors de la mise à jour.', 'erreurs' => $erreurs]);
            }
        }
        // Si c'est une mise à jour de la feuille de match
        elseif (isset($data['match_id']) && isset($data['joueurs'])) {
            $match_id = (int)$data['match_id'];
            supprimerTousJoueursDeFeuilleMatch($match_id);

            foreach ($data['joueurs'] as $joueur) {
                ajouterJoueurFeuilleMatch($match_id, $joueur['joueur_id'], $joueur['statut'], $joueur['poste_prefere']);
            }

            echo json_encode(['success' => true, 'message' => 'Feuille de match mise à jour avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Méthode non supportée.']);
        break;
}
?>