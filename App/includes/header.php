<?php
// On démarre la session ici une seule fois pour tout le site
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon E-Commerce PHP</title>
    <style>
        nav { background: #333; color: white; padding: 10px; display: flex; align-items: center; justify-content: space-between; }
        .nav-links { display: flex; gap: 15px; }
        nav a { color: white; text-decoration: none; font-size: 14px; }
        nav a:hover { text-decoration: underline; }
        
        /* Style spécial pour le bouton admin */
        .btn-admin { background: #dc3545; padding: 5px 10px; border-radius: 4px; font-weight: bold; }
        .btn-admin:hover { background: #c82333; text-decoration: none !important; }
        
        .container { padding: 20px; }
    </style>
</head>
<body>
    <nav>
        <div class="nav-links">
            <a href="index.php">🏠 Accueil</a>
            
            <?php if (isset($_SESSION['username'])): ?>
                <a href="vente.php">📦 Vendre</a>
                <a href="cart.php">🛒 Mon Panier</a>
                <a href="account.php">👤 Mon Compte</a>
                
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php" class="btn-admin">⚙️ Administration</a>
                <?php endif; ?>

            <?php endif; ?>
        </div>

        <div class="nav-auth">
            <?php if (isset($_SESSION['username'])): ?>
                <a href="logout.php" style="color: #bbb;">Déconnexion (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
            <?php else: ?>
                <a href="login.php">Connexion</a>
                <a href="register.php">Inscription</a>
            <?php endif; ?>
        </div>
    </nav>
    <div class="container">