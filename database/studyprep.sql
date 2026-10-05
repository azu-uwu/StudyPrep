-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 05, 2026 at 03:33 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `studyprep`
--

-- --------------------------------------------------------

--
-- Table structure for table `aianalyses`
--

CREATE TABLE `aianalyses` (
  `analysis_id` bigint NOT NULL,
  `attempt_id` bigint NOT NULL,
  `skill_id` tinyint DEFAULT NULL,
  `strengths` json DEFAULT NULL,
  `weaknesses` json DEFAULT NULL,
  `recommendations` json DEFAULT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `answeroptions`
--

CREATE TABLE `answeroptions` (
  `option_id` bigint NOT NULL,
  `question_id` bigint NOT NULL,
  `label` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attemptresponses`
--

CREATE TABLE `attemptresponses` (
  `response_id` bigint NOT NULL,
  `attempt_id` bigint NOT NULL,
  `question_id` bigint NOT NULL,
  `text_response` text COLLATE utf8mb4_unicode_ci,
  `media_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `score` decimal(6,2) DEFAULT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attempts`
--

CREATE TABLE `attempts` (
  `attempt_id` bigint NOT NULL,
  `user_id` bigint NOT NULL,
  `test_form_id` bigint NOT NULL,
  `session_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `started_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ended_at` datetime DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IN_PROGRESS'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blueprintrules`
--

CREATE TABLE `blueprintrules` (
  `rule_id` bigint NOT NULL,
  `blueprint_id` bigint NOT NULL,
  `section_id` bigint NOT NULL,
  `question_type_id` bigint DEFAULT NULL,
  `difficulty` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_count` int NOT NULL,
  `sequence_no` smallint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `examsections`
--

CREATE TABLE `examsections` (
  `section_id` bigint NOT NULL,
  `skill_id` tinyint NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sequence_no` smallint NOT NULL,
  `default_time_limit` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `goals`
--

CREATE TABLE `goals` (
  `goal_id` bigint NOT NULL,
  `user_id` bigint NOT NULL,
  `certificate` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_listening` smallint DEFAULT NULL,
  `target_reading` smallint DEFAULT NULL,
  `target_speaking` smallint DEFAULT NULL,
  `target_writing` smallint DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `question_id` bigint NOT NULL,
  `section_id` bigint NOT NULL,
  `question_type_id` bigint NOT NULL,
  `question_set_id` bigint DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `explanation` text COLLATE utf8mb4_unicode_ci,
  `difficulty` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` decimal(6,2) NOT NULL DEFAULT '1.00',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questionsets`
--

CREATE TABLE `questionsets` (
  `question_set_id` bigint NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stimulus_text` text COLLATE utf8mb4_unicode_ci,
  `media_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transcript` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questiontypes`
--

CREATE TABLE `questiontypes` (
  `question_type_id` bigint NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `response_mode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scoreresults`
--

CREATE TABLE `scoreresults` (
  `result_id` bigint NOT NULL,
  `attempt_id` bigint NOT NULL,
  `listening_score` decimal(6,2) DEFAULT NULL,
  `reading_score` decimal(6,2) DEFAULT NULL,
  `speaking_score` decimal(6,2) DEFAULT NULL,
  `writing_score` decimal(6,2) DEFAULT NULL,
  `total_score` decimal(7,2) DEFAULT NULL,
  `graded_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `skill_id` tinyint NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `skill_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `testblueprints`
--

CREATE TABLE `testblueprints` (
  `blueprint_id` bigint NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exam_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_time` int DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `testformquestions`
--

CREATE TABLE `testformquestions` (
  `test_form_id` bigint NOT NULL,
  `question_id` bigint NOT NULL,
  `sequence_no` int NOT NULL,
  `point` decimal(6,2) NOT NULL DEFAULT '1.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `testforms`
--

CREATE TABLE `testforms` (
  `test_form_id` bigint NOT NULL,
  `blueprint_id` bigint NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `generation_seed` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

CREATE TABLE `topics` (
  `topic_id` bigint NOT NULL,
  `user_id` bigint NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `parent_topic_id` bigint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint NOT NULL,
  `user_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `energy` int NOT NULL DEFAULT '5',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `failed_login_attempts` int NOT NULL DEFAULT '0',
  `locked_until` datetime DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uservocabularies`
--

CREATE TABLE `uservocabularies` (
  `user_voca_id` bigint NOT NULL,
  `vocabulary_id` bigint NOT NULL,
  `user_id` bigint NOT NULL,
  `learned_at` datetime DEFAULT NULL,
  `is_done` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vocabularies`
--

CREATE TABLE `vocabularies` (
  `vocabulary_id` bigint NOT NULL,
  `topic_id` bigint NOT NULL,
  `word` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meaning_vi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `part_of_speech` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pronunciation` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `example_en` text COLLATE utf8mb4_unicode_ci,
  `example_vi` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aianalyses`
--
ALTER TABLE `aianalyses`
  ADD PRIMARY KEY (`analysis_id`),
  ADD KEY `FK_AIAnalyses_Attempts` (`attempt_id`),
  ADD KEY `FK_AIAnalyses_Skills` (`skill_id`);

--
-- Indexes for table `answeroptions`
--
ALTER TABLE `answeroptions`
  ADD PRIMARY KEY (`option_id`),
  ADD UNIQUE KEY `UQ_AnswerOptions` (`question_id`,`label`);

--
-- Indexes for table `attemptresponses`
--
ALTER TABLE `attemptresponses`
  ADD PRIMARY KEY (`response_id`),
  ADD UNIQUE KEY `UQ_AttemptResponses` (`attempt_id`,`question_id`),
  ADD KEY `FK_AttemptResponses_Questions` (`question_id`);

--
-- Indexes for table `attempts`
--
ALTER TABLE `attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `FK_Attempts_Users` (`user_id`),
  ADD KEY `FK_Attempts_TestForms` (`test_form_id`);

--
-- Indexes for table `blueprintrules`
--
ALTER TABLE `blueprintrules`
  ADD PRIMARY KEY (`rule_id`),
  ADD KEY `FK_BlueprintRules_TestBlueprints` (`blueprint_id`),
  ADD KEY `FK_BlueprintRules_ExamSections` (`section_id`),
  ADD KEY `FK_BlueprintRules_QuestionTypes` (`question_type_id`);

--
-- Indexes for table `examsections`
--
ALTER TABLE `examsections`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `FK_ExamSections_Skills` (`skill_id`);

--
-- Indexes for table `goals`
--
ALTER TABLE `goals`
  ADD PRIMARY KEY (`goal_id`),
  ADD KEY `FK_Goals_Users` (`user_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `FK_Questions_ExamSections` (`section_id`),
  ADD KEY `FK_Questions_QuestionTypes` (`question_type_id`),
  ADD KEY `FK_Questions_QuestionSets` (`question_set_id`);

--
-- Indexes for table `questionsets`
--
ALTER TABLE `questionsets`
  ADD PRIMARY KEY (`question_set_id`);

--
-- Indexes for table `questiontypes`
--
ALTER TABLE `questiontypes`
  ADD PRIMARY KEY (`question_type_id`);

--
-- Indexes for table `scoreresults`
--
ALTER TABLE `scoreresults`
  ADD PRIMARY KEY (`result_id`),
  ADD UNIQUE KEY `attempt_id` (`attempt_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`skill_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `testblueprints`
--
ALTER TABLE `testblueprints`
  ADD PRIMARY KEY (`blueprint_id`);

--
-- Indexes for table `testformquestions`
--
ALTER TABLE `testformquestions`
  ADD PRIMARY KEY (`test_form_id`,`question_id`),
  ADD KEY `FK_TestFormQuestions_Questions` (`question_id`);

--
-- Indexes for table `testforms`
--
ALTER TABLE `testforms`
  ADD PRIMARY KEY (`test_form_id`),
  ADD KEY `FK_TestForms_TestBlueprints` (`blueprint_id`);

--
-- Indexes for table `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`topic_id`),
  ADD KEY `FK_Topics_Users` (`user_id`),
  ADD KEY `FK_Topics_Parent` (`parent_topic_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `uservocabularies`
--
ALTER TABLE `uservocabularies`
  ADD PRIMARY KEY (`user_voca_id`),
  ADD UNIQUE KEY `UQ_UserVocabularies` (`user_id`,`vocabulary_id`),
  ADD KEY `FK_UserVocabularies_Vocabularies` (`vocabulary_id`);

--
-- Indexes for table `vocabularies`
--
ALTER TABLE `vocabularies`
  ADD PRIMARY KEY (`vocabulary_id`),
  ADD KEY `FK_Vocabularies_Topics` (`topic_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aianalyses`
--
ALTER TABLE `aianalyses`
  MODIFY `analysis_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `answeroptions`
--
ALTER TABLE `answeroptions`
  MODIFY `option_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attemptresponses`
--
ALTER TABLE `attemptresponses`
  MODIFY `response_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attempts`
--
ALTER TABLE `attempts`
  MODIFY `attempt_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blueprintrules`
--
ALTER TABLE `blueprintrules`
  MODIFY `rule_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `examsections`
--
ALTER TABLE `examsections`
  MODIFY `section_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `goals`
--
ALTER TABLE `goals`
  MODIFY `goal_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questionsets`
--
ALTER TABLE `questionsets`
  MODIFY `question_set_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questiontypes`
--
ALTER TABLE `questiontypes`
  MODIFY `question_type_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `scoreresults`
--
ALTER TABLE `scoreresults`
  MODIFY `result_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `skill_id` tinyint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `testblueprints`
--
ALTER TABLE `testblueprints`
  MODIFY `blueprint_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `testforms`
--
ALTER TABLE `testforms`
  MODIFY `test_form_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `topics`
--
ALTER TABLE `topics`
  MODIFY `topic_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uservocabularies`
--
ALTER TABLE `uservocabularies`
  MODIFY `user_voca_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vocabularies`
--
ALTER TABLE `vocabularies`
  MODIFY `vocabulary_id` bigint NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aianalyses`
--
ALTER TABLE `aianalyses`
  ADD CONSTRAINT `FK_AIAnalyses_Attempts` FOREIGN KEY (`attempt_id`) REFERENCES `attempts` (`attempt_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_AIAnalyses_Skills` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`skill_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `answeroptions`
--
ALTER TABLE `answeroptions`
  ADD CONSTRAINT `FK_AnswerOptions_Questions` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `attemptresponses`
--
ALTER TABLE `attemptresponses`
  ADD CONSTRAINT `FK_AttemptResponses_Attempts` FOREIGN KEY (`attempt_id`) REFERENCES `attempts` (`attempt_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_AttemptResponses_Questions` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `attempts`
--
ALTER TABLE `attempts`
  ADD CONSTRAINT `FK_Attempts_TestForms` FOREIGN KEY (`test_form_id`) REFERENCES `testforms` (`test_form_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_Attempts_Users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `blueprintrules`
--
ALTER TABLE `blueprintrules`
  ADD CONSTRAINT `FK_BlueprintRules_ExamSections` FOREIGN KEY (`section_id`) REFERENCES `examsections` (`section_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_BlueprintRules_QuestionTypes` FOREIGN KEY (`question_type_id`) REFERENCES `questiontypes` (`question_type_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_BlueprintRules_TestBlueprints` FOREIGN KEY (`blueprint_id`) REFERENCES `testblueprints` (`blueprint_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `examsections`
--
ALTER TABLE `examsections`
  ADD CONSTRAINT `FK_ExamSections_Skills` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`skill_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `goals`
--
ALTER TABLE `goals`
  ADD CONSTRAINT `FK_Goals_Users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `FK_Questions_ExamSections` FOREIGN KEY (`section_id`) REFERENCES `examsections` (`section_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_Questions_QuestionSets` FOREIGN KEY (`question_set_id`) REFERENCES `questionsets` (`question_set_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_Questions_QuestionTypes` FOREIGN KEY (`question_type_id`) REFERENCES `questiontypes` (`question_type_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `scoreresults`
--
ALTER TABLE `scoreresults`
  ADD CONSTRAINT `FK_ScoreResults_Attempts` FOREIGN KEY (`attempt_id`) REFERENCES `attempts` (`attempt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `testformquestions`
--
ALTER TABLE `testformquestions`
  ADD CONSTRAINT `FK_TestFormQuestions_Questions` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_TestFormQuestions_TestForms` FOREIGN KEY (`test_form_id`) REFERENCES `testforms` (`test_form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `testforms`
--
ALTER TABLE `testforms`
  ADD CONSTRAINT `FK_TestForms_TestBlueprints` FOREIGN KEY (`blueprint_id`) REFERENCES `testblueprints` (`blueprint_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `topics`
--
ALTER TABLE `topics`
  ADD CONSTRAINT `FK_Topics_Parent` FOREIGN KEY (`parent_topic_id`) REFERENCES `topics` (`topic_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_Topics_Users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `uservocabularies`
--
ALTER TABLE `uservocabularies`
  ADD CONSTRAINT `FK_UserVocabularies_Users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_UserVocabularies_Vocabularies` FOREIGN KEY (`vocabulary_id`) REFERENCES `vocabularies` (`vocabulary_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vocabularies`
--
ALTER TABLE `vocabularies`
  ADD CONSTRAINT `FK_Vocabularies_Topics` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`topic_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
