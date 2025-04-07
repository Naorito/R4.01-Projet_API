<?php
session_start(); // Démarrer la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) {
    header("Location: ../Auth/Connexion.php"); // Redirige vers la page de connexion si non connecté
    exit;
}

require_once __DIR__ . '/CSS/header.php'; // Inclure le header

// Vérifier si un ID est passé en paramètre
if (!isset($_GET['id'])) {
    die("ID du joueur non spécifié");
}

$joueur_id = (int)$_GET['id'];

// Récupérer les informations du joueur
$ch = curl_init("http://localhost/R4.01-Projet_API/Projet_PHP_API/backend/JoueurAPI.php?id=$joueur_id");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $_SESSION['token']
]);
$response = curl_exec($ch);
curl_close($ch);

$joueur = json_decode($response, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id' => $joueur_id,
        'nom' => $joueur['nom'],
        'prenom' => $joueur['prenom'],
        'numero_licence' => $joueur['numero_licence'],
        'date_naissance' => $joueur['date_naissance'],
        'taille' => $joueur['taille'],
        'poids' => $joueur['poids'],
        'statut' => $joueur['statut'],
        'commentaires' => $_POST['commentaires']
    ];

    $ch = curl_init("http://localhost/R4.01-Projet_API/Projet_PHP_API/backend/JoueurAPI.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $_SESSION['token']
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    if ($result['success']) {
        header("Location: ListeJoueur.php");
        exit;
    }
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
