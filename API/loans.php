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

// Pour OPTIONS (pre-flight cors requests)
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Récupérer l'ID depuis l'URL si présent (maintenant disponible via $_GET['id'])
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

error_log("API loans.php - Method: " . $method);
error_log("API loans.php - URI: " . $_SERVER['REQUEST_URI']);
error_log("API loans.php - ID from GET: " . ($id ? $id : 'null'));

switch ($method) {
    case 'GET':
        // Si un ID est fourni, récupérer un prêt spécifique
        if ($id) {
            $stmt = $db->prepare("SELECT * FROM loans WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $loan = $stmt->fetch();

            if ($loan) {
                echo json_encode($loan);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Prêt non trouvé']);
            }
        } else {
            // Sinon, récupérer tous les prêts
            $stmt = $db->query("SELECT * FROM loans");
            $loans = $stmt->fetchAll();
            echo json_encode($loans);
        }
        break;

    case 'POST':
        // Créer un nouvel emprunt
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['user_id']) || !isset($data['media_id']) || !isset($data['loan_date']) || !isset($data['due_date'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Données d\'emprunt incomplètes']);
            exit;
        }

        // Préparer la requête
        $stmt = $db->prepare("INSERT INTO loans (user_id, media_id, loan_date, due_date) 
                             VALUES (:user_id, :media_id, :loan_date, :due_date)");

        // Exécuter la requête
        $result = $stmt->execute([
            ':user_id' => $data['user_id'],
            ':media_id' => $data['media_id'],
            ':loan_date' => $data['loan_date'],
            ':due_date' => $data['due_date']
        ]);

        if ($result) {
            // Récupérer l'ID de l'emprunt créé
            $loanId = $db->lastInsertId();

            // Mettre à jour le statut du média
            $mediaStmt = $db->prepare("UPDATE media SET available = 0 WHERE id = :media_id");
            $mediaStmt->execute([':media_id' => $data['media_id']]);

            // Récupérer l'emprunt créé
            $newLoanStmt = $db->prepare("SELECT * FROM loans WHERE id = :id");
            $newLoanStmt->execute([':id' => $loanId]);
            $newLoan = $newLoanStmt->fetch();

            echo json_encode($newLoan);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la création de l\'emprunt']);
        }
        break;

    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID d\'emprunt manquant dans l\'URL']);
            exit;
        }

        // Récupérer les données envoyées
        $data = json_decode(file_get_contents('php://input'), true);
        error_log("API loans.php - PUT data: " . json_encode($data));

        // Vérifier si c'est une demande de retour
        if (isset($data['return_date'])) {
            // Récupérer l'ID du média pour pouvoir le marquer comme disponible
            $mediaIdStmt = $db->prepare("SELECT media_id FROM loans WHERE id = :id");
            $mediaIdStmt->execute([':id' => $id]);
            $mediaInfo = $mediaIdStmt->fetch();

            if (!$mediaInfo) {
                http_response_code(404);
                echo json_encode(['error' => 'Emprunt non trouvé']);
                exit;
            }

            $mediaId = $mediaInfo['media_id'];

            // Mettre à jour l'emprunt avec la date de retour
            $stmt = $db->prepare("UPDATE loans SET return_date = :return_date WHERE id = :id");
            $result = $stmt->execute([
                ':return_date' => $data['return_date'],
                ':id' => $id
            ]);

            if ($result) {
                // Marquer le média comme disponible
                $mediaStmt = $db->prepare("UPDATE media SET available = 1 WHERE id = :media_id");
                $mediaStmt->execute([':media_id' => $mediaId]);

                // Récupérer l'emprunt mis à jour
                $updatedLoanStmt = $db->prepare("SELECT * FROM loans WHERE id = :id");
                $updatedLoanStmt->execute([':id' => $id]);
                $updatedLoan = $updatedLoanStmt->fetch();

                // S'assurer que media_id est inclus dans la réponse
                if (!isset($updatedLoan['media_id'])) {
                    $updatedLoan['media_id'] = $mediaId;
                }

                echo json_encode($updatedLoan);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors du retour de l\'emprunt']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Données de retour manquantes']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
}