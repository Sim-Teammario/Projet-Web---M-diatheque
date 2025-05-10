<?php
/**
 * LoanModel - Handles loan-related operations using the API
 */
class LoanModel {
    private $apiClient;

    /**
     * Constructor
     */
    public function __construct() {
        $this->apiClient = new ApiClient(API_URL);
    }

    /**
     * Get all loans
     * @return array
     */
    public function getAllLoans() {
        $response = $this->apiClient->get('loans');
        return $response ?? [];
    }

    /**
     * Get loan by ID
     * @param int $id Loan ID
     * @return array|null
     */
    public function getLoanById($id) {
        $allLoans = $this->getAllLoans();

        foreach ($allLoans as $loan) {
            if ($loan['id'] == $id) {
                return $loan;
            }
        }

        return null;
    }

    /**
     * Get loans by user ID
     * @param int $userId User ID
     * @param bool $activeOnly Get only active loans
     * @return array
     */
    public function getLoansByUserId($userId, $activeOnly = false) {
        $allLoans = $this->getAllLoans();
        $result = [];

        foreach ($allLoans as $loan) {
            if ($loan['user_id'] == $userId) {
                if (!$activeOnly || empty($loan['return_date'])) {
                    $result[] = $loan;
                }
            }
        }

        return $result;
    }

    /**
     * Get all active loans
     * @return array
     */
    public function getActiveLoans() {
        $allLoans = $this->getAllLoans();
        $result = [];

        foreach ($allLoans as $loan) {
            if (empty($loan['return_date'])) {
                $result[] = $loan;
            }
        }

        return $result;
    }

    /**
     * Create a new loan
     * @param int $userId User ID
     * @param int $mediaId Media ID
     * @return array|null
     */
    public function createLoan($userId, $mediaId) {
        $data = [
            'user_id' => $userId,
            'media_id' => $mediaId,
            'loan_date' => date('Y-m-d H:i:s'),
            'due_date' => date('Y-m-d H:i:s', strtotime('+14 days')),
        ];
        $response = $this->apiClient->post('loans', $data);

        // Si l'emprunt est réussi, mettre à jour le statut du média (non disponible)
        if ($response) {
            $mediaModel = new MediaModel();
            $media = $mediaModel->getMediaById($mediaId);
            if ($media) {
                $media['available'] = false;
                $mediaModel->updateMedia($mediaId, $media);
            }
        }

        return $response;
    }

    /**
     * Return a loan
     * @param int $id Loan ID
     * @return bool
     */
    public function returnLoan($id) {
        // Ne pas essayer de récupérer le prêt d'abord, envoyez directement la requête PUT
        // avec la date de retour
        $data = ['return_date' => date('Y-m-d H:i:s')];
        error_log("LoanModel::returnLoan() - Returning loan with ID: " . $id);
        error_log("LoanModel::returnLoan() - Return data: " . json_encode($data));

        // Envoyer la requête PUT
        $response = $this->apiClient->put('loans/' . $id, $data);

        if ($response && isset($response['media_id'])) {
            // Si la réponse contient media_id, mettre à jour le média
            $mediaId = $response['media_id'];
            $mediaModel = new MediaModel();
            $media = $mediaModel->getMediaById($mediaId);
            if ($media) {
                $media['available'] = 1;
                $mediaModel->updateMedia($mediaId, $media);
                error_log("LoanModel::returnLoan() - Media " . $mediaId . " marked as available");
            }
            return true;
        } else if ($response) {
            return true;
        }

        error_log("LoanModel::returnLoan() - Failed to return loan: " . $id);
        return false;
    }

    /**
     * Delete a loan
     * @param int $id Loan ID
     * @return bool
     */
    public function deleteLoan($id) {
        $response = $this->apiClient->delete('loans/' . $id);
        return $response !== null;
    }

    /**
     * Get overdue loans
     * @return array
     */
    public function getOverdueLoans() {
        $allLoans = $this->getAllLoans();
        $result = [];
        $now = date('Y-m-d H:i:s');

        foreach ($allLoans as $loan) {
            if ($loan['return_date'] === null && $loan['due_date'] < $now) {
                $result[] = $loan;
            }
        }

        return $result;
    }
}