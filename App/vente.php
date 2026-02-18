<?php
require_once 'includes/header.php';
checkConnexion(); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $desc = $_POST['description'];
    $prix = $_POST['prix'];
    $stock_qty = intval($_POST['stock']);
    $img = $_POST['image_url'] ?? '';
    // On récupère la catégorie (peut être nulle si rien n'est choisi)
    $cat_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $user_id = $_SESSION['user_id'];

    // 1. Insertion Article avec category_id
    $stmt = $mysqli->prepare("INSERT INTO Article (nom, description, prix, auteur_id, image_url, category_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdisi", $nom, $desc, $prix, $user_id, $img, $cat_id);
    
    if ($stmt->execute()) {
        $article_id = $mysqli->insert_id;
        // 2. Insertion Stock obligatoire
        $stmt_s = $mysqli->prepare("INSERT INTO Stock (article_id, quantite) VALUES (?, ?)");
        $stmt_s->bind_param("ii", $article_id, $stock_qty);
        $stmt_s->execute();
        
        echo "<p style='color:green; background:#eaffea; padding:10px; border-radius:5px;'>✅ Article mis en vente avec succès !</p>";
    } else {
        echo "<p style='color:red;'>Erreur lors de la mise en vente.</p>";
    }
}
?>

<h1>Vendre un article</h1>

<form method="POST" style="max-width: 500px; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
    <label>Nom de l'objet :</label><br>
    <input type="text" name="nom" placeholder="Ex: Clavier mécanique" required style="width:100%; padding:8px;"><br><br>
    
    <label>Description :</label><br>
    <textarea name="description" placeholder="Détaillez l'état de l'objet..." required style="width:100%; height:80px; padding:8px;"></textarea><br><br>

    <label>Catégorie :</label><br>
    <select name="category_id" style="width:100%; padding:8px;" required>
        <option value="">-- Sélectionner une catégorie --</option>
        <?php
        $cats = $mysqli->query("SELECT * FROM Category ORDER BY nom ASC");
        while($c = $cats->fetch_assoc()) {
            echo "<option value='{$c['id']}'>".htmlspecialchars($c['nom'])."</option>";
        }
        ?>
    </select><br><br>
    
    <div style="display: flex; gap: 10px;">
        <div style="flex: 1;">
            <label>Prix (€) :</label><br>
            <input type="number" step="0.01" name="prix" placeholder="0.00" required style="width:100%; padding:8px;">
        </div>
        <div style="flex: 1;">
            <label>Stock initial :</label><br>
            <input type="number" name="stock" placeholder="1" min="1" required style="width:100%; padding:8px;">
        </div>
    </div><br>

    <label>Lien de l'image (URL) :</label><br>
    <input type="text" name="image_url" placeholder="https://..." style="width:100%; padding:8px;"><br><br>
    
    <button type="submit" style="width:100%; background: #28a745; color: white; border: none; padding: 12px; border-radius: 4px; cursor: pointer; font-weight: bold;">
        Publier l'annonce
    </button>
</form>

<p style="margin-top: 15px;"><a href="index.php">← Retour à la boutique</a></p>

<?php require_once 'includes/footer.php'; ?>