<?php
require_once 'includes/header.php';

$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. RÉCUPÉRATION DES DONNÉES (Article, Stock, Auteur, Catégorie)
$query = "SELECT Article.*, Stock.quantite, User.username, User.id as auteur_id, Category.nom as cat_nom 
          FROM Article 
          LEFT JOIN Stock ON Article.id = Stock.article_id 
          INNER JOIN User ON Article.auteur_id = User.id
          LEFT JOIN Category ON Article.category_id = Category.id
          WHERE Article.id = $article_id";
$res = $mysqli->query($query);
$art = $res->fetch_assoc();

if (!$art) { 
    header("Location: index.php"); 
    exit; 
}

// 2. LOGIQUE DES FAVORIS
$is_fav = false;
if (isset($_SESSION['user_id'])) {
    $u_id = $_SESSION['user_id'];
    $check_fav = $mysqli->query("SELECT id FROM Favorite WHERE user_id = $u_id AND article_id = $article_id");
    if ($check_fav && $check_fav->num_rows > 0) {
        $is_fav = true;
    }
}

// Traitement favoris
if (isset($_POST['toggle_favorite']) && isset($_SESSION['user_id'])) {
    $u_id = $_SESSION['user_id'];
    if ($is_fav) {
        $mysqli->query("DELETE FROM Favorite WHERE user_id = $u_id AND article_id = $article_id");
    } else {
        $mysqli->query("INSERT INTO Favorite (user_id, article_id) VALUES ($u_id, $article_id)");
    }
    header("Location: detail.php?id=$article_id");
    exit;
}

