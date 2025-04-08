<?php

session_start(); // Démarre la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) {
    header("Location: ../Auth/Connexion.php"); // Redirige vers la page de connexion si non connecté
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données
    if (empty($_POST['nom']) || empty($_POST['prenom']) || empty($_POST['numero_licence']) || 
        empty($_POST['date_naissance']) || empty($_POST['taille']) || empty($_POST['poids']) || 
        empty($_POST['statut'])) {
        $message = "Tous les champs sont obligatoires.";
    } else {
        $data = [
            'nom' => $_POST['nom'],
            'prenom' => $_POST['prenom'],
            'numero_licence' => $_POST['numero_licence'],
            'date_naissance' => $_POST['date_naissance'],
            'taille' => (float)$_POST['taille'],
            'poids' => (float)$_POST['poids'],
            'statut' => $_POST['statut']
        ];

        $ch = curl_init("http://naorito.alwaysdata.net/R4.01-Projet_API/Projet_PHP_API/backend/JoueurAPI.php");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $_SESSION['token']
        ]);
        
        $response = curl_exec($ch);
        
        if ($response === false) {
            $message = "Erreur de connexion à l'API: " . curl_error($ch);
        } else {
            $result = json_decode($response, true);
            if ($result && isset($result['success']) && $result['success']) {
                header("Location: ListeJoueur.php");
                exit;
            } else {
                $message = $result['message'] ?? "Erreur lors de la création du joueur.";
            }
        }
        curl_close($ch);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un joueur</title>
    <link rel="stylesheet" href="CSS/styles.css">
</head>
<body>
    <h1>Créer un joueur</h1>

    <?php if (!empty($message)): ?>
        <p style="color: red;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"><br>

        <label for="prenom">Prénom :</label>
        <input type="text" id="prenom" name="prenom" required value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"><br>

        <label for="numero_licence">N° Licence :</label>
        <input type="text" id="numero_licence" name="numero_licence" required value="<?= htmlspecialchars($_POST['numero_licence'] ?? '') ?>"><br>

        <label for="date_naissance">Date de naissance :</label>
        <input type="date" id="date_naissance" name="date_naissance" required value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>"><br>

        <label for="taille">Taille (en m) :</label>
        <input type="number" id="taille" name="taille" step="0.01" required value="<?= htmlspecialchars($_POST['taille'] ?? '') ?>"><br>

        <label for="poids">Poids (en kg) :</label>
        <input type="number" id="poids" name="poids" step="0.1" required value="<?= htmlspecialchars($_POST['poids'] ?? '') ?>"><br>

        <label for="statut">Statut :</label>
        <select id="statut" name="statut" required>
            <option value="actif" <?= (isset($_POST['statut']) && $_POST['statut'] === 'actif') ? 'selected' : '' ?>>Actif</option>
            <option value="blesse" <?= (isset($_POST['statut']) && $_POST['statut'] === 'blesse') ? 'selected' : '' ?>>Blessé</option>
            <option value="suspendu" <?= (isset($_POST['statut']) && $_POST['statut'] === 'suspendu') ? 'selected' : '' ?>>Suspendu</option>
            <option value="absent" <?= (isset($_POST['statut']) && $_POST['statut'] === 'absent') ? 'selected' : '' ?>>Absent</option>
        </select><br>

        <button type="submit">Créer le joueur</button>
        <a href="ListeJoueur.php" class="button">Retour à la liste</a>
    </form>
</body>
</html>