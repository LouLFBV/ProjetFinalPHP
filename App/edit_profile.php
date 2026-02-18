<?php
require_once 'includes/header.php';
checkConnexion();

$uid = $_SESSION['user_id'];
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email = $_POST['email'];
    $new_pass = $_POST['password'];

    if (!empty($new_email) && !empty($new_pass)) {
        // On hache le nouveau mot de passe (Bcrypt)
        $hashed_pass = password_hash($new_pass, PASSWORD_BCRYPT);
        
        $stmt = $mysqli->prepare("UPDATE User SET email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_email, $hashed_pass, $uid);
        
        if ($stmt->execute()) {
            $success = "Profil mis à jour avec succès !";
        } else {
            $error = "Erreur lors de la mise à jour (l'email est peut-être déjà utilisé).";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    } 
}

// Récupérer l'email actuel pour le pré-remplir
$res = $mysqli->query("SELECT email FROM User WHERE id = $uid");
$current_user = $res->fetch_assoc();
?>

<h1>Modifier mes informations personnelles</h1>

<?php if($success) echo "<p style='color:green;'>$success</p>"; ?>
<?php if($error) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST" action="edit_profile.php">
    <label>Nouvelle adresse email :</label><br>
    <input type="email" name="email" value="<?php echo htmlspecialchars($current_user['email']); ?>" required><br><br>

    <label>Nouveau mot de passe :</label><br>
    <input type="password" name="password" placeholder="Entrez un nouveau mot de passe" required><br><br>

    <button type="submit">Enregistrer les modifications</button>
    <a href="account.php">Retour au compte</a>
</form>

<?php require_once 'includes/footer.php'; ?>