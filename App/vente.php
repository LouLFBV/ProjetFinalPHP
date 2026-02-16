<?php
require_once 'includes/header.php';

// Protection de la page : redirection si non connecté 
checkConnexion();

$message = "";

// Logique de traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $image_url = $_POST['image_url'] ?? '';
    $user_id = $_SESSION['user_id'];

    // Insertion dans la table Article [cite: 69, 83]
    $stmt = $mysqli->prepare("INSERT INTO Article (nom, description, prix, auteur_id, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdis", $nom, $description, $prix, $user_id, $image_url);
    
    if ($stmt->execute()) {
        $message = "<p style='color:green;'>Votre article a été mis en vente avec succès !</p>";
    } else {
        $message = "<p style='color:red;'>Erreur lors de la mise en vente.</p>";
    }
}
?>

<h1>Mettre un article en vente</h1>

<?php echo $message; ?>

<form method="POST" action="vente.php">
    <div>
        <label>Nom de l'article :</label><br>
        <input type="text" name="nom" placeholder="Ex: Gourde isotherme" required>
    </div>
    <br>
    <div>
        <label>Description :</label><br>
        <textarea name="description" rows="5" placeholder="Décrivez votre objet..." required></textarea>
    </div>
    <br>
    <div>
        <label>Prix (€) :</label><br>
        <input type="number" step="0.01" name="prix" placeholder="0.00" required>
    </div>
    <br>
    <div>
        <label>URL de l'image (optionnel) :</label><br>
        <input type="text" name="image_url" placeholder="http://...">
    </div>
    <br>
    <button type="submit">Publier l'annonce</button>
</form>

<?php require_once 'includes/footer.php'; ?>