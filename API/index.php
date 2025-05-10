<?php
require_once 'config.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// Pour OPTIONS (pre-flight cors requests)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Récupérer l'URL demandée
$requestUri = $_SERVER['REQUEST_URI'];

// Extraire le chemin
$path = parse_url($requestUri, PHP_URL_PATH);
$path = trim($path, '/');

// Extraire l'ID si présent dans le chemin (format: endpoint/id)
$pathParts = explode('/', $path);
$endpoint = $pathParts[0] ?? '';
$id = isset($pathParts[1]) ? intval($pathParts[1]) : null;

// Définir une variable globale pour l'ID
if ($id) {
    $_GET['id'] = $id;
    error_log("API Request - Endpoint: $endpoint, ID: $id");
}

// Si le chemin est vide, afficher un message d'accueil
if (empty($endpoint)) {
    jsonResponse([
        'message' => 'Mediatheque API',
        'endpoints' => [
            '/users - Récupérer tous les utilisateurs',
            '/media - Récupérer tous les médias',
            '/loans - Récupérer tous les emprunts'
        ]
    ]);
}

// Router vers le fichier approprié en fonction du chemin
switch ($endpoint) {
    case 'users':
        require_once 'users.php';
        break;
    case 'media':
        require_once 'media.php';
        break;
    case 'loans':
        require_once 'loans.php';
        break;
    default:
        jsonResponse(['error' => 'Endpoint non trouvé'], 404);
}