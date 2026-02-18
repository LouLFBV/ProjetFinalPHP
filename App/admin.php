<?php
require_once 'includes/header.php';
checkConnexion();

// Sécurité Admin 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// --- LOGIQUE DE SUPPRESSION ---

// 1. Supprimer un article 
if (isset($_GET['del_art'])) {
    $id_art = intval($_GET['del_art']);
    $mysqli->query("DELETE FROM Article WHERE id = $id_art");
    header("Location: admin.php?msg=Article supprime");
    exit;
}

// 2. Supprimer un utilisateur 
if (isset($_GET['del_user'])) {
    $id_u = intval($_GET['del_user']);
    if ($id_u !== $_SESSION['user_id']) {
        $mysqli->query("DELETE FROM User WHERE id = $id_u");
        header("Location: admin.php?msg=Utilisateur supprime");
        exit;
    }
}

// 3. Supprimer une catégorie (Bonus !)
if (isset($_GET['del_cat'])) {
    $id_cat = intval($_GET['del_cat']);
    $mysqli->query("DELETE FROM Category WHERE id = $id_cat");
    header("Location: admin.php?msg=Categorie supprimee");
    exit;
}

// --- LOGIQUE D'AJOUT ---

// Ajouter une catégorie
if (isset($_POST['add_cat'])) {
    $nom_cat = $mysqli->real_escape_string($_POST['nom_categorie']);
    if (!empty($nom_cat)) {
        $mysqli->query("INSERT INTO Category (nom) VALUES ('$nom_cat')");
        header("Location: admin.php?msg=Categorie ajoutee");
        exit;
    }
}

// Récupération des données 
$all_articles = $mysqli->query("SELECT Article.*, User.username FROM Article JOIN User ON Article.auteur_id = User.id");
$all_users = $mysqli->query("SELECT * FROM User WHERE id != " . $_SESSION['user_id']); 
$all_cats = $mysqli->query("SELECT * FROM Category ORDER BY nom ASC");
?>

<h1>Panneau d'administration</h1>

<?php if(isset($_GET['msg'])) echo "<p style='color:orange; font-weight:bold;'>[!] ".htmlspecialchars($_GET['msg'])."</p>"; ?>

<section style="background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 8px;">
    <h2>Gestion des Catégories</h2>
    <form method="POST" style="margin-bottom: 15px; display: flex; gap: 10px;">
        <input type="text" name="nom_categorie" placeholder="Nouvelle catégorie (ex: Gaming)" required style="padding: 8px; flex-grow: 1;">
        <button type="submit" name="add_cat" style="background: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">Ajouter</button>
    </form>

    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
        <?php while($c = $all_cats->fetch_assoc()): ?>
            <span style="background: #eee; padding: 5px 10px; border-radius: 20px; font-size: 0.9em; display: flex; align-items: center; gap: 8px;">
                <?php echo htmlspecialchars($c['nom']); ?>
                <a href="admin.php?del_cat=<?php echo $c['id']; ?>" onclick="return confirm('Supprimer cette catégorie ?')" style="color: red; text-decoration: none; font-weight: bold;">×</a>
            </span>
        <?php endwhile; ?>
    </div>
</section>

<br><hr><br>

<section>
    <h2>Tous les Articles </h2>
    <table border="1" style="width:100%; border-collapse: collapse;">
        <tr style="background: #f8f9fa;">
            <th>Article</th>
            <th>Auteur</th>
            <th>Actions</th>
        </tr>
        <?php while($art = $all_articles->fetch_assoc()): ?>
        <tr>
            <td style="padding: 8px;"><?php echo htmlspecialchars($art['nom']); ?></td>
            <td style="padding: 8px;"><?php echo htmlspecialchars($art['username']); ?></td>
            <td style="padding: 8px;">
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
        <tr style="background: #f8f9fa;">
            <th>Username</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Actions</th>
        </tr>
        <?php while($u = $all_users->fetch_assoc()): ?>
        <tr>
            <td style="padding: 8px;">
                <a href="account.php?id=<?php echo $u['id']; ?>">
                    <strong><?php echo htmlspecialchars($u['username']); ?></strong>
                </a>
            </td>
            <td style="padding: 8px;"><?php echo htmlspecialchars($u['email']); ?></td>
            <td style="padding: 8px;"><?php echo $u['role']; ?></td>
            <td style="padding: 8px;">
                <a href="edit_user.php?id=<?php echo $u['id']; ?>">Modifier</a> | 
                <a href="admin.php?del_user=<?php echo $u['id']; ?>" onclick="return confirm('Bannir cet utilisateur ?')" style="color:red;">Supprimer</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</section>

<?php require_once 'includes/footer.php'; ?>