<?php
require_once 'includes/header.php';

// 1. Récupération des paramètres de recherche et de tri
$search = isset($_GET['search']) ? $mysqli->real_escape_string($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';

// 2. Définition de la clause ORDER BY
$order_by = "Article.date_publication DESC"; // Par défaut : plus récent
if ($sort == 'price_asc') $order_by = "Article.prix ASC";
if ($sort == 'price_desc') $order_by = "Article.prix DESC";
if ($sort == 'date_asc') $order_by = "Article.date_publication ASC";

// 3. Construction de la requête avec recherche (si remplie)
$query = "SELECT Article.*, Stock.quantite, User.username as auteur 
          FROM Article 
          LEFT JOIN Stock ON Article.id = Stock.article_id 
          INNER JOIN User ON Article.auteur_id = User.id";

if (!empty($search)) {
    $query .= " WHERE Article.nom LIKE '%$search%' OR Article.description LIKE '%$search%'";
}

$cat_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;

// On modifie la construction de la requête $query
$query = "SELECT Article.*, Stock.quantite, User.username as auteur, Category.nom as cat_nom 
          FROM Article 
          LEFT JOIN Stock ON Article.id = Stock.article_id 
          INNER JOIN User ON Article.auteur_id = User.id
          LEFT JOIN Category ON Article.category_id = Category.id
          WHERE 1=1"; // Astuce SQL pour enchaîner les AND facilement

if (!empty($search)) {
    $query .= " AND (Article.nom LIKE '%$search%' OR Article.description LIKE '%$search%')";
}

if ($cat_filter > 0) {
    $query .= " AND Article.category_id = $cat_filter";
}

$query .= " ORDER BY $order_by";
$res = $mysqli->query($query);
?>

<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <div style="background: #e8f4fd; padding: 15px; border-left: 6px solid #2196f3; border-radius: 4px; margin-bottom: 25px;">
        <h4 style="margin:0; color: #0d47a1;">Espace Administrateur</h4>
        <p style="margin: 5px 0;">Vous pouvez gérer les stocks et les utilisateurs depuis le panneau.</p>
        <a href="admin.php" style="display:inline-block; background:#2196f3; color:white; padding:8px 15px; text-decoration:none; border-radius:4px; font-weight:bold;">
            Accéder à l'Administration
        </a>
    </div>
<?php endif; ?>

<h1>Boutique</h1>

<div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 20px; align-items: center;">
    <form method="GET" style="flex-grow: 1; display: flex; gap: 5px;">
        <input type="text" name="search" placeholder="Rechercher un article..." 
               value="<?php echo htmlspecialchars($search); ?>" style="flex-grow: 1; padding: 8px;">
        <button type="submit">🔍</button>
    </form>

    <form method="GET" style="display: flex; gap: 10px;">
    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
    
    <select name="category" onchange="this.form.submit()" style="padding: 8px;">
        <option value="0">Toutes les catégories</option>
        <?php
        $all_cats = $mysqli->query("SELECT * FROM Category ORDER BY nom ASC");
        while($ac = $all_cats->fetch_assoc()):
            $s = ($ac['id'] == $cat_filter) ? 'selected' : '';
            echo "<option value='{$ac['id']}' $s>{$ac['nom']}</option>";
        endwhile;
        ?>
    </select>

    <select name="sort" onchange="this.form.submit()" style="padding: 8px;">
        <option value="date_desc" <?php if($sort == 'date_desc') echo 'selected'; ?>>Plus récents</option>
        <option value="date_asc" <?php if($sort == 'date_asc') echo 'selected'; ?>>Plus anciens</option>
        <option value="price_asc" <?php if($sort == 'price_asc') echo 'selected'; ?>>Prix croissant</option>
        <option value="price_desc" <?php if($sort == 'price_desc') echo 'selected'; ?>>Prix décroissant</option>
    </select>
</form>
</div>

<h1>Articles en vente</h1>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
    <?php if ($res->num_rows > 0): ?>
        <?php while($art = $res->fetch_assoc()): ?>
            <div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px; position: relative;">
               <h3><?php echo htmlspecialchars($art['nom']); ?></h3>
                <?php if($art['cat_nom']): ?>
                    <span style="background:#eee; padding:2px 6px; border-radius:4px; font-size:0.7em;">
                        <?php echo htmlspecialchars($art['cat_nom']); ?>
                    </span>
                <?php endif; ?>
                <p style="font-size: 0.9em; color: #666;">Par : <?php echo htmlspecialchars($art['auteur']); ?></p>
                <p><strong><?php echo formatPrix($art['prix']); ?></strong></p>
                
                <?php if ($art['quantite'] > 0): ?>
                    <p style="color: green; font-size: 0.8em;">En stock (<?php echo $art['quantite']; ?>)</p>
                <?php else: ?>
                    <p style="color: red; font-size: 0.8em; font-weight: bold;">Rupture de stock</p>
                <?php endif; ?>

                <a href="detail.php?id=<?php echo $art['id']; ?>" style="display: inline-block; margin-top: 10px; background: #007bff; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px;">
                    Voir le produit
                </a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Aucun article ne correspond à votre recherche.</p>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>