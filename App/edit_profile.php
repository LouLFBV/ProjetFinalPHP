<?php
require_once 'includes/header.php';
checkConnexion();

$uid = $_SESSION['user_id'];
$success = "";
$error = "";

$stmt_get = $mysqli->prepare("SELECT username, email, image_url FROM User WHERE id = ?");
$stmt_get->bind_param("i", $uid);
$stmt_get->execute();
$current_user = $stmt_get->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_user = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $new_image = trim($_POST['image_url']); 
    $new_pass = $_POST['password'];

    if (!empty($new_user) && !empty($new_email)) {
        
        $check = $mysqli->prepare("SELECT id FROM User WHERE (username = ? OR email = ?) AND id != ?");
        $check->bind_param("ssi", $new_user, $new_email, $uid);
        $check->execute();
        $res_check = $check->get_result();

        if ($res_check->num_rows > 0) {
            $error = "Désolé, ce pseudo ou cet email est déjà utilisé.";
        } else {
            if (!empty($new_pass)) {
                $hashed_pass = password_hash($new_pass, PASSWORD_BCRYPT);
                $stmt = $mysqli->prepare("UPDATE User SET username = ?, email = ?, image_url = ?, password = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $new_user, $new_email, $new_image, $hashed_pass, $uid);
            } else {
                $stmt = $mysqli->prepare("UPDATE User SET username = ?, email = ?, image_url = ? WHERE id = ?");
                $stmt->bind_param("sssi", $new_user, $new_email, $new_image, $uid);
            }

            if ($stmt->execute()) {
                $_SESSION['username'] = $new_user;
                $success = "✨ Profil mis à jour avec succès !";
                
                $current_user['username'] = $new_user;
                $current_user['email'] = $new_email;
                $current_user['image_url'] = $new_image;
            } else {
                $error = "Erreur lors de la mise à jour.";
            }
        }
    } else {
        $error = "Veuillez remplir les champs obligatoires.";
    } 
}
?>

<div class="account-wrapper" style="max-width: 600px;">
    
    <div style="margin-bottom: 30px; text-align: center;">
        <h1 style="font-size: 2rem; margin-bottom: 10px;">Paramètres du compte</h1>
        <p style="color: #666;">Gérez vos informations personnelles et votre sécurité.</p>
    </div>

    <?php if($success): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; border: 1px solid #c3e6cb;">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if($error): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; border: 1px solid #f5c6cb;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="auth-card" style="max-width: 100%; padding: 40px;">
        <form method="POST" action="edit_profile.php" class="auth-form">
            
            <div style="text-align: center; margin-bottom: 30px;">
                <?php if(!empty($current_user['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($current_user['image_url']); ?>" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary-color); margin-bottom: 10px;">
                <?php else: ?>
                    <div style="width: 100px; height: 100px; border-radius: 50%; background: #f0f0f0; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; color: #ccc; margin-bottom: 10px;">👤</div>
                <?php endif; ?>
                <p style="font-size: 0.8rem; color: #888;">Aperçu de votre avatar</p>
            </div>

            <div class="form-group">
                <label>Nom d'utilisateur</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($current_user['username']); ?>" required>
            </div>

            <div class="form-group">
                <label>Adresse email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($current_user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label>URL de votre photo de profil</label>
                <input type="url" name="image_url" value="<?php echo htmlspecialchars($current_user['image_url'] ?? ''); ?>" placeholder="https://...">
                <small style="display:block; margin-top:5px; color:#999; font-size:0.75rem;">Collez le lien d'une image trouvée sur le web.</small>
            </div>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">

            <div class="form-group">
                <label>Changer le mot de passe</label>
                <input type="password" name="password" placeholder="Laissez vide pour conserver l'actuel">
                <small style="display:block; margin-top:5px; color:#999; font-size:0.75rem;">N'utilisez ce champ que si vous souhaitez changer de mot de passe.</small>
            </div>

            <button type="submit" class="btn-submit" style="margin-top: 20px;">
                💾 Sauvegarder les modifications
            </button>
            
            <a href="account.php" style="display: block; text-align: center; margin-top: 20px; text-decoration: none; color: #888; font-size: 0.9rem;">
                Retourner au profil
            </a>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>