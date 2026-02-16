<?php
// Formater un prix proprement
function formatPrix($montant) {
    return number_format($montant, 2, ',', ' ') . " €";
}

// Vérifier si l'utilisateur est connecté pour protéger les pages
function checkConnexion() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

// Vérifier si l'utilisateur est admin [cite: 236]
function isAdmin($role) {
    return $role === 'admin';
}
?>