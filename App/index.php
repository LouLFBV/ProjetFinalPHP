<?php
// On inclut le header qui s'occupe de la session et de la DB
require_once 'includes/header.php'; 

$result = $mysqli->query("SELECT * FROM Article ORDER BY date_publication DESC");
?>

<h1>Bienvenue sur notre boutique</h1>

<div class="articles-list">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="article">
            <h3><?php echo htmlspecialchars($row['nom']); ?></h3>
            <p>Prix : <?php echo formatPrix($row['prix']); // Utilisation de notre fonction ! ?></p>
            <a href="detail.php?id=<?php echo $row['id']; ?>">Voir le détail</a>
        </div>
    <?php endwhile; ?>
</div>

<?php 
// On inclut le footer pour fermer la page
require_once 'includes/footer.php'; 
?>