<?php
/**
 * LegalController - Handles legal pages
 */
class LegalController {
    private $twig;
    
    public function __construct($twig) {
        $this->twig = $twig;
    }
    
    /**
     * Display legal information
     */
    public function index() {
        echo $this->twig->render('legal.html.twig', [
            'title' => 'Mentions légales - Médiathèque'
        ]);
    }
}
