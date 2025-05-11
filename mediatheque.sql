-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 12 mai 2025 à 00:49
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
-- Base de données : `mediatheque`
--

-- --------------------------------------------------------

--
-- Structure de la table `loans`
--

CREATE TABLE `loans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `loan_date` datetime DEFAULT current_timestamp(),
  `due_date` datetime NOT NULL,
  `return_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `loans`
--

INSERT INTO `loans` (`id`, `user_id`, `media_id`, `loan_date`, `due_date`, `return_date`) VALUES
(4, 3, 1, '2025-04-28 16:30:00', '2025-05-12 16:30:00', '2025-05-10 11:15:22'),
(5, 5, 8, '2025-05-08 23:17:19', '2025-05-22 23:17:19', '2025-05-10 20:16:06'),
(6, 5, 10, '2025-05-08 23:17:26', '2025-05-22 23:17:26', '2025-05-10 20:16:08'),
(7, 5, 4, '2025-05-08 23:42:00', '2025-05-22 23:42:00', '2025-05-10 20:16:04'),
(8, 5, 2, '2025-05-08 23:49:58', '2025-05-22 23:49:58', '2025-05-10 20:16:10'),
(9, 5, 8, '2025-05-10 20:16:19', '2025-05-24 20:16:19', '2025-05-10 20:16:20'),
(11, 5, 8, '2025-05-12 00:25:14', '2025-05-26 00:25:14', '2025-05-12 00:25:15');

-- --------------------------------------------------------

