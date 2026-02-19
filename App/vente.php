<?php
require_once 'includes/header.php';
checkConnexion(); 

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $mysqli->real_escape_string($_POST['nom']);
    $desc = $mysqli->real_escape_string($_POST['description']);
    $prix = floatval($_POST['prix']);
    $stock_qty = intval($_POST['stock']);
    $img = $mysqli->real_escape_string($_POST['image_url'] ?? '');
    $cat_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $user_id = $_SESSION['user_id'];

    $stmt = $mysqli->prepare("INSERT INTO Article (nom, description, prix, auteur_id, image_url, category_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdisi", $nom, $desc, $prix, $user_id, $img, $cat_id);
    
    if ($stmt->execute()) {
        $article_id = $mysqli->insert_id;
        $stmt_s = $mysqli->prepare("INSERT INTO Stock (article_id, quantite) VALUES (?, ?)");
        $stmt_s->bind_param("ii", $article_id, $stock_qty);
        $stmt_s->execute();
        
        $message = "<div class='alert-success' style='background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px;'>🚀 Félicitations ! Votre article est désormais en ligne.</div>";
    } else {
        $message = "<div class='alert-error'>❌ Une erreur est survenue lors de la publication.</div>";
    }
}
?>

<div style="max-width: 700px; margin: 40px auto; padding: 0 20px;">
    
    <div style="margin-bottom: 30px;">
        <h1 style="font-size: 2rem; margin-bottom: 10px;">Vendre un nouvel objet</h1>
        <p style="color: #666;">Remplissez les détails ci-dessous pour publier votre annonce sur la marketplace.</p>
    </div>

    <?php echo $message; ?>

    <div class="auth-card" style="max-width: 100%; text-align: left;">
        <form method="POST" class="auth-form">
            
            <div class="form-group">
                <label>Nom de l'objet</label>
                <input type="text" name="nom" placeholder="Ex: Sony WH-1000XM4..." required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Décrivez l'état, les fonctionnalités, les accessoires inclus..." required 
                          style="width:100%; height:120px; padding:12px; border:1px solid #ddd; border-radius:8px; font-family:inherit;"></textarea>
            </div>

            <div class="form-group">
                <label>Catégorie</label>
                <select name="category_id" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; background:white; cursor:pointer;">
                    <option value="">-- Choisir une catégorie --</option>
                    <?php
                    $cats = $mysqli->query("SELECT * FROM Category ORDER BY nom ASC");
                    while($c = $cats->fetch_assoc()) {
                        echo "<option value='{$c['id']}'>".htmlspecialchars($c['nom'])."</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Prix de vente (€)</label>
                    <input type="number" step="0.01" name="prix" placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label>Nombre d'unités</label>
                    <input type="number" name="stock" placeholder="1" min="1" required>
                </div>
            </div>

            <div class="form-group">
                <label>URL de l'image</label>
                <input type="url" name="image_url" placeholder="https://votre-image.com/photo.jpg">
                <small style="color:#999; display:block; margin-top:5px;">Utilisez un lien direct vers une image hébergée.</small>
            </div>
            
            <button type="submit" class="btn-submit" style="margin-top: 20px; background: #28a745; border: none;">
                📦 Mettre en vente l'article
            </button>
        </form>
    </div>

    <p style="text-align: center; margin-top: 20px;">
        <a href="index.php" style="text-decoration: none; color: #666;">← Annuler et retourner à la boutique</a>
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>