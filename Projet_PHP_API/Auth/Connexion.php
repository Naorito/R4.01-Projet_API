<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $url = 'http://localhost/R4.01-Projet_API/Projet_PHP_API/ConnexionAPI.php'; // Remplacez par le chemin correct vers votre API
    $data = ['username' => $username, 'password' => $password];

    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $response = json_decode($result, true);

    // Vérifiez si la réponse est valide
    if (isset($response['success']) && $response['success']) {
        $_SESSION['user_id'] = $response['data']['user_id'];
        $_SESSION['username'] = $response['data']['username'];
        $_SESSION['token'] = $response['token']; // Stocker le jeton dans la session
        header("Location: ListeJoueur.php");
        exit;
    } else {
        $erreur = $response['message'] ?? "Erreur inconnue.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="./CSS/connexion.css">
</head>
<body>
    <div class="form-container">
        <h1>Connexion</h1>
        <?php if (isset($erreur)): ?>
            <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" id="username" name="username" required>
            <br>
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>
            <br>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>