-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 29 avr. 2025 à 05:36
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
-- Base de données : `pi_devv_new`
--

-- --------------------------------------------------------

--
-- Structure de la table `bienetre`
--

CREATE TABLE `bienetre` (
  `id` int(11) NOT NULL,
  `poubelle_id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `review` longtext NOT NULL,
  `rate` int(11) NOT NULL,
  `sentiment` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `camion`
--

CREATE TABLE `camion` (
  `id` int(11) NOT NULL,
  `matricule` varchar(255) NOT NULL,
  `capacite` double NOT NULL,
  `etat` varchar(255) NOT NULL,
  `type_moteur` varchar(255) DEFAULT NULL,
  `emission_co2` double DEFAULT NULL,
  `consommation` double DEFAULT NULL,
  `annee_fabrication` int(11) DEFAULT NULL,
  `kilometrage` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `conteneur`
--

CREATE TABLE `conteneur` (
  `id` int(11) NOT NULL,
  `type_dechet` varchar(255) NOT NULL,
  `capacite` double NOT NULL,
  `poids_actuel` double NOT NULL,
  `emplacement` varchar(255) NOT NULL,
  `zone` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `conteneur`
--

INSERT INTO `conteneur` (`id`, `type_dechet`, `capacite`, `poids_actuel`, `emplacement`, `zone`) VALUES
(1, 'Plastique', 500, 125, 'Rue de la République', 'Nord'),
(2, 'Plastique', 750, 500, 'Avenue Jean Jaurès', 'Sud'),
(3, 'Plastique', 300, 285, 'Boulevard Victor Hugo', 'Est'),
(4, 'Plastique', 600, 150, 'Place du Marché', 'Ouest'),
(5, 'Plastique', 450, 380, 'Rue Centrale', 'Centre'),
(6, 'Verre', 800, 720, 'Place de la Mairie', 'Nord'),
(8, 'Verre', 500, 450, 'Avenue des Roses', 'Est'),
(9, 'Verre', 700, 525, 'Impasse du Moulin', 'Ouest'),
(10, 'Verre', 550, 275, 'Boulevard Principal', 'Centre'),
(11, 'Papier', 400, 100, 'Rue des Écoles', 'Nord'),
(12, 'Papier', 600, 580, 'Avenue des Fleurs', 'Sud'),
(13, 'Papier', 350, 175, 'Rue du Stade', 'Est'),
(14, 'Papier', 500, 250, 'Boulevard des Arts', 'Ouest'),
(15, 'Papier', 450, 405, 'Place Centrale', 'Centre'),
(16, 'Métal', 700, 630, 'Rue de l\'Industrie', 'Nord'),
(17, 'Métal', 550, 137, 'Avenue du Parc', 'Sud'),
(18, 'Métal', 400, 320, 'Place du Château', 'Est'),
(19, 'Métal', 650, 455, 'Boulevard des Sports', 'Ouest'),
(20, 'Métal', 500, 375, 'Rue du Marché', 'Centre'),
(21, 'Organique', 300, 270, 'Rue des Jardins', 'Nord'),
(22, 'Organique', 450, 360, 'Allée des Peupliers', 'Sud'),
(23, 'Organique', 250, 125, 'Place du Village', 'Est'),
(24, 'Organique', 400, 380, 'Rue des Vignes', 'Ouest'),
(25, 'Organique', 350, 315, 'Avenue des Champs', 'Centre'),
(26, 'Électronique', 600, 330, 'Zone Industrielle Nord', 'Nord'),
(27, 'Électronique', 500, 450, 'Rue Technologique', 'Sud'),
(28, 'Électronique', 400, 80, 'Avenue de l\'Innovation', 'Est'),
(29, 'Électronique', 550, 495, 'Quartier des Entreprises', 'Ouest'),
(30, 'Électronique', 450, 225, 'Boulevard Digital', 'Centre'),
(31, 'Autre', 350, 280, 'Rue Divers', 'Nord'),
(32, 'Autre', 400, 120, 'Avenue Mixte', 'Sud'),
(33, 'Autre', 300, 180, 'Place Polyvalente', 'Est'),
(34, 'Autre', 450, 405, 'Boulevard Composite', 'Ouest');

-- --------------------------------------------------------

--
-- Structure de la table `dechet`
--

CREATE TABLE `dechet` (
  `id` int(11) NOT NULL,
  `camion_id` int(11) NOT NULL,
  `type_dechet` varchar(255) NOT NULL,
  `poids` double NOT NULL,
  `date_depot` date NOT NULL,
  `favori` tinyint(1) NOT NULL,
  `traite` tinyint(1) NOT NULL,
  `date_traitement` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `image`
--

CREATE TABLE `image` (
  `id` int(11) NOT NULL,
  `image_name` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `parking`
--

CREATE TABLE `parking` (
  `id` int(11) NOT NULL,
  `camion_id` int(11) NOT NULL,
  `conteneur_id` int(11) NOT NULL,
  `date_entree` datetime NOT NULL,
  `description` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `poubelle`
--

CREATE TABLE `poubelle` (
  `id` int(11) NOT NULL,
  `localisation` varchar(255) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `niveau_remplissage` double NOT NULL,
  `type` varchar(50) NOT NULL,
  `statut` varchar(50) NOT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reclamation`
--

CREATE TABLE `reclamation` (
  `user_id` int(11) DEFAULT NULL,
  `ID_rec` int(11) NOT NULL,
  `type_rec` varchar(255) NOT NULL,
  `reclamation` longtext NOT NULL,
  `date_rec` datetime NOT NULL DEFAULT current_timestamp(),
  `etat_rec` varchar(50) NOT NULL DEFAULT 'Pending',
  `reponse` longtext DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reclamation`
--

INSERT INTO `reclamation` (`user_id`, `ID_rec`, `type_rec`, `reclamation`, `date_rec`, `etat_rec`, `reponse`, `address`, `latitude`, `longitude`, `photo`, `updated_at`) VALUES
(1, 1, 'Éclairage public', 'gfxjxfgjxfj', '2025-04-28 22:38:27', 'Pending', NULL, 'Bab Souika, Délégation Bab Souika, Tunis, Gouvernorat Tunis, 1006, Tunisie', 36.805271408152, 10.167957213148, 'capturedecran20250407023700-68100363d385f.png', NULL),
(2, 2, 'Propreté', 'chgvnb,', '2025-04-28 23:56:32', 'Pending', NULL, 'Cité Ibn Khaldoun, Cite Ibn Khaldoun I, Délégation El Omrane Supérieur, Tunis, Gouvernorat Tunis, 2062, Tunisie', 36.826984666243, 10.139989854973, 'capturedecran20250407023700-681015b08642c.png', NULL),
(2, 3, 'Espaces verts', 'rtsusxjxj', '2025-04-29 00:03:02', 'Pending', NULL, 'Sidi Mosbah, Délégation La Nouvelle Médina, Gouvernorat Ben Arous, 2014, Tunisie', 36.742075558876, 10.238345793573, 'capturedecran20250407024408-68101736b7f16.png', NULL),
(2, 4, 'Voirie', 'ytfyvvy', '2025-04-29 00:07:02', 'Pending', NULL, 'Route Nationale Bizerte - Tunis, El Bouhaira, Délégation Cité El Khadra, Tunis, Gouvernorat Tunis, 1073, Tunisie', 36.814177343002, 10.189343528287, 'capturedecran20250407162617-68101826b1ddf.png', NULL),
(3, 5, 'Propreté', 'azeazeaz', '2025-04-29 03:31:53', 'Pending', NULL, 'Bab El Assel, Délégation Bab Souika, Tunis, Gouvernorat Tunis, 1005, Tunisie', 36.81137374099, 10.166942580036, 'capturedecran20250407023700-681048295b14e.png', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `signabstract`
--

CREATE TABLE `signabstract` (
  `user_id` int(11) DEFAULT NULL,
  `ID_signa` int(11) NOT NULL,
  `type_sign` varchar(255) DEFAULT NULL,
  `temps_sign` datetime DEFAULT current_timestamp(),
  `zone` varchar(255) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `etat_sign` varchar(50) NOT NULL DEFAULT 'Pending',
  `feedback` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `temp_email`
--

CREATE TABLE `temp_email` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(180) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` varchar(180) NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
  `password` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `is_verified` tinyint(1) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `verification_token` varchar(255) DEFAULT NULL,
  `email_verification_token` varchar(255) DEFAULT NULL,
  `temp_email` varchar(180) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `bio` varchar(255) DEFAULT NULL,
  `theme` varchar(50) DEFAULT NULL,
  `language` varchar(50) DEFAULT NULL,
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`)),
  `last_login` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `first_name`, `last_name`, `is_verified`, `reset_token`, `verification_token`, `email_verification_token`, `temp_email`, `avatar_url`, `phone_number`, `address`, `city`, `postal_code`, `birth_date`, `bio`, `theme`, `language`, `preferences`, `last_login`) VALUES
(1, 'messaoudi.borhen@esprit.tn', '[\"ROLE_ADMIN\"]', '$2y$13$070tdymMbEaPF1nCEUnK1ujwGzDO7ouYedML6Agb7J1oL6qRlZ.y2', 'Messaoudi', 'Borhen', 1, NULL, NULL, NULL, NULL, 'https://static.vecteezy.com/system/resources/thumbnails/048/216/761/small/modern-male-avatar-with-black-hair-and-hoodie-illustration-free-png.png', NULL, NULL, NULL, NULL, '2007-06-29', NULL, 'light', NULL, '{\"notifications\":false,\"newsletter\":false}', NULL),
(2, 'Zied.zied@esprit.tn', '[\"ROLE_ADMIN\"]', '$2y$13$wC38FITrGaUMYAaKntwuReB7lF2v383Tx9YLvyqaD/ATDTNEKd9je', 'Zied', 'Zied', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'stoustou419@gmail.com', '[\"ROLE_USER\"]', '$2y$13$M.DyllSDfYh/Vj.ugjTnB.Oevb1jvKWnaDa/2BrJwy5Qw8n0IdUaW', 'Stou', 'Stou', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `bienetre`
--
ALTER TABLE `bienetre`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_E8286F42F231B082` (`poubelle_id`);

--
-- Index pour la table `camion`
--
ALTER TABLE `camion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_5DD566EC12B2DC9C` (`matricule`);

--
-- Index pour la table `conteneur`
--
ALTER TABLE `conteneur`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `dechet`
--
ALTER TABLE `dechet`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_53C0FC603A706D3` (`camion_id`);

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `image`
--
ALTER TABLE `image`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `parking`
--
ALTER TABLE `parking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B237527A3A706D3` (`camion_id`),
  ADD KEY `IDX_B237527AF7EBA077` (`conteneur_id`);

--
-- Index pour la table `poubelle`
--
ALTER TABLE `poubelle`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `reclamation`
--
ALTER TABLE `reclamation`
  ADD PRIMARY KEY (`ID_rec`),
  ADD KEY `IDX_CE606404A76ED395` (`user_id`);

--
-- Index pour la table `signabstract`
--
ALTER TABLE `signabstract`
  ADD PRIMARY KEY (`ID_signa`),
  ADD KEY `IDX_B415F821A76ED395` (`user_id`);

--
-- Index pour la table `temp_email`
--
ALTER TABLE `temp_email`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_7132DD75A76ED395` (`user_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `bienetre`
--
ALTER TABLE `bienetre`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `camion`
--
ALTER TABLE `camion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `conteneur`
--
ALTER TABLE `conteneur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT pour la table `dechet`
--
ALTER TABLE `dechet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `image`
--
ALTER TABLE `image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `parking`
--
ALTER TABLE `parking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `poubelle`
--
ALTER TABLE `poubelle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reclamation`
--
ALTER TABLE `reclamation`
  MODIFY `ID_rec` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `signabstract`
--
ALTER TABLE `signabstract`
  MODIFY `ID_signa` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `temp_email`
--
ALTER TABLE `temp_email`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `bienetre`
--
ALTER TABLE `bienetre`
  ADD CONSTRAINT `FK_E8286F42F231B082` FOREIGN KEY (`poubelle_id`) REFERENCES `poubelle` (`id`);

--
-- Contraintes pour la table `dechet`
--
ALTER TABLE `dechet`
  ADD CONSTRAINT `FK_53C0FC603A706D3` FOREIGN KEY (`camion_id`) REFERENCES `camion` (`id`);

--
-- Contraintes pour la table `parking`
--
ALTER TABLE `parking`
  ADD CONSTRAINT `FK_B237527A3A706D3` FOREIGN KEY (`camion_id`) REFERENCES `camion` (`id`),
  ADD CONSTRAINT `FK_B237527AF7EBA077` FOREIGN KEY (`conteneur_id`) REFERENCES `conteneur` (`id`);

--
-- Contraintes pour la table `reclamation`
--
ALTER TABLE `reclamation`
  ADD CONSTRAINT `FK_CE606404A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `signabstract`
--
ALTER TABLE `signabstract`
  ADD CONSTRAINT `FK_B415F821A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `temp_email`
--
ALTER TABLE `temp_email`
  ADD CONSTRAINT `FK_7132DD75A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
