<?php
require_once 'includes/header.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $mysqli->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $stmt = $mysqli->prepare("SELECT id, username, password, role FROM User WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; 
        header("Location: index.php");
        exit;
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <h1>Bon retour !</h1>

        <?php if($error): ?>
            <div class="alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Adresse Email</label>
                <input type="email" name="email" id="email" placeholder="exemple@mail.com" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" name="password" id="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-submit">Se connecter</button>
        </form>

        <div class="auth-footer">
            Pas encore de compte ? <a href="register.php">Inscrivez-vous ici</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>