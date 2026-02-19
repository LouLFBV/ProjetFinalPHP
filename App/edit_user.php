<?php
require_once 'includes/header.php';
checkConnexion();

// 1. Sécurité : Seul l'admin entre ici
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = "";

// 2. Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $mysqli->real_escape_string($_POST['username']);
    $new_email = $mysqli->real_escape_string($_POST['email']);
    $new_balance = floatval($_POST['balance']);
    $new_role = $_POST['role'];

    if (!empty($_POST['password'])) {
        $hashed_pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $mysqli->prepare("UPDATE User SET username = ?, email = ?, balance = ?, role = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssdssi", $new_username, $new_email, $new_balance, $new_role, $hashed_pass, $user_id);
    } else {
        $stmt = $mysqli->prepare("UPDATE User SET username = ?, email = ?, balance = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssdsi", $new_username, $new_email, $new_balance, $new_role, $user_id);
    }

    if ($stmt->execute()) {
        $message = "<div class='alert-success' style='background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px;'>✅ Utilisateur mis à jour !</div>";
    } else {
        $message = "<div class='alert-error'>❌ Erreur lors de la mise à jour.</div>";
    }
}

// 3. Récupération des infos
$res = $mysqli->query("SELECT * FROM User WHERE id = $user_id");
$u = $res->fetch_assoc();

if (!$u) {
    die("<div class='container'><h1>Utilisateur introuvable.</h1><a href='admin.php'>Retour</a></div>");
}
?>

<div style="max-width: 600px; margin: 40px auto;">
    <a href="admin.php" style="text-decoration: none; color: #666; display: block; margin-bottom: 20px;">← Retour au Panel Admin</a>

    <div class="auth-card" style="max-width: 100%;">
        <h1>Modifier le profil</h1>
        <p style="color: #666; margin-bottom: 30px;">ID Utilisateur : #<?php echo $u['id']; ?></p>

        <?php echo $message; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label>Nom d'utilisateur</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($u['username']); ?>" required>
            </div>

            <div class="form-group">
                <label>Adresse Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($u['email']); ?>" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Solde (€)</label>
                    <input type="number" step="0.01" name="balance" value="<?php echo $u['balance']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Rôle Système</label>
                    <select name="role" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; background: white;">
                        <option value="user" <?php if($u['role'] === 'user') echo 'selected'; ?>>Utilisateur Standard</option>
                        <option value="admin" <?php if($u['role'] === 'admin') echo 'selected'; ?>>Administrateur</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top: 10px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <label>🔒 Sécurité</label>
                <p style="font-size: 0.8rem; color: #888; margin-bottom: 10px;">Laissez vide pour conserver le mot de passe actuel.</p>
                <input type="password" name="password" placeholder="Nouveau mot de passe">
            </div>

            <button type="submit" class="btn-submit" style="margin-top: 20px;">
                Sauvegarder les modifications
            </button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>