<?php

session_start(); // Démarre la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php"); // Redirige vers la page de connexion si non connecté
    exit;
}

$message = '';
$match = null;

// Vérifie si un ID est passé en paramètre
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Appeler l'API pour récupérer les informations du match
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost/R4.01-Projet_API/Projet_PHP_API/MatchAPI.php?id=$id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $apiResponse = json_decode($response, true);

    if (isset($apiResponse['success']) && !$apiResponse['success']) {
        $message = $apiResponse['message'];
    } else {
        $match = $apiResponse;
    }
} else {
    $message = "ID manquant.";
}

// Traite le formulaire de modification du résultat
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
    $resultat_equipe = ($_POST['resultat_equipe'] !== '') ? (int)$_POST['resultat_equipe'] : null;
    $resultat_adverse = ($_POST['resultat_adverse'] !== '') ? (int)$_POST['resultat_adverse'] : null;

    // Préparer les données pour la requête PATCH
    $data = [
        'id' => $id,
        'resultat_equipe' => $resultat_equipe,
        'resultat_adverse' => $resultat_adverse
    ];

    // Envoyer la requête PATCH à l'API
    $ch = curl_init("http://localhost/R4.01-Projet_API/Projet_PHP_API/MatchAPI.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);

    $apiResponse = json_decode($response, true);

    if ($apiResponse['success']) {
        header("Location: ListeMatch.php");
        exit();
    } else {
        $message = "Erreur lors de la modification du résultat : " . $apiResponse['message'];
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le résultat du match</title>
    <link rel="stylesheet" href="/CSS/styles.css">
</head>
<body>
    <h1>Modifier le résultat du match</h1>

    <?php if (!empty($message)) : ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($match) : ?>
        <form method="POST" action="Resultat.php?id=<?= htmlspecialchars($match['id']) ?>">
            <input type="hidden" name="id" value="<?= htmlspecialchars($match['id']) ?>">

            <label for="resultat_equipe">Résultat de l'équipe :</label>
            <input type="number" id="resultat_equipe" name="resultat_equipe" value="<?= htmlspecialchars($match['resultat_equipe']) ?>"><br>

            <label for="resultat_adverse">Résultat de l'adversaire :</label>
            <input type="number" id="resultat_adverse" name="resultat_adverse" value="<?= htmlspecialchars($match['resultat_adverse']) ?>"><br>

            <button type="submit">Enregistrer</button>
        </form>
    <?php endif; ?>
</body>
</html>