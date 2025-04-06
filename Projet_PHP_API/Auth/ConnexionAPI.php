<?php
require_once '../backend/librairie/BD.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$secretKey = "votre_clé_secrète"; // Changez cela pour une clé secrète plus sécurisée

// Vérifier la méthode de la requête
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    // Vérifier si les identifiants sont fournis
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => "Nom d'utilisateur et mot de passe requis."]);
        http_response_code(400); // Bad Request
        exit;
    }

    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE username = :username AND password = :password");
    $stmt->execute([
        'username' => $username,
        'password' => $password,
    ]);
    $user = $stmt->fetch();

    if ($user) {
        // Créer un jeton JWT
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'iat' => time(), // Date d'émission
            'exp' => time() + 3600 // Expiration dans 1 heure
        ];
        $jwt = createJWT($header, $payload, $secretKey);

        echo json_encode(['success' => true, 'token' => $jwt, 'data' => ['user_id' => $user['id'], 'username' => $user['username']]]);
    } else {
        echo json_encode(['success' => false, 'message' => "Nom d'utilisateur ou mot de passe incorrect."]);
        http_response_code(401); // Unauthorized
    }
} elseif ($method === 'GET') {
    $token = $_GET['token'] ?? '';

    if (empty($token)) {
        echo json_encode(['success' => false, 'message' => 'Jeton requis.']);
        http_response_code(400); // Bad Request
        exit;
    }

    $payload = verifyJWT($token, $secretKey);
    if ($payload === false) {
        echo json_encode(['success' => false, 'message' => 'Jeton invalide.']);
        http_response_code(401); // Unauthorized
    } else {
        echo json_encode(['success' => true, 'data' => $payload]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    http_response_code(405); // Method Not Allowed
}
?>