<?php
require_once 'includes/header.php';
checkConnexion();

$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

// 1. Récupération de l'article ET de son stock (via une JOIN)
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
    // Note: Si tes clés étrangères sont bien configurées avec ON DELETE CASCADE, 
    // supprimer l'article supprimera le stock automatiquement.
    $mysqli->query("DELETE FROM Article WHERE id = $article_id");
    header("Location: account.php");
    exit;
}

// 3. Logique de Modification
if (isset($_POST['update'])) {
    $nom = $_POST['nom'];
    $desc = $_POST['description'];
    $prix = $_POST['prix'];
    $cat_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $nouveau_stock = intval($_POST['stock']);

    // UNE SEULE REQUÊTE UPDATE POUR ARTICLE (Celle avec category_id)
    $upd = $mysqli->prepare("UPDATE Article SET nom = ?, description = ?, prix = ?, category_id = ? WHERE id = ?");
    $upd->bind_param("ssdii", $nom, $desc, $prix, $cat_id, $article_id);
    $res_art = $upd->execute();
    
    // B. Mise à jour ou INSERTION du stock
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
        echo "<p style='color:green; background:#eaffea; padding:10px;'>Article, catégorie et stock mis à jour !</p>";
        $article['nom'] = $nom;
        $article['description'] = $desc;
        $article['prix'] = $prix;
        $article['category_id'] = $cat_id; // Mise à jour de la variable locale
        $article['quantite'] = $nouveau_stock;
    }
}
?>

<h1>Modifier l'article</h1>



<form method="POST">
    <label>Nom de l'article :</label><br>
    <input type="text" name="nom" value="<?php echo htmlspecialchars($article['nom']); ?>" required style="width:100%; padding:8px;"><br><br>
    
    <label>Description :</label><br>
    <textarea name="description" required style="width:100%; height:100px; padding:8px;"><?php echo htmlspecialchars($article['description']); ?></textarea><br><br>
    
    <label>Catégorie :</label><br>
    <select name="category_id" style="padding:8px; width:100%;">
        <option value="">-- Choisir une catégorie --</option>
        <?php
        $cats = $mysqli->query("SELECT * FROM Category ORDER BY nom ASC");
        while($c = $cats->fetch_assoc()):
            $sel = ($c['id'] == $article['category_id']) ? 'selected' : '';
            echo "<option value='{$c['id']}' $sel>{$c['nom']}</option>";
        endwhile;
        ?>
    </select><br><br>

    <label>Prix (€) :</label><br>
    <input type="number" step="0.01" name="prix" value="<?php echo $article['prix']; ?>" required style="padding:8px;"><br><br>
    
    <label>Quantité en stock :</label><br>
    <input type="number" name="stock" value="<?php echo $article['quantite']; ?>" min="0" required style="padding:8px;"><br><br>
    
    <button type="submit" name="update" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">
        Sauvegarder les modifications
    </button>
    
    <button type="submit" name="delete" onclick="return confirm('Supprimer définitivement cet article ?')" 
            style="background: none; border: 1px solid red; color:red; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-left: 10px;">
        Supprimer l'article
    </button>
</form>

<p style="margin-top: 20px;"><a href="account.php">← Retour à mon profil</a></p>

<?php require_once 'includes/footer.php'; ?>