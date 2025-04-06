<?php

session_start(); // Démarre la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php"); // Redirige vers la page de connexion si non connecté
    exit;
}

$message = '';

// Traitement du formulaire d'ajout d'un match
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'date_match' => $_POST['date_match'],
        'heure_match' => $_POST['heure_match'],
        'equipe_adverse' => $_POST['equipe_adverse'],
        'lieu' => $_POST['lieu'],
        'resultat_equipe' => $_POST['resultat_equipe'],
        'resultat_adverse' => $_POST['resultat_adverse']
    ];

    $url = 'http://localhost/R4.01-Projet/Projet_PHP_API/MatchAPI.php';
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

    if ($response['success']) {
        header("Location: ListeMatch.php?match_id=" . $response['match_id']);
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
    <title>Créer un match</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <h1>Créer un match</h1>

    <?php if (!empty($message)) : ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="CreationMatch.php">
        <label for="date_match">Date du match :</label>
        <input type="date" id="date_match" name="date_match" required><br>

        <label for="heure_match">Heure du match :</label>
        <input type="time" id="heure_match" name="heure_match" required><br>

        <label for="equipe_adverse">Nom de l'équipe adverse :</label>
        <input type="text" id="equipe_adverse" name="equipe_adverse" required><br>

        <label for="lieu">Lieu de rencontre :</label>
        <select id="lieu" name="lieu" required>
            <option value="Domicile">Domicile</option>
            <option value="Extérieur">Extérieur</option>
        </select><br>

        <label for="resultat_equipe">Résultat (équipe) :</label>
        <input type="text" id="resultat_equipe" name="resultat_equipe"><br>

        <label for="resultat_adverse">Résultat (adverse) :</label>
        <input type="text" id="resultat_adverse" name="resultat_adverse"><br>

        <button type="submit">Créer le match</button>
    </form>

</body>
</html>