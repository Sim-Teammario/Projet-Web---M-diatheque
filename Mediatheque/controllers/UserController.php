<?php
/**
 * UserController - Handles user management
 */
class UserController {
    private $twig;
    private $userModel;
    private $statisticsModel;

    public function __construct($twig) {
        $this->twig = $twig;
        $this->userModel = new UserModel();
        $this->statisticsModel = new StatisticsModel();
    }

    /**
     * Display user profile
     */
    public function profile() {
        // Créer une instance d'AuthController pour utiliser ses méthodes
        $authController = new AuthController($this->twig);
        // Require login
        $authController->requireLogin();

        $userId = $_SESSION['user_id']; // Clé corrigée
        $user = $this->userModel->getUserById($userId);

        if (!$user) {
            // User not found
            http_response_code(404);
            echo $this->twig->render('error.html.twig', [
                'message' => 'Utilisateur non trouvé'
            ]);
            return;
        }

        // Get user stats
        $userStats = $this->statisticsModel->getUserStats($userId);

        echo $this->twig->render('user/profile.html.twig', [
            'title' => 'Profil - Médiathèque',
            'user' => $user,
            'stats' => $userStats
        ]);
    }

    /**
     * Display user list (admin only)
     */
    public function index() {
        // Créer une instance d'AuthController pour utiliser ses méthodes
        $authController = new AuthController($this->twig);
        // Require admin
        $authController->requireAdmin();

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

        // Get all users
        $users = $this->userModel->getAllUsers();

        // Sort by username
        usort($users, function($a, $b) {
            return strcmp($a['username'], $b['username']);
        });

        // Paginate results
        $paginatedData = paginate($users, $page);

        echo $this->twig->render('user/list.html.twig', [
            'title' => 'Utilisateurs - Médiathèque',
            'users' => $paginatedData['items'],
            'pagination' => [
                'currentPage' => $paginatedData['currentPage'],
                'totalPages' => $paginatedData['totalPages'],
                'hasNextPage' => $paginatedData['hasNextPage'],
                'hasPrevPage' => $paginatedData['hasPrevPage']
            ]
        ]);
    }

    /**
     * Display edit user form (admin only)
     */
    public function edit() {
        // Créer une instance d'AuthController pour utiliser ses méthodes
        $authController = new AuthController($this->twig);
        // Require admin
        $authController->requireAdmin();

        // Get user ID from query parameters
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        // Get user
        $user = $this->userModel->getUserById($id);

        if (!$user) {
            // User not found
            http_response_code(404);
            echo $this->twig->render('error.html.twig', [
                'message' => 'Utilisateur non trouvé'
            ]);
            return;
        }

        $error = null;

        // Process edit form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'role' => $_POST['role'] ?? ROLE_USER
            ];

            // If password field is filled, update password
            if (!empty($_POST['password'])) {
                $formData['password'] = $_POST['password'];
            }

            // Validate form
            if (empty($formData['username']) || empty($formData['email'])) {
                $error = 'Les champs Nom d\'utilisateur et Email sont obligatoires';
            } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
                $error = 'Adresse email invalide';
            } else {
                // Check if username or email already exists (except for current user)
                $existingUser = $this->userModel->getUserByUsername($formData['username']);
                if ($existingUser && $existingUser['id'] != $id) {
                    $error = 'Ce nom d\'utilisateur est déjà utilisé';
                } else {
                    $existingEmail = $this->userModel->getUserByEmail($formData['email']);
                    if ($existingEmail && $existingEmail['id'] != $id) {
                        $error = 'Cette adresse email est déjà utilisée';
                    } else {
                        // Update user
                        $updateResult = $this->userModel->updateUser($id, $formData);

                        if ($updateResult) {
                            $_SESSION['flash_message'] = 'Utilisateur modifié avec succès';
                            header('Location: ' . BASE_URL . '?route=user');
                            exit;
                        } else {
                            $error = 'Erreur lors de la modification de l\'utilisateur';
                        }
                    }
                }
            }
        }

        echo $this->twig->render('user/edit.html.twig', [
            'title' => 'Modifier un utilisateur - Médiathèque',
            'user' => $user,
            'error' => $error
        ]);
    }

    /**
     * Delete user (admin only)
     */
    public function delete() {
        // Créer une instance d'AuthController pour utiliser ses méthodes
        $authController = new AuthController($this->twig);
        // Require admin
        $authController->requireAdmin();

        // Check if POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?route=user');
            exit;
        }

        // Get user ID from POST
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        // Prevent deleting yourself
        if ($id == $_SESSION['user_id']) { // Clé corrigée
            $_SESSION['flash_error'] = 'Vous ne pouvez pas supprimer votre propre compte';
            header('Location: ' . BASE_URL . '?route=user');
            exit;
        }

        // Delete user
        $deleteResult = $this->userModel->deleteUser($id);

        if ($deleteResult) {
            $_SESSION['flash_message'] = 'Utilisateur supprimé avec succès';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la suppression de l\'utilisateur';
        }

        // Redirect to user list
        header('Location: ' . BASE_URL . '?route=user');
        exit;
    }
}