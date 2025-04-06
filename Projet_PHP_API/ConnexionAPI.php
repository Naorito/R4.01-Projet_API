<?php
require_once 'librairie/BD.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE username = :username AND password = :password");
    $stmt->execute([
        'username' => $username,
        'password' => $password,
    ]);
    $user = $stmt->fetch();

    if ($user) {
        echo json_encode(['success' => true, 'user_id' => $user['id'], 'username' => $user['username']]);
    } else {
        echo json_encode(['success' => false, 'message' => "Nom d'utilisateur ou mot de passe incorrect."]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
}
?>