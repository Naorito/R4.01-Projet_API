<?php

session_start(); // Démarrer la session

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

// Récupérer la liste des matchs via l'API
$ch = curl_init("http://naorito.alwaysdata.net/R4.01-Projet_API/Projet_PHP_API/backend/MatchAPI.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $_SESSION['token']
]);
$response = curl_exec($ch);
curl_close($ch);

$matchs = json_decode($response, true);

if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $ch = curl_init("http://naorito.alwaysdata.net/R4.01-Projet_API/Projet_PHP_API/backend/MatchAPI.php?id=$id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $_SESSION['token']
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    if ($result['success']) {
        header("Location: ListeMatch.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des matchs</title>
    <link rel="stylesheet" href="CSS/Styles.css">
</head>
<body>
    <h1>Liste des matchs</h1>

    <!-- Bouton pour ajouter un match -->
<div class="action-buttons">
    <a href="CreationMatch.php" class="btn btn-primary">Ajouter un match</a>
</div>

    <?php if (isset($message)): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if (empty($matchs)): ?>
        <p>Aucun match trouvé.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Date et Heure</th>
                    <th>Équipe adverse</th>
                    <th>Lieu</th>
                    <th>Résultat (Équipe)</th>
                    <th>Résultat (Adverse)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matchs as $match): ?>
                    <tr>
                        <?php
                        // Formater la date et l'heure séparées
                        $date_match = new DateTime($match['date_match']); // Date
                        $heure_match = new DateTime($match['heure_match']); // Heure
                        $date_heure_formatee = $date_match->format('d/m/Y') . ' à ' . $heure_match->format('H:i'); // Format: 11/01/2025 à 14:30
                        ?>
                        <td><?= htmlspecialchars($date_heure_formatee) ?></td>
                        <td><?= htmlspecialchars($match['equipe_adverse']) ?></td>
                        <td><?= htmlspecialchars($match['lieu']) ?></td>
                        <td><?= htmlspecialchars($match['resultat_equipe']) ?></td>
                        <td><?= htmlspecialchars($match['resultat_adverse']) ?></td>
                        <td>
                            <a href="ModifierMatch.php?id=<?= urlencode($match['id']) ?>">Modifier</a>
                            <a href="ModifierFeuilleMatch.php?match_id=<?= urlencode($match['id']) ?>">Voir/Modifier la sélection</a>
                            <a href="Resultat.php?id=<?= urlencode($match['id']) ?>">Modifier le résultat</a>
                            <a href="Evaluations.php?id=<?= urlencode($match['id']) ?>">Évaluer</a>
                            <a href="?action=supprimer&id=<?= $match['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce match ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>