# 🚀 Vendons-les. | Marketplace E-commerce

**Vendons-les.** est une plateforme de marketplace complète développée en PHP natif. Elle permet aux utilisateurs de mettre en vente des objets, de gérer leur solde via un portefeuille virtuel, de constituer un panier et de suivre l'historique détaillé de leurs factures avec précision (lieu, date et heure).

---

## 🛠️ Fonctionnalités principales

### 👤 Espace Utilisateur
* **Authentification** : Système d'inscription et de connexion sécurisé avec hachage des mots de passe via l'algorithme `BCRYPT`.
* **Profil** : Personnalisation des informations (pseudo, email) et gestion de l'avatar par URL.
* **Portefeuille** : Système de solde virtuel rechargeable pour simuler des transactions monétaires.

### 🛒 Marketplace & Panier
* **Catalogue** : Affichage dynamique des annonces avec filtrage par catégories.
* **Panier** : Gestion complète des achats (ajout, suppression, modification des quantités) et calcul du total TTC.
* **Favoris** : Liste de souhaits personnelle (Coup de ❤️) accessible depuis le tableau de bord.

### 📦 Vente & Administration
* **Mise en vente** : Formulaire dédié incluant la gestion des stocks initiaux et le choix de la catégorie.
* **Facturation** : Génération d'historiques d'achats détaillés incluant l'heure de transaction et l'adresse de livraison.
* **Panel Admin** : Interface sécurisée permettant la modération des comptes utilisateurs et des articles en ligne.

---

## 💻 Stack Technique

* **Backend** : PHP 8.2+
* **Base de données** : MariaDB 10.4 (Moteur de stockage InnoDB pour les relations)
* **Frontend** : HTML5 / CSS3 (Interface moderne avec une approche "Clean Design")
* **Sécurité** : 
    * Utilisation systématique de requêtes préparées (`mysqli::prepare`) contre les injections SQL.
    * Protection contre les failles XSS via le filtrage des sorties avec `htmlspecialchars()`.

---

## 📊 Architecture de la Base de Données

Le projet repose sur une base de données relationnelle **MariaDB** (via XAMPP) structurée pour garantir l'intégrité des transactions et la gestion dynamique des stocks.



### Modèle Logique de Données (MLD)

* **USER** (**id**, username, email, password, created_at, balance, image_url, role)
* **CATEGORY** (**id**, nom)
* **ARTICLE** (**id**, nom, description, prix, date_publication, image_url, #auteur_id, #category_id)
* **STOCK** (**id**, #article_id, quantite)
* **CART** (**id**, #user_id, #article_id, quantite)
* **FAVORITE** (**id**, #user_id, #article_id)
* **INVOICE** (**id**, #user_id, total, date_achat, adresse_facturation, ville_facturation, code_postal_facturation)
* **INVOICE_ITEM** (**id**, #invoice_id, #article_id, nom_article, prix_unitaire, quantite)
* **REVIEW** (**id**, #article_id, #user_id, note, commentaire, date_publication)
---

## ⚙️ Procédure d'Installation

### 1. Prérequis
* Un serveur local fonctionnel (XAMPP est recommandé).

### 2. Configuration de la base de données
1. Ouvrez votre interface **phpMyAdmin**.
2. Créez une nouvelle base de données nommée `vendons_les`.
3. Importez le fichier `.sql` fourni avec le projet (Dump SQL).

### 3. Connexion au serveur
Vérifiez et adaptez les accès dans le fichier `includes/db.php` si nécessaire :
```php
$mysqli = new mysqli("localhost", "root", "", "vendons_les");
```

Vous pouvez accéder au compte **admin** avec :
```
mail : admin@secret.com
mdp : 1234
```
## 📂 Structure du projet
```
PROJET_VENDONS_LES/
│
├── 📂 assets/                  # Ressources statiques
│   └── 📂 css/
│       └── 📄 style.css        # Charte graphique globale
│
├── 📂 includes/                # Cœur logique et composants
│   ├── 📄 db.php               # Connexion MySQLi à MariaDB
│   ├── 📄 functions.php        # Fonctions utilitaires (formatage, sécurité)
│   ├── 📄 header.php           # Barre de navigation et gestion de session
│   ├── 📄 footer.php           # Pied de page et scripts
│   └── 📄 php_exam_db.sql         # Base de données
│
├── 📄 index.php                # Boutique et accueil
├── 📄 login.php                # Authentification
├── 📄 logout.php               # Fin de session
├── 📄 register.php             # Inscription
│
├── 📄 account.php              # Dashboard utilisateur (Solde, Factures)
├── 📄 edit_profile.php         # Modification du profil (User)
│
├── 📄 vente.php                # Création d'annonces
├── 📄 edit.php                 # Modification d'annonces
├── 📄 detail.php               # Fiche produit et avis
│
├── 📄 cart.php                 # Gestion du panier
├── 📄 validate.php             # Paiement et génération de facture
│
├── 📄 admin.php                # Panel d'administration globale
└── 📄 edit_user.php            # Gestion admin des utilisateurs
```
---
*Projet réalisé par Lou Lefebvre, Maël Caetano et Hugo Cabanes - 2026*