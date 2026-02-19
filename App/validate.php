<?php
require_once 'includes/header.php';
checkConnexion();
$uid = $_SESSION['user_id'];

// On vérifie qu'on arrive bien avec un total (soit via le bouton 'Valider' du panier, soit via le formulaire interne)
if (!isset($_POST['total'])) {
    header("Location: cart.php");
    exit;
}

$total = floatval($_POST['total']);

if (isset($_POST['confirm_order'])) {
    $address = $mysqli->real_escape_string($_POST['address']);
    $city = $mysqli->real_escape_string($_POST['city']);
    $zip = $mysqli->real_escape_string($_POST['zip']);

    $u_res = $mysqli->query("SELECT balance FROM User WHERE id = $uid");
    $user = $u_res->fetch_assoc();

    if ($user['balance'] >= $total) {
        // 1. Création de la Facture principale [cite: 86]
        $stmt = $mysqli->prepare("INSERT INTO Invoice (user_id, total, adresse_facturation, ville_facturation, code_postal_facturation) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("idsss", $uid, $total, $address, $city, $zip);

        if($stmt->execute()) {
            $invoice_id = $mysqli->insert_id; 

            // 2. Récupération des articles du panier
            $cart_items = $mysqli->query("SELECT Cart.*, Article.nom, Article.prix FROM Cart JOIN Article ON Cart.article_id = Article.id WHERE user_id = $uid");

            while($item = $cart_items->fetch_assoc()) {
                // CORRECTION : On ajoute article_id dans l'INSERT pour permettre les avis clients 
                $stmt_item = $mysqli->prepare("INSERT INTO Invoice_Item (invoice_id, article_id, nom_article, prix_unitaire, quantite) VALUES (?, ?, ?, ?, ?)");
                $stmt_item->bind_param("iisdi", $invoice_id, $item['article_id'], $item['nom'], $item['prix'], $item['quantite']);
                $stmt_item->execute();
                
                // 3. Mise à jour du stock (Bonus Gestion de stock) 
                $mysqli->query("UPDATE Stock SET quantite = quantite - {$item['quantite']} WHERE article_id = {$item['article_id']}");
            }

            // 4. Débit du solde et Nettoyage [cite: 41, 76]
            $mysqli->query("UPDATE User SET balance = balance - $total WHERE id = $uid");
            $mysqli->query("DELETE FROM Cart WHERE user_id = $uid");

            echo "<div style='text-align:center; padding:50px;'>
                    <h1>✅ Commande confirmée !</h1>
                    <p>Merci pour votre achat. Votre facture n°$invoice_id a été générée.</p>
                    <a href='index.php' style='color:#007bff;'>Retour à l'accueil</a>
                  </div>";
            require_once 'includes/footer.php';
            exit;
        }
    } else {
        header("Location: cart.php?msg=Solde insuffisant pour confirmer l'achat.");
        exit;
    }
}
?>

<div style="max-width: 600px; margin: auto; padding: 20px;">
    <h1>Confirmation de commande</h1>
    <div style="background: #e9ecef; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #007bff;">
        <p style="margin:0;">Montant total à régler : <strong><?php echo formatPrix($total); ?></strong></p>
    </div>

    <form method="POST">
        <input type="hidden" name="total" value="<?php echo $total; ?>">
        
        <label><strong>Adresse de livraison :</strong></label><br>
        <input type="text" name="address" placeholder="Ex: 12 rue des Développeurs" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;" required><br>

        <div style="display: flex; gap: 10px;">
            <div style="flex: 2;">
                <label><strong>Ville :</strong></label><br>
                <input type="text" name="city" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            </div>
            <div style="flex: 1;">
                <label><strong>Code Postal :</strong></label><br>
                <input type="text" name="zip" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            </div>
        </div><br><br>

        <button type="submit" name="confirm_order" style="background: #28a745; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 1.2em; width: 100%; font-weight: bold;">
            💳 Confirmer et Payer <?php echo formatPrix($total); ?>
        </button>
        
        <p style="text-align:center; margin-top:15px;">
            <a href="cart.php" style="color: #666; text-decoration:none;">⬅️ Retourner au panier</a>
        </p>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>