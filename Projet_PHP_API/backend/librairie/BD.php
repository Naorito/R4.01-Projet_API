<?php
// Configuration pour la connexion à la base de données
const DB_HOST = 'localhost';
const DB_NAME = 'Gestion-Equipe';
const DB_USER = 'root';
const DB_PASSWORD = '';

/**
 * Connexion à la base de données via PDO.
 * @return PDO
 */
function getDbConnection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
    return $pdo;
}

/**
 * Ajoute un joueur dans la base de données.
 * 
 * @param string $nom
 * @param string $prenom
 * @param string $numero_licence
 * @param string $date_naissance
 * @param float $taille
 * @param float $poids
 * @param string $statut
 * @return bool
 */
function ajouterJoueur(string $nom, string $prenom, string $numero_licence, string $date_naissance, float $taille, float $poids, string $statut): bool {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        INSERT INTO joueurs (nom, prenom, numero_licence, date_naissance, taille, poids, statut)
        VALUES (:nom, :prenom, :numero_licence, :date_naissance, :taille, :poids, :statut)
    ");
    return $stmt->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':numero_licence' => $numero_licence,
        ':date_naissance' => $date_naissance,
        ':taille' => $taille,
        ':poids' => $poids,
        ':statut' => $statut,
    ]);
}

function getAllPlayers($pdo) {
    $query = "SELECT id, nom, prenom, numero_licence, date_naissance, taille, poids, statut FROM joueurs";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère tous les joueurs de la base de données.
 * 
 * @return array
 */
function getTousLesJoueurs(): array {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT id, nom, prenom, numero_licence, date_naissance, taille, poids, statut FROM joueurs");
    return $stmt->fetchAll();
}

function getJoueurParId($id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM joueurs WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function modifierJoueur($id, $nom, $prenom, $numero_licence, $date_naissance, $taille, $poids, $statut, $commentaires) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        UPDATE joueurs 
        SET nom = :nom, prenom = :prenom, numero_licence = :numero_licence, 
            date_naissance = :date_naissance, taille = :taille, poids = :poids, 
            statut = :statut, commentaires = :commentaires
        WHERE id = :id
    ");
    return $stmt->execute([
        'id' => $id,
        'nom' => $nom,
        'prenom' => $prenom,
        'numero_licence' => $numero_licence,
        'date_naissance' => $date_naissance,
        'taille' => $taille,
        'poids' => $poids,
        'statut' => $statut,
        'commentaires' => $commentaires
    ]);
}

/**
 * Supprime un joueur de la base de données, uniquement s'il n'a pas participé à un match.
 * 
 * @param int $id
 * @return bool
 */
function supprimerJoueur($id): bool {
    // Connexion à la base de données
    $pdo = getDbConnection();

    // Vérifier si le joueur a déjà participé à un match
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM feuillematch WHERE joueur_id = :id");
    $stmt->execute(['id' => $id]);
    $participation = $stmt->fetchColumn();

    // Si le joueur a participé à un match, on refuse la suppression
    if ($participation > 0) {
        return false; // Le joueur a déjà joué, on ne peut pas le supprimer
    }

    // Sinon, on procède à la suppression
    $stmt = $pdo->prepare("DELETE FROM joueurs WHERE id = :id");
    return $stmt->execute(['id' => $id]);
}

/**
 * Ajouter un match dans la base de données
 *
 * @param string $date_match
 * @param string $heure_match
 * @param string $equipe_adverse
 * @param string $lieu
 * @param string|null $resultat_equipe
 * @param string|null $resultat_adverse
 * @return bool
 */
function ajouterMatch(string $date_match, string $heure_match, string $equipe_adverse, string $lieu, string $resultat_equipe = null, string $resultat_adverse = null) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        INSERT INTO matchs (date_match, heure_match, equipe_adverse, lieu, resultat_equipe, resultat_adverse)
        VALUES (:date_match, :heure_match, :equipe_adverse, :lieu, :resultat_equipe, :resultat_adverse)
    ");
    $stmt->execute([
        ':date_match' => $date_match,
        ':heure_match' => $heure_match,
        ':equipe_adverse' => $equipe_adverse,
        ':lieu' => $lieu,
        ':resultat_equipe' => $resultat_equipe,
        ':resultat_adverse' => $resultat_adverse
    ]);
    
    // Récupérer l'ID du match ajouté
    return $pdo->lastInsertId();  // Retourne l'ID du match
}


/**
 * Récupérer tous les matchs
 *
 * @return array
 */
