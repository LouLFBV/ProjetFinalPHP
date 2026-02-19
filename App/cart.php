<?php
require_once 'includes/header.php';
checkConnexion(); 

$uid = $_SESSION['user_id'];
$msg_error = "";
$msg_success = "";

// --- 1. TRAITEMENTS DES ACTIONS (POST & GET) ---

// A. AJOUT OU MISE À JOUR (Depuis detail.php ou le bouton "Modifier")
if (isset($_POST['article_id'])) {
    $art_id = intval($_POST['article_id']);
    // Si c'est une mise à jour directe (input), on prend la valeur. Sinon, on ajoute +1 par défaut.
    $new_qty = isset($_POST['new_qty']) ? intval($_POST['new_qty']) : 1;
    $is_update = isset($_POST['update_qty']);

    // Vérification du stock réel
    $res_s = $mysqli->query("SELECT quantite FROM Stock WHERE article_id = $art_id");
    $stock = $res_s->fetch_assoc();
    $max_dispo = $stock['quantite'] ?? 0;

    // Vérifier si l'article est déjà dans le panier
    $check = $mysqli->query("SELECT id, quantite FROM Cart WHERE user_id = $uid AND article_id = $art_id");
    
    if ($check->num_rows > 0) {
        $row = $check->fetch_assoc();
        // Si c'est un "Ajout" depuis la boutique, on cumule. Si c'est une "Modif" dans le panier, on remplace.
        $total_vise = $is_update ? $new_qty : ($row['quantite'] + $new_qty);

        if ($total_vise <= $max_dispo) {
            $mysqli->query("UPDATE Cart SET quantite = $total_vise WHERE id = " . $row['id']);
            $msg_success = "Quantité mise à jour.";
        } else {
            $msg_error = "Action impossible : Le stock maximum est de $max_dispo.";
        }
    } else {
        // Nouvel ajout au panier
        if ($new_qty <= $max_dispo) {
            $mysqli->query("INSERT INTO Cart (user_id, article_id, quantite) VALUES ($uid, $art_id, $new_qty)");
            $msg_success = "Article ajouté au panier.";
        } else {
            $msg_error = "Stock insuffisant pour cette quantité.";
        }
    }
}

// B. SUPPRESSION D'UN ARTICLE
if (isset($_GET['del'])) {
    $cart_id = intval($_GET['del']);
    $mysqli->query("DELETE FROM Cart WHERE id = $cart_id AND user_id = $uid");
    $msg_success = "Article retiré du panier.";
}

// --- 2. RÉCUPÉRATION DES ÉLÉMENTS DU PANIER ---

$query = "SELECT Cart.id as cart_id, Cart.quantite as qty_panier, 
                 Article.id as article_id, Article.nom, Article.prix, 
                 Stock.quantite as qty_stock 
          FROM Cart 
          JOIN Article ON Cart.article_id = Article.id 
          JOIN Stock ON Article.id = Stock.article_id
          WHERE Cart.user_id = $uid";

$res = $mysqli->query($query);
$items = [];
$total_panier = 0;

while($row = $res->fetch_assoc()){
    $items[] = $row;
    $total_panier += ($row['prix'] * $row['qty_panier']);
}

// Récupération du solde de l'utilisateur
$user_data = $mysqli->query("SELECT balance FROM User WHERE id = $uid")->fetch_assoc();
$solde = $user_data['balance'];
?>

<div style="max-width: 900px; margin: auto; padding: 20px;">
    <h1>🛒 Mon Panier</h1>

    <?php if($msg_error): ?>
        <p style="color:#721c24; background:#f8d7da; padding:12px; border-radius:5px; border:1px solid #f5c6cb;"><?php echo $msg_error; ?></p>
    <?php endif; ?>

    <?php if($msg_success): ?>
        <p style="color:#155724; background:#d4edda; padding:12px; border-radius:5px; border:1px solid #c3e6cb;"><?php echo $msg_success; ?></p>
    <?php endif; ?>

    <?php if (count($items) > 0): ?>
        <table style="width:100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <thead style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <tr>
                    <th style="padding: 15px; text-align: left;">Produit</th>
                    <th style="padding: 15px; text-align: center;">Prix Unitaire</th>
                    <th style="padding: 15px; text-align: center;">Quantité</th>
                    <th style="padding: 15px; text-align: right;">Sous-total</th>
                    <th style="padding: 15px; text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $item): $st = $item['prix'] * $item['qty_panier']; ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 15px;">
                        <strong><?php echo htmlspecialchars($item['nom']); ?></strong><br>
                        <small style="color: #666;">Stock dispo: <?php echo $item['qty_stock']; ?></small>
                    </td>
                    <td style="padding: 15px; text-align: center;"><?php echo formatPrix($item['prix']); ?></td>
                    <td style="padding: 15px; text-align: center;">
                        <form method="POST" style="display: flex; justify-content: center; gap: 5px;">
                            <input type="hidden" name="article_id" value="<?php echo $item['article_id']; ?>">
                            <input type="number" name="new_qty" value="<?php echo $item['qty_panier']; ?>" 
                                   min="1" max="<?php echo $item['qty_stock']; ?>" 
                                   style="width: 50px; padding: 5px;">
                            <button type="submit" name="update_qty" style="cursor:pointer;">💾</button>
                        </form>
                    </td>
                    <td style="padding: 15px; text-align: right; font-weight: bold;"><?php echo formatPrix($st); ?></td>
                    <td style="padding: 15px; text-align: center;">
                        <a href="cart.php?del=<?php echo $item['cart_id']; ?>" style="color:red; text-decoration:none; font-size: 1.2em;" title="Supprimer">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top: 30px; display: flex; justify-content: flex-end;">
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; border: 1px solid #ddd; min-width: 300px;">
                <p style="display: flex; justify-content: space-between;"><span>Total :</span> <strong><?php echo formatPrix($total_panier); ?></strong></p>
                <p style="display: flex; justify-content: space-between; color: #666;"><span>Votre solde :</span> <span><?php echo formatPrix($solde); ?></span></p>
                <hr>
                
                <?php if ($solde >= $total_panier): ?>
                    <form action="validate.php" method="POST">
                        <input type="hidden" name="total" value="<?php echo $total_panier; ?>">
                        
                        <button type="submit" style="width:100%; background: #28a745; color: white; border: none; padding: 12px; border-radius: 5px; cursor: pointer; font-size: 1.1em; font-weight: bold;">
                            🚀 Valider ma commande
                        </button>
                    </form>
                <?php else: ?>
                    <p style="color: #721c24; text-align: center; font-weight: bold;">Solde insuffisant</p>
                    <a href="account.php" style="display:block; text-align:center; color:#007bff;">Recharger mon compte</a>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        <div style="text-align: center; padding: 50px; background: #f9f9f9; border-radius: 10px;">
            <p style="font-size: 1.2em; color: #666;">Votre panier est tristement vide...</p>
            <a href="index.php" style="display: inline-block; margin-top: 20px; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Découvrir nos articles</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>