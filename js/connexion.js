document.addEventListener('DOMContentLoaded', function() {
    // Gestion des labels flottants
    const inputs = document.querySelectorAll('.input-container input');
    
    inputs.forEach(input => {
        // Vérifier si le champ a déjà une valeur (pour le rechargement de page)
        if (input.value) {
            input.nextElementSibling.classList.add('active');
        }
        
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
            this.nextElementSibling.classList.add('active');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
            if (!this.value) {
                this.nextElementSibling.classList.remove('active');
            }
        });
    });
    
    // Gestion de l'affichage/masquage du mot de passe
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }
    
    // Gestion du formulaire
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            // Validation simple côté client
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!email || !password) {
                e.preventDefault();
                showNotification('Veuillez remplir tous les champs', 'error');
                return;
            }
            
            // Validation basique de l'email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                showNotification('Veuillez saisir une adresse email valide', 'error');
                return;
            }
            
            // Animation du bouton
            const submitBtn = this.querySelector('.login-btn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Connexion en cours...</span>';
            submitBtn.disabled = true;
        });
    }
    
    // Gestion du "Se souvenir de moi"
    const rememberCheckbox = document.querySelector('input[name="remember"]');
    const savedEmail = localStorage.getItem('saved_email');
    
    if (savedEmail && rememberCheckbox) {
        document.getElementById('email').value = savedEmail;
        rememberCheckbox.checked = true;
        document.querySelector('.input-label').classList.add('active');
    }
    
    if (rememberCheckbox) {
        rememberCheckbox.addEventListener('change', function() {
            const email = document.getElementById('email').value;
            if (this.checked && email) {
                localStorage.setItem('saved_email', email);
            } else {
                localStorage.removeItem('saved_email');
            }
        });
    }
});

// Fonction pour afficher les notifications
function showNotification(message, type = 'info', duration = 5000) {
    const container = document.getElementById('notificationContainer');
    if (!container) return;
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    notification.innerHTML = `
        <i class="fas ${icon}"></i>
        <div class="notification-content">
            <h4>${type === 'success' ? 'Succès' : 'Erreur'}</h4>
            <p>${message}</p>
        </div>
        <i class="fas fa-times close-notification" onclick="closeNotification(this)"></i>
    `;
    
    container.appendChild(notification);
    
    // Auto-remove après la durée spécifiée
    if (duration > 0) {
        setTimeout(() => {
            closeNotification(notification.querySelector('.close-notification'));
        }, duration);
    }
}

// Fonction pour fermer les notifications
function closeNotification(element) {
    const notification = element.closest('.notification');
    if (notification) {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }
}

// Gestion des erreurs de saisie en temps réel
document.addEventListener('input', function(e) {
    if (e.target.type === 'email' || e.target.type === 'password') {
        const input = e.target;
        const container = input.closest('.input-container');
        
        if (input.value) {
            container.classList.remove('error');
        }
    }
});