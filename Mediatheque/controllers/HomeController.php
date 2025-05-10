<?php
/**
 * HomeController - Handles home page and navigation
 */
class HomeController {
    private $twig;
    private $mediaModel;
    private $statisticsModel;

    public function __construct($twig) {
        $this->twig = $twig;
        $this->mediaModel = new MediaModel();
        $this->statisticsModel = new StatisticsModel();
    }

    /**
     * Display home page
     */
    public function index() {
        // Get recently added media
        $allMedia = $this->mediaModel->getAllMedia();

        // Sort by creation date (newest first) if we have media
        if (!empty($allMedia)) {
            usort($allMedia, function($a, $b) {
                return strtotime($b['added_at'] ?? 0) - strtotime($a['added_at'] ?? 0);
            });

            $recentMedia = array_slice($allMedia, 0, 6);
        } else {
            $recentMedia = [];
        }

        // Calculate media stats - CORRIGER pour correspondre au template
        $stats = [
            'book' => 0,    // Clé singulière comme dans le template
            'dvd' => 0,     // Clé singulière comme dans le template
            'game' => 0,    // Clé singulière comme dans le template
            'music' => 0,   // Pour la musique
            'total' => 0    // Total de médias
        ];

        foreach ($allMedia as $media) {
            if ($media['available'] ?? false) {
                $stats['total']++;

                // Utiliser le type tel quel
                $type = $media['type'] ?? '';
                if (isset($stats[$type])) {
                    $stats[$type]++;
                }
            }
        }

        // Déboguer les statistiques
        debug_log("Media stats: " . print_r($stats, true));

        // Déboguer les données de session
        debug_log("Session data in HomeController: " . print_r($_SESSION, true));

        // Render the home page
        echo $this->twig->render('home.html.twig', [
            'title' => 'Accueil - Médiathèque',
            'recentMedia' => $recentMedia,
            'stats' => $stats,
            'isLoggedIn' => isset($_SESSION['user_id']),
            'userData' => [
                'id' => $_SESSION['user_id'] ?? null,
                'username' => $_SESSION['username'] ?? null,
                'role' => $_SESSION['user_role'] ?? null
            ]
        ]);
    }
}