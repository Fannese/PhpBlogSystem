-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 19. Jun 2024 um 15:47
-- Server-Version: 10.4.28-MariaDB
-- PHP-Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `blogprojekt`
--
CREATE DATABASE IF NOT EXISTS `blogprojekt` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `blogprojekt`;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `blogs`
--

DROP TABLE IF EXISTS `blogs`;
CREATE TABLE `blogs` (
  `blogID` int(11) NOT NULL,
  `blogHeadline` varchar(256) NOT NULL,
  `blogImagePath` varchar(256) DEFAULT NULL,
  `blogImageAlignment` varchar(10) NOT NULL,
  `blogContent` text NOT NULL,
  `blogDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `catID` int(11) NOT NULL,
  `userID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `blogs`
--

INSERT INTO `blogs` (`blogID`, `blogHeadline`, `blogImagePath`, `blogImageAlignment`, `blogContent`, `blogDate`, `catID`, `userID`) VALUES
(7, 'Mercedes Benz', 'CEWE_Logo.png', 'Rechts', 'gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr,', '2024-06-19 12:53:40', 0, 0),
(8, 'Sport', NULL, 'Links', 'ertzhjk', '2024-06-19 13:30:54', 7, 1),
(9, 'Sport', NULL, 'Links', 'ertzhjk', '2024-06-19 13:44:15', 7, 1),
(10, 'Sport', NULL, 'Links', 'ertzhjk', '2024-06-19 13:45:47', 7, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `catID` int(11) NOT NULL,
  `catLabel` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `categories`
--

INSERT INTO `categories` (`catID`, `catLabel`) VALUES
(1, 'Mobile'),
(2, 'LifeStyle'),
(4, 'Essen'),
(7, 'fahrrad'),
(9, 'Auto');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `userID` int(11) NOT NULL,
  `userFirstName` varchar(256) NOT NULL,
  `userLastName` varchar(256) NOT NULL,
  `userEmail` varchar(256) NOT NULL,
  `userCity` varchar(256) NOT NULL,
  `userPassword` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`userID`, `userFirstName`, `userLastName`, `userEmail`, `userCity`, `userPassword`) VALUES
(1, 'Fannese', 'FanneseO', 'a@b.c', 'Bremen', '$2y$10$/YTHZ2MvOWJOxzjYEw.8VuqgRzvsUQQb5x1vXCZrCTE4WkaqMcfay'),
(2, 'Muster', 'Mustermann', 'm@b.c', 'Bremen', '$2y$10$noWlbt1gb7N9.70Uk1epM.fDf2ZkPPo1g/HCQ5MLrI4oZVuD.gJiy');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`blogID`),
  ADD KEY `catID` (`catID`) USING BTREE,
  ADD KEY `userID` (`userID`);

--
-- Indizes für die Tabelle `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`catID`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `blogs`
--
ALTER TABLE `blogs`
  MODIFY `blogID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT für Tabelle `categories`
--
ALTER TABLE `categories`
  MODIFY `catID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
