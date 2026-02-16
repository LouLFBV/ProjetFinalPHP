<?php
require_once 'includes/header.php';
checkConnexion(); // Seul un utilisateur connecté accède au panier [cite: 62]

$user_id = $_SESSION['user_id'];

// --- LOGIQUE DE TRAITEMENT ---

// A. Ajout au panier (depuis detail.php)
if (isset($_POST['article_id']) && !isset($_POST['update'])) {
    $art_id = intval($_POST['article_id']);
    $qty = intval($_POST['quantite']);

    // On vérifie si l'article est déjà dans le panier
    $check = $mysqli->prepare("SELECT id, quantite FROM Cart WHERE user_id = ? AND article_id = ?");
    $check->bind_param("ii", $user_id, $art_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        // Mise à jour de la quantité si déjà présent [cite: 35]
        $row = $res->fetch_assoc();
        $new_qty = $row['quantite'] + $qty;
        $upd = $mysqli->prepare("UPDATE Cart SET quantite = ? WHERE id = ?");
        $upd->bind_param("ii", $new_qty, $row['id']);
        $upd->execute();
    } else {
        // Nouvel ajout
        $ins = $mysqli->prepare("INSERT INTO Cart (user_id, article_id, quantite) VALUES (?, ?, ?)");
        $ins->bind_param("iii", $user_id, $art_id, $qty);
        $ins->execute();
    }
}

// B. Suppression d'un article 
if (isset($_GET['delete'])) {
    $cart_id = intval($_GET['delete']);
    $del = $mysqli->prepare("DELETE FROM Cart WHERE id = ? AND user_id = ?");
    $del->bind_param("ii", $cart_id, $user_id);
    $del->execute();
}

// --- AFFICHAGE ---

// Récupération des articles du panier avec une jointure pour avoir les noms et prix 
$query = "SELECT Cart.id as cart_id, Cart.quantite, Article.* FROM Cart 
          JOIN Article ON Cart.article_id = Article.id 
          WHERE Cart.user_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$items = $stmt->get_result();

$total_panier = 0;
?>

<h1>Mon Panier</h1>

<table border="1" width="100%">
    <tr>
        <th>Article</th>
        <th>Prix Unitaire</th>
        <th>Quantité</th>
        <th>Sous-total</th>
        <th>Action</th>
    </tr>
    <?php while ($item = $items->fetch_assoc()): 
        $sous_total = $item['prix'] * $item['quantite'];
        $total_panier += $sous_total;
    ?>
    <tr>
        <td><?php echo htmlspecialchars($item['nom']); ?></td>
        <td><?php echo formatPrix($item['prix']); ?></td>
        <td><?php echo $item['quantite']; ?></td>
        <td><?php echo formatPrix($sous_total); ?></td>
        <td>
            <a href="cart.php?delete=<?php echo $item['cart_id']; ?>" onclick="return confirm('Supprimer ?')">Supprimer</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<h3>Total : <?php echo formatPrix($total_panier); ?></h3>

<?php if ($total_panier > 0): ?>
    <form action="validate.php" method="POST">
        <input type="hidden" name="total" value="<?php echo $total_panier; ?>">
        <button type="submit">Passer la commande</button>
    </form>
<?php else: ?>
    <p>Votre panier est vide.</p>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>