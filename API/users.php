<?php

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Vérifier si le fichier est appelé directement
if (!defined('DB_HOST')) {
    require_once 'config.php';
}

// Connexion à la base de données
$db = getDbConnection();

// Obtenir la méthode HTTP
$method = $_SERVER['REQUEST_METHOD'];

error_log("API users.php - Method: " . $method);

// Gestion des demandes CORS (pre-flight requests)
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Traiter selon la méthode
switch ($method) {
    case 'GET':
        // Récupérer tous les utilisateurs
        $stmt = $db->query("SELECT * FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Renvoyer les données au format JSON
        jsonResponse($users);
        break;

    case 'POST':
        error_log("Processing POST request to users.php");

        // Récupérer les données
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        // Fallback vers $_POST si json_decode échoue
        if ($data === null) {
            $data = $_POST;
        }

        error_log("Received data: " . print_r($data, true));

        // Vérifier que toutes les données nécessaires sont présentes
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            jsonResponse(['error' => 'Données incomplètes']);
        }

        try {
            // Préparer la requête d'insertion
            $stmt = $db->prepare("INSERT INTO users (username, email, password, role, created_at) 
                                 VALUES (:username, :email, :password, :role, :created_at)");

            // Définir les paramètres
            $params = [
                ':username' => $data['username'],
                ':email' => $data['email'],
                ':password' => $data['password'],
                ':role' => $data['role'] ?? 'user',
                ':created_at' => $data['created_at'] ?? date('Y-m-d H:i:s')
            ];

            error_log("Executing query with params: " . print_r($params, true));

            // Exécuter la requête
            $result = $stmt->execute($params);

            if ($result) {
                // Récupérer l'ID inséré
                $userId = $db->lastInsertId();

                // Récupérer l'utilisateur créé
                $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
                $stmt->execute([':id' => $userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                error_log("User created: " . print_r($user, true));

                // Renvoyer l'utilisateur créé
                jsonResponse($user);
            } else {
                http_response_code(500);
                jsonResponse(['error' => 'Erreur lors de la création de l\'utilisateur']);
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            http_response_code(500);
            jsonResponse(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
        }
        break;

    case 'PUT':
        // Mettre à jour un utilisateur
        $urlParts = explode('/', rtrim($_SERVER['REQUEST_URI'], '/'));
        $userId = end($urlParts);

        if (!is_numeric($userId)) {
            http_response_code(400);
            jsonResponse(['error' => 'ID utilisateur non valide']);
        }

        // Récupérer les données
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if ($data === null) {
            http_response_code(400);
            jsonResponse(['error' => 'Données JSON non valides']);
        }

        try {
            // Construire la requête de mise à jour
            $setFields = [];
            $params = [':id' => $userId];

            foreach ($data as $field => $value) {
                if (in_array($field, ['username', 'email', 'password', 'role'])) {
                    $setFields[] = "$field = :$field";
                    $params[":$field"] = $value;
                }
            }

            if (empty($setFields)) {
                http_response_code(400);
                jsonResponse(['error' => 'Aucun champ valide à mettre à jour']);
            }

            $sql = "UPDATE users SET " . implode(', ', $setFields) . " WHERE id = :id";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                // Récupérer l'utilisateur mis à jour
                $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
                $stmt->execute([':id' => $userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                jsonResponse($user);
            } else {
                http_response_code(500);
                jsonResponse(['error' => 'Erreur lors de la mise à jour de l\'utilisateur']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            jsonResponse(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
        }
        break;

    case 'DELETE':
        // Supprimer un utilisateur
        $urlParts = explode('/', rtrim($_SERVER['REQUEST_URI'], '/'));
        $userId = end($urlParts);

        if (!is_numeric($userId)) {
            http_response_code(400);
            jsonResponse(['error' => 'ID utilisateur non valide']);
        }

        try {
            $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $result = $stmt->execute([':id' => $userId]);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Utilisateur supprimé']);
            } else {
                http_response_code(500);
                jsonResponse(['error' => 'Erreur lors de la suppression de l\'utilisateur']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            jsonResponse(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        jsonResponse(['error' => 'Méthode non autorisée']);
        break;
}