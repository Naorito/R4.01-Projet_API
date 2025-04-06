CREATE OR REPLACE TABLE joueurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    numero_licence VARCHAR(50) NOT NULL,
    date_naissance DATE NOT NULL,
    taille FLOAT NOT NULL,
    poids FLOAT NOT NULL,
    statut ENUM('Actif', 'Blessé', 'Suspendu', 'Absent') NOT NULL,
    commentaires VARCHAR(255) DEFAULT NULL
);

CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

INSERT INTO utilisateurs (username, password) VALUES ('Joueur@gmail.com', 'Password');

CREATE OR REPLACE TABLE matchs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_match DATE NOT NULL,
    heure_match TIME NOT NULL,
    equipe_adverse VARCHAR(255) NOT NULL,
    lieu ENUM('Domicile', 'Extérieur') NOT NULL,
    resultat_equipe INT(11) DEFAULT NULL,
    resultat_adverse INT(11) DEFAULT NULL
);

CREATE OR REPLACE TABLE feuillematch (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT,
    joueur_id INT,
    statut ENUM('titulaire', 'remplacant') NOT NULL,
    poste_prefere ENUM('Top Lane', 'Mid Lane', 'Bot Lane ADC', 'Bot Lane Support', 'Jungler') NOT NULL,
    evaluation INT(11) DEFAULT 5,
    CHECK (evaluation BETWEEN 1 AND 5 OR evaluation IS NULL),
    FOREIGN KEY (match_id) REFERENCES matchs(id),
    FOREIGN KEY (joueur_id) REFERENCES joueurs(id)
);

CREATE OR REPLACE TABLE feuillematch (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT,
    joueur_id INT,
    statut ENUM('titulaire', 'remplacant') NOT NULL,
    poste_prefere ENUM('Top Lane', 'Mid Lane', 'Bot Lane ADC', 'Bot Lane Support', 'Jungler') NOT NULL,
    evaluation INT(11) DEFAULT 5,
    CHECK (evaluation BETWEEN 1 AND 5 OR evaluation IS NULL),
    FOREIGN KEY (match_id) REFERENCES matchs(id),
    FOREIGN KEY (joueur_id) REFERENCES joueurs(id)
);