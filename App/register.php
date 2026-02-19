<?php
require_once 'includes/header.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!file_exists('includes/db.php')) {
    die("Erreur critique : Le fichier includes/db.php est introuvable !");
}
require_once 'includes/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($email) && !empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $mysqli->prepare("INSERT INTO User (username, email, password, role) VALUES (?, ?, ?, 'user')");
        
        if ($stmt === false) {
            die("Erreur de préparation : " . $mysqli->error);
        }

        $stmt->bind_param("sss", $username, $email, $hashedPassword);

        try {
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $mysqli->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'user'; 

                header('Location: index.php');
                exit;
            }
        } catch (mysqli_sql_exception $e) {
            $error = "Ce pseudonyme ou cet email est déjà utilisé.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <h1>Rejoindre la communauté</h1>
        <p style="text-align: center; color: #666; margin-bottom: 25px;">Créez votre compte pour vendre et acheter sur Vendons-les.</p>

        <?php if(!empty($error)): ?>
            <div class="alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" class="auth-form">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" name="username" id="username" placeholder="Ex: JeanDupont" required>
            </div>

            <div class="form-group">
                <label for="email">Adresse Email</label>
                <input type="email" name="email" id="email" placeholder="jean@exemple.fr" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" name="password" id="password" placeholder="z!s32pl*KSK" required>
            </div>

            <button type="submit" class="btn-submit">Créer mon compte</button>
        </form>

        <div class="auth-footer">
            Vous avez déjà un compte ? <a href="login.php">Connectez-vous</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>