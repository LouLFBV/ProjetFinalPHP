<?php
require_once 'includes/header.php';
checkConnexion();

// Sécurité Admin 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// --- LOGIQUE DE SUPPRESSION ---

// Supprimer un article 
if (isset($_GET['del_art'])) {
    $id_art = intval($_GET['del_art']);
    $mysqli->query("DELETE FROM Article WHERE id = $id_art");
    header("Location: admin.php?msg=Article supprime");
}

// Supprimer un utilisateur 
if (isset($_GET['del_user'])) {
    $id_u = intval($_GET['del_user']);
    // On évite que l'admin se supprime lui-même par erreur
    if ($id_u !== $_SESSION['user_id']) {
        $mysqli->query("DELETE FROM User WHERE id = $id_u");
        header("Location: admin.php?msg=Utilisateur supprime");
    }
}

// Récupération des données 
$all_articles = $mysqli->query("SELECT Article.*, User.username FROM Article JOIN User ON Article.auteur_id = User.id");
$all_users = $mysqli->query("SELECT * FROM User WHERE id != " . $_SESSION['user_id']); // On cache l'admin connecté 
?>

<h1>Panneau d'administration</h1>

<?php if(isset($_GET['msg'])) echo "<p style='color:orange;'>".htmlspecialchars($_GET['msg'])."</p>"; ?>

<section>
    <h2>Tous les Articles </h2>
    <table border="1" style="width:100%; border-collapse: collapse;">
        <tr>
            <th>Article</th>
            <th>Auteur</th>
            <th>Actions</th>
        </tr>
        <?php while($art = $all_articles->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($art['nom']); ?></td>
            <td><?php echo htmlspecialchars($art['username']); ?></td>
            <td>
                <a href="edit.php?id=<?php echo $art['id']; ?>">Modifier</a> | 
                <a href="admin.php?del_art=<?php echo $art['id']; ?>" onclick="return confirm('Supprimer cet article ?')" style="color:red;">Supprimer</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</section>

<br><hr><br>

<section>
    <h2>Tous les Utilisateurs </h2>
    <table border="1" style="width:100%; border-collapse: collapse;">
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Actions</th>
        </tr>
        <?php while($u = $all_users->fetch_assoc()): ?>
        <tr>
            <td>
                <a href="account.php?id=<?php echo $u['id']; ?>">
                    <strong><?php echo htmlspecialchars($u['username']); ?></strong>
                </a>
            </td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td><?php echo $u['role']; ?></td>
            <td>
                <a href="edit_user.php?id=<?php echo $u['id']; ?>">Modifier</a> | 
                <a href="admin.php?del_user=<?php echo $u['id']; ?>" onclick="..." style="color:red;">Supprimer</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</section>

<?php require_once 'includes/footer.php'; ?>