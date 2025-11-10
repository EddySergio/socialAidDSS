-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 08, 2025 at 07:33 AM
-- Server version: 8.0.31
-- PHP Version: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gestion_stock`
--

-- --------------------------------------------------------

--
-- Table structure for table `article`
--

DROP TABLE IF EXISTS `article`;
CREATE TABLE IF NOT EXISTS `article` (
  `id_article` int NOT NULL AUTO_INCREMENT,
  `nom_article` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `quantite` int NOT NULL,
  `prix_uni` int NOT NULL,
  `prix_gros` int NOT NULL,
  `prix_glace` int NOT NULL,
  `prix_commande` int NOT NULL,
  `categorie` varchar(50) NOT NULL,
  `estArendre` enum('A rendre','Pas a rendre','','') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'A rendre',
  `id_fournisseur` int NOT NULL,
  PRIMARY KEY (`id_article`),
  UNIQUE KEY `nom_article` (`nom_article`),
  KEY `id_categorie` (`categorie`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `article`
--

INSERT INTO `article` (`id_article`, `nom_article`, `quantite`, `prix_uni`, `prix_gros`, `prix_glace`, `prix_commande`, `categorie`, `estArendre`, `id_fournisseur`) VALUES
(24, 'Eau vive GM pack', 1, 15912, 15912, 15912, 15912, 'Eau', 'A rendre', 2),
(25, 'Eau vive PM pack', 2, 12888, 12888, 12888, 12888, 'Eau', 'A rendre', 2),
(26, 'Cristaline 200cl pack', 5, 18540, 18540, 18540, 18540, 'Eau', 'A rendre', 2),
(27, 'Cristaline 100cl pack', 1, 14016, 14016, 14016, 14016, 'Eau', 'A rendre', 2),
(28, 'THB GM cageau', 72, 65040, 65040, 65040, 65040, 'Biere', 'A rendre', 2),
(29, 'THB 33cl CAN pack', 6, 72000, 72000, 72000, 72000, 'Biere', 'A rendre', 2),
(30, 'Fresh PM cageau', 6, 36000, 36000, 36000, 36000, 'Biere', 'A rendre', 2),
(31, 'Gold GM cageau', 3, 66000, 66000, 66000, 66000, 'Biere', 'A rendre', 2),
(32, 'Gold PM cageau', 3, 55296, 55296, 55296, 55296, 'Biere', 'A rendre', 2),
(33, 'GOLD 50Cl CAN cageau', 0, 105696, 105696, 105696, 105696, 'Biere', 'A rendre', 2),
(34, 'Beaufort 50cl CAN cageau', 0, 109872, 109872, 109872, 109872, 'Biere', 'A rendre', 2),
(35, 'THB GM CAN pack', 0, 98496, 98496, 98496, 98496, 'Biere', 'A rendre', 2),
(36, 'Beaufort 50cl cageau', 0, 70080, 70080, 70080, 70080, 'Biere', 'A rendre', 2),
(37, 'Beaufort 33cl cageau', 1, 68400, 68400, 68400, 68400, 'Biere', 'A rendre', 2),
(38, 'QUEENS cageau', 0, 62040, 62040, 62040, 62040, 'Biere', 'A rendre', 2),
(39, '150Cl PET pack', 1, 27108, 27108, 27108, 27108, 'Boisson', 'A rendre', 2),
(40, '50Cl PET pack', 0, 18000, 18000, 18000, 18000, 'Boisson', 'A rendre', 2),
(41, 'Cristal 50Cl pack', 0, 25920, 25920, 25920, 25920, 'Eau', 'A rendre', 2),
(42, 'XXL 35Cl PET pack', 0, 31176, 31176, 31176, 31176, 'Boisson', 'A rendre', 2),
(43, 'XXL 30Cl PET cageau', 0, 50400, 50400, 50400, 50400, 'Boisson', 'A rendre', 2),
(44, 'Booster 50 cl cageau', 0, 65040, 65040, 65040, 65040, 'Biere', 'A rendre', 2),
(46, 'black label', 10, 200000, 200000, 200000, 200000, 'Wisky', 'Pas a rendre', 2),
(48, 'John Peters', 10, 6000, 6000, 6000, 5000, 'Alcool', 'A rendre', 5),
(49, 'marcovitch', 40, 6000, 5500, 6000, 5000, 'Alcool', 'Pas a rendre', 5);

-- --------------------------------------------------------

--
-- Table structure for table `categorie_article`
--

DROP TABLE IF EXISTS `categorie_article`;
CREATE TABLE IF NOT EXISTS `categorie_article` (
  `id_categorie` int NOT NULL AUTO_INCREMENT,
  `categorie` varchar(50) NOT NULL,
  PRIMARY KEY (`id_categorie`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categorie_article`
--

INSERT INTO `categorie_article` (`id_categorie`, `categorie`) VALUES
(1, 'Alcool'),
(2, 'Wisky'),
(70, 'Boisson'),
(71, 'Biere'),
(72, 'Eau');

-- --------------------------------------------------------

--
-- Table structure for table `commande`
--

