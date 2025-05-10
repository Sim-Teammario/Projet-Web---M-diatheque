<?php
/**
 * ApiClient - Handles API requests to the REST API
 */
class ApiClient {
    private $apiUrl;

    /**
     * Constructor
     * @param string $apiUrl Base URL of the API
     */
    public function __construct($apiUrl = 'http://api.local') {
        $this->apiUrl = rtrim($apiUrl, '/');
    }

    /**
     * Send a GET request to the API
     * @param string $endpoint API endpoint (e.g., 'users', 'media')
     * @return array|null Response data as array or null on error
     */
    public function get($endpoint) {
        $url = $this->apiUrl . '/' . ltrim($endpoint, '/');

        // Débogage
        error_log("API GET Request URL: " . $url);

        // Option simple - sans headers spéciaux
        $response = @file_get_contents($url);

        // Vérifie les erreurs
        if ($response === false) {
            $error = error_get_last();
            error_log("API Error: " . ($error['message'] ?? 'Unknown error'));
            return null;
        }

        // Parse JSON response
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON Parse Error: " . json_last_error_msg());
            return null;
        }

        // Pour le débogage
        error_log("API Response Data: " . print_r($data, true));

        return $data;
    }

    /**
     * Send a POST request to the API
     * @param string $endpoint API endpoint
     * @param array $data Data to send
     * @return array|null Response data as array or null on error
     */
    public function post($endpoint, $data) {
        $url = $this->apiUrl . '/' . ltrim($endpoint, '/');

        debug_log("API POST Request URL: " . $url);
        debug_log("API POST Request Data: " . print_r($data, true));

        // Utiliser cURL pour plus de contrôle et de fiabilité
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        debug_log("API POST Response Code: " . $httpCode);
        debug_log("API POST Response: " . $response);

        if ($response === false || $httpCode >= 400) {
            debug_log("API POST Error: " . $error);
            return null;
        }

        $responseData = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            debug_log("API POST JSON Parse Error: " . json_last_error_msg());
            return null;
        }

        return $responseData;
    }

    /**
     * Send a PUT request to the API
     * @param string $endpoint API endpoint
     * @param array $data Data to send
     * @return array|null Response data as array or null on error
     */
    public function put($endpoint, $data) {
        $url = $this->apiUrl . '/' . ltrim($endpoint, '/');
        error_log("API PUT Request URL: " . $url);

        // Utiliser cURL pour plus de contrôle
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        error_log("API PUT Response Code: " . $httpCode);
        error_log("API PUT Response: " . $response);

        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            error_log("API PUT Error: curl error or HTTP code >= 400");
            return null;
        }

        // Parse JSON response
        $responseData = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("API PUT JSON Parse Error: " . json_last_error_msg());
            return null;
        }

        return $responseData;
    }

    /**
     * Send a DELETE request to the API
     * @param string $endpoint API endpoint
     * @return array|null Response data as array or null on error
     */
    public function delete($endpoint) {
        $url = $this->apiUrl . '/' . ltrim($endpoint, '/');

        $context = stream_context_create([
            'http' => [
                'method' => 'DELETE',
                'header' => [
                    'Accept: application/json',
                    'Content-Type: application/json'
                ],
                'timeout' => 10
            ]
        ]);

        $response = @file_get_contents($url, false, $context);

        // Vérifie les erreurs
        if ($response === false) {
            $error = error_get_last();
            error_log("API Error: " . ($error['message'] ?? 'Unknown error'));
            return null;
        }

        // Parse JSON response
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON Parse Error: " . json_last_error_msg());
            return null;
        }

        return $data;
    }
}