-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : mer. 22 avr. 2026 à 09:57
-- Version du serveur : 5.7.39
-- Version de PHP : 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `coloc`
--

-- --------------------------------------------------------

--
-- Structure de la table `classementauteurs`
--

CREATE TABLE `classementauteurs` (
  `competitions_id` int(11) NOT NULL,
  `participants_id` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total` smallint(6) NOT NULL,
  `place` smallint(6) NOT NULL,
  `nb_photos` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `classementclubs`
--

CREATE TABLE `classementclubs` (
  `competitions_id` int(11) NOT NULL,
  `clubs_id` smallint(6) NOT NULL,
  `total` smallint(6) NOT NULL,
  `place` smallint(6) NOT NULL,
  `nb_photos` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `classements`
--

CREATE TABLE `classements` (
  `competitions_id` int(11) NOT NULL,
  `afaire` tinyint(4) NOT NULL DEFAULT '0',
  `graphe` tinyint(4) NOT NULL DEFAULT '0',
  `photos` tinyint(1) NOT NULL DEFAULT '0',
  `clubs` tinyint(1) NOT NULL DEFAULT '0',
  `auteurs` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `clubs`
--

CREATE TABLE `clubs` (
  `id` smallint(6) NOT NULL,
  `urs_id` tinyint(4) NOT NULL,
  `numero` smallint(6) NOT NULL,
  `nom` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `club_metrics`
--

CREATE TABLE `club_metrics` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `saison` int(11) NOT NULL,
  `total_score` int(11) DEFAULT NULL,
  `avg_score` float DEFAULT NULL,
  `avg_rank` float DEFAULT NULL,
  `delta_rank` int(11) DEFAULT NULL,
  `progression` tinyint(1) DEFAULT NULL,
  `nb_competitions` int(11) DEFAULT NULL,
  `nb_montées` int(11) DEFAULT NULL,
  `nb_descentes` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `club_rankings`
--

CREATE TABLE `club_rankings` (
  `id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `saison` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `club_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `score` int(11) NOT NULL,
  `rank` int(11) NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nb_photos` int(11) DEFAULT '0',
  `nb_acceptations` int(11) DEFAULT '0',
  `nb_20` int(11) DEFAULT '0',
  `nb_19` int(11) DEFAULT '0',
  `tie_break_used` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `coloc_suivi`
--

CREATE TABLE `coloc_suivi` (
  `id` int(11) NOT NULL,
  `categorie` varchar(50) DEFAULT NULL,
  `acteur` varchar(50) DEFAULT NULL,
  `quoi` varchar(255) DEFAULT NULL,
  `details` text,
  `analyse` text,
  `benefice` text,
  `risque` text,
  `contrainte` text,
  `impact_systeme` varchar(50) DEFAULT NULL,
  `cout` varchar(20) DEFAULT NULL,
  `statut` varchar(20) DEFAULT NULL,
  `priorite` varchar(20) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `decision` varchar(20) DEFAULT NULL,
  `version` varchar(20) DEFAULT NULL,
  `reunion` varchar(50) DEFAULT NULL,
  `saison` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `competitions`
--

CREATE TABLE `competitions` (
  `id` int(11) NOT NULL,
  `numero` smallint(6) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `urs_id` tinyint(4) DEFAULT NULL,
  `saison` year(4) NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_competition` date NOT NULL,
  `max_photos_club` int(11) NOT NULL,
  `max_photos_auteur` int(11) NOT NULL,
  `param_photos_club` int(11) NOT NULL,
  `param_photos_auteur` int(11) NOT NULL,
  `quota` int(11) NOT NULL DEFAULT '0',
  `note_min` tinyint(4) NOT NULL DEFAULT '6',
  `note_max` tinyint(4) NOT NULL DEFAULT '20',
  `nb_auteurs_ur_n2` tinyint(4) NOT NULL DEFAULT '3',
  `nb_clubs_ur_n2` tinyint(4) NOT NULL DEFAULT '7',
  `pte` tinyint(1) NOT NULL DEFAULT '0',
  `nature` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `competitions_enriched`
--

CREATE TABLE `competitions_enriched` (
  `id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `saison` int(11) NOT NULL,
  `level` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discipline` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `support` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `participants_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `has_progression` tinyint(1) DEFAULT '1',
  `photos_retained` int(11) DEFAULT NULL,
  `photos_max` int(11) DEFAULT NULL,
  `promotion_limit` int(11) DEFAULT NULL,
  `relegation_limit` int(11) DEFAULT NULL,
  `source_label` text COLLATE utf8mb4_unicode_ci,
  `normalized_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `competition_meta`
--

CREATE TABLE `competition_meta` (
  `id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `saison` year(4) NOT NULL,
  `level` enum('REGIONAL','N2','N1','CDF','DIRECT') COLLATE utf8mb4_unicode_ci NOT NULL,
  `discipline` enum('MONOCHROME','COULEUR','NATURE','AUTEUR','QUADRIMAGE','AUDIOVISUEL','UNKNOWN') COLLATE utf8mb4_unicode_ci NOT NULL,
  `support` enum('PAPIER','IP','UNKNOWN') COLLATE utf8mb4_unicode_ci NOT NULL,
  `participants_type` enum('club','author') COLLATE utf8mb4_unicode_ci DEFAULT 'club',
  `is_official` tinyint(1) DEFAULT '1',
  `source_label` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `normalized_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `competition_metrics`
--

CREATE TABLE `competition_metrics` (
  `id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `saison` int(11) NOT NULL,
  `nb_clubs` int(11) DEFAULT NULL,
  `nb_photos` int(11) DEFAULT NULL,
  `avg_score` float DEFAULT NULL,
  `std_deviation` float DEFAULT NULL,
  `avg_score_per_judge` float DEFAULT NULL,
  `jury_variance` float DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `competition_quotas`
--

CREATE TABLE `competition_quotas` (
  `id` int(11) NOT NULL,
  `saison` year(4) NOT NULL,
  `discipline` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `support` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ur_id` tinyint(4) NOT NULL,
  `quota` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `import_logs`
--

CREATE TABLE `import_logs` (
  `id` int(11) NOT NULL,
  `competition_id` int(11) DEFAULT NULL,
  `saison` int(11) DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imported_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `nb_records` int(11) DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `juges`
--

CREATE TABLE `juges` (
  `id` smallint(6) NOT NULL,
  `nom` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `competitions_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `medailles`
--

CREATE TABLE `medailles` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fpf` tinyint(1) NOT NULL DEFAULT '0',
  `competitions_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notes`
--

CREATE TABLE `notes` (
  `juges_id` int(11) NOT NULL,
  `photos_id` int(11) NOT NULL,
  `note` tinyint(4) NOT NULL,
  `competitions_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `participants`
--

CREATE TABLE `participants` (
  `id` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `urs_id` tinyint(4) DEFAULT NULL,
  `clubs_id` smallint(6) DEFAULT NULL,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `etat_adhesion` tinyint(1) NOT NULL DEFAULT '1',
  `annee_cotisation` year(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `photos`
--

CREATE TABLE `photos` (
  `id` int(11) NOT NULL,
  `ean` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `competitions_id` int(11) NOT NULL,
  `participants_id` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` tinyint(4) NOT NULL DEFAULT '1',
  `place` smallint(6) NOT NULL DEFAULT '0',
  `note_totale` smallint(6) NOT NULL DEFAULT '0',
  `saisie` int(11) NOT NULL DEFAULT '0',
  `retenue` tinyint(1) NOT NULL DEFAULT '0',
  `medailles_id` int(11) DEFAULT NULL,
  `passage` int(11) NOT NULL,
  `disqualifie` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `classementauteurs`
--
ALTER TABLE `classementauteurs`
  ADD PRIMARY KEY (`competitions_id`,`participants_id`);

--
-- Index pour la table `classementclubs`
--
ALTER TABLE `classementclubs`
  ADD PRIMARY KEY (`competitions_id`,`clubs_id`);

--
-- Index pour la table `classements`
--
ALTER TABLE `classements`
  ADD PRIMARY KEY (`competitions_id`);

--
-- Index pour la table `clubs`
--
ALTER TABLE `clubs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `club_metrics`
--
ALTER TABLE `club_metrics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_club_saison` (`club_id`,`saison`),
  ADD KEY `idx_progression` (`progression`);

--
-- Index pour la table `club_rankings`
--
ALTER TABLE `club_rankings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_club_comp` (`competition_id`,`club_id`),
  ADD KEY `idx_rank` (`rank`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_club` (`club_id`);

--
-- Index pour la table `coloc_suivi`
--
ALTER TABLE `coloc_suivi`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `competitions`
--
ALTER TABLE `competitions`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `competitions_enriched`
--
ALTER TABLE `competitions_enriched`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_competition` (`competition_id`,`saison`),
  ADD KEY `idx_level` (`level`),
  ADD KEY `idx_access` (`access_type`);

--
-- Index pour la table `competition_meta`
--
ALTER TABLE `competition_meta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_competition` (`competition_id`),
  ADD KEY `idx_level` (`level`),
  ADD KEY `idx_discipline` (`discipline`),
  ADD KEY `idx_official` (`is_official`);

--
-- Index pour la table `competition_metrics`
--
ALTER TABLE `competition_metrics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_comp_metrics` (`competition_id`,`saison`);

--
-- Index pour la table `competition_quotas`
--
ALTER TABLE `competition_quotas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_quota` (`saison`,`discipline`,`support`,`ur_id`);

--
-- Index pour la table `import_logs`
--
ALTER TABLE `import_logs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `juges`
--
ALTER TABLE `juges`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `medailles`
--
ALTER TABLE `medailles`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`juges_id`,`photos_id`),
  ADD KEY `competitions_id` (`competitions_id`);

--
-- Index pour la table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `photos`
--
ALTER TABLE `photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competitions_id` (`competitions_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `club_metrics`
--
ALTER TABLE `club_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `club_rankings`
--
ALTER TABLE `club_rankings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `coloc_suivi`
--
ALTER TABLE `coloc_suivi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `competitions_enriched`
--
ALTER TABLE `competitions_enriched`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `competition_meta`
--
ALTER TABLE `competition_meta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `competition_metrics`
--
ALTER TABLE `competition_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `competition_quotas`
--
ALTER TABLE `competition_quotas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `import_logs`
--
ALTER TABLE `import_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `medailles`
--
ALTER TABLE `medailles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `photos`
--
ALTER TABLE `photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
