<?php

session_start(); // Démarre la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php"); // Redirige vers la page de connexion si non connecté
    exit;
}

require_once __DIR__ . '/librairie/BD.php';

$message = '';
$match = null;

// Vérifie si un ID est passé en paramètre
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $match = getMatchParId($id); // Fonction pour récupérer un match par ID

    if (!$match) {
        $message = "Match introuvable.";
    }
} else {
    $message = "ID manquant.";
}

// Traite le formulaire de modification du résultat
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
    $resultat_equipe = ($_POST['resultat_equipe'] !== '') ? (int)$_POST['resultat_equipe'] : null;
    $resultat_adverse = ($_POST['resultat_adverse'] !== '') ? (int)$_POST['resultat_adverse'] : null;

    // Modifier uniquement les résultats
    if (modifierResultat($id, $resultat_equipe, $resultat_adverse)) {
        header("Location: ListeMatch.php");
        exit();
    } else {
        $message = "Erreur lors de la modification du résultat.";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le résultat du match</title>
    <link rel="stylesheet" href="/CSS/styles.css">
</head>
<body>
    <h1>Modifier le résultat du match</h1>

    <?php if (!empty($message)) : ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($match) : ?>
        <form method="POST" action="Resultat.php">
            <input type="hidden" name="id" value="<?= htmlspecialchars($match['id']) ?>">

            <label>Date :</label>
            <p><?= htmlspecialchars($match['date_match']) ?></p><br>

            <label>Heure :</label>
            <p><?= htmlspecialchars($match['heure_match']) ?></p><br>

            <label>Équipe adverse :</label>
            <p><?= htmlspecialchars($match['equipe_adverse']) ?></p><br>

            <label>Lieu :</label>
            <p><?= htmlspecialchars($match['lieu']) ?></p><br>

            <label for="resultat_equipe">Résultat de l'équipe :</label>
            <input type="number" id="resultat_equipe" name="resultat_equipe" value="<?= htmlspecialchars($match['resultat_equipe']) ?>"><br>

            <label for="resultat_adverse">Résultat de l'adversaire :</label>
            <input type="number" id="resultat_adverse" name="resultat_adverse" value="<?= htmlspecialchars($match['resultat_adverse']) ?>"><br>

            <button type="submit">Enregistrer</button>
        </form>
    <?php endif; ?>
</body>
</html>
