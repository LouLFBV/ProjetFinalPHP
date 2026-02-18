<?php
require_once 'includes/header.php';

$search = isset($_GET['q']) ? $_GET['q'] : '';

// Requête de base : on joint le stock et on filtre uniquement les produits DISPONIBLES (> 0)
$query = "SELECT Article.* FROM Article 
          JOIN Stock ON Article.id = Stock.article_id 
          WHERE Stock.quantite > 0";

if (!empty($search)) {
    // Si recherche, on ajoute les conditions de nom ou description
    $query .= " AND (Article.nom LIKE ? OR Article.description LIKE ?)";
    $query .= " ORDER BY Article.date_publication DESC";
    $stmt = $mysqli->prepare($query);
    $search_param = "%$search%";
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Sinon affichage classique par date
    $result = $mysqli->query($query . " ORDER BY Article.date_publication DESC");
}
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

<h1>Articles en vente</h1>

<form method="GET" action="index.php" style="margin-bottom: 30px; display: flex; gap: 10px;">
    <input type="text" name="q" placeholder="Rechercher un produit..." value="<?php echo htmlspecialchars($search); ?>" style="flex-grow: 1; padding: 10px;">
    <button type="submit" style="padding: 10px 20px; cursor: pointer;">Rechercher</button>
</form>

<div class="articles-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="article-card" style="border: 1px solid #ddd; padding: 15px; border-radius: 8px; text-align: center;">
                <h3 style="margin-top: 0;"><?php echo htmlspecialchars($row['nom']); ?></h3>
                <p style="font-size: 1.2em; color: #2c3e50; font-weight: bold;"><?php echo formatPrix($row['prix']); ?></p>
                <a href="detail.php?id=<?php echo $row['id']; ?>" style="display: inline-block; background: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;">
                    Voir le détail
                </a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Désolé, aucun article ne correspond à votre recherche ou n'est disponible pour le moment.</p>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>