-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 30 avr. 2025 à 15:45
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `watercontrol`
--

-- --------------------------------------------------------

--
-- Structure de la table `abonnement`
--

CREATE TABLE `abonnement` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prix` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `duree` varchar(255) DEFAULT '1 mois'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `abonnement`
--

INSERT INTO `abonnement` (`id`, `nom`, `prix`, `description`, `duree`) VALUES
(1, 'Gratuit', 100, 'Accès limité à certaines fonctionnalités', '1 mois'),
(2, 'Premium', 125, 'Accès complet aux fonctionnalités premium', '1 mois'),
(3, 'VIP', 150, 'Accès complet + support prioritaire', '1 mois');

-- --------------------------------------------------------

--
-- Structure de la table `capteurs`
--

CREATE TABLE `capteurs` (
  `id` int(11) NOT NULL,
  `debit` float DEFAULT NULL,
  `date_mesure` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `capteurs`
--

INSERT INTO `capteurs` (`id`, `debit`, `date_mesure`) VALUES
(1, 100, '2025-04-18 11:07:54'),
(2, 45.7, '2025-04-18 11:08:15'),
(3, 12, '2025-04-19 11:08:55'),
(4, 11.3, '2025-04-19 11:09:07'),
(5, 75, '2025-04-20 11:09:24'),
(6, 28.9, '2025-04-20 11:09:36'),
(7, 14, '2025-03-20 11:10:11'),
(8, 6, '2025-03-20 11:10:20'),
(9, 68.9, '2025-03-22 11:10:46'),
(10, 2.9, '2025-03-22 11:10:54'),
(11, 100, '2025-04-30 13:43:09'),
(12, 12.5, '2025-04-30 13:43:24'),
(13, NULL, '2025-04-29 13:44:01'),
(14, 19, '2025-04-29 13:44:01'),
(15, 25, '2025-04-29 13:44:58'),
(16, 19, '2025-04-29 13:44:58');

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

CREATE TABLE `paiements` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `abonnement_id` int(11) NOT NULL,
  `montant` int(11) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `date_paiement` datetime DEFAULT current_timestamp(),
  `date_expiration` datetime DEFAULT NULL,
  `statut` enum('réussi','échoué','en attente') DEFAULT 'en attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `paiements`
--

INSERT INTO `paiements` (`id`, `utilisateur_id`, `abonnement_id`, `montant`, `transaction_id`, `date_paiement`, `date_expiration`, `statut`) VALUES
(2, 2, 2, 5000, 'TEST_680906cc217c4', '2025-04-23 17:27:08', '2025-05-23 17:27:08', 'réussi'),
(3, 2, 2, 5000, 'TEST_68090a4a2bbc4', '2025-04-23 17:42:02', '2025-05-23 17:42:02', 'réussi');

-- --------------------------------------------------------

--
-- Structure de la table `parametres_utilisateur`
--

CREATE TABLE `parametres_utilisateur` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `notifications_email` tinyint(1) DEFAULT 1,
  `notifications_sms` tinyint(1) DEFAULT 0,
  `langue` varchar(10) DEFAULT 'fr',
  `theme` varchar(10) DEFAULT 'clair',
  `frequence_maj` int(11) DEFAULT 15
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `seuils`
--

CREATE TABLE `seuils` (
  `id` int(11) NOT NULL,
  `seuil` int(11) DEFAULT NULL,
  `utilisateur_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `seuils`
--

INSERT INTO `seuils` (`id`, `seuil`, `utilisateur_id`) VALUES
(1, 100, 1);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `image` text NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `can_set_seuil` tinyint(1) DEFAULT 0,
  `can_control_valve` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `abonnement` enum('gratuit','premium') DEFAULT 'gratuit',
  `date_abonnement` datetime DEFAULT NULL,
  `date_expiration` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `nom`, `adresse`, `mail`, `password`, `image`, `role`, `can_set_seuil`, `can_control_valve`, `is_active`, `abonnement`, `date_abonnement`, `date_expiration`) VALUES
(1, 'admin', 'abomey', 'admin@gmail.com', '$2y$10$I3w2oYcDugcL3KN5qE3vyuMNco9ow1q6PtwFNtUsyXamynIVarmha', '', 'admin', 1, 1, 1, 'gratuit', NULL, NULL),
(2, 'chadas', 'abomey', 'chadasglele@gmail.com', '$2y$10$oSFtdTLZ7bV2kJeRog2Iluc3Z.MhbREq1zhJ/67zKnXg1KHp2/q6y', '', 'user', 0, 0, 0, 'premium', '2025-04-23 16:42:02', '2025-05-23 17:42:02');

-- --------------------------------------------------------

--
-- Structure de la table `vanne_statut`
--

CREATE TABLE `vanne_statut` (
  `id` int(11) NOT NULL,
  `statut` tinyint(1) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `utilisateur_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `vanne_statut`
--

INSERT INTO `vanne_statut` (`id`, `statut`, `updated_at`, `utilisateur_id`) VALUES
(1, 1, '2025-04-18 09:55:43', 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `abonnement`
--
ALTER TABLE `abonnement`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `capteurs`
--
ALTER TABLE `capteurs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `abonnement_id` (`abonnement_id`);

--
-- Index pour la table `parametres_utilisateur`
--
ALTER TABLE `parametres_utilisateur`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `seuils`
--
ALTER TABLE `seuils`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_utilisateur_seuil` (`utilisateur_id`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `vanne_statut`
--
ALTER TABLE `vanne_statut`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_utilisateur_vanne` (`utilisateur_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `abonnement`
--
ALTER TABLE `abonnement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `capteurs`
--
ALTER TABLE `capteurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `paiements`
--
ALTER TABLE `paiements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `parametres_utilisateur`
--
ALTER TABLE `parametres_utilisateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `seuils`
--
ALTER TABLE `seuils`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `vanne_statut`
--
ALTER TABLE `vanne_statut`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD CONSTRAINT `paiements_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`),
  ADD CONSTRAINT `paiements_ibfk_2` FOREIGN KEY (`abonnement_id`) REFERENCES `abonnement` (`id`);

--
-- Contraintes pour la table `parametres_utilisateur`
--
ALTER TABLE `parametres_utilisateur`
  ADD CONSTRAINT `parametres_utilisateur_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `seuils`
--
ALTER TABLE `seuils`
  ADD CONSTRAINT `fk_utilisateur_seuil` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`),
  ADD CONSTRAINT `seuils_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `vanne_statut`
--
ALTER TABLE `vanne_statut`
  ADD CONSTRAINT `fk_utilisateur_vanne` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
