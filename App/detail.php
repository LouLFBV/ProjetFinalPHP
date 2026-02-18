<?php
require_once 'includes/header.php';
$id = intval($_GET['id']);

// Mise à jour de la requête pour inclure le nom de l'auteur (User.username)
$res = $mysqli->query("SELECT Article.*, Stock.quantite as stock, User.username as auteur_nom 
                       FROM Article 
                       LEFT JOIN Stock ON Article.id = Stock.article_id 
                       INNER JOIN User ON Article.auteur_id = User.id
                       WHERE Article.id = $id");
$art = $res->fetch_assoc();

if (!$art) die("Article introuvable.");

// Logique des favoris
if (isset($_POST['toggle_favorite']) && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $check_fav = $mysqli->query("SELECT id FROM Favorite WHERE user_id = $uid AND article_id = $id");
    
    if ($check_fav->num_rows > 0) {
        $mysqli->query("DELETE FROM Favorite WHERE user_id = $uid AND article_id = $id");
    } else {
        $mysqli->query("INSERT INTO Favorite (user_id, article_id) VALUES ($uid, $id)");
    }
    // Redirection pour éviter de renvoyer le formulaire en actualisant
    header("Location: detail.php?id=$id");
    exit;
}

// Vérifier si l'article est en favori pour l'affichage
$is_fav = false;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $check_fav = $mysqli->query("SELECT id FROM Favorite WHERE user_id = $uid AND article_id = $id");
    $is_fav = ($check_fav->num_rows > 0);
}
?>

<h1><?php echo htmlspecialchars($art['nom']); ?></h1>
<?php if (isset($_SESSION['user_id'])): ?>
    <form method="POST" style="display:inline;">
        <button type="submit" name="toggle_favorite" style="background:none; border:none; cursor:pointer; font-size:1.5em;">
            <?php echo $is_fav ? "❤️ (Retirer des favoris)" : "🤍 (Ajouter aux favoris)"; ?>
        </button>
    </form>
<?php endif; ?>
<p>Vendu par : 
    <a href="account.php?id=<?php echo $art['auteur_id']; ?>">
        <strong><?php echo htmlspecialchars($art['auteur_nom']); ?></strong>
    </a>
</p>

<p><?php echo nl2br(htmlspecialchars($art['description'])); ?></p>
<p>Prix : <?php echo formatPrix($art['prix']); ?></p>
<p>Stock disponible : <?php echo $art['stock']; ?></p>

<?php if (isset($_SESSION['user_id'])): ?>
    <?php if ($art['stock'] > 0): ?>
        <form action="cart.php" method="POST">
            <input type="hidden" name="article_id" value="<?php echo $art['id']; ?>">
            <input type="number" name="quantite" value="1" min="1" max="<?php echo $art['stock']; ?>">
            <button type="submit">Ajouter au panier</button>
        </form>
    <?php else: ?>
        <p style="color:red;">Rupture de stock.</p>
    <?php endif; ?>
<?php else: ?>
    <a href="login.php">Connectez-vous pour acheter</a>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>