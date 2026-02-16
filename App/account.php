<?php
require_once 'includes/header.php';
checkConnexion();

$user_id = $_SESSION['user_id'];
$message = "";

// 1. Logique pour ajouter de l'argent (Recharger le compte)
if (isset($_POST['add_money'])) {
    $amount = floatval($_POST['amount']);
    if ($amount > 0) {
        $stmt = $mysqli->prepare("UPDATE User SET balance = balance + ? WHERE id = ?");
        $stmt->bind_param("di", $amount, $user_id);
        $stmt->execute();
        $message = "<p style='color:green;'>Compte rechargé avec succès !</p>";
    }
}

// 2. Récupérer les infos de l'utilisateur (solde actuel)
$stmt = $mysqli->prepare("SELECT username, email, balance FROM User WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// 3. Récupérer l'historique des factures
$stmt = $mysqli->prepare("SELECT * FROM Invoice WHERE user_id = ? ORDER BY date_achat DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$invoices = $stmt->get_result();
?>

<h1>Mon Compte</h1>

<div class="account-info" style="background: #f4f4f4; padding: 20px; border-radius: 8px;">
    <p><strong>Pseudo :</strong> <?php echo htmlspecialchars($user['username']); ?></p>
    <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>Solde actuel :</strong> <span style="font-size: 1.5em; color: green;"><?php echo formatPrix($user['balance']); ?></span></p>
</div>

<hr>

<h3>Recharger mon compte</h3>
<?php echo $message; ?>
<form method="POST">
    <input type="number" name="amount" step="10" min="10" value="50">
    <button type="submit" name="add_money">Ajouter des fonds</button>
</form>

<hr>

<h3>Historique de mes achats (Factures)</h3>
<?php if ($invoices->num_rows > 0): ?>
    <table border="1" width="100%" style="text-align: left; border-collapse: collapse;">
        <tr style="background: #eee;">
            <th style="padding: 10px;">N° Facture</th>
            <th style="padding: 10px;">Date</th>
            <th style="padding: 10px;">Montant Total</th>
        </tr>
        <?php while ($inv = $invoices->fetch_assoc()): ?>
        <tr>
            <td style="padding: 10px;">#<?php echo $inv['id']; ?></td>
            <td style="padding: 10px;"><?php echo date('d/m/Y H:i', strtotime($inv['date_achat'])); ?></td>
            <td style="padding: 10px;"><?php echo formatPrix($inv['total']); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>Vous n'avez pas encore effectué d'achats.</p>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>