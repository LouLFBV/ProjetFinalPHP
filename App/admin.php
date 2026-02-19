<?php
require_once 'includes/header.php';
checkConnexion();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// --- LOGIQUE DE SUPPRESSION (Gardée intacte) ---
if (isset($_GET['del_art'])) {
    $id_art = intval($_GET['del_art']);
    $mysqli->query("DELETE FROM Article WHERE id = $id_art");
    header("Location: admin.php?msg=Article supprimé"); exit;
}
if (isset($_GET['del_user'])) {
    $id_u = intval($_GET['del_user']);
    if ($id_u !== $_SESSION['user_id']) {
        $mysqli->query("DELETE FROM User WHERE id = $id_u");
        header("Location: admin.php?msg=Utilisateur supprimé"); exit;
    }
}
if (isset($_GET['del_cat'])) {
    $id_cat = intval($_GET['del_cat']);
    $mysqli->query("DELETE FROM Category WHERE id = $id_cat");
    header("Location: admin.php?msg=Catégorie supprimée"); exit;
}
if (isset($_POST['add_cat'])) {
    $nom_cat = $mysqli->real_escape_string($_POST['nom_categorie']);
    if (!empty($nom_cat)) {
        $mysqli->query("INSERT INTO Category (nom) VALUES ('$nom_cat')");
        header("Location: admin.php?msg=Catégorie ajoutée"); exit;
    }
}

$all_articles = $mysqli->query("SELECT Article.*, User.username FROM Article JOIN User ON Article.auteur_id = User.id ORDER BY Article.date_publication DESC");
$all_users = $mysqli->query("SELECT * FROM User WHERE id != " . $_SESSION['user_id']); 
$all_cats = $mysqli->query("SELECT * FROM Category ORDER BY nom ASC");
?>

<div class="admin-header" style="margin-bottom: 30px;">
    <h1>Tableau de Bord Admin</h1>
    <p style="color: #666;">Bienvenue, <?php echo $_SESSION['username']; ?>. Vous avez le contrôle total du site.</p>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #ffeeba;">
        <strong>[!]</strong> <?php echo htmlspecialchars($_GET['msg']); ?>
    </div>
<?php endif; ?>

<div class="admin-grid">
    
    <div class="admin-card">
        <h3>📂 Gestion des Catégories</h3>
        <form method="POST" style="display: flex; gap: 10px; margin: 20px 0;">
            <input type="text" name="nom_categorie" placeholder="Nom de la catégorie..." required style="flex-grow:1; padding:10px; border:1px solid #ddd; border-radius:6px;">
            <button type="submit" name="add_cat" class="btn-submit" style="width:auto; margin:0; padding:10px 20px;">Ajouter</button>
        </form>

        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
            <?php while($c = $all_cats->fetch_assoc()): ?>
                <div class="cat-tag">
                    <?php echo htmlspecialchars($c['nom']); ?>
                    <a href="admin.php?del_cat=<?php echo $c['id']; ?>" onclick="return confirm('Supprimer ?')" style="color: #ff7675; text-decoration: none;">✕</a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="admin-card">
        <h3>👥 Utilisateurs inscrits</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($u = $all_users->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><span class="badge-role role-<?php echo $u['role']; ?>"><?php echo strtoupper($u['role']); ?></span></td>
                    <td style="text-align:right;">
                        <a href="edit_user.php?id=<?php echo $u['id']; ?>" style="text-decoration:none; margin-right:10px;">✏️</a>
                        <a href="admin.php?del_user=<?php echo $u['id']; ?>" onclick="return confirm('Bannir ?')" style="text-decoration:none;">🗑️</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-card">
        <h3>🛒 Tous les Articles</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Vendeur</th>
                    <th>Prix</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($art = $all_articles->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($art['nom']); ?></td>
                    <td><?php echo htmlspecialchars($art['username']); ?></td>
                    <td><?php echo formatPrix($art['prix']); ?></td>
                    <td style="text-align:right;">
                        <a href="edit.php?id=<?php echo $art['id']; ?>" style="text-decoration:none; margin-right:10px;">✏️</a>
                        <a href="admin.php?del_art=<?php echo $art['id']; ?>" onclick="return confirm('Supprimer ?')" style="text-decoration:none;">🗑️</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>