<?php
/**
 * AuthController - Handles user authentication
 */
class AuthController {
    private $userModel;
    private $twig;

    /**
     * Constructor
     * @param Twig\Environment $twig
     */
    public function __construct($twig) {
        $this->userModel = new UserModel();
        $this->twig = $twig;
    }

    /**
     * Display login form
     */
    public function showLoginForm() {
        echo $this->twig->render('login.html.twig', [
            'title' => 'Connexion',
            'error' => $_SESSION['login_error'] ?? null
        ]);

        // Clear error message after displaying
        unset($_SESSION['login_error']);
    }

    /**
     * Handle login form submission
     */
    public function login() {
        debug_log("Login method called, Request Method: " . $_SERVER['REQUEST_METHOD']);
        debug_log("POST data: " . print_r($_POST, true));

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?route=auth/login');
            exit;
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        debug_log("Attempting login with username: " . $username);

        // Utilise authenticate() au lieu de validateCredentials()
        $user = $this->userModel->authenticate($username, $password);

        debug_log("Authentication result: " . ($user ? "Success" : "Failed"));

        if ($user) {
            // Set user session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];

            debug_log("User session set, redirecting to dashboard");

            // Redirect to dashboard
            header('Location: ' . BASE_URL);
            exit;
        } else {
            $_SESSION['login_error'] = 'Nom d\'utilisateur ou mot de passe incorrect';

            debug_log("Login failed, redirecting back to login form");

            header('Location: ' . BASE_URL . '?route=auth/login');
            exit;
        }
    }

    /**
     * Display registration form
     */
    public function showRegistrationForm() {
        echo $this->twig->render('register.html.twig', [
            'title' => 'Inscription',
            'error' => $_SESSION['register_error'] ?? null
        ]);

        // Clear error message after displaying
        unset($_SESSION['register_error']);
    }

    /**
     * Handle registration form submission
     */
    public function register() {
        debug_log("Register method called, Request Method: " . $_SERVER['REQUEST_METHOD']);
        debug_log("POST data: " . print_r($_POST, true));

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?route=auth/register');
            exit;
        }

        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate input
        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION['register_error'] = 'Tous les champs sont obligatoires';
            header('Location: ' . BASE_URL . '?route=auth/register');
            exit;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['register_error'] = 'Les mots de passe ne correspondent pas';
            header('Location: ' . BASE_URL . '?route=auth/register');
            exit;
        }

        // Check if username or email already exists
        if ($this->userModel->getUserByUsername($username)) {
            $_SESSION['register_error'] = 'Ce nom d\'utilisateur est déjà pris';
            header('Location: ' . BASE_URL . '?route=auth/register');
            exit;
        }

        if ($this->userModel->getUserByEmail($email)) {
            $_SESSION['register_error'] = 'Cette adresse email est déjà utilisée';
            header('Location: ' . BASE_URL . '?route=auth/register');
            exit;
        }

        // Create user
        $userData = [
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => ROLE_USER,
            'created_at' => date('Y-m-d H:i:s')
        ];

        debug_log("Attempting to create user with username: " . $username);

        $result = $this->userModel->createUser($userData);

        debug_log("Create user result: " . print_r($result, true));

        if ($result) {
            // Set user session
            $_SESSION['user_id'] = $result['id'];
            $_SESSION['username'] = $result['username'];
            $_SESSION['user_role'] = $result['role'];

            debug_log("User session set, redirecting to dashboard");

            // Redirect to dashboard
            header('Location: ' . BASE_URL);
            exit;
        } else {
            $_SESSION['register_error'] = 'Erreur lors de la création du compte';

            debug_log("Registration failed, redirecting back to registration form");

            header('Location: ' . BASE_URL . '?route=auth/register');
            exit;
        }
    }

    /**
     * Handle logout
     */
    public function logout() {
        // Clear user session
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['user_role']);

        // Redirect to home
        header('Location: ' . BASE_URL);
        exit;
    }

    /**
     * Check if the current user is an admin
     * @return bool
     */
    public function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === ROLE_ADMIN;
    }

    /**
     * Check if user is logged in
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Require user to be logged in
     * Redirects to login page if not
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            $_SESSION['login_error'] = 'Vous devez être connecté pour accéder à cette page';
            header('Location: ' . BASE_URL . '?route=auth/login');
            exit;
        }
    }

    /**
     * Require user to be admin
     * Redirects to login page if not
     */
    public function requireAdmin() {
        if (!$this->isAdmin()) {
            $_SESSION['login_error'] = 'Vous devez être administrateur pour accéder à cette page';
            header('Location: ' . BASE_URL . '?route=auth/login');
            exit;
        }
    }
}