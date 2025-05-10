<?php
/**
 * StatisticsController - Handles statistics dashboard
 */
class StatisticsController {
    private $twig;
    private $statisticsModel;
    private $mediaModel;
    private $loanModel;
    private $userModel;

    public function __construct($twig) {
        $this->twig = $twig;
        $this->statisticsModel = new StatisticsModel();
        $this->mediaModel = new MediaModel();
        $this->loanModel = new LoanModel();
        $this->userModel = new UserModel();
    }

    /**
     * Display statistics dashboard
     */
    public function index() {
        // Créer une instance d'AuthController pour utiliser ses méthodes
        $authController = new AuthController($this->twig);

        // Require admin for full dashboard
        $isAdmin = $authController->isAdmin();

        if (!$isAdmin) {
            // Regular users can only see their own stats
            $authController->requireLogin();
            $userId = $_SESSION['user_id']; // Clé corrigée
            $userStats = $this->statisticsModel->getUserStats($userId);

            echo $this->twig->render('statistics/user.html.twig', [
                'title' => 'Mes statistiques - Médiathèque',
                'stats' => $userStats
            ]);
            return;
        }

        // Admin dashboard
        $stats = $this->statisticsModel->getDashboardStats();

        // Get additional data needed for the dashboard
        $overdueLoans = $this->loanModel->getOverdueLoans();

        // Enhance loans with media and user info
        foreach ($overdueLoans as &$loan) {
            $loan['media'] = $this->mediaModel->getMediaById($loan['media_id']);
            $loan['user'] = $this->userModel->getUserById($loan['user_id']);

            // Calculate days overdue
            $dueDate = new DateTime($loan['due_date']);
            $now = new DateTime();
            $interval = $now->diff($dueDate);
            $loan['days_overdue'] = $interval->days;
        }

        echo $this->twig->render('statistics/dashboard.html.twig', [
            'title' => 'Tableau de bord - Médiathèque',
            'stats' => $stats,
            'overdueLoans' => $overdueLoans
        ]);
    }
}