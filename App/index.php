<?php
require_once 'includes/header.php';

$search = isset($_GET['search']) ? $mysqli->real_escape_string($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';
$cat_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;

$order_by = "Article.date_publication DESC";
if ($sort == 'price_asc') $order_by = "Article.prix ASC";
if ($sort == 'price_desc') $order_by = "Article.prix DESC";
if ($sort == 'date_asc') $order_by = "Article.date_publication ASC";

$query = "SELECT Article.*, Stock.quantite, User.username as auteur, Category.nom as cat_nom,
                 AVG(Review.note) as moyenne_note, COUNT(Review.id) as nb_avis
          FROM Article 
          LEFT JOIN Stock ON Article.id = Stock.article_id 
          INNER JOIN User ON Article.auteur_id = User.id
          LEFT JOIN Category ON Article.category_id = Category.id
          LEFT JOIN Review ON Article.id = Review.article_id
          WHERE 1=1";

if (!empty($search)) { $query .= " AND (Article.nom LIKE '%$search%' OR Article.description LIKE '%$search%')"; }
if ($cat_filter > 0) { $query .= " AND Article.category_id = $cat_filter"; }

$query .= " GROUP BY Article.id ORDER BY $order_by";
$res = $mysqli->query($query);
?>

<h1 class="shop-title">Découvrez les pépites de "Vendons-les"</h1>

<div class="filter-bar">
    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Que recherchez-vous ?" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn-login">Rechercher</button>
    </form>

    <form method="GET" style="display: flex; gap: 10px;">
        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
        
        <select name="category" onchange="this.form.submit()">
            <option value="0">Toutes catégories</option>
            <?php
            $all_cats = $mysqli->query("SELECT * FROM Category ORDER BY nom ASC");
            while($ac = $all_cats->fetch_assoc()):
                $s = ($ac['id'] == $cat_filter) ? 'selected' : '';
                echo "<option value='{$ac['id']}' $s>{$ac['nom']}</option>";
            endwhile;
            ?>
        </select>

        <select name="sort" onchange="this.form.submit()">
            <option value="date_desc" <?php if($sort == 'date_desc') echo 'selected'; ?>>Plus récents</option>
            <option value="price_asc" <?php if($sort == 'price_asc') echo 'selected'; ?>>Prix croissant</option>
            <option value="price_desc" <?php if($sort == 'price_desc') echo 'selected'; ?>>Prix décroissant</option>
        </select>
    </form>
</div>

<div class="products-grid">
    <?php if ($res && $res->num_rows > 0): ?>
        <?php while($art = $res->fetch_assoc()): ?>
            <div class="product-card">
                
                <div class="product-image">
                    <span class="category-badge"><?php echo htmlspecialchars($art['cat_nom'] ?? 'Général'); ?></span>
                    <?php if(!empty($art['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($art['image_url']); ?>" alt="<?php echo htmlspecialchars($art['nom']); ?>">
                    <?php else: ?>
                        <span style="color: #ccc; font-size: 3rem;">📦</span>
                    <?php endif; ?>
                </div>

                <div class="product-info">
                    <h3><?php echo htmlspecialchars($art['nom']); ?></h3>
                    
                    <div class="product-rating">
                        <?php 
                        if ($art['nb_avis'] > 0) {
                            $moyenne = round($art['moyenne_note'], 1);
                            echo str_repeat('⭐', floor($moyenne)) . " <small>($moyenne)</small>";
                        } else {
                            echo "<small style='color:#bbb;'>Aucun avis</small>";
                        }
                        ?>
                    </div>

                    <p style="font-size: 0.85rem; color: #777; margin: 0;">Vendu par <strong><?php echo htmlspecialchars($art['auteur']); ?></strong></p>
                    
                    <div class="product-price">
                        <?php echo formatPrix($art['prix']); ?>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <?php if ($art['quantite'] > 0): ?>
                            <small style="color: #28a745;">● En stock</small>
                        <?php else: ?>
                            <small style="color: #dc3545;">● Rupture</small>
                        <?php endif; ?>
                    </div>

                    <a href="detail.php?id=<?php echo $art['id']; ?>" class="btn-view">
                        Voir l'article
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 100px 0;">
            <p style="font-size: 1.5rem; color: #999;">Oups ! Aucun trésor ne correspond à cette recherche.</p>
            <a href="index.php" style="color: var(--primary-color);">Voir tous les articles</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>