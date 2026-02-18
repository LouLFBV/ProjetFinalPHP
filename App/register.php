<?php
require_once 'includes/header.php';
// 1. Activation totale des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Vérification du fichier de connexion
if (!file_exists('includes/db.php')) {
    die("Erreur critique : Le fichier includes/db.php est introuvable !");
}
require_once 'includes/db.php';

$error = "";

// 3. Logique de traitement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($email) && !empty($password)) {
        // Le hashage du mot de passe
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // Requête préparée
        $stmt = $mysqli->prepare("INSERT INTO User (username, email, password) VALUES (?, ?, ?)");

        if ($stmt === false) {
            die("Erreur de préparation : " . $mysqli->error);
        }

        $stmt->bind_param("sss", $username, $email, $hashedPassword);

        try {
            if ($stmt->execute()) {
                // Connexion automatique après inscription (consigne projet)
                $_SESSION['user_id'] = $mysqli->insert_id;
                $_SESSION['username'] = $username;

                // Redirection vers index.php
                header('Location: index.php');
                exit;
            }
        } catch (mysqli_sql_exception $e) {
            $error = "Erreur : L'utilisateur ou l'email existe déjà.";
        }
    } else {
        $error = "Tous les champs sont obligatoires.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - E-Commerce</title>
</head>
<body>
    <h1>Créer un compte</h1>
    
    <?php if(!empty($error)): ?>
        <p style="color:red; border: 1px solid red; padding: 10px;">
            <?php echo $error; ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <input type="text" name="username" placeholder="Username" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Mot de passe" required><br><br>
        <button type="submit">S'inscrire</button>
    </form>
</body>
</html>

<?php require_once 'includes/footer.php'; ?>