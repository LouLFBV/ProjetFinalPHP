<?php
require_once 'includes/header.php';
checkConnexion();

// 1. Sécurité : Vérifier si l'utilisateur est admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = "";

// 2. Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];
    $new_balance = floatval($_POST['balance']);
    $new_role = $_POST['role'];

    // Si un mot de passe est saisi, on le met à jour, sinon on garde l'ancien
    if (!empty($_POST['password'])) {
        $hashed_pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $mysqli->prepare("UPDATE User SET username = ?, email = ?, balance = ?, role = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssdsii", $new_username, $new_email, $new_balance, $new_role, $hashed_pass, $user_id);
    } else {
        $stmt = $mysqli->prepare("UPDATE User SET username = ?, email = ?, balance = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssdsi", $new_username, $new_email, $new_balance, $new_role, $user_id);
    }

    if ($stmt->execute()) {
        $message = "<p style='color:green;'>Utilisateur mis à jour avec succès !</p>";
    } else {
        $message = "<p style='color:red;'>Erreur lors de la mise à jour.</p>";
    }
}

// 3. Récupération des infos actuelles de l'utilisateur
$res = $mysqli->query("SELECT * FROM User WHERE id = $user_id");
$u = $res->fetch_assoc();

if (!$u) {
    die("Utilisateur introuvable.");
}
?>

<h1>Modifier l'utilisateur : <?php echo htmlspecialchars($u['username']); ?></h1>
<?php echo $message; ?>

<form method="POST" style="max-width: 400px;">
    <label>Nom d'utilisateur :</label><br>
    <input type="text" name="username" value="<?php echo htmlspecialchars($u['username']); ?>" required><br><br>

    <label>Email :</label><br>
    <input type="email" name="email" value="<?php echo htmlspecialchars($u['email']); ?>" required><br><br>

    <label>Solde actuel (€) :</label><br>
    <input type="number" step="0.01" name="balance" value="<?php echo $u['balance']; ?>" required><br><br>

    <label>Rôle :</label><br>
    <select name="role">
        <option value="user" <?php if($u['role'] === 'user') echo 'selected'; ?>>Utilisateur (user)</option>
        <option value="admin" <?php if($u['role'] === 'admin') echo 'selected'; ?>>Administrateur (admin)</option>
    </select><br><br>

    <label>Changer le mot de passe (laisser vide pour ne pas modifier) :</label><br>
    <input type="password" name="password" placeholder="Nouveau mot de passe"><br><br>

    <button type="submit">Enregistrer les modifications</button>
    <a href="admin.php" style="margin-left: 10px;">Retour à l'admin</a>
</form>

<?php require_once 'includes/footer.php'; ?>