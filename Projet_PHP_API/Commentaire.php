<?php
session_start(); // Démarrer la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php"); // Redirige vers la page de connexion si non connecté
    exit;
}

require_once __DIR__ . '/librairie/BD.php'; // Inclure la librairie pour la base de données
require_once __DIR__ . '/CSS/header.php'; // Inclure le header

// Vérifier si un ID est passé en paramètre
if (!isset($_GET['id'])) {
    header("Location: ListeJoueur.php"); // Redirige si l'ID n'est pas fourni
    exit;
}

$id = (int) $_GET['id'];
$url = 'http://localhost/R4.01-Projet/Projet_PHP_API/JoueurAPI.php?id=' . $id;
$joueur = json_decode(file_get_contents($url), true);

if (!$joueur) {
    $message = "Joueur introuvable.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commentaires du joueur</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <!-- Le menu de navigation est inclus ici -->
    <?php use CSS\header; ?>

    <h1>Commentaires du joueur</h1>

    <?php if (isset($message)): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php else: ?>
        <p><strong>Nom :</strong> <?= htmlspecialchars($joueur['nom']) ?></p>
        <p><strong>Prénom :</strong> <?= htmlspecialchars($joueur['prenom']) ?></p>
        <p><strong>Commentaires :</strong></p>
        <p><?= htmlspecialchars($joueur['commentaires'] ?? 'Aucun commentaire.') ?></p>
    <?php endif; ?>

    <a href="ListeJoueur.php" class="btn btn-primary">Retour à la liste</a>
</body>
</html>
