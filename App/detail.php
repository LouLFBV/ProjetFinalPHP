<?php
require_once 'includes/header.php';

$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Récupération de l'article, de son stock, de son auteur et de sa catégorie
$query = "SELECT Article.*, Stock.quantite, User.username, Category.nom as cat_nom 
          FROM Article 
          LEFT JOIN Stock ON Article.id = Stock.article_id 
          INNER JOIN User ON Article.auteur_id = User.id
          LEFT JOIN Category ON Article.category_id = Category.id
          WHERE Article.id = $article_id";
$res = $mysqli->query($query);
$art = $res->fetch_assoc();

if (!$art) { header("Location: index.php"); exit; }

// 2. Vérification : L'utilisateur a-t-il acheté cet article ?
$peut_noter = false;
if (isset($_SESSION['user_id'])) {
    $u_id = $_SESSION['user_id'];
    
    // Requête corrigée avec tes noms de tables et colonnes
    $check_achat = $mysqli->query("SELECT ii.id 
                                   FROM invoice_item ii
                                   INNER JOIN invoice i ON ii.invoice_id = i.id 
                                   WHERE i.user_id = $u_id 
                                   AND ii.article_id = $article_id");
    
    // Vérification si un avis existe déjà
    $check_avis = $mysqli->query("SELECT id FROM review WHERE user_id = $u_id AND article_id = $article_id");
    
    if ($check_achat && $check_achat->num_rows > 0 && $check_avis->num_rows == 0) {
        $peut_noter = true;
    }
}

// 3. Logique d'insertion de l'avis
if (isset($_POST['submit_review']) && $peut_noter) {
    $note = intval($_POST['note']);
    $comm = $mysqli->real_escape_string($_POST['commentaire']);
    $u_id = $_SESSION['user_id'];
    
    $mysqli->query("INSERT INTO Review (article_id, user_id, note, commentaire) VALUES ($article_id, $u_id, $note, '$comm')");
    header("Location: detail.php?id=$article_id&msg=Merci pour votre avis !");
    exit;
}

// 4. Calcul de la moyenne
$avg_res = $mysqli->query("SELECT AVG(note) as moyenne, COUNT(*) as nb_avis FROM Review WHERE article_id = $article_id");
$stats = $avg_res->fetch_assoc();
?>

<div style="max-width: 800px; margin: auto;">
    <p><a href="index.php">← Retour boutique</a></p>

    <div style="display: flex; gap: 30px; background: #fff; padding: 20px; border-radius: 10px; border: 1px solid #ddd;">
        <div style="flex: 1; background: #eee; height: 300px; display: flex; align-items: center; justify-content: center;">
            <img src="<?php echo htmlspecialchars($art['image_url']); ?>" alt="Image Article" style="max-width:100%; max-height:100%;">
        </div>
        
        <div style="flex: 1;">
            <h1><?php echo htmlspecialchars($art['nom']); ?></h1>
            <span style="background: #007bff; color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.8em;">
                <?php echo htmlspecialchars($art['cat_nom'] ?? 'Sans catégorie'); ?>
            </span>
            <p style="color: #666;">Vendu par <strong><?php echo htmlspecialchars($art['username']); ?></strong></p>
            <h2 style="color: #28a745;"><?php echo formatPrix($art['prix']); ?></h2>
            
            <p><?php echo nl2br(htmlspecialchars($art['description'])); ?></p>
            
            <p><strong>Stock :</strong> <?php echo ($art['quantite'] > 0) ? $art['quantite'] : '<span style="color:red;">Rupture</span>'; ?></p>
            
            <form action="cart.php" method="POST">
                <input type="hidden" name="article_id" value="<?php echo $art['id']; ?>">
                
                <?php if($art['quantite'] > 0): ?>
                    <label>Quantité :</label>
                    <input type="number" name="new_qty" value="1" min="1" max="<?php echo $art['quantite']; ?>" style="width:60px; margin-bottom:10px;"><br>
                <?php endif; ?>

                <button type="submit" <?php if($art['quantite'] <= 0) echo 'disabled'; ?> 
                    style="background: #ffc107; border: none; padding: 10px 20px; font-weight: bold; cursor: pointer; border-radius: 5px; width: 100%;">
                    <?php echo ($art['quantite'] > 0) ? 'Ajouter au panier' : 'Indisponible'; ?>
                </button>
            </form>
        </div>
    </div>

    <hr style="margin: 40px 0;">

    <h3>Avis des clients 
        (<?php echo round($stats['moyenne'], 1); ?>/5 ⭐ sur <?php echo $stats['nb_avis']; ?> avis)
    </h3>

    <?php if ($peut_noter): ?>
        <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <h4>Laissez votre avis</h4>
            <form method="POST">
                <label>Note :</label>
                <select name="note" required>
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Très bien</option>
                    <option value="3">3 - Moyen</option>
                    <option value="2">2 - Décevant</option>
                    <option value="1">1 - À fuir</option>
                </select><br><br>
                <textarea name="commentaire" placeholder="Votre commentaire..." required style="width: 100%; height: 80px;"></textarea><br><br>
                <button type="submit" name="submit_review" style="background: #333; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer;">
                    Publier l'avis
                </button>
            </form>
        </div>
    <?php elseif (isset($_SESSION['user_id']) && $check_achat->num_rows == 0): ?>
        <p style="font-size: 0.9em; color: #666; font-style: italic;">Vous devez avoir acheté ce produit pour laisser un avis.</p>
    <?php endif; ?>

    <?php
    $reviews = $mysqli->query("SELECT Review.*, User.username FROM Review JOIN User ON Review.user_id = User.id WHERE article_id = $article_id ORDER BY date_publication DESC");
    while($rev = $reviews->fetch_assoc()):
    ?>
        <div style="border-bottom: 1px solid #eee; padding: 15px 0;">
            <strong><?php echo htmlspecialchars($rev['username']); ?></strong> 
            <span style="color: #ffc107;"><?php echo str_repeat('⭐', $rev['note']); ?></span>
            <small style="color: #999; margin-left: 10px;"><?php echo date('d/m/Y', strtotime($rev['date_publication'])); ?></small>
            <p style="margin: 5px 0;"><?php echo nl2br(htmlspecialchars($rev['commentaire'])); ?></p>
        </div>
    <?php endwhile; ?>
</div>

<?php require_once 'includes/footer.php'; ?>