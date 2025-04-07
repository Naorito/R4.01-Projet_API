<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth/Connexion.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID du match non spécifié.");
}

$match_id = (int)$_GET['id'];
$message = "";

// Récupérer les données via l'API FeuilleMatch
$ch = curl_init("http://localhost/R4.01-Projet_API/Projet_PHP_API/backend/FeuilleMatchAPI.php?match_id=$match_id");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

// Vérifier si la requête cURL a réussi
if ($response === false) {
    die("Erreur lors de la connexion à l'API: " . curl_error($ch));
}
curl_close($ch);

$data = json_decode($response, true);

// Vérifier si le décodage JSON a réussi et si la réponse est valide
if ($data === null) {
    die("Erreur lors du décodage de la réponse de l'API");
}

if (!isset($data['success'])) {
    die("Format de réponse de l'API invalide");
}

if (!$data['success']) {
    die("Erreur lors de la récupération des données: " . ($data['message'] ?? 'Erreur inconnue'));
}

// Vérifier si la clé 'joueurs' existe
if (!isset($data['joueurs'])) {
    die("Données des joueurs non trouvées dans la réponse de l'API");
}

$joueurs = $data['joueurs'];

// Traitement du formulaire d'évaluation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $evaluations = [];
    foreach ($_POST['evaluations'] as $joueur_id => $evaluation) {
        if (is_numeric($evaluation) && $evaluation >= 1 && $evaluation <= 5) {
            $evaluations[] = [
                'joueur_id' => (int)$joueur_id,
                'evaluation' => (int)$evaluation
            ];
        }
    }

    $data_to_send = json_encode([
        'match_id' => $match_id,
        'evaluations' => $evaluations
    ]);

    $ch = curl_init("http://localhost/R4.01-Projet_API/Projet_PHP_API/backend/FeuilleMatchAPI.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_to_send);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    
    if ($response === false) {
        $message = "Erreur lors de la connexion à l'API: " . curl_error($ch);
    } else {
        $result = json_decode($response, true);
        if ($result && isset($result['success']) && $result['success']) {
            header("Location: ListeMatch.php");
            exit;
        } else {
            $message = "Erreur lors de l'enregistrement des évaluations: " . ($result['message'] ?? 'Erreur inconnue');
        }
    }
    curl_close($ch);
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
    <h1>Évaluations des joueurs</h1>

    <?php if (!empty($message)): ?>
        <p style="color: <?= strpos($message, 'succès') !== false ? 'green' : 'red' ?>;">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <?php if (empty($joueurs)): ?>
        <p>Aucun joueur trouvé pour ce match.</p>
    <?php else: ?>
        <form method="POST">
            <h2>Joueurs titulaires</h2>
            <ul>
                <?php foreach ($joueurs as $joueur): ?>
                    <?php if ($joueur['statut'] === 'titulaire'): ?>
                        <li>
                            <?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom'] . ' - ' . $joueur['poste_prefere']) ?>
                            <label for="evaluation_<?= $joueur['joueur_id'] ?>">Évaluation (1-5):</label>
                            <select name="evaluations[<?= $joueur['joueur_id'] ?>]" id="evaluation_<?= $joueur['joueur_id'] ?>">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?= $i ?>" <?= isset($joueur['evaluation']) && $joueur['evaluation'] == $i ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>

            <h2>Joueurs remplaçants</h2>
            <ul>
                <?php foreach ($joueurs as $joueur): ?>
                    <?php if ($joueur['statut'] === 'remplacant'): ?>
                        <li>
                            <?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom'] . ' - ' . $joueur['poste_prefere']) ?>
                            <label for="evaluation_<?= $joueur['joueur_id'] ?>">Évaluation (1-5):</label>
                            <select name="evaluations[<?= $joueur['joueur_id'] ?>]" id="evaluation_<?= $joueur['joueur_id'] ?>">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?= $i ?>" <?= isset($joueur['evaluation']) && $joueur['evaluation'] == $i ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>

            <button type="submit">Enregistrer les évaluations</button>
        </form>
    <?php endif; ?>
</body>
</html>