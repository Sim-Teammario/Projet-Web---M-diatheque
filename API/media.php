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

// Déterminer le type de requête
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Récupérer tous les médias ou un média spécifique
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $db->prepare("SELECT * FROM media WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $media = $stmt->fetch();

            if ($media) {
                jsonResponse($media);
            } else {
                http_response_code(404);
                jsonResponse(['error' => 'Média non trouvé']);
            }
        } else {
            $stmt = $db->query("SELECT * FROM media");
            $media = $stmt->fetchAll();
            jsonResponse($media);
        }
        break;

    case 'POST':
        // Créer un nouveau média
        $data = json_decode(file_get_contents('php://input'), true);

        // Valider les données minimales requises
        if (!isset($data['title']) || !isset($data['type']) || !isset($data['creator'])) {
            http_response_code(400);
            jsonResponse(['error' => 'Données incomplètes pour créer un média']);
            exit;
        }

        // S'assurer que available est défini à 1 par défaut (média disponible)
        if (!isset($data['available'])) {
            $data['available'] = 1;
        }

        // Ajouter la date de création
        $data['added_at'] = date('Y-m-d H:i:s');

        // Préparer les champs et valeurs pour l'insertion
        $fields = [];
        $placeholders = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = $key;
            $placeholders[] = ":$key";
            $params[":$key"] = $value;
        }

        // Construire la requête d'insertion
        $query = "INSERT INTO media (" . implode(', ', $fields) . ") 
              VALUES (" . implode(', ', $placeholders) . ")";

        try {
            $stmt = $db->prepare($query);
            $result = $stmt->execute($params);

            if ($result) {
                // Récupérer l'ID du nouveau média
                $newId = $db->lastInsertId();

                // Récupérer le média créé
                $getStmt = $db->prepare("SELECT * FROM media WHERE id = :id");
                $getStmt->execute([':id' => $newId]);
                $newMedia = $getStmt->fetch();

                jsonResponse($newMedia);
            } else {
                http_response_code(500);
                jsonResponse(['error' => 'Erreur lors de la création du média']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            jsonResponse(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
        }
        break;

    case 'PUT':
        // Mettre à jour un média
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            jsonResponse(['error' => 'ID du média manquant']);
            exit;
        }

        $id = $data['id'];
        unset($data['id']); // Enlever l'ID des données à mettre à jour

        // Construire la requête de mise à jour
        $setClause = [];
        $params = [];

        foreach ($data as $key => $value) {
            $setClause[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        if (empty($setClause)) {
            http_response_code(400);
            jsonResponse(['error' => 'Aucune donnée à mettre à jour']);
            exit;
        }

        $params[':id'] = $id;

        $query = "UPDATE media SET " . implode(', ', $setClause) . " WHERE id = :id";
        $stmt = $db->prepare($query);
        $result = $stmt->execute($params);

        if ($result) {
            // Récupérer le média mis à jour
            $getStmt = $db->prepare("SELECT * FROM media WHERE id = :id");
            $getStmt->execute([':id' => $id]);
            $updatedMedia = $getStmt->fetch();

            jsonResponse($updatedMedia);
        } else {
            http_response_code(500);
            jsonResponse(['error' => 'Erreur lors de la mise à jour du média']);
        }
        break;

    default:
        http_response_code(405);
        jsonResponse(['error' => 'Méthode non autorisée']);
}