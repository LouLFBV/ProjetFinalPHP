<?php
require_once 'includes/header.php';
checkConnexion();

$target_id = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['user_id'];
$is_me = ($target_id == $_SESSION['user_id']);

// Logique de rechargement
if ($is_me && isset($_POST['add_balance'])) {
    $amount = floatval($_POST['amount']);
    if ($amount > 0) {
        $stmt_upd = $mysqli->prepare("UPDATE User SET balance = balance + ? WHERE id = ?");
        $stmt_upd->bind_param("di", $amount, $_SESSION['user_id']);
        $stmt_upd->execute();
        header("Location: account.php?msg=Solde rechargé !");
        exit;
    }
}

// Infos User
$stmt_u = $mysqli->prepare("SELECT username, email, balance, image_url FROM User WHERE id = ?");
$stmt_u->bind_param("i", $target_id);
$stmt_u->execute();
$user = $stmt_u->get_result()->fetch_assoc();
if (!$user) die("Utilisateur introuvable.");

// Données articles
$mes_favoris = $is_me ? $mysqli->query("SELECT Article.* FROM Favorite JOIN Article ON Favorite.article_id = Article.id WHERE Favorite.user_id = $target_id") : null;
$mes_articles = $mysqli->query("SELECT Article.*, Category.nom as cat_nom FROM Article LEFT JOIN Category ON Article.category_id = Category.id WHERE auteur_id = $target_id ORDER BY date_publication DESC");
?>

<div class="account-wrapper">
    
    <div class="header-profile">
        <div class="profile-info-main">
            <?php if(!empty($user['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($user['image_url']); ?>" class="avatar-large">
            <?php else: ?>
                <div class="avatar-large" style="background:#eee; display:flex; align-items:center; justify-content:center; font-size:2rem;">👤</div>
            <?php endif; ?>
            
            <div>
                <h1 style="margin:0; font-size:1.8rem;"><?php echo htmlspecialchars($user['username']); ?></h1>
                <p style="color:#888; margin:5px 0;"><?php echo $is_me ? htmlspecialchars($user['email']) : "Membre certifié"; ?></p>
                <?php if($is_me): ?>
                    <a href="edit_profile.php" style="font-size:0.85rem; color:var(--primary-color); text-decoration:none; font-weight:bold;">⚙️ Modifier mes infos</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if($is_me): ?>
        <div class="balance-card">
            <small style="opacity:0.8; text-transform:uppercase; letter-spacing:1px;">Mon Solde</small>
            <div style="font-size:2rem; font-weight:800; margin:5px 0;"><?php echo formatPrix($user['balance']); ?></div>
            <form method="POST" style="display:flex; gap:5px;">
                <input type="number" name="amount" step="0.1" min="1" placeholder="Montant" required style="width:80px; padding:8px; border-radius:8px; border:none;">
                <button type="submit" name="add_balance" class="btn-submit" style="margin:0; padding:8px 15px; width:auto; background:rgba(255,255,255,0.2); color:white; border:1px solid rgba(255,255,255,0.4);">+ Recharger</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($is_me && $mes_favoris->num_rows > 0): ?>
    <div class="dashboard-section">
        <h3 class="section-title">❤️ Mes Coups de Coeur</h3>
        <div style="display:flex; gap:15px; overflow-x:auto; padding-bottom:10px;">
            <?php while($fav = $mes_favoris->fetch_assoc()): ?>
                <a href="detail.php?id=<?php echo $fav['id']; ?>" style="text-decoration:none; background:white; padding:15px 25px; border-radius:15px; box-shadow:0 4px 15px rgba(0,0,0,0.05); color:#333; min-width:200px; display:flex; align-items:center; justify-content:space-between;">
                    <strong><?php echo htmlspecialchars($fav['nom']); ?></strong>
                    <span style="color:var(--primary-color); font-weight:bold;"><?php echo formatPrix($fav['prix']); ?></span>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="dashboard-section">
        <h3 class="section-title">🛒 Annonces en ligne</h3>
        <div class="mini-grid">
            <?php if ($mes_articles->num_rows > 0): ?>
                <?php while($row = $mes_articles->fetch_assoc()): ?>
                <div class="article-card" style="background:white; border-radius:15px; overflow:hidden;">
                    <div style="padding:20px;">
                        <span style="font-size:0.7rem; background:#f0f0f0; padding:3px 8px; border-radius:4px;"><?php echo $row['cat_nom'] ?? 'Sans catégorie'; ?></span>
                        <h4 style="margin:10px 0;"><?php echo htmlspecialchars($row['nom']); ?></h4>
                        <p style="color:var(--primary-color); font-weight:bold; font-size:1.2rem;"><?php echo formatPrix($row['prix']); ?></p>
                        
                        <div style="display:flex; gap:10px; margin-top:15px; border-top:1px solid #eee; padding-top:15px;">
                            <a href="detail.php?id=<?php echo $row['id']; ?>" class="btn-view" style="flex:1; text-align:center; text-decoration:none; font-size:0.8rem;">Voir</a>
                            <?php if ($is_me || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-view" style="flex:1; text-align:center; text-decoration:none; font-size:0.8rem; border-color:orange; color:orange;">Gérer</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color:#999; grid-column: 1/-1;">Aucun article publié pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($is_me): ?>
    <div class="dashboard-section">
        <h3 class="section-title">📄 Historique de mes commandes</h3>
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Détails</th>
                    <th>Date</th>
                    <th>Total TTC</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt_inv = $mysqli->prepare("SELECT * FROM Invoice WHERE user_id = ? ORDER BY date_achat DESC");
                $stmt_inv->bind_param("i", $_SESSION['user_id']);
                $stmt_inv->execute();
                $res_inv = $stmt_inv->get_result();
                
                while($f = $res_inv->fetch_assoc()): ?>
                <tr>
                    <td><span style="font-family:monospace; background:#eee; padding:4px 8px; border-radius:4px;">#INV-<?php echo $f['id']; ?></span></td>
                    <td>
                        <small style="color:#666;">
                        <?php
                        $items = $mysqli->query("SELECT nom_article, quantite FROM Invoice_Item WHERE invoice_id = ".$f['id']);
                        $it_names = [];
                        while($it = $items->fetch_assoc()) $it_names[] = $it['nom_article'] . " (x".$it['quantite'].")";
                        echo implode(', ', $it_names);
                        ?>
                        </small>
                    </td>
                    <td>
                        <div style="font-weight: 500;"><?php echo date('d/m/Y', strtotime($f['date_achat'])); ?></div>
                        <div style="font-size: 0.75rem; color: #999;">à <?php echo date('H:i', strtotime($f['date_achat'])); ?></div>
                    </td>
                    <td><strong style="color:var(--primary-color);"><?php echo formatPrix($f['total']); ?></strong></td>
                    <td style="font-size: 0.85rem; color: #666;">
                        📍 <?php echo htmlspecialchars($f['ville_facturation']); ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                
                <?php if($res_inv->num_rows == 0): ?>
                    <tr><td colspan="5" style="text-align:center; color:#999; padding:30px;">Aucun achat effectué.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>