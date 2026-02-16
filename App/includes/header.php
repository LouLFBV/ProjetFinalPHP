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
        nav { background: #333; color: white; padding: 10px; }
        nav a { color: white; margin-right: 15px; text-decoration: none; }
        .container { padding: 20px; }
    </style>
</head>
<body>
    <nav>
        <a href="index.php">Accueil</a>
        <?php if (isset($_SESSION['username'])): ?>
            <a href="vente.php">Vendre</a>
            <a href="cart.php">Mon Panier</a>
            <a href="account.php">Mon Compte</a>
            <a href="logout.php">Déconnexion (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
        <?php else: ?>
            <a href="login.php">Connexion</a>
            <a href="register.php">Inscription</a>
        <?php endif; ?>
    </nav>
    <div class="container">