--
-- Structure de la table `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` enum('book','dvd','game','music') NOT NULL,
  `creator` varchar(255) NOT NULL,
  `year` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `pages` int(11) DEFAULT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `genre` varchar(128) DEFAULT NULL,
  `platform` varchar(128) DEFAULT NULL,
  `artist` varchar(255) DEFAULT NULL,
  `album` varchar(255) DEFAULT NULL,
  `available` tinyint(1) DEFAULT 1,
  `added_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `media`
--

INSERT INTO `media` (`id`, `title`, `type`, `creator`, `year`, `description`, `isbn`, `pages`, `publisher`, `duration`, `genre`, `platform`, `artist`, `album`, `available`, `added_at`) VALUES
(1, 'Le Seigneur des Anneaux', 'book', 'J.R.R. Tolkien', 1954, 'Une ?pop?e fantastique classique', '9782267011258', 1216, 'Christian Bourgois', NULL, 'Fantasy', NULL, NULL, NULL, 1, '2025-05-08 18:36:27'),
(2, 'Harry Potter à l\'école des sorciers', 'book', 'J.K. Rowling', 1997, 'Premier tome de la série Harry Potter', '9782070643028', 308, 'Gallimard', NULL, 'Fantasy', NULL, NULL, NULL, 1, '2025-05-08 18:36:27'),
(3, '1984', 'book', 'George Orwell', 1949, 'Un roman dystopique sur la surveillance de masse', '9782070368228', 438, 'Gallimard', NULL, 'Science-fiction', NULL, NULL, NULL, 0, '2025-05-08 18:36:27'),
(4, 'Inception', 'dvd', 'Christopher Nolan', 2010, 'Un voleur qui s\'infiltre dans les r?ves', NULL, NULL, NULL, NULL, 'Science-fiction', NULL, NULL, NULL, 1, '2025-05-08 18:36:27'),
(5, 'The Dark Knight', 'dvd', 'Christopher Nolan', 2008, 'Batman affronte le Joker', NULL, NULL, NULL, NULL, 'Action', NULL, NULL, NULL, 0, '2025-05-08 18:36:27'),
(6, 'The Legend of Zelda: Breath of the Wild', 'game', 'Nintendo', 2017, 'Une aventure ?pique dans un monde ouvert', NULL, NULL, 'Nintendo', NULL, 'Aventure', NULL, NULL, NULL, 1, '2025-05-08 18:36:27'),
(7, 'The Witcher 3: Wild Hunt', 'game', 'CD Projekt RED', 2015, 'Un RPG d\'action bas? sur la s?rie de livres The Witcher', NULL, NULL, 'CD Projekt', NULL, 'RPG', NULL, NULL, NULL, 1, '2025-05-08 18:36:27'),
(8, 'Abbey Road', 'music', 'The Beatles', 1969, 'Le dernier album enregistr? par les Beatles', NULL, NULL, NULL, NULL, 'Rock', NULL, NULL, NULL, 1, '2025-05-08 18:36:27'),
(9, 'Thriller', 'music', 'Michael Jackson', 1982, 'L\'album le plus vendu de tous les temps', NULL, NULL, NULL, NULL, 'Pop', NULL, NULL, NULL, 0, '2025-05-08 18:36:27'),
(10, 'Dark Side of the Moon', 'music', 'Pink Floyd', 1973, 'Un album concept sur les pressions de la vie moderne', NULL, NULL, NULL, NULL, 'Rock progressif', NULL, NULL, NULL, 1, '2025-05-08 18:36:27'),
(11, 'Fallout 4', 'game', 'Bethesda Games Studio', 2015, 'Fallout 4 est un jeu d\'action-RPG post-apocalyptique où le joueur explore les terres désolées de Boston, construit des colonies et combat divers ennemis dans un monde ravagé par la guerre nucléaire.', NULL, NULL, 'Bethesda Softworks', NULL, 'action-RPG', 'PC, XBOX, PLAYSTATION', NULL, NULL, 0, '2025-05-10 21:28:31'),
(12, 'Le rouge et le noir', 'book', 'Stendhal', 1830, 'Romance', '', 0, '', NULL, NULL, NULL, NULL, NULL, 1, '2025-05-10 21:37:59'),
(13, 'Orgueil et préjugés ', 'book', 'Jane Austen ', 2000, 'Mme Bennet prête à tout pour marier ses cinq filles à un homme fortunée.', '', 60, '', NULL, NULL, NULL, NULL, NULL, 1, '2025-05-10 21:47:44'),
(14, 'Boule et Bill ', 'book', 'Jean Roba ', 1980, 'Bande dessinée sur les mésaventures d\'un petit garçon et son chien\r\n', '', 30, '', NULL, NULL, NULL, NULL, NULL, 1, '2025-05-10 21:52:54');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(64) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Jack', 'jack.duflantier@wanadoo.fr', '$2y$10$UHkjWn0F4.aZa7ZUt.4bI.mlMYx0aLUazApW594c6F.y9ylDYbR1W', 'user', '2025-05-12 00:35:24'),
(2, 'Steve', 'steve.pickaxe@minecraft.com', '$2y$10$q/G2nEIw/pGg0mFReNlX3ePaE.KZkf3ga.Yq0pkxNrXosGHGK0k4S', 'user', '2025-05-12 00:37:10'),
(3, 'SimTeammario', 'simon.taveirne@proton.me', '$2y$10$N1xeyAutUHO/EvoLDflZ/ur2pg/HJWDgLaWk3nRNX2vprQLTM9X12', 'admin', '2025-05-08 17:39:54'),
(4, 'Mira', 'Mira.diva@love.fr', '$2y$10$Q2F3tCcA9R99iM2xFGRYQ.X3x6vohve06K/CQSVQWR5UyVQ/hrVku', 'admin', '2025-05-12 00:39:05'),
(5, 'Pierre', 'pierre.caillou@gmail.com', '$2y$10$9euc4hJ4XJo9.V.r9uvDlex4H63TkEulawmMnpa32WMkx.668adeO', 'user', '2025-05-08 22:35:57'),
(6, 'Admin', 'Administrator@mediatheque.fr', '$2y$10$EJdJMSjkroGCNbNXMn0qTOR3dMCEGlvZxEe58Pe..5KRfXt48Xg1G', 'admin', '2025-05-12 00:31:38'),
(7, 'user', 'user@example.fr', '$2y$10$Zf6igcsUdgpQGBnkUuPdaecgvvo6tLTxnJ8.0C8n0KS1Wiudcl2Au', 'user', '2025-05-12 00:32:27');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `media_id` (`media_id`);

--
-- Index pour la table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
