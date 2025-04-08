<?php

session_start(); // Démarre la session

require_once __DIR__ . '/CSS/header.php'; // Inclure le header

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) {
    header("Location: ../Auth/Connexion.php"); // Redirige vers la page de connexion si non connecté
    exit;
}

// Déconnexion
if (isset($_GET['deconnexion'])) {
    session_destroy();
    header("Location: ../Auth/Connexion.php");
    exit;
}

$message = '';
$match = null;

// Vérifie si un ID est passé en paramètre
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Récupérer les données du match
    $ch = curl_init("http://naorito.alwaysdata.net/R4.01-Projet_API/Projet_PHP_API/backend/MatchAPI.php?id=$id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $_SESSION['token']
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $match = json_decode($response, true);

    if (!$match) {
        $message = "Match introuvable.";
    }
} else {
    $message = "ID manquant.";
}

// Traite le formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id' => (int) $_POST['id'],
        'date_match' => $_POST['date_match'],
        'heure_match' => $_POST['heure_match'],
        'equipe_adverse' => $_POST['equipe_adverse'],
        'lieu' => $_POST['lieu'],
        'resultat_equipe' => $_POST['resultat_equipe'] ?? $match['resultat_equipe'],
        'resultat_adverse' => $_POST['resultat_adverse'] ?? $match['resultat_adverse']
    ];

    $url = 'http://naorito.alwaysdata.net/R4.01-Projet_API/Projet_PHP_API/backend/MatchAPI.php';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $_SESSION['token']
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($result, true);

    if ($response['success']) {
        header("Location: ListeMatch.php");
        exit();
    } else {
        $message = $response['message'];
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un match</title>
    <link rel="stylesheet" href="CSS/Styles.css">
</head>
<body>
    <h1>Modifier un match</h1>

    <?php if (!empty($message)) : ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($match) : ?>
        <form method="POST" action="ModifierMatch.php?id=<?= htmlspecialchars($match['id']) ?>">
            <input type="hidden" name="id" value="<?= htmlspecialchars($match['id']) ?>">

            <label for="date_match">Date :</label>
            <input type="date" id="date_match" name="date_match" value="<?= htmlspecialchars($match['date_match']) ?>" required><br>

            <label for="heure_match">Heure :</label>
            <input type="time" id="heure_match" name="heure_match" value="<?= htmlspecialchars($match['heure_match']) ?>" required><br>

            <label for="equipe_adverse">Équipe adverse :</label>
            <input type="text" id="equipe_adverse" name="equipe_adverse" value="<?= htmlspecialchars($match['equipe_adverse']) ?>" required><br>

            <label for="lieu">Lieu :</label>
            <input type="text" id="lieu" name="lieu" value="<?= htmlspecialchars($match['lieu']) ?>" required><br>

            <!-- Champs pour les résultats -->
            <input type="hidden" name="resultat_equipe" value="<?= htmlspecialchars($match['resultat_equipe']) ?>">
            <input type="hidden" name="resultat_adverse" value="<?= htmlspecialchars($match['resultat_adverse']) ?>">

            <button type="submit">Enregistrer</button>
        </form>
    <?php endif; ?>
</body>
</html>