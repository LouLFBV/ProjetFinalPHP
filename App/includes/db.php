<?php
$host = "127.0.0.1";
$user = "root";
$pass = ""; // Laisse vide ou mets "root" selon ton installation [cite: 160]
$db   = "php_exam";

// Connexion à la DB [cite: 159]
$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die("Échec de la connexion : " . $mysqli->connect_error);
}
?>