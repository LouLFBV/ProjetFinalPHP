<?php
// 1. On initialise la session pour pouvoir la manipuler
session_start();

// 2. On vide toutes les variables de session (ex: $_SESSION['username'])
$_SESSION = array();

// 3. On détruit physiquement la session sur le serveur
session_destroy();

// 4. On redirige vers l'accueil
header("Location: index.php");
exit;
?>