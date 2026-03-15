<?php
/**
 * dnk.php — Tableau de bord principal (page protégée)
 */
require_once 'php/auth.php'; // Démarre la session + fonctions de garde
requireLogin();               // Redirige vers index.php si non connecté
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DNK - Gestion RH</title>
    <link rel="icon" href="img/logo/logo.png" type="image/png">
    <!-- Tous les imports et scripts sont maintenant dans link.php -->
    <?php include 'php/link.php'; ?>
</head>
<body class="bg-gray-50">
    <!-- Notification Container -->
    <div id="notification-container"></div>
    
    <!-- NavBar -->
    <?php include 'php/navbar.php'; ?>
    
    <div class="flex pt-16">
        <!-- SideBar -->
        <?php include 'php/sidebar.php'; ?>
        
        <!-- Main Content avec Statistiques -->
        <main class="flex-1 p-6 ml-64">
            <!-- En-tête du tableau de bord -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Tableau de Bord</h1>
                <p class="text-gray-600">Vue d'ensemble des statistiques du système</p>
            </div>

            <!-- Inclusion des statistiques -->
            <?php include 'php/stat.php'; ?>
        </main>
    </div>
    
    <!-- Modals Container -->
    <?php include 'php/modals.php'; ?>
    
    
</body>
</html>