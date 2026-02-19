<?php
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendons-les | Boutique d'occasion</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav>
        <a href="index.php" class="logo">VENDONS-LES.</a>
        
        <div class="nav-links">
            <a href="index.php">🏠 Accueil</a>
            <?php if (isset($_SESSION['username'])): ?>
                <a href="vente.php">📦 Vendre</a>
                <a href="cart.php">🛒 Panier</a>
                <a href="account.php">👤 Mon Compte</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php" class="btn-admin">⚙️ Admin</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="nav-auth">
            <?php if (isset($_SESSION['username'])): ?>
                
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin'): ?>
                    <span style="font-weight: bold; margin-right: 10px;">Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <?php endif; ?>

                <a href="logout.php" class="logout-link">Déconnexion</a>

            <?php else: ?>
                <a href="login.php" class="btn-login">Connexion</a>
                <a href="register.php">S'inscrire</a>
            <?php endif; ?>
        </div>
    </nav>
    <main class="container">