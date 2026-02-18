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
?>

<h1><?php echo htmlspecialchars($art['nom']); ?></h1>

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