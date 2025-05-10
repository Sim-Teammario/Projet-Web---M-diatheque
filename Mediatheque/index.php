<?php
/**
 * Main entry point for the Media Library Management System
 * Handles routing and initializes the application
 */

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log de débogage
function debug_log($message) {
    error_log("[DEBUG] " . $message);
    if (isset($_ENV['DEBUG']) && $_ENV['DEBUG']) {
        // Écrire dans le fichier de log standard
        error_log("[DEBUG] " . $message);
    }
}

// précharger les modèles
require_once 'models/ApiClient.php';
require_once 'models/UserModel.php';
require_once 'models/MediaModel.php';
require_once 'models/LoanModel.php';
require_once 'models/StatisticsModel.php';

debug_log("=== DÉBUT DU TRAITEMENT PHP ===");
debug_log("GET: " . print_r($_GET, true));
debug_log("SERVER: " . print_r($_SERVER['QUERY_STRING'] ?? 'no_query_string', true));
debug_log("ENV: " . print_r($_ENV, true));

// Start session
session_start();

// Load dependencies
require_once 'vendor/autoload.php';
require_once 'config.php';

// Load all models
$modelFiles = glob('models/*.php');
debug_log("Chargement des modèles: " . implode(", ", $modelFiles));
foreach ($modelFiles as $modelFile) {
    require_once $modelFile;
}

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader, [
    'cache' => false, // No cache for development
    'debug' => true,
]);

// Add Twig extensions for debugging
$twig->addExtension(new \Twig\Extension\DebugExtension());

// Add global variables to Twig
$twig->addGlobal('session', $_SESSION);
$twig->addGlobal('baseUrl', BASE_URL);

// Get the route from query string
debug_log("Analysing route...");
debug_log("GET array content: " . print_r($_GET, true));
debug_log("REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'No REQUEST_URI'));

// Priorité donnée au paramètre POST route (pour les formulaires)
if (isset($_POST['route'])) {
    $route = $_POST['route'];
    debug_log("Route trouvée dans _POST: " . $route);
    // Pour le débogage, afficher le contenu de POST
    debug_log("POST content: " . print_r($_POST, true));
}
// Ensuite, vérifier dans QUERY_STRING
else {
    $queryString = $_SERVER['QUERY_STRING'] ?? '';
    debug_log("QUERY_STRING direct from SERVER: " . $queryString);

    if (!empty($queryString)) {
        // Parse la chaîne de requête avec parse_str pour gérer correctement les URL encodées
        $params = [];
        parse_str($queryString, $params);

        if (isset($params['route'])) {
            $route = urldecode($params['route']);
            debug_log("Route trouvée dans QUERY_STRING: " . $route);
            // Si l'ID est présent, le logger aussi
            if (isset($params['id'])) {
                debug_log("ID trouvé dans QUERY_STRING: " . $params['id']);
            }
        } else {
            $route = 'home';
            debug_log("Aucune route trouvée dans QUERY_STRING, utilisation de la valeur par défaut 'home'");
        }
    } else if (isset($_GET['route'])) {
        $route = $_GET['route'];
        debug_log("Route trouvée dans _GET: " . $route);
    } else {
        $route = 'home';
        debug_log("Aucune route trouvée, utilisation de la valeur par défaut 'home'");
    }
}

debug_log("Route finale: " . $route);

// Traitement spécial pour les routes d'authentification
if ($route === 'auth/login') {
    require_once "controllers/AuthController.php";
    $controller = new AuthController($twig);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Traitement du formulaire de connexion
        $controller->login();
    } else {
        // Affichage du formulaire de connexion
        $controller->showLoginForm();
    }

    debug_log("=== FIN DU TRAITEMENT PHP ===");
    exit;
}
else if ($route === 'auth/register') {
    require_once "controllers/AuthController.php";
    $controller = new AuthController($twig);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Traitement du formulaire d'inscription
        $controller->register();
    } else {
        // Affichage du formulaire d'inscription
        $controller->showRegistrationForm();
    }

    debug_log("=== FIN DU TRAITEMENT PHP ===");
    exit;
}

// Extract controller and action from route for les autres routes
$parts = explode('/', $route);
$controllerName = ucfirst($parts[0] ?? 'home') . 'Controller';
$action = $parts[1] ?? 'index';

// Sanitize controller and action names
$controllerName = preg_replace('/[^a-zA-Z0-9]/', '', $controllerName);
$action = preg_replace('/[^a-zA-Z0-9]/', '', $action);

debug_log("Controller: {$controllerName}, Action: {$action}");
debug_log("Checking if controller file exists: controllers/{$controllerName}.php");

// Check if controller exists
if (file_exists("controllers/{$controllerName}.php")) {
    debug_log("Controller file exists");
    require_once "controllers/{$controllerName}.php";

    // Check if class exists
    if (class_exists($controllerName)) {
        debug_log("Controller class exists");

        // Create controller instance
        $controller = new $controllerName($twig);

        // Check if action exists
        if (method_exists($controller, $action)) {
            debug_log("Action method exists, calling it");

            // Call controller action
            $controller->$action();
        } else {
            debug_log("Action '{$action}' not found in controller");

            // Action not found
            http_response_code(404);
            echo $twig->render('error.html.twig', [
                'message' => "Action '{$action}' not found",
                'title' => 'Erreur 404 - Médiathèque'
            ]);
        }
    } else {
        debug_log("Controller class does not exist, even though file exists");

        // Controller class doesn't exist
        http_response_code(500);
        echo $twig->render('error.html.twig', [
            'message' => "Erreur interne: La classe '{$controllerName}' n'existe pas",
            'title' => 'Erreur 500 - Médiathèque'
        ]);
    }
} else {
    debug_log("Controller file does not exist: controllers/{$controllerName}.php");

    // Controller not found, default to home
    if ($controllerName !== 'HomeController') {
        debug_log("Redirecting to HomeController");
        require_once "controllers/HomeController.php";
        $controller = new HomeController($twig);
        $controller->index();
    } else {
        debug_log("HomeController not found either");

        // Controller still not found
        http_response_code(404);
        echo $twig->render('error.html.twig', [
            'message' => "Page not found",
            'title' => 'Erreur 404 - Médiathèque'
        ]);
    }
}

debug_log("=== FIN DU TRAITEMENT PHP ===");