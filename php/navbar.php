<?php
// La session est déjà démarrée par auth.php — ne pas rappeler session_start() ici
?>
<nav id="navbar" class="fixed top-0 left-0 right-0 bg-white shadow-lg z-40 transition-all duration-300">
    <div class="flex items-center justify-between px-6 py-3">
        <!-- Logo -->
        <div class="flex items-center space-x-4">
            <img src="img/logo/logo.png" alt="DNK Logo" class="h-10 w-10 object-contain">
            <span class="text-xl font-bold text-gray-800">DNK GRH</span>
        </div>

        <!-- Date, Heure et IP -->
        <div class="flex items-center space-x-6 text-gray-600">
            <div id="datetime" class="text-sm font-medium"></div>
            <div id="ip-address" class="text-sm font-medium">Chargement IP...</div>
        </div>

        <!-- User Menu -->
        <div class="relative">
            <button id="user-menu-btn" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                <img src="img/user/<?php echo htmlspecialchars(sessionGet('user_photo', 'user.png')); ?>"
                     alt="User" class="h-8 w-8 rounded-full object-cover border-2 border-gray-200"
                     onerror="this.src='img/user/user.png'">
                <span class="text-gray-700 font-medium"><?php echo htmlspecialchars(sessionGet('user_name', 'Utilisateur')); ?></span>
                <i id="chevron-user-menu" class="fas fa-chevron-down text-xs text-gray-400 transition-transform duration-200"></i>
            </button>

            <!-- Dropdown -->
            <div id="user-dropdown" class="absolute right-0 top-full mt-2 w-52 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible transition-all duration-200 transform translate-y-1 z-50">

                <!-- Infos utilisateur -->
                <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-xs font-semibold text-gray-800 truncate"><?php echo htmlspecialchars(sessionGet('user_name', 'Utilisateur')); ?></p>
                    <p class="text-xs text-gray-400 truncate"><?php echo htmlspecialchars(sessionGet('user_email', '')); ?></p>
                    <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold
                        <?php echo sessionGet('user_role') === 'admin' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700'; ?>">
                        <i class="fas <?php echo sessionGet('user_role') === 'admin' ? 'fa-crown' : 'fa-user'; ?> mr-1 text-[8px]"></i>
                        <?php echo sessionGet('user_role') === 'admin' ? 'Administrateur' : 'Utilisateur'; ?>
                    </span>
                </div>

                <div class="py-1">
                    <a href="#" class="flex items-center space-x-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-blue-500 text-xs"></i>
                        </div>
                        <span>Mon profil</span>
                    </a>

                    <div class="border-t border-gray-100 my-1"></div>

                    <!-- Déconnexion → ouvre le popup de confirmation -->
                    <button id="btn-logout-trigger"
                            class="w-full flex items-center space-x-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors rounded-b-xl">
                        <div class="w-7 h-7 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-sign-out-alt text-red-500 text-xs"></i>
                        </div>
                        <span class="font-medium">Déconnexion</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- POPUP CONFIRMATION DÉCONNEXION                             -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div id="logout-overlay"
     class="fixed inset-0 z-[200] hidden items-center justify-center"
     style="background: rgba(15,23,42,0.55); backdrop-filter: blur(4px);">

    <div id="logout-popup"
         class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden
                transform transition-all duration-300 scale-90 opacity-0">

        <!-- Bande décorative haut -->
        <div class="h-1.5 w-full bg-gradient-to-r from-red-500 via-red-400 to-orange-400"></div>

        <!-- Contenu -->
        <div class="px-8 py-8 text-center">

            <!-- Icone animée -->
            <div class="relative inline-flex items-center justify-center mb-5">
                <div class="w-20 h-20 rounded-full bg-red-50 flex items-center justify-center">
                    <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center">
                        <i class="fas fa-sign-out-alt text-2xl text-red-500 logout-icon-anim"></i>
                    </div>
                </div>
                <!-- Cercle animé -->
                <span class="absolute inset-0 rounded-full border-2 border-red-200 logout-pulse"></span>
            </div>

            <h2 class="text-xl font-bold text-gray-800 mb-2">Déconnexion</h2>
            <p class="text-gray-500 text-sm leading-relaxed mb-1">
                Êtes-vous sûr de vouloir quitter votre session ?
            </p>
            <p class="text-xs text-gray-400 mb-8">
                Connecté en tant que
                <span class="font-semibold text-gray-600"><?php echo htmlspecialchars(sessionGet('user_name', '')); ?></span>
            </p>

            <!-- Boutons -->
            <div class="flex gap-3">
                <button id="btn-logout-cancel"
                        class="flex-1 flex items-center justify-center space-x-2 px-5 py-3 rounded-xl
                               border-2 border-gray-200 text-gray-600 font-semibold text-sm
                               hover:bg-gray-50 hover:border-gray-300 transition-all duration-200">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span>Rester</span>
                </button>

                <a href="php/logout.php" id="btn-logout-confirm"
                   class="flex-1 flex items-center justify-center space-x-2 px-5 py-3 rounded-xl
                          bg-gradient-to-r from-red-500 to-red-600 text-white font-semibold text-sm
                          hover:from-red-600 hover:to-red-700 shadow-md hover:shadow-red-200
                          hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5">
                    <i class="fas fa-sign-out-alt text-xs"></i>
                    <span>Se déconnecter</span>
                </a>
            </div>
        </div>
    </div>
</div>