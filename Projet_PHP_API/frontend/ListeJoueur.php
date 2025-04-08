<?php

session_start(); // Démarrer la session

require_once __DIR__ . '/CSS/header.php'; // Inclure le header

// Vérifier si l'utilisateur est connecté et a un token
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) {
    header("Location: ../Auth/Connexion.php");
    exit;
}

// Déconnexion
if (isset($_GET['deconnexion'])) {
    session_destroy(); // Détruire la session
    header("Location: ../Auth/Connexion.php"); // Rediriger vers la page de connexion
    exit;
}

// Traiter la suppression si demandée
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    
    $ch = curl_init("http://naorito.alwaysdata.net/R4.01-Projet_API/Projet_PHP_API/backend/JoueurAPI.php?id=$id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $_SESSION['token']
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    if ($result && isset($result['success']) && $result['success']) {
        header("Location: ListeJoueur.php");
        exit;
    } else {
        $message = $result['message'] ?? "Erreur lors de la suppression du joueur.";
    }
}

// Récupérer la liste des joueurs via l'API
$ch = curl_init("http://naorito.alwaysdata.net/R4.01-Projet_API/Projet_PHP_API/backend/JoueurAPI.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Ajouter le token dans le header
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $_SESSION['token']
]);
$response = curl_exec($ch);
curl_close($ch);

$joueurs = json_decode($response, true);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des joueurs</title>
    <link rel="stylesheet" href="CSS/Styles.css"> <!-- Lien vers le CSS -->
</head>
<body>
    
    <!-- Conteneur principal -->
    <div class="container">
        <h1>Liste des joueurs</h1>

        <!-- Bouton pour ajouter un joueur -->
        <div class="action-buttons">
            <a href="CreationJoueur.php" class="btn btn-primary">Ajouter un joueur</a>
        </div>

        <!-- Affichage du message -->
        <?php if (isset($message)): ?>
            <p class="error-message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <!-- Table des joueurs -->
        <?php if (empty($joueurs)): ?>
            <p>Aucun joueur trouvé dans la base de données.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>N° Licence</th>
                        <th>Date de naissance</th>
                        <th>Taille (cm)</th>
                        <th>Poids (kg)</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($joueurs as $joueur): ?>
                        <tr>
                            <td><?= htmlspecialchars($joueur['nom']) ?></td>
                            <td><?= htmlspecialchars($joueur['prenom']) ?></td>
                            <td><?= htmlspecialchars($joueur['numero_licence']) ?></td>
                            <td><?= htmlspecialchars($joueur['date_naissance']) ?></td>
                            <td><?= htmlspecialchars($joueur['taille']) ?></td>
                            <td><?= htmlspecialchars($joueur['poids']) ?></td>
                            <td><?= htmlspecialchars($joueur['statut']) ?></td>
                            <td>
                                <a href="ModifierJoueur.php?id=<?= urlencode($joueur['id']) ?>">Modifier</a>
                                <a href="?supprimer=<?= $joueur['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce joueur ?')">Supprimer</a>
                                <a href="Commentaire.php?id=<?= urlencode($joueur['id']) ?>">Commentaires</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>