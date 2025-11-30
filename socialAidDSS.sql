-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 30, 2025 at 05:50 PM
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
-- Database: `prioritycare`
--

-- --------------------------------------------------------

--
-- Table structure for table `critere`
--

DROP TABLE IF EXISTS `critere`;
CREATE TABLE IF NOT EXISTS `critere` (
  `ID_CRITERE` int NOT NULL AUTO_INCREMENT,
  `ID_PROJET` int NOT NULL,
  `NOM_CRITERE` char(32) DEFAULT NULL,
  `TYPE_CRITERE` char(32) DEFAULT NULL,
  `RANG` int DEFAULT NULL,
  `OBJECTIF` varchar(3) NOT NULL,
  `poids_l` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'Borne inférieure du poids flou',
  `poids_m` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'Valeur modale (milieu) du poids flou',
  `poids_u` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'Borne supérieure du poids flou',
  PRIMARY KEY (`ID_CRITERE`),
  KEY `critere_ibfk_1` (`ID_PROJET`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `critere`
--

INSERT INTO `critere` (`ID_CRITERE`, `ID_PROJET`, `NOM_CRITERE`, `TYPE_CRITERE`, `RANG`, `OBJECTIF`, `poids_l`, `poids_m`, `poids_u`) VALUES
(38, 6, 'test', 'quantitative', 1, 'max', '0.2174', '0.5455', '1.4789'),
(41, 6, 'test4', 'qualitative', 2, 'max', '0.1304', '0.2727', '0.4930'),
(44, 8, 'Nbr de menage', 'quantitative', 1, 'max', '0.2500', '0.6667', '1.8750'),
(45, 8, 'Etat_maison', 'qualitative', 2, 'min', '0.1500', '0.3333', '0.6250'),
(52, 6, 'test3', 'quantitative', 3, 'max', '0.0932', '0.1818', '0.2958');

-- --------------------------------------------------------

--
-- Table structure for table `performance`
--

DROP TABLE IF EXISTS `performance`;
CREATE TABLE IF NOT EXISTS `performance` (
  `ID_PERSONNE` int NOT NULL,
  `ID_CRITERE` int NOT NULL,
  `ID_PERFORMANCE` int DEFAULT NULL,
  `VALEURNUM` double(5,2) DEFAULT NULL,
  `VALEURQUAL` char(32) DEFAULT NULL,
  `valeur_l` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'Borne inférieure du poids flou',
  `valeur_m` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'Valeur modale (milieu) du poids flou',
  `valeur_u` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'Borne supérieure du poids flou',
  PRIMARY KEY (`ID_PERSONNE`,`ID_CRITERE`),
  KEY `I_FK_PERFORMANCE_PERSONNE` (`ID_PERSONNE`),
  KEY `I_FK_PERFORMANCE_CRITERE` (`ID_CRITERE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `performance`
--

INSERT INTO `performance` (`ID_PERSONNE`, `ID_CRITERE`, `ID_PERFORMANCE`, `VALEURNUM`, `VALEURQUAL`, `valeur_l`, `valeur_m`, `valeur_u`) VALUES
(16, 44, NULL, 5.00, NULL, '5.0000', '5.0000', '5.0000'),
(16, 45, NULL, NULL, 'TSARA', '3.0000', '5.0000', '5.0000'),
(17, 44, NULL, 10.00, NULL, '10.0000', '10.0000', '10.0000'),
(17, 45, NULL, NULL, 'EOEO', '1.0000', '3.0000', '5.0000'),
(18, 38, NULL, 2.00, NULL, '2.0000', '2.0000', '2.0000'),
(18, 41, NULL, NULL, '2', '1.0000', '3.0000', '5.0000'),
(18, 52, NULL, 0.00, NULL, '0.0000', '0.0000', '0.0000'),
(19, 38, NULL, 5.00, NULL, '5.0000', '5.0000', '5.0000'),
(19, 41, NULL, NULL, '1', '3.0000', '5.0000', '5.0000'),
(19, 52, NULL, 5.00, NULL, '5.0000', '5.0000', '5.0000'),
(25, 44, NULL, 1.00, NULL, '1.0000', '1.0000', '1.0000'),
(25, 45, NULL, NULL, 'RATSY', '1.0000', '1.0000', '3.0000'),
(26, 44, NULL, 2.00, NULL, '2.0000', '2.0000', '2.0000'),
(26, 45, NULL, NULL, 'TSARA', '3.0000', '5.0000', '5.0000'),
(27, 44, NULL, 3.00, NULL, '3.0000', '3.0000', '3.0000'),
(27, 45, NULL, NULL, 'EOEO', '1.0000', '3.0000', '5.0000'),
(28, 44, NULL, 4.00, NULL, '4.0000', '4.0000', '4.0000'),
(28, 45, NULL, NULL, 'RATSY', '1.0000', '1.0000', '3.0000'),
(29, 44, NULL, 5.00, NULL, '5.0000', '5.0000', '5.0000'),
(29, 45, NULL, NULL, 'TSARA', '3.0000', '5.0000', '5.0000');

-- --------------------------------------------------------

--
-- Table structure for table `personne`
--

DROP TABLE IF EXISTS `personne`;
CREATE TABLE IF NOT EXISTS `personne` (
  `ID_PERSONNE` int NOT NULL AUTO_INCREMENT,
  `ID_PROJET` int NOT NULL,
  `NOM_PERSONNE` char(32) DEFAULT NULL,
  PRIMARY KEY (`ID_PERSONNE`),
  KEY `I_FK_PERSONNE_PROJET` (`ID_PROJET`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `personne`
--

INSERT INTO `personne` (`ID_PERSONNE`, `ID_PROJET`, `NOM_PERSONNE`) VALUES
(16, 8, 'enafant01'),
(17, 8, 'enfant02'),
(18, 6, 'prs1'),
(19, 6, 'prs02'),
(25, 8, 'prs01'),
(26, 8, 'prs02'),
(27, 8, 'prs03'),
(28, 8, 'prs04'),
(29, 8, 'prs05');

-- --------------------------------------------------------

--
-- Table structure for table `projet`
--

DROP TABLE IF EXISTS `projet`;
CREATE TABLE IF NOT EXISTS `projet` (
  `ID_PROJET` int NOT NULL AUTO_INCREMENT,
  `ID_USER` int NOT NULL,
  `NOM_PROJET` varchar(128) DEFAULT NULL,
  `DESCRIPTION_PROJET` char(255) DEFAULT NULL,
  `DATE_CREATION` datetime DEFAULT NULL,
  `methode_poids` varchar(10) NOT NULL DEFAULT 'auto' COMMENT 'Définit la méthode de pondération : auto ou manual',
  PRIMARY KEY (`ID_PROJET`),
  KEY `I_FK_PROJET_UTILISATEUR` (`ID_USER`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `projet`
--

INSERT INTO `projet` (`ID_PROJET`, `ID_USER`, `NOM_PROJET`, `DESCRIPTION_PROJET`, `DATE_CREATION`, `methode_poids`) VALUES
(5, 5, 'test', 'ddytc', '2025-11-27 18:32:00', 'auto'),
(6, 3, 'test', '', '2025-11-28 17:07:46', 'auto'),
(7, 4, 'eddy', 'test eddy', '2025-11-29 18:08:42', 'auto'),
(8, 3, 'Materiel Enfant', 'Choix de materielle pour aider les enfants', '2025-11-30 17:19:18', 'auto');

-- --------------------------------------------------------

--
-- Table structure for table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `ID_USER` int NOT NULL AUTO_INCREMENT,
  `NOM_USER` char(32) DEFAULT NULL,
  `EMAIL` varchar(128) DEFAULT NULL,
  `PASSWORD` char(255) DEFAULT NULL,
  PRIMARY KEY (`ID_USER`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `utilisateur`
--

INSERT INTO `utilisateur` (`ID_USER`, `NOM_USER`, `EMAIL`, `PASSWORD`) VALUES
(1, 'test', 'test@example.com', '$2y$10$7Pgy8lFLjA0bWcA4IbitHuQLBQ8sYlwOYKHEfoe.JxhW1nDPcavB6'),
(2, 'test1', 'test1@example.com', '$2y$10$gRI4gnJcGh.G2Eb6VPybge5YMdWt877ZNErKHsTYVE5w26yJWCjSC'),
(3, 'sergio', 'sergio@example.com', '$2y$10$DmUuODDfqW7/W6mEzjpUNuBZaOLILA.whvr.DhSZ0JcACmxNXw5cS'),
(4, 'eddy', 'eddy@gmail.com', '$2y$10$b9XG8jFosUmpp1S2zepJm.BiFbXOsEUQXlE3pgT/fTdOjrE3L4Jou'),
(5, 'test27', 'test27@gmail.com', '$2y$10$vkvGuvcFd132JZ2VsZaCr.djnUiMIFNIaCx/h1.hv0Kb.DeP7gGgS');

-- --------------------------------------------------------

--
-- Table structure for table `valeurqualitative`
--

DROP TABLE IF EXISTS `valeurqualitative`;
CREATE TABLE IF NOT EXISTS `valeurqualitative` (
  `ID_VALQUAL` int NOT NULL AUTO_INCREMENT,
  `ID_CRITERE` int NOT NULL,
  `LIBELLE` varchar(128) DEFAULT NULL,
  `RANG` int DEFAULT NULL,
  PRIMARY KEY (`ID_VALQUAL`),
  KEY `I_FK_VALEURQUALITATIVE_CRITERE` (`ID_CRITERE`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `valeurqualitative`
--

INSERT INTO `valeurqualitative` (`ID_VALQUAL`, `ID_CRITERE`, `LIBELLE`, `RANG`) VALUES
(12, 41, '1', 1),
(13, 41, '2', 2),
(14, 41, '3', 3),
(18, 45, 'TSARA', 1),
(19, 45, 'EOEO', 2),
(20, 45, 'RATSY', 3);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `critere`
--
ALTER TABLE `critere`
  ADD CONSTRAINT `critere_ibfk_1` FOREIGN KEY (`ID_PROJET`) REFERENCES `projet` (`ID_PROJET`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `performance`
--
ALTER TABLE `performance`
  ADD CONSTRAINT `performance_ibfk_1` FOREIGN KEY (`ID_CRITERE`) REFERENCES `critere` (`ID_CRITERE`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `performance_ibfk_2` FOREIGN KEY (`ID_PERSONNE`) REFERENCES `personne` (`ID_PERSONNE`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `personne`
--
ALTER TABLE `personne`
  ADD CONSTRAINT `personne_ibfk_1` FOREIGN KEY (`ID_PROJET`) REFERENCES `projet` (`ID_PROJET`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `projet`
--
ALTER TABLE `projet`
  ADD CONSTRAINT `projet_ibfk_1` FOREIGN KEY (`ID_USER`) REFERENCES `utilisateur` (`ID_USER`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `valeurqualitative`
--
ALTER TABLE `valeurqualitative`
  ADD CONSTRAINT `valeurqualitative_ibfk_1` FOREIGN KEY (`ID_CRITERE`) REFERENCES `critere` (`ID_CRITERE`) ON DELETE CASCADE ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
