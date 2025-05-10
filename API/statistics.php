<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Vérifier si le fichier est appelé directement
if (!defined('DB_HOST')) {
    require_once 'config.php';
}

// Récupérer la méthode HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Récupérer les statistiques de la médiathèque
if ($method === 'GET') {
    $db = getDbConnection();
    $statistics = [];

    // Nombre total d'utilisateurs
    $stmt = $db->query("SELECT COUNT(*) as total FROM users");
    $statistics['total_users'] = $stmt->fetch()['total'];

    // Nombre total de médias
    $stmt = $db->query("SELECT COUNT(*) as total FROM media");
    $statistics['total_media'] = $stmt->fetch()['total'];

    // Médias par type
    $stmt = $db->query("SELECT type, COUNT(*) as count FROM media GROUP BY type");
    $statistics['media_by_type'] = $stmt->fetchAll();

    // Nombre de médias disponibles
    $stmt = $db->query("SELECT COUNT(*) as total FROM media WHERE available = 1");
    $statistics['available_media'] = $stmt->fetch()['total'];

    // Nombre total d'emprunts
    $stmt = $db->query("SELECT COUNT(*) as total FROM loans");
    $statistics['total_loans'] = $stmt->fetch()['total'];

    // Emprunts actifs
    $stmt = $db->query("SELECT COUNT(*) as total FROM loans WHERE return_date IS NULL");
    $statistics['active_loans'] = $stmt->fetch()['total'];

    // Emprunts en retard
    $stmt = $db->query("
        SELECT COUNT(*) as total 
        FROM loans 
        WHERE return_date IS NULL 
        AND due_date < NOW()
    ");
    $statistics['overdue_loans'] = $stmt->fetch()['total'];

    // Médias les plus empruntés
    $stmt = $db->query("
        SELECT m.id, m.title, m.type, COUNT(l.id) as loan_count
        FROM media m
        JOIN loans l ON m.id = l.media_id
        GROUP BY m.id
        ORDER BY loan_count DESC
        LIMIT 5
    ");
    $statistics['most_borrowed'] = $stmt->fetchAll();

    // Utilisateurs les plus actifs
    $stmt = $db->query("
        SELECT u.id, u.username, COUNT(l.id) as loan_count
        FROM users u
        JOIN loans l ON u.id = l.user_id
        GROUP BY u.id
        ORDER BY loan_count DESC
        LIMIT 5
    ");
    $statistics['most_active_users'] = $stmt->fetchAll();

    jsonResponse($statistics);
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}