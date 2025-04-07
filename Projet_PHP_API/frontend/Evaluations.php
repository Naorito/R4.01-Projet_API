<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth/Connexion.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID du match non spécifié.");
}

$match_id = (int)$_GET['id'];
$message = "";

// Récupérer les données via l'API FeuilleMatch
$ch = curl_init("http://localhost/R4.01-Projet_API/Projet_PHP_API/backend/FeuilleMatchAPI.php?match_id=$match_id");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (!$data['success']) {
    die("Erreur lors de la récupération des données: " . ($data['message'] ?? 'Erreur inconnue'));
}

$joueurs = $data['joueurs'];

// Traitement du formulaire d'évaluation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $evaluations = [];
    foreach ($_POST['evaluations'] as $joueur_id => $evaluation) {
        if (is_numeric($evaluation) && $evaluation >= 1 && $evaluation <= 5) {
            $evaluations[] = [
                'joueur_id' => (int)$joueur_id,
                'evaluation' => (int)$evaluation
            ];
        }
    }

    $data_to_send = json_encode([
        'match_id' => $match_id,
        'evaluations' => $evaluations
    ]);

    $ch = curl_init("http://localhost/R4.01-Projet_API/Projet_PHP_API/backend/FeuilleMatchAPI.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_to_send);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    if ($result['success']) {
        header("Location: ListeMatch.php");
        exit;
    } else {
        $message = "Erreur lors de l'enregistrement des évaluations: " . ($result['message'] ?? 'Erreur inconnue');
    }
}

// ... Le reste du code HTML reste identique ...
?>