<?php
require_once 'includes/header.php';
checkConnexion();
$uid = $_SESSION['user_id'];

if (!isset($_POST['total'])) {
    header("Location: cart.php");
    exit;
}

$total = floatval($_POST['total']);

if (isset($_POST['confirm_order'])) {
    $address = $_POST['address'];
    $city = $_POST['city'];
    $zip = $_POST['zip'];

    $u_res = $mysqli->query("SELECT balance FROM User WHERE id = $uid");
    $user = $u_res->fetch_assoc();

    if ($user['balance'] >= $total) {
        // 1. D'abord, on prépare et on exécute l'INSERT de la Facture
        $stmt = $mysqli->prepare("INSERT INTO Invoice (user_id, total, adresse_facturation, ville_facturation, code_postal_facturation) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("idsss", $uid, $total, $address, $city, $zip);

        if($stmt->execute()) {
            // 2. MAINTENANT on récupère l'ID généré
            $invoice_id = $mysqli->insert_id; 

            // 3. On enregistre les détails des articles
            $cart_items = $mysqli->query("SELECT Cart.*, Article.nom, Article.prix FROM Cart JOIN Article ON Cart.article_id = Article.id WHERE user_id = $uid");

            while($item = $cart_items->fetch_assoc()) {
                $stmt_item = $mysqli->prepare("INSERT INTO Invoice_Item (invoice_id, nom_article, prix_unitaire, quantite) VALUES (?, ?, ?, ?)");
                $stmt_item->bind_param("isdi", $invoice_id, $item['nom'], $item['prix'], $item['quantite']);
                $stmt_item->execute();
                
                // 4. On baisse le stock en même temps
                $mysqli->query("UPDATE Stock SET quantite = quantite - {$item['quantite']} WHERE article_id = {$item['article_id']}");
            }

            // 5. Débit du solde
            $mysqli->query("UPDATE User SET balance = balance - $total WHERE id = $uid");

            // 6. Vider panier
            $mysqli->query("DELETE FROM Cart WHERE user_id = $uid");

            echo "<h1>Commande confirmée !</h1><p>Merci pour votre achat.</p><a href='index.php'>Retour à l'accueil</a>";
            require_once 'includes/footer.php';
            exit;
        }
    } else {
        die("Erreur : Solde insuffisant.");
    }
}
?>

<h1>Confirmation de commande</h1>
<div style="background: #e9ecef; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
    <p>Montant à régler : <strong><?php echo formatPrix($total); ?></strong></p>
    <p>Veuillez renseigner vos informations de livraison pour finaliser votre achat.</p>
</div>

<form method="POST">
    <input type="hidden" name="total" value="<?php echo $total; ?>">
    
    <label>Adresse de livraison :</label><br>
    <input type="text" name="address" style="width: 100%; padding: 10px; margin-bottom: 10px;" required><br>

    <div style="display: flex; gap: 10px;">
        <div style="flex: 2;">
            <label>Ville :</label><br>
            <input type="text" name="city" style="width: 100%; padding: 10px;" required>
        </div>
        <div style="flex: 1;">
            <label>Code Postal :</label><br>
            <input type="text" name="zip" style="width: 100%; padding: 10px;" required>
        </div>
    </div><br>

    <button type="submit" name="confirm_order" style="background: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 1.1em;">
        Confirmer et Payer <?php echo formatPrix($total); ?>
    </button>
    <a href="cart.php" style="margin-left: 15px; color: #666;">Retour au panier</a>
</form>

<?php require_once 'includes/footer.php'; ?>