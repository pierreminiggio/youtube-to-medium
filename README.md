# youtube-to-medium

Migration :

```sql
-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le :  mer. 30 déc. 2020 à 23:52
-- Version du serveur :  5.7.17
-- Version de PHP :  5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `channel-storage`
--

-- --------------------------------------------------------

--
-- Structure de la table `medium_website`
--

CREATE TABLE `medium_website` (
  `id` int(11) NOT NULL,
  `token` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `medium_website_youtube_channel`
--

CREATE TABLE `medium_website_youtube_channel` (
  `id` int(11) NOT NULL,
  `medium_id` int(11) NOT NULL,
  `youtube_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `medium_post`
--

CREATE TABLE `medium_post` (
  `id` int(11) NOT NULL,
  `website_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
ALTER TABLE `medium_post` ADD `post_id` INT NOT NULL AFTER `website_id`;
-- --------------------------------------------------------

--
-- Structure de la table `medium_post_youtube_video`
--

CREATE TABLE `medium_post_youtube_video` (
  `id` int(11) NOT NULL,
  `medium_id` int(11) NOT NULL,
  `youtube_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `medium_website`
--
ALTER TABLE `medium_website`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `medium_website_youtube_channel`
--
ALTER TABLE `medium_website_youtube_channel`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `medium_post`
--
ALTER TABLE `medium_post`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `medium_post_youtube_video`
--
ALTER TABLE `medium_post_youtube_video`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `medium_website`
--
ALTER TABLE `medium_website`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `medium_website_youtube_channel`
--
ALTER TABLE `medium_website_youtube_channel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `medium_post`
--
ALTER TABLE `medium_post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `medium_post_youtube_video`
--
ALTER TABLE `medium_post_youtube_video`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

```
