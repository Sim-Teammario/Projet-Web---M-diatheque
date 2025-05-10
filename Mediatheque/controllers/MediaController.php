<?php
/**
 * MediaController - Handles media-related operations
 */
class MediaController {
    private $twig;
    private $mediaModel;
    private $authController;

    public function __construct($twig) {
        $this->twig = $twig;
        $this->mediaModel = new MediaModel();
        $this->authController = new AuthController($twig);
    }

    /**
     * Display media list
     */
    public function index() {
        // Get page and type from query parameters
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $type = isset($_GET['type']) ? $_GET['type'] : null;

        // Get media items
        $allMedia = $type ? $this->mediaModel->getMediaByType($type) : $this->mediaModel->getAllMedia();

        // Sort by title
        usort($allMedia, function($a, $b) {
            return strcmp($a['title'], $b['title']);
        });

        // Paginate results
        $paginatedData = paginate($allMedia, $page);

        echo $this->twig->render('media/list.html.twig', [
            'title' => 'Liste des médias - Médiathèque',
            'mediaItems' => $paginatedData['items'],
            'pagination' => [
                'currentPage' => $paginatedData['currentPage'],
                'totalPages' => $paginatedData['totalPages'],
                'hasNextPage' => $paginatedData['hasNextPage'],
                'hasPrevPage' => $paginatedData['hasPrevPage']
            ],
            'type' => $type,
            'isAdmin' => $this->authController->isAdmin()
        ]);
    }

    /**
     * Display media details
     */
    public function details() {
        // Tenter de récupérer l'ID du média de plusieurs façons
        $id = 0;

        // Méthode 1: depuis les paramètres GET
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            debug_log("ID récupéré depuis \$_GET['id']: {$id}");
        }
        // Méthode 2: depuis la requête directe
        else if (isset($_SERVER['QUERY_STRING'])) {
            $queryString = $_SERVER['QUERY_STRING'];
            $params = [];
            parse_str($queryString, $params);

            if (isset($params['id'])) {
                $id = intval($params['id']);
                debug_log("ID récupéré depuis QUERY_STRING: {$id}");
            }
        }

        // Méthode 3: Essayer de récupérer l'ID depuis l'URL complète
        if ($id == 0 && isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
            debug_log("REQUEST_URI pour debug: {$uri}");

            // Recherche du paramètre id dans l'URL
            if (preg_match('/[?&]id=(\d+)/', $uri, $matches)) {
                $id = intval($matches[1]);
                debug_log("ID récupéré depuis l'analyse de REQUEST_URI: {$id}");
            }
        }

        debug_log("Details requested for media ID final: {$id}");
        debug_log("GET parameters: " . print_r($_GET, true));

        // Get media
        $media = $this->mediaModel->getMediaById($id);

        if (!$media) {
            // Media not found
            debug_log("Media with ID {$id} not found");
            debug_log("Liste de tous les IDs disponibles: " . print_r(array_column($this->mediaModel->getAllMedia(), 'id'), true));
            http_response_code(404);
            echo $this->twig->render('error.html.twig', [
                'message' => 'Média non trouvé (ID: ' . $id . ')'
            ]);
            return;
        }

        debug_log("Media found: " . print_r($media, true));

