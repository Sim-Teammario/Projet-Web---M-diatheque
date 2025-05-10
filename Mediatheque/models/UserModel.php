<?php
/**
 * UserModel - Handles user-related operations using the API
 */
class UserModel {
    private $apiClient;

    /**
     * Constructor
     */
    public function __construct() {
        $this->apiClient = new ApiClient(API_URL);
    }

    /**
     * Get all users
     * @return array
     */
    public function getAllUsers() {
        $response = $this->apiClient->get('users');
        return $response ?? [];
    }

    /**
     * Get user by ID
     * @param int $id User ID
     * @return array|null
     */
    public function getUserById($id) {
        $allUsers = $this->getAllUsers();

        foreach ($allUsers as $user) {
            if ($user['id'] == $id) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Get user by username
     * @param string $username Username
     * @return array|null
     */
    public function getUserByUsername($username) {
        $allUsers = $this->getAllUsers();

        foreach ($allUsers as $user) {
            if ($user['username'] === $username) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Get user by email
     * @param string $email Email
     * @return array|null
     */
    public function getUserByEmail($email) {
        $allUsers = $this->getAllUsers();

        foreach ($allUsers as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Authenticate user
     * @param string $username Username
     * @param string $password Password
     * @return array|null
     */
    public function authenticate($username, $password) {
        debug_log("Authenticating user: " . $username);
        $user = $this->getUserByUsername($username);

        debug_log("User found: " . ($user ? "Yes" : "No"));

        if (!$user) {
            return null;
        }

        debug_log("User data: " . print_r($user, true));
        debug_log("Input password: " . $password);
        debug_log("Stored password hash: " . $user['password']);

        // Plusieurs méthodes de vérification
        // 1. password_verify standard
        $method1 = password_verify($password, $user['password']);
        debug_log("Method 1 (password_verify): " . ($method1 ? "Success" : "Failed"));

        // 2. Comparaison directe de texte
        $method2 = ($password === $user['password']);
        debug_log("Method 2 (direct comparison): " . ($method2 ? "Success" : "Failed"));

        // Si l'une des méthodes réussit, on authentifie l'utilisateur
        if ($method1 || $method2) {
            debug_log("Authentication successful");
            return $user;
        }

        debug_log("Authentication failed");
        return null;
    }

    /**
     * Create a new user
     * @param array $data User data
     * @return array|null
     */
    public function createUser($data) {
        debug_log("Creating user with API: " . print_r($data, true));
        // On revient à la méthode API standard car c'est plus cohérent avec le reste du code
        $response = $this->apiClient->post('users', $data);
        debug_log("API response for user creation: " . print_r($response, true));
        return $response;
    }

    /**
     * Update a user
     * @param int $id User ID
     * @param array $data User data
     * @return bool
     */
    public function updateUser($id, $data) {
        $response = $this->apiClient->put('users/' . $id, $data);
        return $response !== null;
    }

    /**
     * Delete a user
     * @param int $id User ID
     * @return bool
     */
    public function deleteUser($id) {
        $response = $this->apiClient->delete('users/' . $id);
        return $response !== null;
    }
}