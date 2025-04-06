<?php
session_start(); // Démarrer la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php"); // Rediriger vers la page de connexion si non connecté
    exit;
}

require_once __DIR__ . '/librairie/BD.php'; // Inclure le fichier BD.php

// Récupérer l'ID du match depuis l'URL
if (!isset($_GET['match_id']) || empty($_GET['match_id'])) {
    die("ID du match non spécifié.");
}

$match_id = (int)$_GET['match_id'];
$message = "";

// Récupérer les joueurs actifs
$joueurs_actifs = getJoueursActifs(); // Assurez-vous que cette fonction est définie dans BD.php

// Vérifiez que $joueurs_actifs est un tableau avant d'utiliser foreach
if (!is_array($joueurs_actifs)) {
    $joueurs_actifs = [];
}

// Gestion des ajouts/suppressions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $joueur_id = (int)($_POST['joueur_id'] ?? 0);
        $statut = $_POST['statut'] ?? '';
        $poste_prefere = $_POST['poste_prefere'] ?? '';

        if ($_POST['action'] === 'ajouter') {
            // Ajouter le joueur à la session
            if (!isset($_SESSION['feuille_match'][$match_id][$statut][$joueur_id])) {
                $_SESSION['feuille_match'][$match_id][$statut][$joueur_id] = $poste_prefere;
                $message = "Joueur ajouté avec succès.";
            } else {
                $message = "Ce joueur est déjà sélectionné.";
            }
        } elseif ($_POST['action'] === 'supprimer') {
            // Supprimer le joueur de la session
            foreach (['titulaire', 'remplacant'] as $key) {
                if (isset($_SESSION['feuille_match'][$match_id][$key][$joueur_id])) {
                    unset($_SESSION['feuille_match'][$match_id][$key][$joueur_id]);
                    $message = "Joueur supprimé avec succès.";
                    break;
                }
            }
        } elseif ($_POST['action'] === 'valider') {
            // Valider la sélection et mettre à jour la base de données
            $joueurs = [];
            foreach ($_SESSION['feuille_match'][$match_id] as $statut => $joueurs_statut) {
                foreach ($joueurs_statut as $joueur_id => $poste_prefere) {
                    $joueurs[] = [
                        'joueur_id' => $joueur_id,
                        'statut' => $statut,
                        'poste_prefere' => $poste_prefere
                    ];
                }
            }

            $data = json_encode([
                'match_id' => $match_id,
                'joueurs' => $joueurs
            ]);

            $ch = curl_init("http://localhost/R4.01-Projet_API/Projet_PHP_API/FeuilleMatchAPI.php");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);
            $message = $result['message'];

            // Redirection après validation réussie
            if ($result['success']) {
                header("Location: ListeMatch.php");
                exit;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Feuille de Match</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <h1>Feuille de Match</h1>

    <?php if (!empty($message)): ?>
        <p style="<?= strpos($message, 'succès') !== false ? 'color: green;' : 'color: red;' ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <h2>Joueurs sélectionnés</h2>
    <h3>Titulaires</h3>
    <ul>
        <?php
        $titulaire_found = false;
        if (!empty($_SESSION['feuille_match'][$match_id]['titulaire'])) {
            foreach ($_SESSION['feuille_match'][$match_id]['titulaire'] as $joueur_id => $poste_prefere) {
                $joueur = array_filter($joueurs_actifs, fn($j) => $j['id'] === $joueur_id);
                if (!empty($joueur)) {
                    $joueur = reset($joueur);
                    $titulaire_found = true;
                    ?>
                    <li>
                        <?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom'] . ' - ' . $poste_prefere) ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="joueur_id" value="<?= $joueur_id ?>">
                            <input type="hidden" name="statut" value="titulaire">
                            <input type="hidden" name="action" value="supprimer">
                            <button type="submit">Supprimer</button>
                        </form>
                    </li>
                    <?php
                }
            }
        }
        if (!$titulaire_found) {
            echo "<li>Aucun titulaire sélectionné.</li>";
        }
        ?>
    </ul>

    <h3>Remplaçants</h3>
    <ul>
        <?php
        $remplacant_found = false;
        if (!empty($_SESSION['feuille_match'][$match_id]['remplacant'])) {
            foreach ($_SESSION['feuille_match'][$match_id]['remplacant'] as $joueur_id => $poste_prefere) {
                $joueur = array_filter($joueurs_actifs, fn($j) => $j['id'] === $joueur_id);
                if (!empty($joueur)) {
                    $joueur = reset($joueur);
                    $remplacant_found = true;
                    ?>
                    <li>
                        <?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom'] . ' - ' . $poste_prefere) ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="joueur_id" value="<?= $joueur_id ?>">
                            <input type="hidden" name="statut" value="remplacant">
                            <input type="hidden" name="action" value="supprimer">
                            <button type="submit">Supprimer</button>
                        </form>
                    </li>
                    <?php
                }
            }
        }
        if (!$remplacant_found) {
            echo "<li>Aucun remplaçant sélectionné.</li>";
        }
        ?>
    </ul>

    <h2>Ajouter un joueur</h2>
    <form method="post">
        <label for="joueur_id">Joueur :</label>
        <select name="joueur_id" id="joueur_id" required>
            <option value="">-- Sélectionnez un joueur --</option>
            <?php foreach ($joueurs_actifs as $joueur): ?>
                <?php if (!isset($_SESSION['feuille_match'][$match_id]['titulaire'][$joueur['id']]) && 
                          !isset($_SESSION['feuille_match'][$match_id]['remplacant'][$joueur['id']])): ?>
                    <option value="<?= $joueur['id'] ?>"><?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom']) ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <label for="statut">Statut :</label>
        <select name="statut" id="statut" required>
            <option value="titulaire">Titulaire</option>
            <option value="remplacant">Remplaçant</option>
        </select>

        <label for="poste_prefere">Poste :</label>
        <select name="poste_prefere" id="poste_prefere" required>
            <option value="Top Lane">Top Lane</option>
            <option value="Mid Lane">Mid Lane</option>
            <option value="Bot Lane ADC">Bot Lane ADC</option>
            <option value="Bot Lane Support">Bot Lane Support</option>
            <option value="Jungler">Jungler</option>
        </select>

        <button type="submit" name="action" value="ajouter">Ajouter</button>
    </form>

    <h2>Valider la sélection</h2>
    <form method="post">
        <button type="submit" name="action" value="valider">Valider la sélection</button>
    </form>

</body>
</html>