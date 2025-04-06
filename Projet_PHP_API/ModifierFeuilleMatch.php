<?php
session_start(); // Démarrer la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php"); // Rediriger vers la page de connexion si non connecté
    exit;
}

// Récupérer l'ID du match depuis l'URL
if (!isset($_GET['match_id']) || empty($_GET['match_id'])) {
    die("ID du match non spécifié.");
}

$match_id = (int)$_GET['match_id'];
$message = "";

// Récupérer la feuille de match via l'API
$url = 'http://localhost/R4.01-Projet/Projet_PHP_API/FeuilleMatchAPI.php?match_id=' . $match_id;
$joueurs_feuille = json_decode(file_get_contents($url), true);

// Récupérer les joueurs actifs
$joueurs_actifs = json_decode(file_get_contents('http://localhost/R4.01-Projet/Projet_PHP_API/JoueurAPI.php?actifs=true'), true);

// Gestion des ajouts/suppressions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'ajouter') {
        $data = [
            'match_id' => $match_id,
            'joueur_id' => $_POST['joueur_id'],
            'statut' => $_POST['statut'],
            'poste_prefere' => $_POST['poste_prefere']
        ];

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
            $message = "Joueur ajouté avec succès.";
        } else {
            $message = $response['message'];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Feuille de Match</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <h1>Feuille de Match</h1>

    <?php if (!empty($message)): ?>
        <p style="<?= strpos($message, 'succès') !== false ? 'color: green;' : 'color: red;' ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <h2>Joueurs sélectionnés</h2>
    <h3>Titulaires</h3>
    <ul>
        <?php foreach ($joueurs_feuille as $joueur): ?>
            <?php if ($joueur['statut'] === 'titulaire'): ?>
                <li>
                    <?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom'] . ' - ' . $joueur['poste_prefere']) ?>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>

    <h3>Remplaçants</h3>
    <ul>
        <?php foreach ($joueurs_feuille as $joueur): ?>
            <?php if ($joueur['statut'] === 'remplacant'): ?>
                <li>
                    <?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom'] . ' - ' . $joueur['poste_prefere']) ?>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>

    <h2>Ajouter un joueur</h2>
    <form method="post">
        <label for="joueur_id">Joueur :</label>
        <select name="joueur_id" id="joueur_id" required>
            <option value="">-- Sélectionnez un joueur --</option>
            <?php foreach ($joueurs_actifs as $joueur): ?>
                <option value="<?= $joueur['id'] ?>"><?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="statut">Statut :</label>
        <select name="statut" id="statut" required>
            <option value="titulaire">Titulaire</option>
            <option value="remplacant">Remplaçant</option>
        </select>

        <label for="poste_prefere">Poste :</label>
        <select name="poste_prefere" id="poste_prefere" required>
            <option value="Top Lane">Top Lane</option>
            <option value="Mid Lane">Mid Lane</option>
            <option value="Bot Lane ADC">Bot Lane ADC</option>
            <option value="Bot Lane Support">Bot Lane Support</option>
            <option value="Jungler">Jungler</option>
        </select>

        <button type="submit" name="action" value="ajouter">Ajouter</button>
    </form>

    <h2>Valider la sélection</h2>
    <form method="post">
        <button type="submit" name="valider">Valider la sélection</button>
    </form>

</body>
</html>