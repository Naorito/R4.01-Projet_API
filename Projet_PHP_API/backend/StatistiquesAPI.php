<?php
header('Content-Type: application/json'); // Définir le type de contenu comme JSON

require_once __DIR__ . '/librairie/BD.php'; // Inclure le fichier BD.php

// Vérifier la méthode de la requête
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Récupérer les statistiques
    $statistiques = getStatistiques();

    // Vérifier si des statistiques ont été récupérées
    if (!empty($statistiques)) {
        // Retourner les statistiques en JSON
        echo json_encode([
            'success' => true,
            'data' => $statistiques
        ]);
    } else {
        // Retourner un message d'erreur si aucune statistique n'est disponible
        echo json_encode([
            'success' => false,
            'message' => 'Aucune statistique disponible.'
        ]);
    }
} else {
    // Retourner une erreur si la méthode n'est pas GET
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée. Utilisez GET.'
    ]);
}
?>