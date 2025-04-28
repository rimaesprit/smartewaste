-- Création de la base de données (à exécuter dans phpMyAdmin)
CREATE DATABASE IF NOT EXISTS `smartwaste` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Sélectionner la base de données
USE `smartwaste`;

-- Création de la table des camions
CREATE TABLE IF NOT EXISTS `camion` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `matricule` VARCHAR(255) NOT NULL,
  `modele` VARCHAR(255) NOT NULL,
  `capacite` DOUBLE PRECISION NOT NULL,
  `type_carburant` VARCHAR(255) NOT NULL,
  `etat` VARCHAR(50) NOT NULL,
  `date_acquisition` DATE DEFAULT NULL,
  `derniere_maintenance` DATE DEFAULT NULL,
  `prochaine_maintenance` DATE DEFAULT NULL,
  `kilometrage` INT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_3B46967E61B51B` (`matricule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Création de la table des déchets
CREATE TABLE IF NOT EXISTS `dechet` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `camion_id` INT NOT NULL,
  `type_dechet` VARCHAR(255) NOT NULL,
  `poids` DOUBLE PRECISION NOT NULL,
  `date_depot` DATE NOT NULL,
  `favori` TINYINT(1) NOT NULL DEFAULT 0,
  `traite` TINYINT(1) NOT NULL DEFAULT 0,
  `date_traitement` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_872DDD1132B446C` (`camion_id`),
  CONSTRAINT `FK_872DDD1132B446C` FOREIGN KEY (`camion_id`) REFERENCES `camion` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion de données de test pour les camions
INSERT INTO `camion` (`matricule`, `modele`, `capacite`, `type_carburant`, `etat`, `date_acquisition`) VALUES
('CAM-2023-001', 'Renault Trucks D', 12.5, 'Diesel', 'En service', '2023-01-15'),
('CAM-2023-002', 'Mercedes-Benz Actros', 18.0, 'Diesel', 'En service', '2023-02-20'),
('CAM-2023-003', 'Volvo FE Electric', 10.0, 'Électrique', 'En service', '2023-03-10'),
('CAM-2023-004', 'Scania P-Series', 15.0, 'Diesel', 'En maintenance', '2023-01-05'),
('CAM-2023-005', 'MAN TGM', 14.0, 'Diesel', 'Hors service', '2022-11-12');

-- Insertion de données de test pour les déchets
INSERT INTO `dechet` (`camion_id`, `type_dechet`, `poids`, `date_depot`, `favori`, `traite`, `date_traitement`) VALUES
(1, 'Verre', 1.2, '2023-07-01', 0, 1, '2023-07-02 10:30:00'),
(1, 'Plastique', 0.8, '2023-07-01', 0, 1, '2023-07-02 11:15:00'),
(2, 'Carton', 1.5, '2023-07-01', 1, 1, '2023-07-02 12:00:00'),
(2, 'Métaux', 0.5, '2023-07-02', 0, 1, '2023-07-03 09:45:00'),
(3, 'Organique', 2.0, '2023-07-02', 0, 0, NULL); 