DROP TABLE IF EXISTS `commande`;
CREATE TABLE IF NOT EXISTS `commande` (
  `id_commande` int NOT NULL AUTO_INCREMENT,
  `id_article` int NOT NULL,
  `id_lscommande` int NOT NULL,
  `quantite` int NOT NULL,
  `prix` int NOT NULL,
  PRIMARY KEY (`id_commande`),
  KEY `id_article` (`id_article`),
  KEY `id_fournisseur` (`id_lscommande`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `commande`
--

INSERT INTO `commande` (`id_commande`, `id_article`, `id_lscommande`, `quantite`, `prix`) VALUES
(21, 39, 1, 10, 271080),
(22, 37, 1, 6, 410400),
(23, 39, 2, 5, 135540),
(24, 40, 2, 10, 180000),
(25, 24, 3, 5, 79560),
(26, 26, 3, 1, 18540),
(27, 48, 4, 10, 50000),
(28, 49, 4, 30, 150000);

-- --------------------------------------------------------

--
-- Table structure for table `fournisseur`
--

DROP TABLE IF EXISTS `fournisseur`;
CREATE TABLE IF NOT EXISTS `fournisseur` (
  `id_fournisseur` int NOT NULL AUTO_INCREMENT,
  `nom_fournisseur` varchar(50) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `adresse` varchar(50) NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_fournisseur`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `fournisseur`
--

INSERT INTO `fournisseur` (`id_fournisseur`, `nom_fournisseur`, `telephone`, `adresse`, `actif`) VALUES
(2, 'STAR', '0344878890', 'campus B5 batiment G porte 35', 1),
(4, 'simourai', '66666666', 'bota', 1),
(5, 'John Peters', '99999999', 'Morafeno', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lscommande`
--

DROP TABLE IF EXISTS `lscommande`;
CREATE TABLE IF NOT EXISTS `lscommande` (
  `id_lscommande` int NOT NULL,
  `id_fournisseur` int NOT NULL,
  `estLivre` enum('livre','non livre') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'non livre',
  `date_commande` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_livraison` date DEFAULT '1111-11-11',
  `total_commande` int NOT NULL,
  PRIMARY KEY (`id_lscommande`),
  KEY `id_fournisseur` (`id_fournisseur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `lscommande`
--

INSERT INTO `lscommande` (`id_lscommande`, `id_fournisseur`, `estLivre`, `date_commande`, `date_livraison`, `total_commande`) VALUES
(1, 2, 'livre', '2024-10-07 13:52:38', '2024-10-07', 681480),
(2, 2, 'livre', '2024-10-07 14:25:01', '2024-12-15', 315540),
(3, 2, 'livre', '2024-12-15 08:04:33', '2024-12-15', 98100),
(4, 5, 'non livre', '2024-12-16 06:39:38', '1111-11-11', 200000);

-- --------------------------------------------------------

--
-- Table structure for table `panier`
--

DROP TABLE IF EXISTS `panier`;
CREATE TABLE IF NOT EXISTS `panier` (
  `id_panier` int NOT NULL,
  `id_user` int NOT NULL,
  `paiement` float NOT NULL,
  `nom_client` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'inconnue',
  `date_vente` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total` int NOT NULL,
  `reste` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_panier`),
  KEY `id_user` (`id_user`),
  KEY `id_paiement` (`paiement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `panier`
--

INSERT INTO `panier` (`id_panier`, `id_user`, `paiement`, `nom_client`, `date_vente`, `total`, `reste`) VALUES
(1, 4, 2180000, 'brigitte', '2024-12-15 09:08:00', 225540, 0),
(2, 4, 110000, 'mazeur/ 034586245', '2024-12-16 09:26:07', 91680, 0),
(3, 4, 20000, 'test', '2024-12-16 08:44:37', 144216, 124216),
(4, 4, 30000, 'test', '2025-03-13 07:37:25', 27108, 0),
(5, 4, 30000, 'TEST / 999999999', '2025-11-07 11:05:43', 27108, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `mdp` varchar(30) NOT NULL,
  `type_user` enum('admin','caissier') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `mdp`, `type_user`) VALUES
(1, 'ornella', '1234', 'caissier'),
(4, 'sergio', '1234', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `vente`
--

DROP TABLE IF EXISTS `vente`;
CREATE TABLE IF NOT EXISTS `vente` (
  `id_vente` int NOT NULL AUTO_INCREMENT,
  `quantite` int NOT NULL,
  `prix` int NOT NULL,
  `id_article` int NOT NULL,
  `id_panier` int NOT NULL,
  `arendre` int NOT NULL,
  PRIMARY KEY (`id_vente`),
  KEY `id_article` (`id_article`),
  KEY `id_panier` (`id_panier`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `vente`
--

INSERT INTO `vente` (`id_vente`, `quantite`, `prix`, `id_article`, `id_panier`, `arendre`) VALUES
(41, 5, 135540, 39, 1, 5),
(42, 5, 90000, 40, 1, 5),
(43, 2, 28032, 27, 2, 2),
(44, 4, 63648, 24, 2, 4),
(45, 5, 90000, 40, 3, 5),
(46, 2, 54216, 39, 3, 2),
(47, 1, 27108, 39, 4, 1),
(48, 1, 27108, 39, 5, 1);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `commande_ibfk_1` FOREIGN KEY (`id_article`) REFERENCES `article` (`id_article`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `commande_ibfk_2` FOREIGN KEY (`id_lscommande`) REFERENCES `lscommande` (`id_lscommande`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `lscommande`
--
ALTER TABLE `lscommande`
  ADD CONSTRAINT `lscommande_ibfk_1` FOREIGN KEY (`id_fournisseur`) REFERENCES `fournisseur` (`id_fournisseur`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `panier`
--
ALTER TABLE `panier`
  ADD CONSTRAINT `panier_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `vente`
--
ALTER TABLE `vente`
  ADD CONSTRAINT `vente_ibfk_1` FOREIGN KEY (`id_article`) REFERENCES `article` (`id_article`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `vente_ibfk_2` FOREIGN KEY (`id_panier`) REFERENCES `panier` (`id_panier`) ON DELETE CASCADE ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
