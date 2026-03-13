<?php
require_once 'connect_db.php';
require_once 'fonctions.php';
// Statistiques du système (valeurs statiques pour le moment)

//countEmployees
$countActiveEmployees = countActiveEmployees($conn);
$countActiveContrats = countActiveContrats($conn);
$statistiques = [
    'employés actifs' => [
        'valeur' => $countActiveEmployees,
        'icon' => 'fas fa-users',
        'couleur' => 'blue',
        'description' => 'Total des employés actifs'
    ],
    'contrats actifs' => [
        'valeur' => $countActiveContrats,
        'icon' => 'fas fa-file-contract',
        'couleur' => 'green',
        'description' => 'Total des Contrats actifs'
    ],
    'Déclaration CNAS' => [
        'valeur' => 0,
        'icon' => 'fas fa-heartbeat',
        'couleur' => 'red',
        'description' => 'Dossiers médicaux'
    ],
    'absences' => [
        'valeur' => 0,
        'icon' => 'fas fa-clock',
        'couleur' => 'yellow',
        'description' => 'Absences ce mois'
    ],
    'attestations' => [
        'valeur' => 0,
        'icon' => 'fas fa-file-contract',
        'couleur' => 'blue',
        'description' => 'Attestations émises'
    ],
    'certificats' => [
        'valeur' => 0,
        'icon' => 'fas fa-certificate',
        'couleur' => 'purple',
        'description' => 'Certificats émis'
    ],
    'fin_relation' => [
        'valeur' => 0,
        'icon' => 'fas fa-handshake',
        'couleur' => 'orange',
        'description' => 'Fin de relations'
    ],
    'mise_en_demeure' => [
        'valeur' => 0,
        'icon' => 'fas fa-exclamation-triangle',
        'couleur' => 'red',
        'description' => 'Mises en demeure'
    ],
    'titre_conge' => [
        'valeur' => 0,
        'icon' => 'fas fa-umbrella-beach',
        'couleur' => 'cyan',
        'description' => 'Titres de congé'
    ],
    'pointages' => [
        'valeur' => 0,
        'icon' => 'fas fa-fingerprint',
        'couleur' => 'indigo',
        'description' => 'Pointages aujourd\'hui'
    ],
    'reprise_travail' => [
        'valeur' => 0,
        'icon' => 'fas fa-briefcase',
        'couleur' => 'green',
        'description' => 'Reprises de travail'
    ],
    'situation_effectifs' => [
        'valeur' => 0,
        'icon' => 'fas fa-chart-bar',
        'couleur' => 'blue',
        'description' => 'Situation des effectifs'
    ],
    'ordre_mission' => [
        'valeur' => 0,
        'icon' => 'fas fa-plane',
        'couleur' => 'teal',
        'description' => 'Ordres de mission'
    ],
    'decharge' => [
        'valeur' => 0,
        'icon' => 'fas fa-file-signature',
        'couleur' => 'amber',
        'description' => 'Décharges'
    ],
    'global' => [
        'valeur' => 0,
        'icon' => 'fas fa-globe',
        'couleur' => 'gray',
        'description' => 'Vue globale'
    ]
];
?>

