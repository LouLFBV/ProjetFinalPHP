<?php
require_once 'includes/header.php';
// On s'assure que l'utilisateur est connecté
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$uid = $_SESSION['user_id'];
$msg_error = "";
$msg_success = "";

// --- 1. LOGIQUE DE MISE À JOUR (CRUD) ---

// Modification de quantité ou Ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['article_id'])) {
    $art_id = intval($_POST['article_id']);
    $new_qty = isset($_POST['new_qty']) ? intval($_POST['new_qty']) : 1;
    $is_update = isset($_POST['update_qty']);

    $stock = $mysqli->query("SELECT quantite FROM Stock WHERE article_id = $art_id")->fetch_assoc();
    $max_dispo = $stock['quantite'] ?? 0;

    $check = $mysqli->query("SELECT id, quantite FROM Cart WHERE user_id = $uid AND article_id = $art_id");
    
    if ($check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $total_vise = $is_update ? $new_qty : ($row['quantite'] + $new_qty);

        if ($total_vise <= $max_dispo) {
            $mysqli->query("UPDATE Cart SET quantite = $total_vise WHERE id = " . $row['id']);
            $msg_success = "Panier mis à jour.";
        } else {
            $msg_error = "Désolé, seulement $max_dispo articles en stock.";
        }
    } else {
        if ($new_qty <= $max_dispo) {
            $mysqli->query("INSERT INTO Cart (user_id, article_id, quantite) VALUES ($uid, $art_id, $new_qty)");
            $msg_success = "Ajouté au panier !";
        }
    }
}

// Suppression
if (isset($_GET['del'])) {
    $cart_id = intval($_GET['del']);
    $mysqli->query("DELETE FROM Cart WHERE id = $cart_id AND user_id = $uid");
    $msg_success = "Article retiré.";
}

// --- 2. AFFICHAGE ---

$res = $mysqli->query("SELECT Cart.id as cart_id, Cart.quantite as qty_panier, Article.id as article_id, Article.nom, Article.prix, Stock.quantite as qty_stock 
                       FROM Cart JOIN Article ON Cart.article_id = Article.id JOIN Stock ON Article.id = Stock.article_id WHERE Cart.user_id = $uid");

$items = $res->fetch_all(MYSQLI_ASSOC);
$total_panier = 0;
foreach($items as $i) $total_panier += ($i['prix'] * $i['qty_panier']);

$user = $mysqli->query("SELECT balance FROM User WHERE id = $uid")->fetch_assoc();
$solde = $user['balance'];
?>

<div style="max-width: 1000px; margin: auto;">
    <h1 style="margin-bottom: 30px;">Votre Panier</h1>

    <?php if($msg_error): ?> <div class="alert-error" style="margin-bottom:20px;"><?php echo $msg_error; ?></div> <?php endif; ?>
    <?php if($msg_success): ?> <div style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #c3e6cb;"><?php echo $msg_success; ?></div> <?php endif; ?>

    <?php if (!empty($items)): ?>
        <div style="display: flex; gap: 30px; flex-wrap: wrap; align-items: flex-start;">
            
            <div style="flex: 2; min-width: 600px;">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th style="text-align: center;">Prix</th>
                            <th style="text-align: center;">Quantité</th>
                            <th style="text-align: right;">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($items as $item): $st = $item['prix'] * $item['qty_panier']; ?>
                        <tr>
                            <td class="cart-item-info">
                                <strong><?php echo htmlspecialchars($item['nom']); ?></strong>
                                <small style="color:#999;">En stock : <?php echo $item['qty_stock']; ?></small>
                            </td>
                            <td style="text-align: center;"><?php echo formatPrix($item['prix']); ?></td>
                            <td>
                                <form method="POST" class="cart-qty-form">
                                    <input type="hidden" name="article_id" value="<?php echo $item['article_id']; ?>">
                                    <input type="number" name="new_qty" value="<?php echo $item['qty_panier']; ?>" min="1" max="<?php echo $item['qty_stock']; ?>">
                                    <button type="submit" name="update_qty" class="btn-update" title="Actualiser">🔄</button>
                                </form>
                            </td>
                            <td style="text-align: right; font-weight: bold;"><?php echo formatPrix($st); ?></td>
                            <td style="text-align: center;">
                                <a href="cart.php?del=<?php echo $item['cart_id']; ?>" style="text-decoration:none;">❌</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="index.php" style="color: var(--primary-color); text-decoration: none; font-weight: bold;">← Continuer mes achats</a>
            </div>

            <div class="cart-summary">
                <h3 style="margin-top:0; margin-bottom:20px;">Résumé de la commande</h3>
                <div class="summary-line">
                    <span>Sous-total</span>
                    <span><?php echo formatPrix($total_panier); ?></span>
                </div>
                <div class="summary-line" style="color: #666;">
                    <span>Votre solde actuel</span>
                    <span><?php echo formatPrix($solde); ?></span>
                </div>
                
                <div class="summary-line summary-total">
                    <span>Total TTC</span>
                    <span><?php echo formatPrix($total_panier); ?></span>
                </div>

                <?php if ($solde >= $total_panier): ?>
                    <form action="validate.php" method="POST" style="margin-top: 25px;">
                        <input type="hidden" name="total" value="<?php echo $total_panier; ?>">
                        <button type="submit" class="btn-submit" style="width: 100%; background: #28a745;">
                            Payer avec mon solde
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert-error" style="margin-top: 25px;">
                        Solde insuffisant (manque <?php echo formatPrix($total_panier - $solde); ?>)
                    </div>
                    <a href="account.php" class="btn-view" style="display:block; margin-top:10px;">Recharger mon compte</a>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        <div style="text-align: center; padding: 80px 20px; background: white; border-radius: 12px; box-shadow: var(--nav-shadow);">
            <span style="font-size: 4rem;">🛒</span>
            <h2>Votre panier est vide</h2>
            <p style="color: #666; margin-bottom: 30px;">Il semblerait que vous n'ayez pas encore trouvé votre bonheur.</p>
            <a href="index.php" class="btn-submit" style="text-decoration: none; display: inline-block;">Parcourir la boutique</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>