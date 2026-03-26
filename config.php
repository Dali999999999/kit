<?php

/**
 * Fonction de connexion sécurisée à la base de données via PDO
 * Cette fonction est conçue pour fonctionner parfaitement avec les fonctions CRUD générées.
 * 
 * @param string $host L'hôte de la base de données (ex: 'localhost')
 * @param string $dbname Le nom de la base de données à laquelle se connecter
 * @param string $user L'utilisateur de la base de données (ex: 'root')
 * @param string $pass Le mot de passe de la base de données
 * @return PDO Retourne l'instance PDO représentant la connexion
 */
function connectbd($host = 'localhost', $dbname = 'votre_base_de_donnees', $user = 'root', $pass = '') {
    try {
        // Construction du DSN (Data Source Name)
        // utf8mb4 garantit la bonne prise en charge de tous les caractères (y compris les emojis)
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        
        // Configuration des options PDO pour un comportement optimal et sécurisé
        $options = [
            // Lève une exception (PDOException) à chaque erreur SQL (Très important pour le debug)
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Retourne les résultats sous forme de tableau associatif par défaut (Ex: $row['nom'])
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Désactive l'émulation, ce qui confie la préparation réelle à MySQL (Plus performant et sûr)
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        // Tentative de connexion
        $pdo = new PDO($dsn, $user, $pass, $options);
        
        return $pdo;
        
    } catch (PDOException $e) {
        // Si la connexion fige, on intercepte l'erreur proprement
        // En production, il vaut mieux loguer l'erreur plutôt que de l'afficher avec die()
        die("Erreur de connexion à la BDD : " . $e->getMessage());
    }
}

// --- Exemple d'utilisation dans vos projets ---
// 1. Inclure ce fichier :
//    require_once 'config.php';
//
// 2. Initialiser la connexion (A adapter avec vos identifiants) :
//    $pdo = connectbd('localhost', 'nom_de_la_bdd_generée', 'root', '');
//
// 3. Utiliser avec une fonction générée :
//    $utilisateurs = get_all_utilisateurs($pdo);
//    print_r($utilisateurs);

?>
