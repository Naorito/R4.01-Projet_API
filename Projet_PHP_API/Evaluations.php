<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php");
    exit;
}

$message = '';
$joueurs = [];
$match_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$match_id) {
    $message = "ID du match manquant.";
} else {
    // Récupérer les joueurs et évaluations
    $ch = curl_init("http://localhost/R4.01-Projet_API/Projet_PHP_API/EvaluationsAPI.php?match_id=$match_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $apiResponse = json_decode($response, true);

    if (!isset($apiResponse['success']) || !$apiResponse['success']) {
        $message = $apiResponse['message'] ?? "Erreur lors de la récupération des données.";
    } else {
        $joueurs = $apiResponse['joueurs'] ?? [];
    }
}

// Enregistrement des évaluations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $match_id) {
    $evaluations = $_POST['evaluations'] ?? [];

    $data = [
        'match_id' => $match_id,
        'evaluations' => $evaluations
    ];

    $ch = curl_init("http://localhost/R4.01-Projet_API/Projet_PHP_API/EvaluationsAPI.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);

    $apiResponse = json_decode($response, true);

    if ($apiResponse['success'] ?? false) {
        header("Location: ListeMatch.php");
        exit;
    } else {
        $message = "Erreur lors de la mise à jour des évaluations : " . ($apiResponse['message'] ?? 'Erreur inconnue.');
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
    <h1>Évaluations - Match #<?= htmlspecialchars($match_id) ?></h1>

    <?php if (!empty($message)): ?>
        <p style="color: red"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST">
        <h2>Titulaires</h2>
        <ul>
            <?php
            $hasTitulaires = false;
            foreach ($joueurs as $joueur):
                if ($joueur['statut'] === 'titulaire'):
                    $hasTitulaires = true;
            ?>
                <li>
                    <?= htmlspecialchars("{$joueur['nom']} {$joueur['prenom']} - {$joueur['poste_prefere']}") ?>
                    <label for="evaluation_<?= $joueur['joueur_id'] ?>">Évaluation :</label>
                    <select name="evaluations[<?= $joueur['joueur_id'] ?>]" id="evaluation_<?= $joueur['joueur_id'] ?>">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>" <?= isset($joueur['evaluation']) && $joueur['evaluation'] == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </li>
            <?php
                endif;
            endforeach;
            if (!$hasTitulaires): ?>
                <li>Aucun titulaire trouvé.</li>
            <?php endif; ?>
        </ul>

        <h2>Remplaçants</h2>
        <ul>
            <?php
            $hasRemplacants = false;
            foreach ($joueurs as $joueur):
                if ($joueur['statut'] === 'remplacant'):
                    $hasRemplacants = true;
            ?>
                <li>
                    <?= htmlspecialchars("{$joueur['nom']} {$joueur['prenom']} - {$joueur['poste_prefere']}") ?>
                    <label for="evaluation_<?= $joueur['joueur_id'] ?>">Évaluation :</label>
                    <select name="evaluations[<?= $joueur['joueur_id'] ?>]" id="evaluation_<?= $joueur['joueur_id'] ?>">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>" <?= isset($joueur['evaluation']) && $joueur['evaluation'] == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </li>
            <?php
                endif;
            endforeach;
            if (!$hasRemplacants): ?>
                <li>Aucun remplaçant trouvé.</li>
            <?php endif; ?>
        </ul>

        <button type="submit">Enregistrer les évaluations</button>
    </form>
</body>
</html>
