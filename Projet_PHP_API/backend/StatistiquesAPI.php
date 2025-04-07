<?php
header('Content-Type: application/json'); // Définir le type de contenu comme JSON

require_once __DIR__ . '/librairie/BD.php'; // Inclure le fichier BD.php

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

// Vérifier la méthode de la requête
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Récupérer les statistiques
    $statistiques = getStatistiques();

    // Vérifier si des statistiques ont été récupérées
    if (!empty($statistiques)) {
        // Retourner les statistiques en JSON
        echo json_encode([
            'success' => true,
            'data' => $statistiques
        ]);
    } else {
        // Retourner un message d'erreur si aucune statistique n'est disponible
        echo json_encode([
            'success' => false,
            'message' => 'Aucune statistique disponible.'
        ]);
    }
} else {
    // Retourner une erreur si la méthode n'est pas GET
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée. Utilisez GET.'
    ]);
}
?>