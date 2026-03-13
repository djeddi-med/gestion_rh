<?php
/**
 * logout.php — Point de déconnexion
 * Appelé via le lien "Déconnexion" dans la navbar.
 */
require_once __DIR__ . '/auth.php';

logout(); // Vide la session, détruit le cookie, redirige vers index.php