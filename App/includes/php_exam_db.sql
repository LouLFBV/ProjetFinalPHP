-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 19 fév. 2026 à 17:55
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `php_exam`
--

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(10,2) NOT NULL,
  `date_publication` timestamp NOT NULL DEFAULT current_timestamp(),
  `auteur_id` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`id`, `nom`, `description`, `prix`, `date_publication`, `auteur_id`, `image_url`, `category_id`) VALUES
(1, 'Manette PS3', 'Manette peu utilisée', 10.00, '2026-02-16 15:00:55', 1, NULL, 1),
(2, 'Iphone X', 'Iphone X avec nouvelle batterie.', 239.02, '2026-02-16 15:14:44', 1, 'https://encrypted-tbn1.gstatic.com/shopping?q=tbn:ANd9GcQ0jFDtru1hXeQ-3qiEnE6kaHIRpkxUVl-jGcIIb_JAV1sUx6x3FpuLhWAzpC8yZSQpTOQSH1LW4WDrDuAEW-9ueD6Mn_hdprM4zuzpQmCpmeX4xuEA_80TvQ', 1),
(3, 'Samsumg s25', 'Téléphone peut utiliser, remis à neuf.', 800.00, '2026-02-17 15:19:12', 3, 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxISEBIQEBAQEBASEA8QDw8QEA8PDw8QFRIWFhURFRUYHSggGBolGxUVITEhJSkrLi4uFx8zRDMxNygtLisBCgoKDg0OGhAQFy0fHR0tLS0tKy0tLS0tLSstLS0tLS0tKy0tLS0tLS0rMC0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAKgBLAMBIgACEQEDEQH/', NULL),
(4, 'nom', 'aa', 0.03, '2026-02-17 15:37:51', 1, 'https://encrypted-tbn1.gstatic.com/shopping?q=tbn:ANd9GcQ0jFDtru1hXeQ-3qiEnE6kaHIRpkxUVl-jGcIIb_JAV1sUx6x3FpuLhWAzpC8yZSQpTOQSH1LW4WDrDuAEW-9ueD6Mn_hdprM4zuzpQmCpmeX4xuEA_80TvQ', NULL),
(5, 'téléphone', 'test', 1000.00, '2026-02-17 20:12:42', 4, 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxISEBIQEBAQEBASEA8QDw8QEA8PDw8QFRIWFhURFRUYHSggGBolGxUVITEhJSkrLi4uFx8zRDMxNygtLisBCgoKDg0OGhAQFy0fHR0tLS0tKy0tLS0tLSstLS0tLS0tKy0tLS0tLS0rMC0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAKgBLAMBIgACEQEDEQH/', NULL),
(6, 'Manette de WII', 'Manette comme neuve', 20.00, '2026-02-18 17:05:44', 5, 'https://encrypted-tbn2.gstatic.com/shopping?q=tbn:ANd9GcSYF7qA_RF4zKwFNQzoCC6FsvTHqNgZt0cQvu7a7yEo2ja-dAgKMjGdt7alIdd4-c30Ulw73prhUlciuOh2PK_lUyYmVmo1ud6ajBbRzDJR2OBbdxG6ZSvX3g', NULL),
(7, 'Pull Nike', 'Pull très utilisé.', 15.00, '2026-02-19 14:29:21', 6, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRT58rLIwGytgOB3FkpXf2rQogDNOFyZIAsUw&s', 2);

-- --------------------------------------------------------

--
-- Structure de la table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `quantite` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `article_id`, `quantite`) VALUES
(17, 1, 7, 1);

-- --------------------------------------------------------

--
-- Structure de la table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `category`
--

INSERT INTO `category` (`id`, `nom`) VALUES
(1, 'Électronique'),
(2, 'Vêtements'),
(3, 'Maison'),
(4, 'Loisirs');

-- --------------------------------------------------------

--
-- Structure de la table `favorite`
--

CREATE TABLE `favorite` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `favorite`
--

INSERT INTO `favorite` (`id`, `user_id`, `article_id`) VALUES
(2, 4, 6),
(4, 1, 7);

-- --------------------------------------------------------

--
-- Structure de la table `invoice`
--

CREATE TABLE `invoice` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `date_achat` timestamp NOT NULL DEFAULT current_timestamp(),
  `adresse_facturation` varchar(255) NOT NULL,
  `ville_facturation` varchar(100) NOT NULL,
  `code_postal_facturation` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `invoice`
--

INSERT INTO `invoice` (`id`, `user_id`, `total`, `date_achat`, `adresse_facturation`, `ville_facturation`, `code_postal_facturation`) VALUES
(1, 1, 20.00, '2026-02-16 15:28:50', '', '', ''),
(2, 1, 478.00, '2026-02-17 08:44:41', '', '', ''),
(3, 1, 10.00, '2026-02-17 15:09:36', '', '', ''),
(4, 1, 0.03, '2026-02-17 19:37:26', '23 Rue de Peyandreau', 'Le Pian-Médoc', '22222'),
(5, 1, 0.03, '2026-02-17 19:37:40', '23 Rue de Peyandreau', 'Le Pian-Médoc', '22222'),
(6, 1, 180.00, '2026-02-18 17:23:50', '23 Rue de Peyandreau', 'Le Pian-Médoc', '22222'),
(7, 1, 100.00, '2026-02-18 17:44:56', '23 Rue de Peyandreau', 'Le Pian-Médoc', '22222'),
(8, 1, 20.00, '2026-02-19 13:57:46', '23 Rue de Peyandreau', 'Le Pian-Médoc', '22222'),
(9, 6, 10.03, '2026-02-19 14:31:12', '23 Rue de Peyandreau', 'Le Pian-Médoc', '22222');

-- --------------------------------------------------------

--
-- Structure de la table `invoice_item`
--

CREATE TABLE `invoice_item` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `article_id` int(11) DEFAULT NULL,
  `nom_article` varchar(255) DEFAULT NULL,
  `prix_unitaire` decimal(10,2) DEFAULT NULL,
  `quantite` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `invoice_item`
--

INSERT INTO `invoice_item` (`id`, `invoice_id`, `article_id`, `nom_article`, `prix_unitaire`, `quantite`) VALUES
(1, 7, NULL, 'téléphone', 100.00, 1),
(2, 8, 6, 'Manette de WII', 20.00, 1),
(3, 9, 4, 'nom', 0.03, 1),
(4, 9, 1, 'Manette PS3', 10.00, 1);

-- --------------------------------------------------------

--
-- Structure de la table `review`
--

CREATE TABLE `review` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `note` int(11) NOT NULL,
  `commentaire` text DEFAULT NULL,
  `date_publication` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `review`
--

INSERT INTO `review` (`id`, `article_id`, `user_id`, `note`, `commentaire`, `date_publication`) VALUES
(1, 6, 1, 5, 'Fonctionne parfaitement !', '2026-02-19 14:58:36');

-- --------------------------------------------------------

--
-- Structure de la table `stock`
--

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `quantite` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `stock`
--

INSERT INTO `stock` (`id`, `article_id`, `quantite`) VALUES
(1, 4, 1),
(2, 5, 15),
(3, 6, 16),
(4, 1, 3),
(5, 2, 6),
(6, 3, 5),
(7, 7, 3);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `balance` decimal(10,2) DEFAULT 100.00,
  `image_url` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `password`, `created_at`, `balance`, `image_url`, `role`) VALUES
(1, 'TestUser', 'test@exemple.com', '$2y$10$T2dKFJVYe9n5LoEPsFoDZ.yfsSzFnw1A/hOLzpiywpJnEsSjHxCqO', '2026-02-16 14:40:25', 792.91, NULL, 'user'),
(3, 'toto', 'toto@gmail.com', '$2y$10$Th3Oj4THcUrHVXd6pGrsIe3lQuoENDHncwNlJrBNa7utZwcFQ9rnO', '2026-02-17 15:17:58', 100.00, NULL, 'user'),
(4, 'admin', 'admin@secret.com', '$2y$10$EQwXBbMMrg0q3F7HgOeh6.JqsUtb2xyA.zOkipS7EdPQWvr9W9TqS', '2026-02-17 19:45:57', 100.00, 'https://i.pinimg.com/originals/8c/25/8a/8c258a005656cd086282bbc8e59f1de6.jpg', 'admin'),
(5, 'momo', 'momo@gmail.com', '$2y$10$A.clNdOgdOuxSGKDkPEt0OQll0rnOU/N9.37COlO24aOW/6cb4aNC', '2026-02-18 17:04:43', 100.00, NULL, 'user'),
(6, 'riri', 'ririo@exemple.com', '$2y$10$AcdpFCmBVDjB7ySgcjfGb.U/vnLZT1khcYvGU8nBmqUnpo1.QtbuS', '2026-02-19 14:20:08', 89.97, NULL, 'user');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auteur_id` (`auteur_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Index pour la table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `favorite`
--
ALTER TABLE `favorite`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `invoice_item`
--
ALTER TABLE `invoice_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `article`
--
ALTER TABLE `article`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `favorite`
--
ALTER TABLE `favorite`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `invoice_item`
--
ALTER TABLE `invoice_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `review`
--
ALTER TABLE `review`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `article`
--
ALTER TABLE `article`
  ADD CONSTRAINT `article_ibfk_1` FOREIGN KEY (`auteur_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `article_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `favorite`
--
ALTER TABLE `favorite`
  ADD CONSTRAINT `favorite_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorite_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `invoice_item`
--
ALTER TABLE `invoice_item`
  ADD CONSTRAINT `invoice_item_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_item_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
