<?php
/**
 * StatisticsModel - Handles statistics-related operations using the API
 */
class StatisticsModel {
    private $apiClient;
    private $mediaModel;
    private $userModel;
    private $loanModel;

    /**
     * Constructor
     */
    public function __construct() {
        $this->apiClient = new ApiClient(API_URL);
        $this->mediaModel = new MediaModel();
        $this->userModel = new UserModel();
        $this->loanModel = new LoanModel();
    }

    /**
     * Get statistics
     * @return array
     */
    public function getStatistics() {
        $mediaItems = $this->mediaModel->getAllMedia();
        $users = $this->userModel->getAllUsers();
        $loans = $this->loanModel->getAllLoans();

        // Calculate statistics
        $stats = [
            'total_users' => count($users),
            'total_media' => count($mediaItems),
            'total_loans' => count($loans),
            'available_media' => 0,
            'unavailable_media' => 0,
            'media_by_type' => [
                'book' => 0,
                'dvd' => 0,
                'game' => 0,
                'music' => 0
            ],
            'active_loans' => 0,
            'returned_loans' => 0,
            'overdue_loans' => 0
        ];

        // Count media by type and availability
        foreach ($mediaItems as $media) {
            $type = $media['type'] ?? 'unknown';
            if (isset($stats['media_by_type'][$type])) {
                $stats['media_by_type'][$type]++;
            }

            if ($media['available'] ?? false) {
                $stats['available_media']++;
            } else {
                $stats['unavailable_media']++;
            }
        }

        // Count loan status
        $now = time();
        foreach ($loans as $loan) {
            if ($loan['return_date'] === null) {
                $stats['active_loans']++;

                // Check if overdue
                $dueDate = strtotime($loan['due_date'] ?? 0);
                if ($dueDate && $dueDate < $now) {
                    $stats['overdue_loans']++;
                }
            } else {
                $stats['returned_loans']++;
            }
        }

        return $stats;
    }

    /**
     * Get user-specific statistics
     * @param int $userId User ID
     * @return array
     */
    public function getUserStats($userId) {
        // Get user loans
        $loans = $this->loanModel->getLoansByUserId($userId);

        // Initialize stats
        $stats = [
            'total_loans' => count($loans),
            'active_loans' => 0,
            'favorite_type' => null,
            'has_overdue' => false
        ];

        // Media type counter
        $typeCount = [
            'book' => 0,
            'dvd' => 0,
            'game' => 0,
            'music' => 0
        ];

        // Count active loans and check for overdue
        $now = new DateTime();
        foreach ($loans as $loan) {
            // If loan is active (not returned)
            if ($loan['return_date'] === null) {
                $stats['active_loans']++;

                // Check if overdue
                $dueDate = new DateTime($loan['due_date'] ?? 'now');
                if ($now > $dueDate) {
                    $stats['has_overdue'] = true;
                }

                // Get media type
                $media = $this->mediaModel->getMediaById($loan['media_id']);
                $type = $media['type'] ?? null;

                if ($type && isset($typeCount[$type])) {
                    $typeCount[$type]++;
                }
            }
        }

        // Determine favorite type
        $maxCount = 0;
        foreach ($typeCount as $type => $count) {
            if ($count > $maxCount) {
                $maxCount = $count;
                $stats['favorite_type'] = $type;
            }
        }

        return $stats;
    }

    /**
     * Get popular media
     * @param int $limit Number of items to return
     * @return array
     */
    public function getPopularMedia($limit = 5) {
        return $this->mediaModel->getPopularMedia($limit);
    }

    /**
     * Get recent loans
     * @param int $limit Number of items to return
     * @return array
     */
    public function getRecentLoans($limit = 5) {
        $loans = $this->loanModel->getAllLoans();

        // Sort by loan date in descending order
        usort($loans, function($a, $b) {
            return strtotime($b['loan_date'] ?? 0) - strtotime($a['loan_date'] ?? 0);
        });

        // Return only the first $limit items
        $loans = array_slice($loans, 0, $limit);

        // Add user and media information
        foreach ($loans as &$loan) {
            $loan['user'] = $this->userModel->getUserById($loan['user_id'] ?? 0);
            $loan['media'] = $this->mediaModel->getMediaById($loan['media_id'] ?? 0);
        }

        return $loans;
    }

    /**
     * Get overdue loans
     * @return array
     */
    public function getOverdueLoans() {
        return $this->loanModel->getOverdueLoans();
    }

    /**
     * Get dashboard statistics
     * @return array
     */
    public function getDashboardStats() {
        $stats = $this->getStatistics();

        // Add popular media
        $stats['popular_media'] = $this->getPopularMedia();

        // Add recent loans
        $stats['recent_loans'] = $this->getRecentLoans();

        return $stats;
    }
}