function getAllMatchs() {
    $pdo = getDbConnection(); // Appel à la fonction de connexion
    $stmt = $pdo->query("SELECT id, date_match, heure_match, equipe_adverse, lieu, resultat_equipe, resultat_adverse FROM matchs ORDER BY date_match DESC, heure_match DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Supprime un match de la base de données.
 * 
 * @param int $id
 * @return bool
 */
function supprimerMatch($id): bool {
    $pdo = getDbConnection();

    // Récupérer la date et l'heure du match
    $stmt = $pdo->prepare("SELECT date_match, heure_match FROM matchs WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $match = $stmt->fetch();

    if ($match) {
        // Fusionner la date et l'heure pour créer un objet DateTime
        $date_heure_match = new DateTime($match['date_match'] . ' ' . $match['heure_match']);
        $now = new DateTime();

        // Vérifier si le match a déjà eu lieu
        if ($date_heure_match < $now) {
            return false; // Le match a déjà eu lieu, ne pas supprimer
        }

        // Supprimer d'abord les joueurs associés au match
        $stmt = $pdo->prepare("DELETE FROM feuillematch WHERE match_id = :id");
        $stmt->execute(['id' => $id]);

        // Supprimer ensuite le match
        $stmt = $pdo->prepare("DELETE FROM matchs WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    return false; // Si le match n'existe pas, retourner false
}

function getMatchParId($id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM matchs WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function modifierMatch($id, $date_match, $heure_match, $equipe_adverse, $lieu) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        UPDATE matchs
        SET date_match = :date_match,
            heure_match = :heure_match,
            equipe_adverse = :equipe_adverse,
            lieu = :lieu
        WHERE id = :id
    ");
    return $stmt->execute([
        'date_match' => $date_match,
        'heure_match' => $heure_match,
        'equipe_adverse' => $equipe_adverse,
        'lieu' => $lieu,
        'id' => $id
    ]);
}

function getJoueursActifs() {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT id, nom, prenom, taille, poids FROM joueurs WHERE statut = 'actif' ORDER BY nom, prenom");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function ajouterJoueurFeuilleMatch($match_id, $joueur_id, $statut, $poste_prefere) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        INSERT INTO feuillematch (match_id, joueur_id, statut, poste_prefere)
        VALUES (:match_id, :joueur_id, :statut, :poste_prefere)
    ");
    $stmt->execute([
        ':match_id' => $match_id,
        ':joueur_id' => $joueur_id,
        ':statut' => $statut,
        ':poste_prefere' => $poste_prefere
    ]);
}

function getJoueursDeFeuilleMatchComplet($match_id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT joueur_id, statut, poste_prefere FROM feuillematch WHERE match_id = :match_id");
    $stmt->execute(['match_id' => $match_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function supprimerJoueurDeFeuilleMatch($match_id, $joueur_id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("DELETE FROM feuillematch WHERE match_id = :match_id AND joueur_id = :joueur_id");
    $stmt->execute(['match_id' => $match_id, 'joueur_id' => $joueur_id]);
}

function supprimerTousJoueursDeFeuilleMatch($match_id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("DELETE FROM feuillematch WHERE match_id = :match_id");
    $stmt->execute(['match_id' => $match_id]);
}

// Fonction pour récupérer les statistiques des matchs et des joueurs
function getStatistiques() {
    $pdo = getDbConnection();

    // Statistiques générales sur les matchs
    $queryMatchs = "SELECT 
                        COUNT(*) AS total_matchs,
                        SUM(CASE WHEN resultat_equipe > resultat_adverse THEN 1 ELSE 0 END) AS matchs_gagnés,
                        SUM(CASE WHEN resultat_equipe < resultat_adverse THEN 1 ELSE 0 END) AS matchs_perdus,
                        SUM(CASE WHEN resultat_equipe = resultat_adverse THEN 1 ELSE 0 END) AS matchs_nuls
                    FROM matchs
                    WHERE resultat_equipe IS NOT NULL AND resultat_adverse IS NOT NULL";  // Exclure les matchs sans résultat
    $stmtMatchs = $pdo->query($queryMatchs);
    $matchs = $stmtMatchs->fetch(PDO::FETCH_ASSOC);

    // Statistiques des joueurs
    $queryJoueurs = "SELECT 
                        j.id,
                        j.nom,
                        j.prenom,
                        j.statut,
                        fm.poste_prefere,
                        COUNT(CASE WHEN fm.statut = 'titulaire' THEN 1 END) AS titulaire_count,
                        COUNT(CASE WHEN fm.statut = 'remplaçant' THEN 1 END) AS remplaçant_count,
                        AVG(fm.evaluation) AS moyenne_evaluation,  -- Calculer la moyenne des évaluations depuis la table feuillematch
                        COUNT(m.id) AS total_matchs,  -- Total des matchs pour le joueur
                        SUM(CASE WHEN m.resultat_equipe > m.resultat_adverse THEN 1 ELSE 0 END) AS matchs_gagnés
                    FROM joueurs j
                    LEFT JOIN feuillematch fm ON j.id = fm.joueur_id
                    LEFT JOIN matchs m ON fm.match_id = m.id
                    WHERE fm.joueur_id IS NOT NULL  -- Exclure les joueurs supprimés de feuillematch
                    AND m.resultat_equipe IS NOT NULL AND m.resultat_adverse IS NOT NULL  -- Exclure les matchs sans résultat
                    GROUP BY j.id
                    HAVING COUNT(fm.match_id) > 0";  // Exclure les joueurs sans matchs
    $stmtJoueurs = $pdo->query($queryJoueurs);
    $joueurs = $stmtJoueurs->fetchAll(PDO::FETCH_ASSOC);

    // Calcul du pourcentage de victoires et gestion des joueurs sans match
    foreach ($joueurs as &$joueur) {
        // Vérifie si le joueur a des matchs
        if ($joueur['total_matchs'] > 0) {
            $joueur['pourcentage_victoires'] = ($joueur['matchs_gagnés'] / $joueur['total_matchs']) * 100;
            $joueur['selections_consecutives'] = getSelectionsConsecutives($joueur['id']);  // Ajouter les sélections consécutives
        } else {
            $joueur['pourcentage_victoires'] = 'Aucune donnée';  // Pas de match, donc message spécifique
            $joueur['moyenne_evaluation'] = 'Aucune donnée';     // Pas de match, donc pas d'évaluation
            $joueur['selections_consecutives'] = 'Aucune donnée';  // Pas de match, donc pas de sélections consécutives
        }
    }

    return [
        'matchs' => $matchs,
        'joueurs' => $joueurs
    ];
}

/**
 * Modifier les résultats d'un match
 *
 * @param int $id
 * @param int|null $resultat_equipe
 * @param int|null $resultat_adverse
 * @return bool
 */
function modifierResultat($id, $resultat_equipe, $resultat_adverse) {
    $pdo = getDbConnection(); // Connexion à la base de données
    $sql = "UPDATE matchs SET resultat_equipe = :resultat_equipe, resultat_adverse = :resultat_adverse WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':resultat_equipe', $resultat_equipe, PDO::PARAM_INT);
    $stmt->bindParam(':resultat_adverse', $resultat_adverse, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}

// Récupère les évaluations des joueurs pour un match donné
function getEvaluations($match_id) {
    $pdo = getDbConnection(); // Remplace par ta fonction de connexion à la base de données
    $sql = "SELECT joueur_id, evaluation FROM feuillematch WHERE match_id = :match_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':match_id' => $match_id]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Met à jour l'évaluation d'un joueur pour un match donné
function setEvaluation($match_id, $joueur_id, $evaluation) {
    $pdo = getDbConnection(); // Remplace par ta fonction de connexion à la base de données
    $sql = "UPDATE feuillematch SET evaluation = :evaluation WHERE match_id = :match_id AND joueur_id = :joueur_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':match_id' => $match_id,
        ':joueur_id' => $joueur_id,
        ':evaluation' => $evaluation
    ]);
}

function getSelectionsConsecutives($joueur_id) {
    $pdo = getDbConnection();
    
    // Récupérer tous les matchs pour le joueur, triés par date et heure
    $query = "SELECT 
                m.date_match, 
                m.heure_match
              FROM matchs m
              JOIN feuillematch fm ON m.id = fm.match_id
              WHERE fm.joueur_id = :joueur_id
              ORDER BY m.date_match ASC, m.heure_match ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['joueur_id' => $joueur_id]);
    $matchs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si aucun match n'est trouvé, retourner 0
    if (empty($matchs)) {
        return 0;
    }

    $maxConsecutifs = 1;  // Minimum un match consécutif
    $consecutifsActuels = 1;
    
    // Comparer les dates et heures des matchs pour calculer les consécutifs
    for ($i = 1; $i < count($matchs); $i++) {
        $dateActuelle = $matchs[$i]['date_match'];
        $heureActuelle = $matchs[$i]['heure_match'];
        $datePrecedente = $matchs[$i - 1]['date_match'];
        $heurePrecedente = $matchs[$i - 1]['heure_match'];
        
        // Comparer la date et l'heure pour vérifier si le match est consécutif
        $dateDiff = (strtotime($dateActuelle) - strtotime($datePrecedente)) / 86400;  // Différence en jours
        
        // Vérifier si les matchs sont consécutifs (différence de 1 jour ou moins)
        if ($dateDiff == 1 || ($dateDiff == 0 && strtotime($heureActuelle) > strtotime($heurePrecedente))) {
            $consecutifsActuels++;
        } else {
            $maxConsecutifs = max($maxConsecutifs, $consecutifsActuels);
            $consecutifsActuels = 1;  // Réinitialiser le compteur pour la nouvelle séquence
        }
    }
    
    // Retourner le maximum des matchs consécutifs
    return max($maxConsecutifs, $consecutifsActuels);
}

function createJWT($header, $payload, $secret) {
    $headerEncoded = base64_encode(json_encode($header));
    $payloadEncoded = base64_encode(json_encode($payload));
    $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
    $signatureEncoded = base64_encode($signature);
    return "$headerEncoded.$payloadEncoded.$signatureEncoded";
}

function verifyJWT($jwt, $secret) {
    list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $jwt);
    $signature = base64_decode($signatureEncoded);
    $expectedSignature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);

    if ($signature !== $expectedSignature) {
        return false; // Signature invalide
    }

    $payload = json_decode(base64_decode($payloadEncoded), true);
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return false; // Jeton expiré
    }

    return $payload; // Retourne la charge utile si tout est valide
}

?>
