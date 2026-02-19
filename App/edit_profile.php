<?php
require_once 'includes/header.php';
checkConnexion();

$uid = $_SESSION['user_id'];
$success = "";
$error = "";

// 1. Récupérer les infos actuelles (dont l'image_url) pour pré-remplir le formulaire
$stmt_get = $mysqli->prepare("SELECT username, email, image_url FROM User WHERE id = ?");
$stmt_get->bind_param("i", $uid);
$stmt_get->execute();
$current_user = $stmt_get->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_user = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $new_image = trim($_POST['image_url']); // Nouveau champ photo
    $new_pass = $_POST['password'];

    if (!empty($new_user) && !empty($new_email)) {
        
        // 2. VÉRIFICATION DE DISPONIBILITÉ (Username et Email unique)
        $check = $mysqli->prepare("SELECT id FROM User WHERE (username = ? OR email = ?) AND id != ?");
        $check->bind_param("ssi", $new_user, $new_email, $uid);
        $check->execute();
        $res_check = $check->get_result();

        if ($res_check->num_rows > 0) {
            $error = "Désolé, ce pseudo ou cette adresse email est déjà utilisé par un autre compte.";
        } else {
            // 3. MISE À JOUR (Gestion avec ou sans mot de passe)
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
                $success = "Profil mis à jour avec succès !";
                
                // Rafraîchir les données locales pour le formulaire
                $current_user['username'] = $new_user;
                $current_user['email'] = $new_email;
                $current_user['image_url'] = $new_image;
            } else {
                $error = "Une erreur technique est survenue.";
            }
        }
    } else {
        $error = "Le pseudo et l'email ne peuvent pas être vides.";
    } 
}
?>

<div style="max-width: 500px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
    <h1 style="margin-top: 0; font-size: 1.5em;">Modifier mes informations</h1>

    <?php if($success): ?>
        <p style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px;"><?php echo $success; ?></p>
    <?php endif; ?>

    <?php if($error): ?>
        <p style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="edit_profile.php">
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Nom d'utilisateur :</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($current_user['username']); ?>" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Adresse email :</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($current_user['email']); ?>" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Lien de la photo de profil (URL) :</label>
            <input type="url" name="image_url" value="<?php echo htmlspecialchars($current_user['image_url'] ?? ''); ?>" placeholder="https://exemple.com/photo.jpg" 
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            <small style="color: #888;">Utilisez un lien vers une image hébergée en ligne.</small>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Nouveau mot de passe :</label>
            <input type="password" name="password" placeholder="Laissez vide pour ne pas changer" 
                   style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            <small style="color: #888;">Si vous ne voulez pas modifier votre mot de passe, laissez ce champ vide.</small>
        </div>

        <button type="submit" style="background: #333; color: white; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; width: 100%; font-size: 1em;">
            Enregistrer les modifications
        </button>
        
        <p style="text-align: center; margin-top: 15px;">
            <a href="account.php" style="color: #666; text-decoration: none;">← Annuler et retourner au profil</a>
        </p>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>