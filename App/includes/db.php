<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "php_exam";

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die("Échec de la connexion : " . $mysqli->connect_error);
}
?>