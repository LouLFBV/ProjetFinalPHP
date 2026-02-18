<?php
require_once 'includes/header.php';
checkConnexion(); 
$uid = $_SESSION['user_id'];
$msg_error = "";

// --- 1. LOGIQUE DE TRAITEMENT ---

// A. AJOUT AU PANIER (Depuis detail.php)
if (isset($_POST['article_id']) && !isset($_POST['update_qty'])) {
    $art_id = intval($_POST['article_id']);
    $qty_asked = isset($_POST['quantite']) ? intval($_POST['quantite']) : 1;

    // Vérifier le stock disponible en base
    $res_s = $mysqli->query("SELECT quantite FROM Stock WHERE article_id = $art_id");
    $stock = $res_s->fetch_assoc();
    $max_dispo = $stock['quantite'] ?? 0;

    // Vérifier si déjà dans le panier pour ne pas dépasser le cumul
    $check = $mysqli->prepare("SELECT id, quantite FROM Cart WHERE user_id = ? AND article_id = ?");
    $check->bind_param("ii", $uid, $art_id);
    $check->execute();
    $res_check = $check->get_result();

    if ($res_check->num_rows > 0) {
        $row = $res_check->fetch_assoc();
        $total_final = $row['quantite'] + $qty_asked;

        if ($total_final <= $max_dispo) {
            $upd = $mysqli->prepare("UPDATE Cart SET quantite = ? WHERE id = ?");
            $upd->bind_param("ii", $total_final, $row['id']);
            $upd->execute();
        } else {
            $msg_error = "Impossible d'ajouter plus d'articles : Stock insuffisant.";
        }
    } else {
        if ($qty_asked <= $max_dispo) {
            $ins = $mysqli->prepare("INSERT INTO Cart (user_id, article_id, quantite) VALUES (?, ?, ?)");
            $ins->bind_param("iii", $uid, $art_id, $qty_asked);
            $ins->execute();
        } else {
            $msg_error = "Stock insuffisant pour cette quantité.";
        }
    }
}

// B. SUPPRESSION
if (isset($_GET['del'])) {
    $cart_id = intval($_GET['del']);
    $del = $mysqli->prepare("DELETE FROM Cart WHERE id = ? AND user_id = ?");
    $del->bind_param("ii", $cart_id, $uid);
    $del->execute();
}

// C. MISE À JOUR QUANTITÉ (Vérification du stock ici aussi !)
if (isset($_POST['update_qty'])) {
    $cart_id = intval($_POST['cart_id']);
    $new_qty = intval($_POST['new_qty']);

    // Trouver l'article lié à cette ligne de panier pour vérifier son stock
    $res_info = $mysqli->query("SELECT article_id FROM Cart WHERE id = $cart_id");
    $info = $res_info->fetch_assoc();
    $art_id = $info['article_id'];

    $res_s = $mysqli->query("SELECT quantite FROM Stock WHERE article_id = $art_id");
    $stock = $res_s->fetch_assoc();

    if ($new_qty <= $stock['quantite']) {
        $stmt = $mysqli->prepare("UPDATE Cart SET quantite = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $new_qty, $cart_id, $uid);
        $stmt->execute();
    } else {
        $msg_error = "Quantité refusée : Seulement " . $stock['quantite'] . " articles en stock.";
    }
}

// --- 2. RÉCUPÉRATION DES DONNÉES ET CALCUL ---
$res = $mysqli->query("SELECT Cart.*, Article.nom, Article.prix, Stock.quantite as max_qty 
                       FROM Cart 
                       JOIN Article ON Cart.article_id = Article.id 
                       JOIN Stock ON Article.id = Stock.article_id
                       WHERE Cart.user_id = $uid");

$total = 0;
$items = [];
while($row = $res->fetch_assoc()){
    $total += ($row['prix'] * $row['quantite']);
    $items[] = $row;
}

// Récupérer le solde actuel pour la vérification
$u_res = $mysqli->query("SELECT balance FROM User WHERE id = $uid");
$user = $u_res->fetch_assoc();
$solde_actuel = $user['balance'];
?>

<h1>Mon Panier</h1>

<?php if($msg_error): ?>
    <p style="color:red; background:#ffdada; padding:10px; border-radius:5px;"><?php echo $msg_error; ?></p>
<?php endif; ?>

<?php if (count($items) > 0): ?>
    <?php foreach($items as $c): $st = $c['prix'] * $c['quantite']; ?>
        <div style="margin-bottom:10px; border-bottom: 1px solid #ddd; padding-bottom: 5px;">
            <strong><?php echo htmlspecialchars($c['nom']); ?></strong> - <?php echo formatPrix($st); ?>
            <p style="font-size: 0.8em; color: gray; margin: 0;">Stock : <?php echo $c['max_qty']; ?></p>
            
            <form method="POST" style="display:inline;">
                <input type="hidden" name="cart_id" value="<?php echo $c['id']; ?>">
                <input type="number" name="new_qty" value="<?php echo $c['quantite']; ?>" min="1" max="<?php echo $c['max_qty']; ?>" style="width: 50px;">
                <button type="submit" name="update_qty">Modifier</button>
            </form>
            <a href="cart.php?del=<?php echo $c['id']; ?>" style="color:red; margin-left: 10px;">Supprimer</a>
        </div>
    <?php endforeach; ?>

    <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px;">
        <h3>Total Panier : <?php echo formatPrix($total); ?></h3>
        <p>Votre solde actuel : <strong><?php echo formatPrix($solde_actuel); ?></strong></p>

        <?php if ($solde_actuel >= $total): ?>
            <form action="validate.php" method="POST">
                <input type="hidden" name="total" value="<?php echo $total; ?>">
                <button type="submit" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    Passer la commande
                </button>
            </form>
        <?php else: ?>
            <div style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;">
                <strong>Solde insuffisant !</strong> Il vous manque <?php echo formatPrix($total - $solde_actuel); ?> pour valider.
                <br><br>
                <a href="account.php" style="color: #721c24; font-weight: bold;">Cliquez ici pour recharger votre compte</a>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <p>Votre panier est vide.</p>
    <a href="index.php">Retourner à la boutique</a>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>