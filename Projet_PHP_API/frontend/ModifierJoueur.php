<?php

session_start(); // Démarre la session

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

$message = '';
$joueur = null;

// Vérifie si un ID est passé en paramètre
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Récupérer les données du joueur
    $ch = curl_init("http://naorito.alwaysdata.net/R4.01-Projet_API/Projet_PHP_API/backend/JoueurAPI.php?id=$id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $_SESSION['token']
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $joueur = json_decode($response, true);

    if (!$joueur) {
        $message = "Joueur introuvable.";
    }
} else {
    $message = "ID manquant.";
}

// Traite le formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id' => (int) $_POST['id'],
        'nom' => $_POST['nom'],
        'prenom' => $_POST['prenom'],
        'numero_licence' => $_POST['numero_licence'],
        'date_naissance' => $_POST['date_naissance'],
        'taille' => (float) $_POST['taille'],
        'poids' => (float) $_POST['poids'],
        'statut' => $_POST['statut'],
        'commentaires' => $_POST['commentaires']
    ];

    $url = 'http://naorito.alwaysdata.net/R4.01-Projet_API/Projet_PHP_API/backend/JoueurAPI.php';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $_SESSION['token']
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($result, true);

    if ($response['success']) {
        header("Location: ListeJoueur.php");
        exit();
    } else {
        $message = $response['message'];
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un joueur</title>
    <link rel="stylesheet" href="CSS/Styles.css">
</head>
<body>
    <h1>Modifier un joueur</h1>

    <?php if (!empty($message)) : ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($joueur) : ?>
        <form method="POST" action="ModifierJoueur.php?id=<?= htmlspecialchars($joueur['id']) ?>">
            <input type="hidden" name="id" value="<?= htmlspecialchars($joueur['id']) ?>">

            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($joueur['nom']) ?>" required><br>

            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($joueur['prenom']) ?>" required><br>

            <label for="numero_licence">N° Licence :</label>
            <input type="text" id="numero_licence" name="numero_licence" value="<?= htmlspecialchars($joueur['numero_licence']) ?>" required><br>

            <label for="date_naissance">Date de naissance :</label>
            <input type="date" id="date_naissance" name="date_naissance" value="<?= htmlspecialchars($joueur['date_naissance']) ?>" required><br>

            <label for="taille">Taille (en cm) :</label>
            <input type="number" id="taille" name="taille" step="0.1" value="<?= htmlspecialchars($joueur['taille']) ?>" required><br>

            <label for="poids">Poids (en kg) :</label>
            <input type="number" id="poids" name="poids" step="0.1" value="<?= htmlspecialchars($joueur['poids']) ?>" required><br>

            <label for="statut">Statut :</label>
            <select id="statut" name="statut" required>
                <option value="Actif" <?= $joueur['statut'] === 'Actif' ? 'selected' : '' ?>>Actif</option>
                <option value="Blessé" <?= $joueur['statut'] === 'Blessé' ? 'selected' : '' ?>>Blessé</option>
                <option value="Suspendu" <?= $joueur['statut'] === 'Suspendu' ? 'selected' : '' ?>>Suspendu</option>
                <option value="Absent" <?= $joueur['statut'] === 'Absent' ? 'selected' : '' ?>>Absent</option>
            </select><br>

            <label for="commentaires">Commentaires :</label>
            <textarea id="commentaires" name="commentaires" maxlength="255"><?= htmlspecialchars($joueur['commentaires'] ?? '') ?></textarea><br>

            <button type="submit">Enregistrer</button>
        </form>
    <?php endif; ?>
</body>
</html>