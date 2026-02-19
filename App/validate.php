<?php
require_once 'includes/header.php';
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$uid = $_SESSION['user_id'];

if (!isset($_POST['total'])) { header("Location: cart.php"); exit; }
$total = floatval($_POST['total']);

if (isset($_POST['confirm_order'])) {
    $address = $mysqli->real_escape_string($_POST['address']);
    $city = $mysqli->real_escape_string($_POST['city']);
    $zip = $mysqli->real_escape_string($_POST['zip']);

    $user = $mysqli->query("SELECT balance FROM User WHERE id = $uid")->fetch_assoc();

    if ($user['balance'] >= $total) {

        $mysqli->begin_transaction();

        try {
            $stmt = $mysqli->prepare("INSERT INTO Invoice (user_id, total, adresse_facturation, ville_facturation, code_postal_facturation) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("idsss", $uid, $total, $address, $city, $zip);
            $stmt->execute();
            $invoice_id = $mysqli->insert_id;

            $cart_items = $mysqli->query("SELECT Cart.*, Article.nom, Article.prix FROM Cart JOIN Article ON Cart.article_id = Article.id WHERE user_id = $uid");

            while($item = $cart_items->fetch_assoc()) {
                $stmt_item = $mysqli->prepare("INSERT INTO Invoice_Item (invoice_id, article_id, nom_article, prix_unitaire, quantite) VALUES (?, ?, ?, ?, ?)");
                $stmt_item->bind_param("iisdi", $invoice_id, $item['article_id'], $item['nom'], $item['prix'], $item['quantite']);
                $stmt_item->execute();
                
                $mysqli->query("UPDATE Stock SET quantite = quantite - {$item['quantite']} WHERE article_id = {$item['article_id']}");
            }

            $mysqli->query("UPDATE User SET balance = balance - $total WHERE id = $uid");
            $mysqli->query("DELETE FROM Cart WHERE user_id = $uid");

            $mysqli->commit();

            echo "<div class='success-container'>
                    <span class='success-icon'>✅</span>
                    <h1>Commande confirmée !</h1>
                    <p>Merci pour votre confiance. Votre facture <strong>#$invoice_id</strong> est disponible dans votre compte.</p>
                    <a href='index.php' class='btn-submit' style='display:inline-block; margin-top:20px; text-decoration:none;'>Retour à la boutique</a>
                  </div>";
            require_once 'includes/footer.php';
            exit;

        } catch (Exception $e) {
            $mysqli->rollback(); 
            $error = "Une erreur est survenue lors du paiement. Veuillez réessayer.";
        }
    } else {
        header("Location: cart.php?msg=Solde insuffisant.");
        exit;
    }
}
?>

<div style="max-width: 700px; margin: 40px auto;">
    <div class="auth-card" style="max-width: 100%;">
        <h1>Finaliser la commande</h1>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 30px; border-left: 4px solid var(--primary-color);">
            <div style="display:flex; justify-content: space-between; align-items: center;">
                <span>Montant total à payer :</span>
                <span style="font-size: 1.5rem; font-weight: 800; color: var(--primary-color);"><?php echo formatPrix($total); ?></span>
            </div>
        </div>

        <form method="POST" class="auth-form">
            <input type="hidden" name="total" value="<?php echo $total; ?>">
            
            <div class="form-group">
                <label>Adresse de livraison complète</label>
                <input type="text" name="address" placeholder="N°, nom de rue..." required>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Ville</label>
                    <input type="text" name="city" placeholder="Ex: Paris" required>
                </div>
                <div class="form-group">
                    <label>Code Postal</label>
                    <input type="text" name="zip" placeholder="75000" required>
                </div>
            </div>

            <p style="font-size: 0.85rem; color: #666; margin-top: 20px;">
                En cliquant sur confirmer, votre solde de <strong><?php echo formatPrix($total); ?></strong> sera débité immédiatement.
            </p>

            <button type="submit" name="confirm_order" class="btn-submit" style="background: #28a745;">
                💳 Confirmer et Payer
            </button>
            
            <a href="cart.php" style="display:block; text-align:center; margin-top:15px; color:#999; text-decoration:none;">Annuler</a>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>