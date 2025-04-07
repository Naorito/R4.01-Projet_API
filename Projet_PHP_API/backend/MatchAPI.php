<?php
require_once 'librairie/BD.php';

header("Content-Type: application/json");

// Vérification du token JWT
$token = null;

// Récupérer le token du header Authorization de différentes manières possibles
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $auth_header = $headers['Authorization'];
    } elseif (isset($headers['authorization'])) {
        $auth_header = $headers['authorization'];
    }
} 

if (!isset($auth_header)) {
    // Si getallheaders() ne fonctionne pas, essayer avec $_SERVER
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $auth_header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
}

// Extraire le token si l'en-tête Authorization est présent
if (isset($auth_header)) {
    if (strpos($auth_header, 'Bearer ') === 0) {
        $token = substr($auth_header, 7);
    }
}

// Si pas de token, renvoyer une erreur
if (!$token) {
    echo json_encode(['success' => false, 'message' => 'Jeton requis.']);
    http_response_code(401);
    exit;
}

// Vérifier la validité du token
$secretKey = "votre_clé_secrète"; // Utiliser la même clé que dans ConnexionAPI.php
$payload = verifyJWT($token, $secretKey);

if ($payload === false) {
    echo json_encode(['success' => false, 'message' => 'Jeton invalide.']);
    http_response_code(401);
    exit;
}

// Si le token est valide, continuer avec le reste du code
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
    if (isset($data['id'], $data['date_match'], $data['heure_match'], $data['equipe_adverse'], $data['lieu'], $data['resultat_equipe'], $data['resultat_adverse'])) {
        if (modifierMatch($data['id'], $data['date_match'], $data['heure_match'], $data['equipe_adverse'], $data['lieu']) &&
            modifierResultat($data['id'], $data['resultat_equipe'], $data['resultat_adverse'])) {
            echo json_encode(['success' => true, 'message' => 'Match et résultats modifiés avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => "Erreur lors de la modification du match ou des résultats."]);
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