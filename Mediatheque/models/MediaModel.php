<?php
/**
 * MediaModel - Handles media-related operations using the API
 */
class MediaModel {
    private $apiClient;

    /**
     * Constructor
     */
    public function __construct() {
        $this->apiClient = new ApiClient(API_URL);
    }


    /**
     * Get all media items
     * @return array
     */
    public function getAllMedia() {
        $response = $this->apiClient->get('media');

        // Débogage - Pour voir ce que retourne l'API
        error_log('API Media Response: ' . print_r($response, true));

        return $response ?? [];
    }

    /**
     * Get media by ID
     * @param int $id Media ID
     * @return array|null
     */
    public function getMediaById($id) {
        $allMedia = $this->getAllMedia();

        foreach ($allMedia as $media) {
            if ($media['id'] == $id) {
                return $media;
            }
        }

        return null;
    }

    /**
     * Get media by type
     * @param string $type Media type
     * @return array
     */
    public function getMediaByType($type) {
        $allMedia = $this->getAllMedia();
        $result = [];

        foreach ($allMedia as $media) {
            if ($media['type'] == $type) {
                $result[] = $media;
            }
        }

        return $result;
    }

    /**
     * Search media
     * @param string $query Search query
     * @param string|null $type Media type
     * @return array
     */
    public function searchMedia($query, $type = null) {
        $allMedia = $type ? $this->getMediaByType($type) : $this->getAllMedia();
        $result = [];
        $query = strtolower($query);

        foreach ($allMedia as $media) {
            if (
                stripos($media['title'] ?? '', $query) !== false ||
                stripos($media['creator'] ?? '', $query) !== false ||
                stripos($media['description'] ?? '', $query) !== false
            ) {
                $result[] = $media;
            }
        }

        return $result;
    }

    /**
     * Create a new media item
     * @param array $data Media data
     * @return array|null
     */
    public function createMedia($data) {
        $response = $this->apiClient->post('media', $data);
        return $response;
    }

    /**
     * Update a media item
     * @param int $id Media ID
     * @param array $data Media data
     * @return bool
     */
    public function updateMedia($id, $data) {
        $response = $this->apiClient->put('media/' . $id, $data);
        return $response !== null;
    }

    /**
     * Delete a media item
     * @param int $id Media ID
     * @return bool
     */
    public function deleteMedia($id) {
        $response = $this->apiClient->delete('media/' . $id);
        return $response !== null;
    }

    /**
     * Get recently added media
     * @param int $limit Number of items to return
     * @return array
     */
    public function getRecentMedia($limit = 5) {
        $allMedia = $this->getAllMedia();

        // Sort by added_at in descending order
        usort($allMedia, function($a, $b) {
            return strtotime($b['added_at'] ?? 0) - strtotime($a['added_at'] ?? 0);
        });

        // Return only the first $limit items
        return array_slice($allMedia, 0, $limit);
    }

    /**
     * Get popular media based on loan count
     * @param int $limit Number of items to return
     * @return array
     */
    public function getPopularMedia($limit = 5) {
        $mediaItems = $this->getAllMedia();
        $loans = (new LoanModel())->getAllLoans();

        // Count loans for each media
        $mediaCounts = [];
        foreach ($loans as $loan) {
            $mediaId = $loan['media_id'];
            if (!isset($mediaCounts[$mediaId])) {
                $mediaCounts[$mediaId] = 0;
            }
            $mediaCounts[$mediaId]++;
        }

        // Add count to media items
        foreach ($mediaItems as &$media) {
            $media['loan_count'] = $mediaCounts[$media['id']] ?? 0;
        }

        // Sort by loan count
        usort($mediaItems, function($a, $b) {
            return ($b['loan_count'] ?? 0) - ($a['loan_count'] ?? 0);
        });

        // Return only the first $limit items
        return array_slice($mediaItems, 0, $limit);
    }

    /**
     * Get count of available media
     * @return int
     */
    public function getAvailableMediaCount() {
        $allMedia = $this->getAllMedia();
        $count = 0;

        foreach ($allMedia as $media) {
            if ($media['available'] ?? false) {
                $count++;
            }
        }

        return $count;
    }



}