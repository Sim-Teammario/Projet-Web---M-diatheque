<?php
/**
 * Configuration file for the Media Library Management System
 */

// Base URL for the application
define('BASE_URL', '/Mediatheque/'); // Assure-toi que cela correspond à ton installation

// API URL
define('API_URL', 'http://api.local');

// Pagination settings
define('ITEMS_PER_PAGE', 10);

// User roles
define('ROLE_USER', 'user');
define('ROLE_ADMIN', 'admin');

// Media types
define('MEDIA_BOOK', 'book');
define('MEDIA_DVD', 'dvd');
define('MEDIA_GAME', 'game');
define('MEDIA_MUSIC', 'music');

// Ne déclare la fonction de débogage que si elle n'existe pas déjà
if (!function_exists('debug_log')) {
    function debug_log($message) {
        if (defined('DEBUG') && DEBUG) {
            error_log($message);
        }
    }
}

// Ces définitions ne sont plus utilisées mais peuvent être conservées pour la compatibilité
// avec d'autres parties du code qui pourraient y faire référence
define('USERS_FILE', 'data/users.json');
define('MEDIA_FILE', 'data/media.json');
define('LOANS_FILE', 'data/loans.json');

// Helper function for pagination
function paginate($items, $page = 1, $perPage = ITEMS_PER_PAGE) {
    $page = max(1, $page);
    $totalItems = count($items);
    $totalPages = ceil($totalItems / $perPage);

    $offset = ($page - 1) * $perPage;
    $paginatedItems = array_slice($items, $offset, $perPage);

    return [
        'items' => $paginatedItems,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'totalItems' => $totalItems,
        'hasNextPage' => $page < $totalPages,
        'hasPrevPage' => $page > 1
    ];
}