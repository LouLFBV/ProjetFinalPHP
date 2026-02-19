<?php
require_once 'includes/header.php';

$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. RÉCUPÉRATION DES DONNÉES
$query = "SELECT Article.*, Stock.quantite, User.username, User.id as auteur_id, Category.nom as cat_nom 
          FROM Article 
          LEFT JOIN Stock ON Article.id = Stock.article_id 
          INNER JOIN User ON Article.auteur_id = User.id
          LEFT JOIN Category ON Article.category_id = Category.id
          WHERE Article.id = $article_id";
$res = $mysqli->query($query);
$art = $res->fetch_assoc();

if (!$art) { header("Location: index.php"); exit; }

// 2. LOGIQUE DES FAVORIS
$is_fav = false;
if (isset($_SESSION['user_id'])) {
    $u_id = $_SESSION['user_id'];
    $check_fav = $mysqli->query("SELECT id FROM Favorite WHERE user_id = $u_id AND article_id = $article_id");
    if ($check_fav && $check_fav->num_rows > 0) $is_fav = true;
}

if (isset($_POST['toggle_favorite']) && isset($_SESSION['user_id'])) {
    if ($is_fav) {
        $mysqli->query("DELETE FROM Favorite WHERE user_id = $u_id AND article_id = $article_id");
    } else {
        $mysqli->query("INSERT INTO Favorite (user_id, article_id) VALUES ($u_id, $article_id)");
    }
    header("Location: detail.php?id=$article_id");
    exit;
}

// 3. LOGIQUE DES AVIS
$peut_noter = false;
$u_id = $_SESSION['user_id'] ?? 0;
if ($u_id > 0) {
    $check_achat = $mysqli->query("SELECT ii.id FROM invoice_item ii INNER JOIN invoice i ON ii.invoice_id = i.id WHERE i.user_id = $u_id AND ii.article_id = $article_id");
    $check_avis = $mysqli->query("SELECT id FROM review WHERE user_id = $u_id AND article_id = $article_id");
    if ($check_achat->num_rows > 0 && $check_avis->num_rows == 0) $peut_noter = true;
}

if (isset($_POST['submit_review']) && $peut_noter) {
    $note = intval($_POST['note']);
    $comm = $mysqli->real_escape_string($_POST['commentaire']);
    $mysqli->query("INSERT INTO Review (article_id, user_id, note, commentaire) VALUES ($article_id, $u_id, $note, '$comm')");
    header("Location: detail.php?id=$article_id&msg=Avis publié !");
    exit;
}

$stats = $mysqli->query("SELECT AVG(note) as moyenne, COUNT(*) as nb_avis FROM Review WHERE article_id = $article_id")->fetch_assoc();
?>

<div style="margin-bottom: 20px;">
    <a href="index.php" style="text-decoration: none; color: #888;">← Retour aux articles</a>
</div>

<div class="detail-container">
    <div class="detail-image-side">
        <?php if(!empty($art['image_url'])): ?>
            <img src="<?php echo htmlspecialchars($art['image_url']); ?>" alt="Produit">
        <?php else: ?>
            <span style="font-size: 5rem;">📷</span>
        <?php endif; ?>
    </div>

    <div class="detail-info-side">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="badge-cat"><?php echo htmlspecialchars($art['cat_nom'] ?? 'Général'); ?></span>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST">
                    <button type="submit" name="toggle_favorite" class="btn-fav">
                        <?php echo $is_fav ? '❤️' : '🤍'; ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <h1 style="font-size: 2.5rem; margin: 15px 0;"><?php echo htmlspecialchars($art['nom']); ?></h1>
        
        <p style="color: #666;">
            Vendeur : <strong><?php echo htmlspecialchars($art['username']); ?></strong> 
            <small>(le <?php echo date('d/m/Y', strtotime($art['date_publication'])); ?>)</small>
        </p>

        <div class="detail-price"><?php echo formatPrix($art['prix']); ?></div>

        <div class="description-box">
            <h4 style="margin-top:0">Description</h4>
            <?php echo nl2br(htmlspecialchars($art['description'])); ?>
        </div>

        <div class="buy-box">
            <?php if ($u_id == $art['auteur_id']): ?>
                <div style="color: #856404; background: #fff3cd; padding: 15px; border-radius: 8px; text-align: center;">
                    C'est votre article. <br> <a href="edit.php?id=<?php echo $art['id']; ?>">Modifier l'annonce</a>
                </div>
            <?php elseif($art['quantite'] <= 0): ?>
                <button disabled class="btn-submit" style="background: #ccc; width: 100%;">Rupture de stock</button>
            <?php else: ?>
                <form action="cart.php" method="POST">
                    <input type="hidden" name="article_id" value="<?php echo $art['id']; ?>">
                    <label>Quantité :</label><br>
                    <input type="number" name="new_qty" value="1" min="1" max="<?php echo $art['quantite']; ?>" class="qty-input"><br>
                    <button type="submit" class="btn-submit" style="width: 100%;">🛒 Ajouter au panier</button>
                </form>
                <small style="color: #28a745; display: block; margin-top: 10px;">✔ <?php echo $art['quantite']; ?> en stock</small>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="reviews-section">
    <h2 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 30px;">
        Avis clients 
        <span style="font-size: 1rem; color: #ffc107;">
            <?php echo ($stats['nb_avis'] > 0) ? round($stats['moyenne'], 1)."/5 ⭐" : "Pas encore d'avis"; ?>
        </span>
    </h2>

    <?php if ($peut_noter): ?>
        <div class="review-card" style="background: #f8f9fa;">
            <h4>Laisser un avis</h4>
            <form method="POST" class="auth-form">
                <select name="note" class="qty-input" style="width: 100%;">
                    <option value="5">5/5 - Excellent</option>
                    <option value="4">4/5 - Très bien</option>
                    <option value="3">3/5 - Moyen</option>
                    <option value="2">2/5 - Décevant</option>
                    <option value="1">1/5 - Mauvais</option>
                </select>
                <textarea name="commentaire" placeholder="Votre avis..." class="qty-input" style="width: 100%; height: 100px;"></textarea>
                <button type="submit" name="submit_review" class="btn-submit">Publier</button>
            </form>
        </div>
    <?php endif; ?>

    <?php
    $reviews = $mysqli->query("SELECT Review.*, User.username FROM Review JOIN User ON Review.user_id = User.id WHERE article_id = $article_id ORDER BY date_publication DESC");
    while($rev = $reviews->fetch_assoc()):
    ?>
        <div class="review-card">
            <div style="display: flex; justify-content: space-between;">
                <strong><?php echo htmlspecialchars($rev['username']); ?></strong>
                <span style="color: #ffc107;"><?php echo str_repeat('⭐', $rev['note']); ?></span>
            </div>
            <p style="font-style: italic; color: #444; margin: 15px 0;">"<?php echo nl2br(htmlspecialchars($rev['commentaire'])); ?>"</p>
            <small style="color: #999;">Publié le <?php echo date('d/m/Y', strtotime($rev['date_publication'])); ?></small>
        </div>
    <?php endwhile; ?>
</div>

<?php require_once 'includes/footer.php'; ?>