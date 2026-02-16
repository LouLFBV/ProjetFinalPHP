<?php
require_once 'includes/header.php'; // Gère déjà session_start, db.php et functions.php

// 1. Récupération de l'ID via la requête GET [cite: 30]
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($article_id <= 0) {
    header("Location: index.php");
    exit;
}

// 2. Requête pour les détails de l'article et son auteur [cite: 29, 83]
$query = "SELECT Article.*, User.username FROM Article 
          LEFT JOIN User ON Article.auteur_id = User.id 
          WHERE Article.id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();

if (!$article) {
    echo "<p>Article introuvable.</p>";
    require_once 'includes/footer.php';
    exit;
}
?>

<a href="index.php">← Retour aux articles</a>

<div class="product-detail">
    <h1><?php echo htmlspecialchars($article['nom']); ?></h1>
    
    <?php if (!empty($article['image_url'])): ?>
        <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="Image" style="max-width:400px;">
    <?php endif; ?>

    <p><strong>Prix :</strong> <?php echo formatPrix($article['prix']); ?></p>
    <p><strong>Vendu par :</strong> <?php echo htmlspecialchars($article['username'] ?? 'Anonyme'); ?></p>
    <p><strong>Description :</strong></p>
    <p><?php echo nl2br(htmlspecialchars($article['description'])); ?></p>
    
    <hr>

    <?php if (isset($_SESSION['user_id'])): ?>
        <form action="cart.php" method="POST">
            <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
            <label for="quantite">Quantité :</label>
            <input type="number" name="quantite" value="1" min="1" required>
            <button type="submit">Ajouter au panier</button>
        </form>
    <?php else: ?>
        <p><em><a href="login.php">Connectez-vous</a> pour acheter cet article.</em></p>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>