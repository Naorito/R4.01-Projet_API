<?php
session_start();

require_once __DIR__ . '/CSS/header.php'; // Inclure le header

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) {
    header("Location: ../Auth/Connexion.php");
    exit;
}

// Déconnexion
if (isset($_GET['deconnexion'])) {
    session_start();
    session_destroy();
    header("Location: ../Auth/Connexion.php");
    exit;
}

// Récupérer les statistiques via l'API
$ch = curl_init("http://naorito.alwaysdata.net/R4.01-Projet_API/Projet_PHP_API/backend/StatistiquesAPI.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $_SESSION['token']
]);
$response = curl_exec($ch);
curl_close($ch);

$statistiques = json_decode($response, true);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques des matchs et joueurs</title>
    <link rel="stylesheet" href="CSS/Styles.css">
</head>
<body>
    <h1>Statistiques</h1>

    <?php if ($statistiques['success']): ?>
        <!-- Statistiques générales sur les matchs -->
        <h2>Statistiques des matchs</h2>
        <?php if ($statistiques['data']['matchs']['total_matchs'] > 0): ?>
            <p>Total des matchs : <?= $statistiques['data']['matchs']['total_matchs'] ?></p>
            <p>Matchs gagnés : <?= $statistiques['data']['matchs']['matchs_gagnés'] ?> (<?= number_format($statistiques['data']['matchs']['matchs_gagnés'] / $statistiques['data']['matchs']['total_matchs'] * 100, 2) ?>%)</p>
            <p>Matchs perdus : <?= $statistiques['data']['matchs']['matchs_perdus'] ?> (<?= number_format($statistiques['data']['matchs']['matchs_perdus'] / $statistiques['data']['matchs']['total_matchs'] * 100, 2) ?>%)</p>
            <p>Matchs nuls : <?= $statistiques['data']['matchs']['matchs_nuls'] ?> (<?= number_format($statistiques['data']['matchs']['matchs_nuls'] / $statistiques['data']['matchs']['total_matchs'] * 100, 2) ?>%)</p>
        <?php else: ?>
            <p>Aucune donnée disponible pour les matchs.</p>
        <?php endif; ?>

        <!-- Tableau des statistiques des joueurs -->
        <h2>Statistiques des joueurs</h2>
        <?php if (!empty($statistiques['data']['joueurs'])): ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Statut</th>
                        <th>Poste Préféré</th>
                        <th>Titulaire (sélections)</th>
                        <th>Remplaçant (sélections)</th>
                        <th>Moyenne évaluation</th>
                        <th>% matchs gagnés</th>
                        <th>Sélections consécutives</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($statistiques['data']['joueurs'] as $joueur) : ?>
                        <tr>
                            <td><?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom']) ?></td>
                            <td><?= htmlspecialchars($joueur['statut']) ?></td>
                            <td><?= htmlspecialchars($joueur['poste_prefere']) ?></td>
                            <td><?= $joueur['titulaire_count'] ?></td>
                            <td><?= $joueur['remplaçant_count'] ?></td>
                            <td><?= $joueur['moyenne_evaluation'] !== null ? number_format($joueur['moyenne_evaluation'], 2) : 'Aucune donnée' ?></td>
                            <td><?= $joueur['pourcentage_victoires'] !== null ? number_format($joueur['pourcentage_victoires'], 2) . "%" : 'Aucune donnée' ?></td>
                            <td><?= $joueur['selections_consecutives'] !== null ? $joueur['selections_consecutives'] : 'Aucune donnée' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucune donnée disponible pour les joueurs.</p>
        <?php endif; ?>
    <?php else: ?>
        <p>Erreur : <?= htmlspecialchars($statistiques['message']) ?></p>
    <?php endif; ?>
</body>
</html>