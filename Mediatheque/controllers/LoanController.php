<?php
/**
 * LoanController - Handles loan-related operations
 */
class LoanController {
    private $twig;
    private $loanModel;
    private $mediaModel;
    private $userModel;

    public function __construct($twig) {
        $this->twig = $twig;
        $this->loanModel = new LoanModel();
        $this->mediaModel = new MediaModel();
        $this->userModel = new UserModel();
    }

    /**
     * Display active loans
     */
    public function index() {
        // Créer une instance d'AuthController pour utiliser ses méthodes
        $authController = new AuthController($this->twig);
        // Require login
        $authController->requireLogin();

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

        if ($authController->isAdmin()) {
            // Get all active loans for admin
            $loans = $this->loanModel->getActiveLoans();
        } else {
            // Get user's active loans
            $userId = $_SESSION['user_id']; // Clé corrigée
            $loans = $this->loanModel->getLoansByUserId($userId, true);
        }

        // Enhance loans with media and user info
        foreach ($loans as &$loan) {
            $loan['media'] = $this->mediaModel->getMediaById($loan['media_id']);

            if ($authController->isAdmin()) {
                $loan['user'] = $this->userModel->getUserById($loan['user_id']);
            }

            // Calculate days remaining or overdue
            $dueDate = new DateTime($loan['due_date']);
            $now = new DateTime();
            $interval = $now->diff($dueDate);

            if ($now > $dueDate) {
                $loan['status'] = 'overdue';
                $loan['days_overdue'] = $interval->days;
            } else {
                $loan['status'] = 'active';
                $loan['days_remaining'] = $interval->days;
            }
        }

        // Sort by due date (ascending)
        usort($loans, function($a, $b) {
            return strtotime($a['due_date']) - strtotime($b['due_date']);
        });

        // Paginate results
        $paginatedData = paginate($loans, $page);

        echo $this->twig->render('loans/list.html.twig', [
            'title' => 'Emprunts en cours - Médiathèque',
            'loans' => $paginatedData['items'],
            'pagination' => [
                'currentPage' => $paginatedData['currentPage'],
                'totalPages' => $paginatedData['totalPages'],
                'hasNextPage' => $paginatedData['hasNextPage'],
                'hasPrevPage' => $paginatedData['hasPrevPage']
            ],
            'isAdmin' => $authController->isAdmin()
        ]);
    }

    /**
     * Display loan history
     */
    public function history() {
        // Créer une instance d'AuthController pour utiliser ses méthodes
        $authController = new AuthController($this->twig);
        // Require login
        $authController->requireLogin();

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $userId = isset($_GET['user_id']) && $authController->isAdmin() ? intval($_GET['user_id']) : $_SESSION['user_id']; // Clé corrigée

        // Get user loans
        $loans = $this->loanModel->getLoansByUserId($userId);

        // Enhance loans with media info
        foreach ($loans as &$loan) {
            $loan['media'] = $this->mediaModel->getMediaById($loan['media_id']);

            // Calculate loan duration
            if ($loan['return_date'] !== null) {
                $loanDate = new DateTime($loan['loan_date']);
                $returnDate = new DateTime($loan['return_date']);
                $interval = $loanDate->diff($returnDate);
                $loan['duration_days'] = $interval->days;
            }
        }

        // Sort by loan date (newest first)
        usort($loans, function($a, $b) {
            return strtotime($b['loan_date']) - strtotime($a['loan_date']);
        });

        // Paginate results
        $paginatedData = paginate($loans, $page);

        // Get user if admin
        $user = null;
        if ($authController->isAdmin() && $userId != $_SESSION['user_id']) { // Clé corrigée
            $user = $this->userModel->getUserById($userId);
        }

        echo $this->twig->render('loans/history.html.twig', [
            'title' => 'Historique des emprunts - Médiathèque',
            'loans' => $paginatedData['items'],
            'pagination' => [
                'currentPage' => $paginatedData['currentPage'],
                'totalPages' => $paginatedData['totalPages'],
                'hasNextPage' => $paginatedData['hasNextPage'],
                'hasPrevPage' => $paginatedData['hasPrevPage']
            ],
            'isAdmin' => $authController->isAdmin(),
            'user' => $user
        ]);
    }

    /**
     * Create a new loan
     */
    public function borrow() {
        // Créer une instance d'AuthController pour utiliser ses méthodes
        $authController = new AuthController($this->twig);
        // Require login
        $authController->requireLogin();

        // Check if POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?route=media');
            exit;
        }

        // Get media ID from POST
        $mediaId = isset($_POST['media_id']) ? intval($_POST['media_id']) : 0;
        $userId = $_SESSION['user_id']; // Clé corrigée

        // Check if admin is borrowing for another user
        if ($authController->isAdmin() && isset($_POST['user_id'])) {
            $userId = intval($_POST['user_id']);
        }

        // Vérifier que le média existe et est disponible
        $media = $this->mediaModel->getMediaById($mediaId);
        if (!$media) {
            $_SESSION['flash_error'] = 'Média non trouvé';
            header('Location: ' . BASE_URL . '?route=media');
            exit;
        }

        if (!($media['available'] ?? false)) {
            $_SESSION['flash_error'] = 'Ce média n\'est pas disponible';
            header('Location: ' . BASE_URL . '?route=media/details&id=' . $mediaId);
            exit;
        }

        // Create loan
        $loan = $this->loanModel->createLoan($userId, $mediaId);

        if ($loan) {
            $_SESSION['flash_message'] = 'Média emprunté avec succès';
            header('Location: ' . BASE_URL . '?route=loan');
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de l\'emprunt du média';
            header('Location: ' . BASE_URL . '?route=media/details&id=' . $mediaId);
        }

        exit;
    }

    /**
     * Return a loan
     */
    public function return() {
        // Créer une instance d'AuthController pour utiliser ses méthodes
        $authController = new AuthController($this->twig);
        // Require login
        $authController->requireLogin();

        // Check if POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?route=loan');
            exit;
        }

        // Get loan ID from POST
        $loanId = isset($_POST['loan_id']) ? intval($_POST['loan_id']) : 0;

        // Get loan
        $loan = $this->loanModel->getLoanById($loanId);

        if (!$loan) {
            $_SESSION['flash_error'] = 'Emprunt non trouvé';
            header('Location: ' . BASE_URL . '?route=loan');
            exit;
        }

        // Check if user can return this loan
        if (!$authController->isAdmin() && $loan['user_id'] != $_SESSION['user_id']) { // Clé corrigée
            $_SESSION['flash_error'] = 'Vous n\'êtes pas autorisé à retourner cet emprunt';
            header('Location: ' . BASE_URL . '?route=loan');
            exit;
        }

        // Return loan
        $returnResult = $this->loanModel->returnLoan($loanId);

        if ($returnResult) {
            $_SESSION['flash_message'] = 'Média retourné avec succès';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors du retour du média';
        }

        header('Location: ' . BASE_URL . '?route=loan');
        exit;
    }
}