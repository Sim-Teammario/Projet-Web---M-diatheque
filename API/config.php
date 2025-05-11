<?php
// Désactiver les erreurs en production (commenter pour voir les erreurs en développement)
// error_reporting(0);

// Activer CORS pour permettre les requêtes depuis n'importe quelle origine
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Répondre directement aux requêtes OPTIONS (pre-flight CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_PORT', '3307');  //port spécifique à ma configuration, à adapter selon la configuration
define('DB_NAME', 'mediatheque');
define('DB_USER', 'root'); // À adapter selon la configuration
define('DB_PASS', '');     // À adapter selon la configuration

// Fonction pour obtenir la connexion PDO
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        //lignes pour forcer l'utilisation d'UTF-8
        $pdo->exec("SET NAMES utf8mb4");
        $pdo->exec("SET CHARACTER SET utf8mb4");
        $pdo->exec("SET COLLATION_CONNECTION = 'utf8mb4_unicode_ci'");

        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    }
}

// Fonction pour répondre avec un JSON
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Récupérer les données du corps de la requête JSON
function getRequestData() {
    $json = file_get_contents('php://input');
    return json_decode($json, true) ?? [];
}

// Vérifier si une chaîne est vide ou nulle
function isEmpty($value) {
    return $value === null || trim($value) === '';
}
