<?php
/**
 * sidebar.php
 * Affiche uniquement les modules pour lesquels l'utilisateur
 * a au moins la permission 'view'.
 * L'admin voit tout sans restriction.
 */

// Sécurité : garantir que auth.php est chargé (et donc can() disponible)
// même si sidebar.php est inclus depuis un contexte inattendu
if (!function_exists('can')) {
    require_once __DIR__ . '/auth.php';
}

// Modules disponibles : clé = identifiant DB, valeur = [label, icône]
$allModules = [
    'employee'           => ['label' => 'EMPLOYEE',            'icon' => 'fas fa-users'],
    'contrat'            => ['label' => 'CONTRAT',             'icon' => 'fas fa-file-contract'],
    'cnas'               => ['label' => 'CNAS',                'icon' => 'fas fa-heartbeat'],
    'certificat'         => ['label' => 'CERTIFICAT',          'icon' => 'fas fa-certificate'],
    'fin_relation'       => ['label' => 'FIN RELATION',        'icon' => 'fas fa-handshake'],
    'mise_en_demeure'    => ['label' => 'MISE EN DEMEURE',     'icon' => 'fas fa-exclamation-triangle'],
    'titre_conge'        => ['label' => 'TITRE CONGE',         'icon' => 'fas fa-umbrella-beach'],
    'reprise_travail'    => ['label' => 'REPRISE TRAVAIL',     'icon' => 'fas fa-briefcase'],
    'absences'           => ['label' => 'ABSENCES',            'icon' => 'fas fa-clock'],
    'pointages'          => ['label' => 'POINTAGES',           'icon' => 'fas fa-fingerprint'],
    'situation_effectifs'=> ['label' => 'SITUATION EFFECTIFS', 'icon' => 'fas fa-chart-bar'],
    'ordre_mission'      => ['label' => 'ORDRE MISSION',       'icon' => 'fas fa-plane'],
    'decharge'           => ['label' => 'DECHARGE',            'icon' => 'fas fa-file-signature'],
    'global'             => ['label' => 'GLOBAL',              'icon' => 'fas fa-globe'],
];
?>

<aside id="sidebar" class="fixed left-0 top-16 h-[calc(100vh-4rem)] w-64 bg-white shadow-lg border-r border-gray-200 overflow-y-auto transition-all duration-300">
    <nav class="p-4 space-y-2">

        <?php foreach ($allModules as $moduleKey => $moduleInfo): ?>
            <?php if (can($moduleKey, 'view')): // N'affiche que si l'utilisateur peut voir ?>
                <button class="sidebar-item w-full flex items-center space-x-3 px-4 py-3 text-left text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 group"
                        data-modal="<?php echo $moduleKey; ?>">
                    <i class="<?php echo $moduleInfo['icon']; ?> w-5 text-gray-400 group-hover:text-blue-500"></i>
                    <span class="font-medium"><?php echo $moduleInfo['label']; ?></span>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>

<?php // Bouton PARAMETRES géré ci-dessous ?>
            <div class="border-t border-gray-200 mt-4 pt-4">
                <button class="sidebar-item w-full flex items-center space-x-3 px-4 py-3 text-left text-blue-700 rounded-lg hover:bg-blue-50 transition-all duration-200 group"
                        data-modal="utilisateurs">
                    <i class="fas fa-user-shield w-5 text-blue-400 group-hover:text-blue-600"></i>
                    <span class="font-medium">UTILISATEURS</span>
                </button>
            </div>


        <?php if (isAdmin()): ?>
            <div class="border-t border-gray-200 mt-4 pt-4">
                <button class="sidebar-item w-full flex items-center space-x-3 px-4 py-3 text-left text-slate-700 rounded-lg hover:bg-slate-50 hover:text-slate-900 transition-all duration-200 group"
                        data-modal="parametres">
                    <i class="fas fa-cog w-5 text-slate-400 group-hover:text-slate-600 transition-all duration-300"></i>
                    <span class="font-medium">PARAMÈTRES</span>
                </button>
            </div>
        <?php endif; ?>

    </nav>
</aside>