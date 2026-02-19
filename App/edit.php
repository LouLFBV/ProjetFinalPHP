<?php
require_once 'includes/header.php';
checkConnexion();

$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
$msg = "";

// 1. Récupération de l'article ET de son stock
$stmt = $mysqli->prepare("SELECT Article.*, Stock.quantite FROM Article 
                          LEFT JOIN Stock ON Article.id = Stock.article_id 
                          WHERE Article.id = ?");
$stmt->bind_param("i", $article_id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

// Vérification des droits
if (!$article || ($article['auteur_id'] != $user_id && !$is_admin)) {
    header("Location: index.php");
    exit;
}

// 2. Logique de Suppression
if (isset($_POST['delete'])) {
    $mysqli->query("DELETE FROM Article WHERE id = $article_id");
    header("Location: account.php?msg=Article supprimé");
    exit;
}

// 3. Logique de Modification
if (isset($_POST['update'])) {
    $nom = $mysqli->real_escape_string($_POST['nom']);
    $desc = $mysqli->real_escape_string($_POST['description']);
    $prix = floatval($_POST['prix']);
    $image_url = $mysqli->real_escape_string($_POST['image_url']); // <-- Nouvelle variable
    $cat_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $nouveau_stock = intval($_POST['stock']);

    // Mise à jour Article (Ajout de image_url dans la requête)
    $upd = $mysqli->prepare("UPDATE Article SET nom = ?, description = ?, prix = ?, category_id = ?, image_url = ? WHERE id = ?");
    $upd->bind_param("ssdisi", $nom, $desc, $prix, $cat_id, $image_url, $article_id);
    $res_art = $upd->execute();
    
    // Mise à jour Stock
    $check_stock = $mysqli->query("SELECT article_id FROM Stock WHERE article_id = $article_id");
    if ($check_stock->num_rows > 0) {
        $upd_stock = $mysqli->prepare("UPDATE Stock SET quantite = ? WHERE article_id = ?");
        $upd_stock->bind_param("ii", $nouveau_stock, $article_id);
    } else {
        $upd_stock = $mysqli->prepare("INSERT INTO Stock (quantite, article_id) VALUES (?, ?)");
        $upd_stock->bind_param("ii", $nouveau_stock, $article_id);
    }
    $res_stock = $upd_stock->execute();
    
    if ($res_art && $res_stock) {
        $msg = "<div style='background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px;'>✨ Article, image et stock mis à jour avec succès !</div>";
        // On rafraîchit les données pour l'affichage du formulaire
        $article['nom'] = $nom;
        $article['description'] = $desc;
        $article['prix'] = $prix;
        $article['image_url'] = $image_url;
        $article['category_id'] = $cat_id;
        $article['quantite'] = $nouveau_stock;
    }
}
?>

<div style="max-width: 800px; margin: 40px auto; padding: 0 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>Modifier l'annonce</h1>
        <a href="account.php" class="btn-view" style="text-decoration: none;">Annuler</a>
    </div>

    <?php echo $msg; ?>

    <div class="auth-card" style="max-width: 100%;">
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label>Titre de l'article</label>
                <input type="text" name="nom" value="<?php echo htmlspecialchars($article['nom']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Lien de l'image (URL)</label>
                <input type="url" name="image_url" value="<?php echo htmlspecialchars($article['image_url']); ?>" placeholder="https://exemple.com/image.jpg">
                <?php if(!empty($article['image_url'])): ?>
                    <small>Aperçu actuel : <a href="<?php echo $article['image_url']; ?>" target="_blank">Voir l'image</a></small>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Description détaillée</label>
                <textarea name="description" rows="6" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-family:inherit;" required><?php echo htmlspecialchars($article['description']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Catégorie</label>
                <select name="category_id" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; background:white;">
                    <option value="">-- Sans catégorie --</option>
                    <?php
                    $cats = $mysqli->query("SELECT * FROM Category ORDER BY nom ASC");
                    while($c = $cats->fetch_assoc()):
                        $sel = ($c['id'] == $article['category_id']) ? 'selected' : '';
                        echo "<option value='{$c['id']}' $sel>{$c['nom']}</option>";
                    endwhile;
                    ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Prix de vente (€)</label>
                    <input type="number" step="0.01" name="prix" value="<?php echo $article['prix']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Quantité disponible (Stock)</label>
                    <input type="number" name="stock" value="<?php echo $article['quantite']; ?>" min="0" required>
                </div>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 15px;">
                <button type="submit" name="update" class="btn-submit" style="flex: 2;">
                    💾 Enregistrer les modifications
                </button>
                
                <button type="submit" name="delete" class="btn-submit" 
                        onclick="return confirm('Attention : Cette action est irréversible. Supprimer ?')" 
                        style="flex: 1; background: #fff; color: #dc3545; border: 1px solid #dc3545;">
                    🗑️ Supprimer
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>