<!-- Cartes de statistiques principales -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <?php
    $mainStats = ['employés actifs', 'contrats actifs', 'Déclaration CNAS', 'absences'];
    foreach ($mainStats as $statKey):
        $stat = $statistiques[$statKey];
        $colorClasses = [
            'blue' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'hover' => 'hover:border-blue-300'],
            'green' => ['bg' => 'bg-green-50', 'text' => 'text-green-600', 'hover' => 'hover:border-green-300'],
            'red' => ['bg' => 'bg-red-50', 'text' => 'text-red-600', 'hover' => 'hover:border-red-300'],
            'yellow' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-600', 'hover' => 'hover:border-yellow-300']
        ];
        $colorClass = $colorClasses[$stat['couleur']];
    ?>
    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-6 <?php echo $colorClass['hover']; ?> transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600"><?php echo ucfirst(str_replace('_', ' ', $statKey)); ?></p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1 stat-value"><?php echo $stat['valeur']; ?></h3>
            </div>
            <div class="p-3 <?php echo $colorClass['bg']; ?> rounded-lg">
                <i class="<?php echo $stat['icon']; ?> <?php echo $colorClass['text']; ?> text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-xs text-gray-500"><?php echo $stat['description']; ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Statistiques détaillées par module -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Section gauche : Modules principaux -->
    <div class="space-y-6">
        <!-- Statistiques des modules RH -->
        <div class="stat-section bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistiques des Modules RH</h3>
            <div class="space-y-4">
                <?php
                $rhStats = ['certificats', 'attestations','fin_relation', 'mise_en_demeure', 'titre_conge'];
                foreach ($rhStats as $statKey):
                    $stat = $statistiques[$statKey];
                    $colorClasses = [
                        'purple' => 'text-purple-500',
                        'orange' => 'text-orange-500',
                        'red' => 'text-red-500',
                        'cyan' => 'text-cyan-500',
                        'blue' => 'text-blue-500'
                    ];
                ?>
                <div class="flex items-center justify-between py-2 border-b border-gray-100 stat-item">
                    <div class="flex items-center space-x-3">
                        <i class="<?php echo $stat['icon']; ?> <?php echo $colorClasses[$stat['couleur']]; ?> w-5"></i>
                        <span class="text-gray-700"><?php echo ucfirst(str_replace('_', ' ', $statKey)); ?></span>
                    </div>
                    <span class="font-semibold text-gray-800 stat-value"><?php echo $stat['valeur']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Statistiques de présence -->
        <div class="stat-section bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Présence & Pointages</h3>
            <div class="space-y-4">
                <?php
                $presenceStats = ['pointages', 'reprise_travail'];
                foreach ($presenceStats as $statKey):
                    $stat = $statistiques[$statKey];
                    $colorClasses = [
                        'indigo' => 'text-indigo-500',
                        'green' => 'text-green-500'
                    ];
                ?>
                <div class="flex items-center justify-between py-2 border-b border-gray-100 stat-item">
                    <div class="flex items-center space-x-3">
                        <i class="<?php echo $stat['icon']; ?> <?php echo $colorClasses[$stat['couleur']]; ?> w-5"></i>
                        <span class="text-gray-700"><?php echo ucfirst(str_replace('_', ' ', $statKey)); ?></span>
                    </div>
                    <span class="font-semibold text-gray-800 stat-value"><?php echo $stat['valeur']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Section droite : Modules supplémentaires -->
    <div class="space-y-6">
        <!-- Statistiques administratives -->
        <div class="stat-section bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistiques Administratives</h3>
            <div class="space-y-4">
                <?php
                $adminStats = ['situation_effectifs', 'ordre_mission', 'decharge', 'global'];
                foreach ($adminStats as $statKey):
                    $stat = $statistiques[$statKey];
                    $colorClasses = [
                        'blue' => 'text-blue-500',
                        'teal' => 'text-teal-500',
                        'amber' => 'text-amber-500',
                        'gray' => 'text-gray-500'
                    ];
                ?>
                <div class="flex items-center justify-between py-2 border-b border-gray-100 stat-item">
                    <div class="flex items-center space-x-3">
                        <i class="<?php echo $stat['icon']; ?> <?php echo $colorClasses[$stat['couleur']]; ?> w-5"></i>
                        <span class="text-gray-700"><?php echo ucfirst(str_replace('_', ' ', $statKey)); ?></span>
                    </div>
                    <span class="font-semibold text-gray-800 stat-value"><?php echo $stat['valeur']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Résumé rapide -->
        <div class="stat-summary bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-sm p-6 text-white">
            <h3 class="text-lg font-semibold mb-2">Résumé du Système</h3>
            <p class="text-blue-100 text-sm mb-4">Toutes les statistiques sont actuellement à 0 en attendant l'ajout des données.</p>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold total-records">0</p>
                    <p class="text-blue-200 text-xs">Total des enregistrements</p>
                </div>
                <i class="fas fa-database text-blue-200 text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Message d'information -->
<div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-xl p-6 stat-info">
    <div class="flex items-start space-x-3">
        <i class="fas fa-info-circle text-yellow-600 text-xl mt-1"></i>
        <div>
            <h4 class="font-semibold text-yellow-800">Information</h4>
            <p class="text-yellow-700 text-sm mt-1">
                Les statistiques afficheront 0 jusqu'à ce que les données soient ajoutées via les différents modules du système.
                Utilisez le menu latéral pour accéder aux fonctionnalités.
            </p>
        </div>
    </div>
</div>