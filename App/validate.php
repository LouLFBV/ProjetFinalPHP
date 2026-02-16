<?php
require_once 'includes/header.php';
checkConnexion();

$user_id = $_SESSION['user_id'];
$total_commande = isset($_POST['total']) ? floatval($_POST['total']) : 0;

if ($total_commande <= 0) {
    header("Location: cart.php");
    exit;
}

// 1. Récupérer le solde actuel de l'utilisateur
$stmt = $mysqli->prepare("SELECT balance FROM User WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

$message = "";
$status = "error";

// 2. Vérification du solde
if ($user['balance'] >= $total_commande) {
    // A. Débiter l'utilisateur
    $nouveau_solde = $user['balance'] - $total_commande;
    $upd_user = $mysqli->prepare("UPDATE User SET balance = ? WHERE id = ?");
    $upd_user->bind_param("di", $nouveau_solde, $user_id);
    $upd_user->execute();

    // B. Créer la facture
    $ins_invoice = $mysqli->prepare("INSERT INTO Invoice (user_id, total) VALUES (?, ?)");
    $ins_invoice->bind_param("id", $user_id, $total_commande);
    $ins_invoice->execute();

    // C. Vider le panier
    $del_cart = $mysqli->prepare("DELETE FROM Cart WHERE user_id = ?");
    $del_cart->bind_param("i", $user_id);
    $del_cart->execute();

    $message = "Commande validée ! Merci pour votre achat.";
    $status = "success";
} else {
    $message = "Fonds insuffisants. Il vous manque " . formatPrix($total_commande - $user['balance']) . ".";
}
?>

<div style="text-align: center; margin-top: 50px;">
    <?php if ($status === "success"): ?>
        <h1 style="color: green;">✔ <?php echo $message; ?></h1>
        <p>Votre nouveau solde est de : <strong><?php echo formatPrix($nouveau_solde); ?></strong></p>
    <?php else: ?>
        <h1 style="color: red;">✘ Erreur</h1>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <br>
    <a href="index.php">Retour à la boutique</a> | 
    <a href="account.php">Voir mes factures</a>
</div>

<?php require_once 'includes/footer.php'; ?>