        echo $this->twig->render('media/details.html.twig', [
            'title' => $media['title'] . ' - Médiathèque',
            'media' => $media,
            'isLoggedIn' => $this->authController->isLoggedIn(),
            'isAdmin' => $this->authController->isAdmin()
        ]);
    }

    /**
     * Display search page
     */
    public function search() {
        $query = isset($_GET['q']) ? trim($_GET['q']) : '';
        $type = isset($_GET['type']) ? $_GET['type'] : null;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

        $results = [];
        $paginatedData = ['items' => [], 'currentPage' => 1, 'totalPages' => 1, 'hasNextPage' => false, 'hasPrevPage' => false];

        if (!empty($query)) {
            // Search media
            $results = $this->mediaModel->searchMedia($query, $type);

            // Paginate results
            $paginatedData = paginate($results, $page);
        }

        echo $this->twig->render('media/search.html.twig', [
            'title' => 'Recherche - Médiathèque',
            'query' => $query,
            'type' => $type,
            'mediaItems' => $paginatedData['items'],
            'totalResults' => count($results),
            'pagination' => [
                'currentPage' => $paginatedData['currentPage'],
                'totalPages' => $paginatedData['totalPages'],
                'hasNextPage' => $paginatedData['hasNextPage'],
                'hasPrevPage' => $paginatedData['hasPrevPage']
            ]
        ]);
    }

    /**
     * Display add media form
     */
    public function add() {
        // Require admin
        $this->authController->requireAdmin();

        $error = null;
        $formData = [];

        // Process add form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = [
                'title' => $_POST['title'] ?? '',
                'type' => $_POST['type'] ?? '',
                'creator' => $_POST['creator'] ?? '',
                'year' => $_POST['year'] ?? '',
                'description' => $_POST['description'] ?? ''
            ];

            // Add type-specific fields
            switch ($formData['type']) {
                case MEDIA_BOOK:
                    $formData['isbn'] = $_POST['isbn'] ?? '';
                    $formData['pages'] = $_POST['pages'] ?? '';
                    $formData['publisher'] = $_POST['publisher'] ?? '';
                    break;

                case MEDIA_DVD:
                    $formData['duration'] = $_POST['duration'] ?? '';
                    $formData['genre'] = $_POST['genre'] ?? '';
                    break;

                case MEDIA_GAME:
                    $formData['platform'] = $_POST['platform'] ?? '';
                    $formData['publisher'] = $_POST['publisher'] ?? '';
                    $formData['genre'] = $_POST['genre'] ?? '';
                    break;

                case MEDIA_MUSIC:
                    $formData['duration'] = $_POST['duration'] ?? '';
                    $formData['genre'] = $_POST['genre'] ?? '';
                    $formData['artist'] = $_POST['artist'] ?? $formData['creator'];
                    $formData['album'] = $_POST['album'] ?? $formData['title'];
                    break;
            }

            // Validate form
            if (empty($formData['title']) || empty($formData['type']) || empty($formData['creator'])) {
                $error = 'Les champs Titre, Type et Auteur/Créateur sont obligatoires';
            } else {
                // Create media
                $newMedia = $this->mediaModel->createMedia($formData);

                if ($newMedia) {
                    // Redirect to media details
                    header('Location: ' . BASE_URL . '?route=media/details&id=' . $newMedia['id']);
                    exit;
                } else {
                    $error = 'Erreur lors de la création du média';
                }
            }
        }

        echo $this->twig->render('media/add.html.twig', [
            'title' => 'Ajouter un média - Médiathèque',
            'error' => $error,
            'formData' => $formData
        ]);
    }

    /**
     * Display edit media form
     */
    public function edit() {
        // Require admin
        $this->authController->requireAdmin();

        // Get media ID from query parameters
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        // Get media
        $media = $this->mediaModel->getMediaById($id);

        if (!$media) {
            // Media not found
            http_response_code(404);
            echo $this->twig->render('error.html.twig', [
                'message' => 'Média non trouvé'
            ]);
            return;
        }

        $error = null;

        // Process edit form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = [
                'title' => $_POST['title'] ?? '',
                'creator' => $_POST['creator'] ?? '',
                'year' => $_POST['year'] ?? '',
                'description' => $_POST['description'] ?? ''
            ];

            // Add type-specific fields
            switch ($media['type']) {
                case MEDIA_BOOK:
                    $formData['isbn'] = $_POST['isbn'] ?? '';
                    $formData['pages'] = $_POST['pages'] ?? '';
                    $formData['publisher'] = $_POST['publisher'] ?? '';
                    break;

                case MEDIA_DVD:
                    $formData['duration'] = $_POST['duration'] ?? '';
                    $formData['genre'] = $_POST['genre'] ?? '';
                    break;

                case MEDIA_GAME:
                    $formData['platform'] = $_POST['platform'] ?? '';
                    $formData['publisher'] = $_POST['publisher'] ?? '';
                    $formData['genre'] = $_POST['genre'] ?? '';
                    break;

                case MEDIA_MUSIC:
                    $formData['duration'] = $_POST['duration'] ?? '';
                    $formData['genre'] = $_POST['genre'] ?? '';
                    $formData['artist'] = $_POST['artist'] ?? $formData['creator'];
                    $formData['album'] = $_POST['album'] ?? $formData['title'];
                    break;
            }

            // Validate form
            if (empty($formData['title']) || empty($formData['creator'])) {
                $error = 'Les champs Titre et Auteur/Créateur sont obligatoires';
            } else {
                // Update media
                $updateResult = $this->mediaModel->updateMedia($id, $formData);

                if ($updateResult) {
                    // Redirect to media details
                    header('Location: ' . BASE_URL . '?route=media/details&id=' . $id);
                    exit;
                } else {
                    $error = 'Erreur lors de la modification du média';
                }
            }
        }

        echo $this->twig->render('media/edit.html.twig', [
            'title' => 'Modifier un média - Médiathèque',
            'media' => $media,
            'error' => $error
        ]);
    }

    /**
     * Delete media
     */
    public function delete() {
        // Require admin
        $this->authController->requireAdmin();

        // Check if POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?route=media');
            exit;
        }

        // Get media ID from POST
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        // Delete media
        $deleteResult = $this->mediaModel->deleteMedia($id);

        if ($deleteResult) {
            $_SESSION['flash_message'] = 'Média supprimé avec succès';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la suppression du média. Vérifiez qu\'il n\'est pas actuellement emprunté.';
        }

        // Redirect to media list
        header('Location: ' . BASE_URL . '?route=media');
        exit;
    }
}