// Statistics management
class StatisticsManager {
    constructor() {
        this.statistiques = {};
        this.init();
    }

    init() {
        this.loadStatistics();
        this.setupEventListeners();
        console.log('Statistics Manager Initialized');
    }

    loadStatistics() {
        // Pour le moment, les statistiques sont gérées en dur dans stat.php
        // Plus tard, nous pourrons charger depuis une API
        this.updateTotalRecords();
    }

    setupEventListeners() {
        // Écouteur pour les mises à jour des statistiques
        document.addEventListener('statisticsUpdate', (event) => {
            this.updateStatistics(event.detail);
        });

        // Écouteur pour le rechargement des statistiques
        document.addEventListener('reloadStatistics', () => {
            this.reloadStatistics();
        });
    }

    updateStatistics(newStats) {
        // Mettre à jour les valeurs affichées avec animation
        Object.keys(newStats).forEach(statKey => {
            const elements = document.querySelectorAll(`[data-stat="${statKey}"]`);
            elements.forEach(element => {
                this.animateValueChange(element, newStats[statKey]);
            });
        });

        this.updateTotalRecords();
    }

    animateValueChange(element, newValue) {
        const currentValue = parseInt(element.textContent) || 0;
        
        if (currentValue !== newValue) {
            element.classList.add('updating');
            
            // Animation de comptage
            this.animateCounter(element, currentValue, newValue, 500);
            
            setTimeout(() => {
                element.classList.remove('updating');
            }, 500);
        }
    }

    animateCounter(element, start, end, duration) {
        const startTime = performance.now();
        const updateValue = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function for smooth animation
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const currentValue = Math.floor(start + (end - start) * easeOutQuart);
            
            element.textContent = currentValue;
            
            if (progress < 1) {
                requestAnimationFrame(updateValue);
            } else {
                element.textContent = end;
            }
        };
        
        requestAnimationFrame(updateValue);
    }

    updateTotalRecords() {
        // Calculer le total des enregistrements
        const statValues = document.querySelectorAll('.stat-value');
        let total = 0;
        
        statValues.forEach(element => {
            const value = parseInt(element.textContent) || 0;
            total += value;
        });

        const totalElement = document.querySelector('.total-records');
        if (totalElement) {
            this.animateCounter(totalElement, parseInt(totalElement.textContent) || 0, total, 500);
        }
    }

    reloadStatistics() {
        // Simuler le rechargement des statistiques
        document.querySelectorAll('.stat-card, .stat-section').forEach(element => {
            element.classList.add('stat-loading');
        });

        setTimeout(() => {
            document.querySelectorAll('.stat-card, .stat-section').forEach(element => {
                element.classList.remove('stat-loading');
            });
            
            // Pour le moment, on ne change pas les valeurs (toujours 0)
            console.log('Statistics reloaded');
        }, 1000);
    }

    // Méthode pour mettre à jour une statistique spécifique
    updateStat(statKey, newValue) {
        const event = new CustomEvent('statisticsUpdate', {
            detail: { [statKey]: newValue }
        });
        document.dispatchEvent(event);
    }

    // Méthode pour réinitialiser toutes les statistiques
    resetStatistics() {
        const resetStats = {
            utilisateurs: 0,
            contrats: 0,
            cnas: 0,
            absences: 0,
            certificats: 0,
            fin_relation: 0,
            mise_en_demeure: 0,
            titre_conge: 0,
            pointages: 0,
            reprise_travail: 0,
            situation_effectifs: 0,
            ordre_mission: 0,
            decharge: 0,
            global: 0
        };

        this.updateStatistics(resetStats);
    }
}

// Initialize Statistics Manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.statisticsManager = new StatisticsManager();
});

// Utility functions for statistics
const StatUtils = {
    // Formater les grands nombres
    formatNumber: (number) => {
        if (number >= 1000000) {
            return (number / 1000000).toFixed(1) + 'M';
        }
        if (number >= 1000) {
            return (number / 1000).toFixed(1) + 'K';
        }
        return number.toString();
    },

    // Générer une couleur basée sur la valeur
    getColorForValue: (value, maxValue) => {
        const percentage = maxValue > 0 ? (value / maxValue) * 100 : 0;
        
        if (percentage >= 80) return 'text-red-500';
        if (percentage >= 60) return 'text-orange-500';
        if (percentage >= 40) return 'text-yellow-500';
        if (percentage >= 20) return 'text-blue-500';
        return 'text-green-500';
    },

    // Calculer la tendance
    calculateTrend: (current, previous) => {
        if (previous === 0) return current > 0 ? 'up' : 'stable';
        const change = ((current - previous) / previous) * 100;
        
        if (change > 5) return 'up';
        if (change < -5) return 'down';
        return 'stable';
    }
};