<?php
session_start();

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php");
    exit;
}

$message = '';
$joueurs = [];
$match_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// ⚠️ Validation de l’ID match
if (!$match_id) {
    $message = "ID du match manquant.";
} else {
    // Appel API pour récupérer joueurs + évaluations
    $url = "http://localhost/R4.01-Projet_API/Projet_PHP_API/EvaluationsAPI.php?match_id=$match_id";
    $ch = curl_init($url);
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

// Traitement du formulaire
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

    if (isset($apiResponse['success']) && $apiResponse['success']) {
        header("Location: ListeMatch.php");
        exit;
    } else {
        $message = "Erreur lors de l'enregistrement : " . ($apiResponse['message'] ?? 'Erreur inconnue.');
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Évaluations - Match #<?= htmlspecialchars($match_id) ?></title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <h1>Évaluations - Match #<?= htmlspecialchars($match_id) ?></h1>

    <?php if (!empty($message)): ?>
        <p style="color:red"><?= htmlspecialchars($message) ?></p>
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
                    <select name="evaluations[<?= $joueur['joueur_id'] ?>]">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>" <?= (isset($joueur['evaluation']) && $joueur['evaluation'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </li>
            <?php endif; endforeach; ?>
            <?php if (!$hasTitulaires): ?>
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
                    <select name="evaluations[<?= $joueur['joueur_id'] ?>]">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>" <?= (isset($joueur['evaluation']) && $joueur['evaluation'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </li>
            <?php endif; endforeach; ?>
            <?php if (!$hasRemplacants): ?>
                <li>Aucun remplaçant trouvé.</li>
            <?php endif; ?>
        </ul>

        <button type="submit">Enregistrer les évaluations</button>
    </form>
</body>
</html>
