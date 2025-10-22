<?php
session_start();

// Si l'utilisateur est déjà connecté, on le redirige vers son dashboard selon le rôle
if (!empty($_SESSION['user_id'])) {
    switch ($_SESSION['user_role']) {
        case 'admin':
            header('Location: dashboards/admin_dashboard.php');
            break;
        case 'encadrant':
            header('Location: dashboards/encadrant_dashboard.php');
            break;
        case 'etudiant':
            header('Location: dashboards/etudiant_dashboard.php');
            break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion Encadrement</title>
    <!-- Lien vers la feuille de styles -->
    <link rel="stylesheet" href="style.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="portal-container">
        <!-- Illustration portail -->
        <img class="portal-img" src="istockphoto-1420039900-612x612.jpg" alt="Illustration portail" />

        <div class="glass-login-box">
            <h1>Bienvenue</h1>
            <p>Connectez-vous pour continuer</p>

            <form action="login_process.php" method="POST" onsubmit="return validateLogin()">
                <!-- Nom d'utilisateur -->
                <div class="form-group">
                    <input type="text" id="username" name="username" required placeholder="" />
                    <label for="username">Nom d’utilisateur</label>
                    <span class="input-icon">
                        <!-- Icône utilisateur SVG -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="8" r="4" stroke="#7ef1ff" stroke-width="2"/>
                            <path d="M4 20c0-4 4-6 8-6s8 2 8 6" stroke="#7ef1ff" stroke-width="2"/>
                        </svg>
                    </span>
                </div>

                <!-- Mot de passe -->
                <div class="form-group">
                    <input type="password" id="password" name="password" required placeholder="" />
                    <label for="password">Mot de passe</label>
                    <span class="input-icon password-toggle" onclick="togglePassword()">
                        <!-- Icône œil SVG -->
                        <svg id="eyeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M1 12s4-8 11-8s11 8 11 8s-4 8-11 8s-11-8-11-8z" stroke="#7ef1ff" stroke-width="2"/>
                            <circle cx="12" cy="12" r="3" stroke="#7ef1ff" stroke-width="2"/>
                        </svg>
                    </span>
                </div>

                <!-- Bouton de connexion -->
                <button type="submit" class="btn-primary">Se connecter</button>

                <!-- Message d'erreur -->
                <?php if (!empty($_SESSION['login_error'])): ?>
                    <div class="error-message"><?= htmlspecialchars($_SESSION['login_error']) ?></div>
                    <?php unset($_SESSION['login_error']); ?>
                <?php endif; ?>

            </form>
        </div>
    </div>

    <script>
    // Validation simple avant soumission
    function validateLogin() {
        const u = document.getElementById('username').value.trim();
        const p = document.getElementById('password').value.trim();
        if (!u || !p) {
            alert("Veuillez remplir tous les champs.");
            return false;
        }
        return true;
    }

    // Afficher/masquer le mot de passe
    function togglePassword() {
        const pw = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');
        if (pw.type === 'password') {
            pw.type = 'text';
            icon.innerHTML = '<path d="M3 12a9 9 0 0118 0 9 9 0 01-18 0z" stroke="#7ef1ff" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="#7ef1ff" stroke-width="2"/>';
        } else {
            pw.type = 'password';
            icon.innerHTML = '<path d="M1 12s4-8 11-8s11 8 11 8s-4 8-11 8s-11-8-11-8z" stroke="#7ef1ff" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="#7ef1ff" stroke-width="2"/>';
        }
    }
    </script>
</body>
</html>
