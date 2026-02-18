<?php
require_once 'includes/header.php';
checkConnexion(); // Protection obligatoire [cite: 62]

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $desc = $_POST['description'];
    $prix = $_POST['prix'];
    $stock_qty = intval($_POST['stock']);
    $img = $_POST['image_url'] ?? '';
    $user_id = $_SESSION['user_id'];

    // 1. Insertion Article [cite: 69, 83]
    $stmt = $mysqli->prepare("INSERT INTO Article (nom, description, prix, auteur_id, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdis", $nom, $desc, $prix, $user_id, $img);
    
    if ($stmt->execute()) {
        $article_id = $mysqli->insert_id;
        // 2. Insertion Stock obligatoire [cite: 28, 92, 93]
        $stmt_s = $mysqli->prepare("INSERT INTO Stock (article_id, quantite) VALUES (?, ?)");
        $stmt_s->bind_param("ii", $article_id, $stock_qty);
        $stmt_s->execute();
        echo "<p style='color:green;'>Article mis en vente avec stock !</p>";
    }
}
?>

<h1>Vendre un article</h1>
<form method="POST">
    <input type="text" name="nom" placeholder="Nom" required><br><br>
    <textarea name="description" placeholder="Description" required></textarea><br><br>
    <input type="number" step="0.01" name="prix" placeholder="Prix (€)" required><br><br>
    <input type="number" name="stock" placeholder="Quantité en stock" min="1" required><br><br>
    <input type="text" name="image_url" placeholder="Lien image"><br><br>
    <button type="submit">Publier</button>
</form>

<?php require_once 'includes/footer.php'; ?>