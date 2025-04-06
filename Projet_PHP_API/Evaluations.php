<?php
session_start(); // Démarrer la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php"); // Rediriger vers la page de connexion si non connecté
    exit;
}

$message = '';
$match = null;

// Vérifie si un ID est passé en paramètre
if (isset($_GET['id'])) {
    $match_id = (int)$_GET['id'];

    // Appeler l'API pour récupérer les informations du match et les évaluations
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost/R4.01-Projet_API/Projet_PHP_API/EvaluationsAPI.php?match_id=$match_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $apiResponse = json_decode($response, true);

    if (isset($apiResponse['success']) && !$apiResponse['success']) {
        $message = $apiResponse['message'];
    } else {
        $joueurs = $apiResponse['joueurs'];
    }
} else {
    $message = "ID du match manquant.";
}

if (isset($apiResponse['success']) && !$apiResponse['success']) {
    $message = $apiResponse['message'];
    $joueurs = []; // Initialiser $joueurs comme un tableau vide
} else {
    $joueurs = $apiResponse['joueurs'] ?? []; // Utiliser l'opérateur null coalescent
}

// Traite le formulaire d'évaluation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $evaluations = $_POST['evaluations'];

    // Préparer les données pour la requête PUT
    $data = [
        'match_id' => $match_id,
        'evaluations' => $evaluations
    ];

    // Envoyer la requête PUT à l'API
    $ch = curl_init("http://localhost/R4.01-Projet_API/Projet_PHP_API/EvaluationsAPI.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);

    $apiResponse = json_decode($response, true);

    if ($apiResponse['success']) {
        header("Location: ListeMatch.php");
        exit();
    } else {
        $message = "Erreur lors de la mise à jour des évaluations : " . $apiResponse['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Évaluations - Feuille de Match</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <h1>Feuille de Match (Évaluation)</h1>

    <?php if (!empty($message)): ?>
        <p style="<?= strpos($message, 'succès') !== false ? 'color: green;' : 'color: red;' ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <h2>Joueurs sélectionnés</h2>
    <form method="POST">
        <h3>Titulaires</h3>
        <ul>
        <?php if (!empty($joueurs)): // Vérifier si $joueurs n'est pas vide ?>
        <?php foreach ($joueurs as $joueur): ?>
            <?php if ($joueur['statut'] === 'titulaire'): ?>
                <li>
                    <?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom'] . ' - ' . $joueur['poste_prefere']) ?>
                    <label for="evaluation_<?= $joueur['joueur_id'] ?>">Évaluation (1-5):</label>
                    <select name="evaluations[<?= $joueur['joueur_id'] ?>]" id="evaluation_<?= $joueur['joueur_id'] ?>">
                        <option value="1" <?= isset($joueur['evaluation']) && $joueur['evaluation'] == 1 ? 'selected' : '' ?>>1</option>
                        <option value="2" <?= isset($joueur['evaluation']) && $joueur['evaluation'] == 2 ? 'selected' : '' ?>>2</option>
                        <option value="3" <?= isset($joueur['evaluation']) && $joueur['evaluation'] == 3 ? 'selected' : '' ?>>3</option>
                        <option value="4" <?= isset($joueur['evaluation']) && $joueur['evaluation'] == 4 ? 'selected' : '' ?>>4</option>
                        <option value="5" <?= isset($joueur['evaluation']) && $joueur['evaluation'] == 5 ? 'selected' : '' ?>>5</option>
                    </select>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <li>Aucun joueur trouvé.</li> <!-- Message si aucun joueur -->
    <?php endif; ?>
    </ul>

        <h3>Remplaçants</h3>
        <ul>
        <?php foreach ($joueurs as $joueur): ?>
            <?php if ($joueur['statut'] === 'remplacant'): ?>
                <li>
                    <?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom'] . ' - ' . $joueur['poste_prefere']) ?>
                    <label for="evaluation_<?= $joueur['joueur_id'] ?>">Évaluation (1-5):</label>
                    <select name="evaluations[<?= $joueur['joueur_id'] ?>]" id="evaluation_<?= $joueur['joueur_id'] ?>">
                        <option value="1" <?= isset($joueur['evaluation']) && $joueur['evaluation'] == 1 ? 'selected' : '' ?>>1</option>
                        <option value="2" <?= isset($joueur['evaluation']) && $joueur['evaluation'] == 2 ? 'selected' : '' ?>>2</option>
                        <option value="3" <?= isset($joueur['evaluation']) && $joueur['evaluation'] == 3 ? 'selected' : '' ?>>3</option>
                        <option value="4" <?= isset($joueur['evaluation']) && $joueur['evaluation'] == 4 ? 'selected' : '' ?>>4</option>
                        <option value="5" <?= isset($joueur['evaluation']) && $joueur['evaluation'] == 5 ? 'selected' : '' ?>>5</option>
                    </select>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
        </ul>

        <button type="submit">Enregistrer les évaluations</button>
    </form>
</body>
</html>