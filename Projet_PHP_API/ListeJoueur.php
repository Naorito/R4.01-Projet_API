<?php

session_start(); // Démarrer la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php"); // Redirige vers la page de connexion si non connecté
    exit;
}

// Déconnexion
if (isset($_GET['deconnexion'])) {
    session_destroy(); // Détruire la session
    header("Location: Connexion.php"); // Rediriger vers la page de connexion
    exit;
}

// Récupération des données via l'API
$url = 'http://localhost/R4.01-Projet/Projet_PHP_API/JoueurAPI.php';
$joueurs = json_decode(file_get_contents($url), true);

if (isset($_GET['supprimer'])) {
    $id = (int) $_GET['supprimer']; // Sécuriser l'entrée
    $deleteUrl = $url . '?id=' . $id;
    $options = [
        'http' => [
            'method' => 'DELETE',
        ],
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($deleteUrl, false, $context);
    $response = json_decode($result, true);

    if ($response['success']) {
        header("Location: ListeJoueur.php"); // Rafraîchir la page après suppression
        exit;
    } else {
        $message = $response['message'];
        echo "<script>
                setTimeout(function() {
                    window.location.href = 'ListeJoueur.php';
                }, 3000);
              </script>";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des joueurs</title>
    <link rel="stylesheet" href="./css/ListeJoueur.css"> <!-- Lien vers le CSS -->
</head>
<body>
    <!-- Bandeau de navigation -->
    <header class="header">
        <nav class="navbar">
            <a href="ListeJoueur.php" class="btn btn-primary">Liste des joueurs</a>
            <a href="ListeMatch.php" class="btn btn-primary">Liste des matchs</a>
            <a href="Statistiques.php" class="btn btn-primary">Statistiques</a>
            <a href="?deconnexion=1" class="btn btn-secondary">Se déconnecter</a>
        </nav>
    </header>

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
                                <a href="Commentaire.php?id=<?= urlencode($joueur['id']) ?>" class="btn btn-secondary">Commentaires</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>