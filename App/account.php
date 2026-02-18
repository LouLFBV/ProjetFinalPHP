<?php
require_once 'includes/header.php';
checkConnexion();

$target_id = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['user_id'];
$is_me = ($target_id == $_SESSION['user_id']);

// --- LOGIQUE DE RECHARGEMENT ---
if ($is_me && isset($_POST['add_balance'])) {
    $amount = floatval($_POST['amount']);
    if ($amount > 0) {
        $stmt_upd = $mysqli->prepare("UPDATE User SET balance = balance + ? WHERE id = ?");
        $stmt_upd->bind_param("di", $amount, $_SESSION['user_id']);
        $stmt_upd->execute();
        // On redirige pour rafraîchir l'affichage du solde
        header("Location: account.php");
        exit;
    }
}

// Infos de l'utilisateur visé
$stmt_u = $mysqli->prepare("SELECT username, email, balance FROM User WHERE id = ?");
$stmt_u->bind_param("i", $target_id);
$stmt_u->execute();
$user = $stmt_u->get_result()->fetch_assoc();

if (!$user) die("Utilisateur introuvable.");

// Articles postés par ce compte
$stmt_art = $mysqli->prepare("SELECT * FROM Article WHERE auteur_id = ? ORDER BY date_publication DESC");
$stmt_art->bind_param("i", $target_id);
$stmt_art->execute();
$mes_articles = $stmt_art->get_result();
?>

<h1>Profil de <?php echo htmlspecialchars($user['username']); ?></h1>

<div style="background:#f4f4f4; padding:15px; border-radius:8px; margin-bottom:20px;">
    <p><strong>Pseudo :</strong> <?php echo htmlspecialchars($user['username']); ?></p>
    
    <?php if ($is_me): ?>
        <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Mon Solde :</strong> <span style="color:green; font-weight:bold;"><?php echo formatPrix($user['balance']); ?></span></p>
        
        <form method="POST" style="margin-top: 10px; padding: 10px; border: 1px dashed #ccc; display: inline-block;">
            <label>Ajouter des fonds (€) :</label><br>
            <input type="number" name="amount" step="0.01" min="1" placeholder="Ex: 50" required>
            <button type="submit" name="add_balance">Recharger</button>
        </form>
        <br><br>
        
        <a href="edit_profile.php">Modifier mes infos</a>
    <?php endif; ?>
</div>

<hr>

<h3>Articles mis en vente</h3>
<div class="list">
    <?php if ($mes_articles->num_rows > 0): ?>
        <?php while($row = $mes_articles->fetch_assoc()): ?>
            <p>
                <strong><?php echo htmlspecialchars($row['nom']); ?></strong> - <?php echo formatPrix($row['prix']); ?>
                <a href="detail.php?id=<?php echo $row['id']; ?>">Voir</a>
                <?php if ($is_me || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
                    | <a href="edit.php?id=<?php echo $row['id']; ?>">Modifier/Supprimer</a>
                <?php endif; ?>
            </p>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Aucun article publié.</p>
    <?php endif; ?>
</div>

<?php if ($is_me): ?>
    <hr>
    <h3>Mes Factures</h3>
    <?php
    $stmt_inv = $mysqli->prepare("SELECT * FROM Invoice WHERE user_id = ? ORDER BY date_achat DESC");
    $stmt_inv->bind_param("i", $_SESSION['user_id']);
    $stmt_inv->execute();
    $res_inv = $stmt_inv->get_result();

    if ($res_inv->num_rows > 0): ?>
        <table border="1" style="width:100%; border-collapse: collapse;">
            <tr style="background:#eee;">
                <th>N° Facture</th>
                <th>Détails des articles</th>
                <th>Total</th>
                <th>Date & Ville</th>
            </tr>
            <?php while($f = $res_inv->fetch_assoc()): ?>
                <tr>
                    <td style="text-align:center;">#<?php echo $f['id']; ?></td>
                    <td>
                        <ul style="margin: 5px 0; padding-left: 20px; font-size: 0.9em;">
                        <?php
                        // On va chercher les articles liés à CETTE facture
                        $inv_id = $f['id'];
                        $items_res = $mysqli->query("SELECT * FROM Invoice_Item WHERE invoice_id = $inv_id");
                        while($it = $items_res->fetch_assoc()): ?>
                            <li>
                                <?php echo htmlspecialchars($it['nom_article']); ?> 
                                (x<?php echo $it['quantite']; ?>) : 
                                <strong><?php echo formatPrix($it['prix_unitaire'] * $it['quantite']); ?></strong>
                            </li>
                        <?php endwhile; ?>
                        </ul>
                    </td>
                    <td style="font-weight:bold; color: #2c3e50;">
                        <?php echo formatPrix($f['total']); ?>
                    </td>
                    <td style="font-size: 0.8em;">
                        Le <?php echo date('d/m/Y', strtotime($f['date_achat'])); ?><br>
                        à <?php echo htmlspecialchars($f['ville_facturation']); ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Vous n'avez pas encore d'historique d'achat.</p>
    <?php endif; ?>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>