// 3. LOGIQUE DES AVIS (Peut-on noter ?)
$peut_noter = false;
$u_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
if ($u_id > 0) {
    // A-t-il acheté l'article ?
    $check_achat = $mysqli->query("SELECT ii.id FROM invoice_item ii
                                   INNER JOIN invoice i ON ii.invoice_id = i.id 
                                   WHERE i.user_id = $u_id AND ii.article_id = $article_id");
    
    // A-t-il déjà noté ?
    $check_avis = $mysqli->query("SELECT id FROM review WHERE user_id = $u_id AND article_id = $article_id");
    
    if ($check_achat && $check_achat->num_rows > 0 && $check_avis->num_rows == 0) {
        $peut_noter = true;
    }
}

// Soumission avis
if (isset($_POST['submit_review']) && $peut_noter) {
    $note = intval($_POST['note']);
    $comm = $mysqli->real_escape_string($_POST['commentaire']);
    $mysqli->query("INSERT INTO Review (article_id, user_id, note, commentaire) VALUES ($article_id, $u_id, $note, '$comm')");
    header("Location: detail.php?id=$article_id&msg=Merci pour votre avis !");
    exit;
}

// Statistiques avis
$avg_res = $mysqli->query("SELECT AVG(note) as moyenne, COUNT(*) as nb_avis FROM Review WHERE article_id = $article_id");
$stats = $avg_res->fetch_assoc();
?>

<div style="max-width: 1000px; margin: auto; padding: 20px;">
    
    <p style="margin-bottom: 20px;">
        <a href="index.php" style="text-decoration: none; color: #666;">← Retour à la boutique</a>
    </p>

    <div style="display: flex; gap: 40px; background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); flex-wrap: wrap;">
        
        <div style="flex: 1; min-width: 350px; background: #f8f9fa; height: 450px; display: flex; align-items: center; justify-content: center; border-radius: 10px; overflow: hidden; border: 1px solid #eee;">
            <?php if(!empty($art['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($art['image_url']); ?>" alt="Produit" style="max-width:100%; max-height:100%; object-fit: contain;">
            <?php else: ?>
                <span style="color: #bbb; font-size: 80px;">📷</span>
            <?php endif; ?>
        </div>
        
        <div style="flex: 1; min-width: 350px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="background: #e3f2fd; color: #007bff; padding: 6px 15px; border-radius: 20px; font-size: 0.8em; font-weight: bold; text-transform: uppercase;">
                    <?php echo htmlspecialchars($art['cat_nom'] ?? 'Général'); ?>
                </span>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST">
                        <button type="submit" name="toggle_favorite" style="background: none; border: none; font-size: 2.2em; cursor: pointer; color: red; line-height: 1;" title="Ajouter aux favoris">
                            <?php echo $is_fav ? '❤️' : '🤍'; ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <h1 style="margin: 15px 0; font-size: 2.5em; color: #2c3e50;"><?php echo htmlspecialchars($art['nom']); ?></h1>
            
            <div style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                <p style="margin: 0; font-size: 1.1em; color: #555;">
                    Vendeur : 
                    <a href="account.php?id=<?php echo $art['auteur_id']; ?>" style="color: #007bff; text-decoration: none; font-weight: bold;">
                        <?php echo htmlspecialchars($art['username']); ?>
                    </a>
                </p>
                <p style="margin: 5px 0 0 0; font-size: 0.9em; color: #888;">
                    Publié le <?php echo date('d/m/Y à H:i', strtotime($art['date_publication'])); ?>
                </p>
            </div>

            <div style="margin: 20px 0;">
                <span style="font-size: 2.5em; color: #28a745; font-weight: bold;"><?php echo formatPrix($art['prix']); ?></span>
            </div>
            
            <div style="background: #fdfdfe; border-left: 4px solid #007bff; padding: 15px; margin-bottom: 25px;">
                <h4 style="margin-top:0;">Description :</h4>
                <p style="margin: 0; line-height: 1.6; color: #444;"><?php echo nl2br(htmlspecialchars($art['description'])); ?></p>
            </div>
            
            <div style="margin-bottom: 25px;">
                <strong>État du stock :</strong> 
                <?php if($art['quantite'] > 0): ?>
                    <span style="color: #28a745; font-weight: bold;">✅ <?php echo $art['quantite']; ?> exemplaires disponibles</span>
                <?php else: ?>
                    <span style="color: #dc3545; font-weight: bold;">❌ Actuellement indisponible</span>
                <?php endif; ?>
            </div>
            
            <div style="background: #f8f9fa; padding: 25px; border-radius: 12px; border: 1px solid #e9ecef;">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $art['auteur_id']): ?>
                    <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; border: 1px solid #ffeeba; text-align: center;">
                        <p style="margin: 0 0 10px 0;"><strong>Vous êtes l'auteur de cet article.</strong></p>
                        <a href="edit.php?id=<?php echo $art['id']; ?>" style="display: inline-block; background: #856404; color: #fff; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-size: 0.9em;">Modifier mon article</a>
                    </div>

                <?php elseif($art['quantite'] <= 0): ?>
                    <button disabled style="background: #dee2e6; color: #6c757d; border: none; padding: 18px; font-size: 1.1em; font-weight: bold; border-radius: 8px; width: 100%; cursor: not-allowed;">
                        Produit épuisé
                    </button>

                <?php else: ?>
                    <form action="cart.php" method="POST">
                        <input type="hidden" name="article_id" value="<?php echo $art['id']; ?>">
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: bold;">Quantité souhaitée :</label>
                            <input type="number" name="new_qty" value="1" min="1" max="<?php echo $art['quantite']; ?>" style="width: 100px; padding: 12px; border: 1px solid #ced4da; border-radius: 6px; font-size: 1em;">
                        </div>
                        <button type="submit" style="background: #ffc107; color: #212529; border: none; padding: 18px; font-size: 1.2em; font-weight: bold; cursor: pointer; border-radius: 8px; width: 100%; transition: background 0.3s;">
                            🛒 Ajouter au panier
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div style="margin-top: 60px;">
        <h3 style="border-bottom: 2px solid #eee; padding-bottom: 15px; display: flex; align-items: center; justify-content: space-between;">
            Avis des acheteurs
            <span style="font-size: 0.9em;">
                <span style="color: #ffc107; font-size: 1.2em;">
                    <?php echo ($stats['nb_avis'] > 0) ? round($stats['moyenne'], 1)."/5 ⭐" : "Aucune note"; ?>
                </span>
                <small style="font-weight: normal; color: #888; margin-left: 10px;">(<?php echo $stats['nb_avis']; ?> avis)</small>
            </span>
        </h3>

        <?php if ($peut_noter): ?>
            <div style="background: #fff; padding: 30px; border-radius: 12px; border: 1px solid #dee2e6; margin: 30px 0;">
                <h4 style="margin-top: 0; color: #2c3e50;">Laissez votre avis</h4>
                <form method="POST">
                    <div style="margin-bottom: 20px;">
                        <label style="display:block; margin-bottom:8px; font-weight:bold;">Note globale :</label>
                        <select name="note" required style="width: 100%; padding: 12px; border-radius: 6px; border: 1px solid #ced4da; font-size: 1em;">
                            <option value="5">⭐⭐⭐⭐⭐ (Excellent)</option>
                            <option value="4">⭐⭐⭐⭐ (Très bien)</option>
                            <option value="3">⭐⭐⭐ (Moyen)</option>
                            <option value="2">⭐⭐ (Décevant)</option>
                            <option value="1">⭐ (À fuir)</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display:block; margin-bottom:8px; font-weight:bold;">Votre commentaire :</label>
                        <textarea name="commentaire" placeholder="Qu'avez-vous pensé de cet article ?" required style="width: 100%; height: 120px; padding: 15px; border: 1px solid #ced4da; border-radius: 6px; font-family: inherit; font-size: 1em;"></textarea>
                    </div>
                    <button type="submit" name="submit_review" style="background: #2c3e50; color: white; padding: 12px 30px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 1em;">
                        Publier mon avis
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div style="margin-top: 40px; display: grid; gap: 20px;">
            <?php
            $reviews = $mysqli->query("SELECT Review.*, User.username, User.image_url as user_img 
                                       FROM Review 
                                       JOIN User ON Review.user_id = User.id 
                                       WHERE article_id = $article_id 
                                       ORDER BY date_publication DESC");
            if ($reviews->num_rows > 0):
                while($rev = $reviews->fetch_assoc()):
            ?>
                <div style="background: white; padding: 25px; border-radius: 10px; border: 1px solid #f0f0f0; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width:35px; height:35px; border-radius:50%; background:#eee; overflow:hidden;">
                                <?php if(!empty($rev['user_img'])): ?>
                                    <img src="<?php echo $rev['user_img']; ?>" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: ?>
                                    <div style="text-align:center; line-height:35px; color:#aaa;">👤</div>
                                <?php endif; ?>
                            </div>
                            <a href="account.php?id=<?php echo $rev['user_id']; ?>" style="text-decoration: none; color: #333; font-weight: bold;">
                                <?php echo htmlspecialchars($rev['username']); ?>
                            </a>
                        </div>
                        <span style="color: #ffc107; letter-spacing: 2px;"><?php echo str_repeat('⭐', $rev['note']); ?></span>
                    </div>
                    <p style="margin: 0 0 15px 0; color: #444; line-height: 1.5; font-style: italic;">
                        "<?php echo nl2br(htmlspecialchars($rev['commentaire'])); ?>"
                    </p>
                    <small style="color: #aaa;">Posté le <?php echo date('d/m/Y', strtotime($rev['date_publication'])); ?></small>
                </div>
            <?php endwhile; else: ?>
                <p style="text-align: center; color: #999; padding: 40px; background: #f9f9f9; border-radius: 10px;">
                    Aucun avis pour le moment. Soyez le premier à en laisser un !
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>