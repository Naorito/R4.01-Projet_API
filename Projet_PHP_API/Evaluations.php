<?php
session_start(); // Démarrer la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php"); // Rediriger vers la page de connexion si non connecté
    exit;
}

require_once __DIR__ . '/librairie/BD.php'; // Inclure le fichier BD.php

// Récupérer l'ID du match depuis l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID du match non spécifié.");
}

$match_id = (int)$_GET['id'];

$message = "";

// Récupérer tous les matchs
$matchs = getAllMatchs();

// Trouver le match spécifique avec l'ID fourni
$match = null;
foreach ($matchs as $m) {
    if ($m['id'] === $match_id) {
        $match = $m;
        break;
    }
}

if (!$match) {
    die("Match non trouvé.");
}

// Initialiser ou récupérer les joueurs stockés en session
if (!isset($_SESSION['feuille_match'][$match_id])) {
    $_SESSION['feuille_match'][$match_id] = [
        'titulaire' => [],
        'remplacant' => []
    ];

    // Charger les joueurs existants de la feuille de match avec leur poste préféré
    $joueurs_feuille = getJoueursDeFeuilleMatchComplet($match_id); // Inclut les statuts et les postes
    foreach ($joueurs_feuille as $joueur) {
        $joueur_id = $joueur['joueur_id'];
        $statut = $joueur['statut'];
        $poste_prefere = $joueur['poste_prefere'];
        
        // Ajouter le joueur avec son poste préféré dans la session
        $_SESSION['feuille_match'][$match_id][$statut][$joueur_id] = $poste_prefere;
    }
}

// Récupérer les joueurs actifs
$joueurs_actifs = getJoueursActifs();

// Récupérer les évaluations actuelles des joueurs pour ce match
$evaluationsExistantes = getEvaluations($match_id);

// Convertir en tableau associatif avec joueur_id comme clé
$evaluations = [];
foreach ($evaluationsExistantes as $evaluation) {
    $evaluations[$evaluation['joueur_id']] = $evaluation['evaluation'];
}

// Traitement du formulaire d'évaluation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['evaluations'] as $joueur_id => $evaluation) {
        // Vérifie que l'évaluation est un nombre entre 1 et 5
        if (is_numeric($evaluation) && $evaluation >= 1 && $evaluation <= 5) {
            // Mettre à jour l'évaluation du joueur dans la base de données
            setEvaluation($match_id, $joueur_id, (int)$evaluation);
        }
    }
    $message = "Évaluations enregistrées avec succès.";
    // Redirection vers ListeMatch.php après la soumission
    header("Location: ListeMatch.php");
    exit; // Assurez-vous d'appeler exit après une redirection
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
    <h1>Feuille de Match (Évaluation)</h1>

    <?php if (!empty($message)): ?>
        <p style="<?= strpos($message, 'succès') !== false ? 'color: green;' : 'color: red;' ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <h2>Joueurs sélectionnés</h2>
    <h3>Titulaires</h3>
    <form method="POST">
    <ul>
    <?php foreach ($_SESSION['feuille_match'][$match_id]['titulaire'] as $joueur_id => $poste_prefere): ?>
        <?php 
            $joueur = array_filter($joueurs_actifs, fn($j) => $j['id'] === $joueur_id);
            $joueur = reset($joueur);
        ?>
        <li>
            <?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom'] . ' - ' . $poste_prefere) ?>
            <label for="evaluation_<?= $joueur_id ?>">Évaluation (1-5):</label>
            <select name="evaluations[<?= $joueur_id ?>]" id="evaluation_<?= $joueur_id ?>">
                <option value="1" <?= isset($evaluations[$joueur_id]) && $evaluations[$joueur_id] == 1 ? 'selected' : '' ?>>1</option>
                <option value="2" <?= isset($evaluations[$joueur_id]) && $evaluations[$joueur_id] == 2 ? 'selected' : '' ?>>2</option>
                <option value="3" <?= isset($evaluations[$joueur_id]) && $evaluations[$joueur_id] == 3 ? 'selected' : '' ?>>3</option>
                <option value="4" <?= isset($evaluations[$joueur_id]) && $evaluations[$joueur_id] == 4 ? 'selected' : '' ?>>4</option>
                <option value="5" <?= isset($evaluations[$joueur_id]) && $evaluations[$joueur_id] == 5 ? 'selected' : '' ?>>5</option>
            </select>
        </li>
    <?php endforeach; ?>
</ul>


        <h3>Remplaçants</h3>
        <ul>
    <?php foreach ($_SESSION['feuille_match'][$match_id]['remplacant'] as $joueur_id => $poste_prefere): ?>
        <?php 
            $joueur = array_filter($joueurs_actifs, fn($j) => $j['id'] === $joueur_id);
            $joueur = reset($joueur);
        ?>
        <li>
            <?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom'] . ' - ' . $poste_prefere) ?>
            <label for="evaluation_<?= $joueur_id ?>">Évaluation (1-5):</label>
            <select name="evaluations[<?= $joueur_id ?>]" id="evaluation_<?= $joueur_id ?>">
                <option value="1" <?= isset($evaluations[$joueur_id]) && $evaluations[$joueur_id] == 1 ? 'selected' : '' ?>>1</option>
                <option value="2" <?= isset($evaluations[$joueur_id]) && $evaluations[$joueur_id] == 2 ? 'selected' : '' ?>>2</option>
                <option value="3" <?= isset($evaluations[$joueur_id]) && $evaluations[$joueur_id] == 3 ? 'selected' : '' ?>>3</option>
                <option value="4" <?= isset($evaluations[$joueur_id]) && $evaluations[$joueur_id] == 4 ? 'selected' : '' ?>>4</option>
                <option value="5" <?= isset($evaluations[$joueur_id]) && $evaluations[$joueur_id] == 5 ? 'selected' : '' ?>>5</option>
            </select>
        </li>
    <?php endforeach; ?>
</ul>

        <button type="submit">Enregistrer les évaluations</button>
    </form>
</body>
</html>
