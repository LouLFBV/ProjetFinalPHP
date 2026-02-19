<?php
require_once 'includes/header.php';

// 1. Récupération des paramètres
$search = isset($_GET['search']) ? $mysqli->real_escape_string($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';
$cat_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;

// 2. Définition du tri
$order_by = "Article.date_publication DESC";
if ($sort == 'price_asc') $order_by = "Article.prix ASC";
if ($sort == 'price_desc') $order_by = "Article.prix DESC";
if ($sort == 'date_asc') $order_by = "Article.date_publication ASC";

// 3. Construction de la requête avec calcul de la moyenne (Note)
// On utilise LEFT JOIN sur Review et un GROUP BY pour avoir la moyenne par article
$query = "SELECT Article.*, Stock.quantite, User.username as auteur, Category.nom as cat_nom,
                 AVG(Review.note) as moyenne_note, COUNT(Review.id) as nb_avis
          FROM Article 
          LEFT JOIN Stock ON Article.id = Stock.article_id 
          INNER JOIN User ON Article.auteur_id = User.id
          LEFT JOIN Category ON Article.category_id = Category.id
          LEFT JOIN Review ON Article.id = Review.article_id
          WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (Article.nom LIKE '%$search%' OR Article.description LIKE '%$search%')";
}
if ($cat_filter > 0) {
    $query .= " AND Article.category_id = $cat_filter";
}

// Groupement obligatoire à cause de l'utilisation de AVG()
$query .= " GROUP BY Article.id ORDER BY $order_by";
$res = $mysqli->query($query);
?>

<h1>Boutique</h1>

<div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 20px; align-items: center;">
    <form method="GET" style="flex-grow: 1; display: flex; gap: 5px;">
        <input type="text" name="search" placeholder="Rechercher un article..." value="<?php echo htmlspecialchars($search); ?>" style="flex-grow: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        <button type="submit" style="padding: 8px 15px; cursor:pointer;">🔍 Rechercher</button>
    </form>

    <form method="GET" style="display: flex; gap: 10px;">
        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
        <select name="category" onchange="this.form.submit()" style="padding: 8px; border-radius: 4px;">
            <option value="0">Toutes les catégories</option>
            <?php
            $all_cats = $mysqli->query("SELECT * FROM Category ORDER BY nom ASC");
            while($ac = $all_cats->fetch_assoc()):
                $s = ($ac['id'] == $cat_filter) ? 'selected' : '';
                echo "<option value='{$ac['id']}' $s>{$ac['nom']}</option>";
            endwhile;
            ?>
        </select>

        <select name="sort" onchange="this.form.submit()" style="padding: 8px; border-radius: 4px;">
            <option value="date_desc" <?php if($sort == 'date_desc') echo 'selected'; ?>>Plus récents</option>
            <option value="price_asc" <?php if($sort == 'price_asc') echo 'selected'; ?>>Prix croissant</option>
            <option value="price_desc" <?php if($sort == 'price_desc') echo 'selected'; ?>>Prix décroissant</option>
        </select>
    </form>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px;">
    <?php if ($res && $res->num_rows > 0): ?>
        <?php while($art = $res->fetch_assoc()): ?>
            <div style="border: 1px solid #eee; background: white; padding: 0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s; display: flex; flex-direction: column;">
                
                <div style="height: 180px; background: #f4f4f4; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                    <?php if(!empty($art['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($art['image_url']); ?>" alt="Image" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <span style="color: #ccc;">Pas d'image</span>
                    <?php endif; ?>
                </div>

                <div style="padding: 15px; flex-grow: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <h3 style="margin: 0; font-size: 1.1em;"><?php echo htmlspecialchars($art['nom']); ?></h3>
                        <span style="background:#e3f2fd; color:#1976d2; padding:2px 8px; border-radius:12px; font-size:0.7em; font-weight: bold;">
                            <?php echo htmlspecialchars($art['cat_nom'] ?? 'Général'); ?>
                        </span>
                    </div>

                    <div style="margin: 8px 0; color: #ffc107; font-size: 0.9em;">
                        <?php 
                        if ($art['nb_avis'] > 0) {
                            $moyenne = round($art['moyenne_note'], 1);
                            echo str_repeat('⭐', floor($moyenne)) . " <span style='color:#666;'>($moyenne)</span>";
                        } else {
                            echo "<span style='color:#bbb; font-style:italic;'>Aucun avis</span>";
                        }
                        ?>
                    </div>

                    <p style="font-size: 0.8em; color: #888;">Par : <?php echo htmlspecialchars($art['auteur']); ?></p>
                    <p style="font-size: 1.2em; color: #28a745; font-weight: bold; margin: 10px 0;">
                        <?php echo formatPrix($art['prix']); ?>
                    </p>
                    
                    <?php if ($art['quantite'] > 0): ?>
                        <p style="color: #28a745; font-size: 0.8em; margin-bottom: 15px;">✅ En stock (<?php echo $art['quantite']; ?>)</p>
                    <?php else: ?>
                        <p style="color: #dc3545; font-size: 0.8em; font-weight: bold; margin-bottom: 15px;">❌ Rupture de stock</p>
                    <?php endif; ?>

                    <a href="detail.php?id=<?php echo $art['id']; ?>" style="display: block; text-align: center; background: #333; color: white; padding: 10px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                        Voir le produit
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="grid-column: 1/-1; text-align: center; padding: 50px; color: #666;">Aucun article ne correspond à votre recherche.</p>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>