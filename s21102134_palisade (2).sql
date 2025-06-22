-- phpMyAdmin SQL Dump
-- version 5.2.1deb1+deb12u1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 22, 2025 at 03:05 AM
-- Server version: 10.11.11-MariaDB-0+deb12u1
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `s21102134_palisade`
--

-- --------------------------------------------------------

--
-- Table structure for table `channels`
--

CREATE TABLE `channels` (
  `channel_id` char(36) NOT NULL,
  `channel_type` enum('event','group','dm','system') NOT NULL,
  `related_id` varchar(36) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `channel_members`
--

CREATE TABLE `channel_members` (
  `channel_id` char(36) NOT NULL,
  `uid` char(36) NOT NULL,
  `role` enum('member','admin') DEFAULT 'member',
  `joined_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `school_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `eventid` varchar(36) NOT NULL,
  `participantcount` int(11) NOT NULL,
  `startdate` datetime DEFAULT NULL,
  `enddate` datetime DEFAULT NULL,
  `eventinfopath` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `eventname` varchar(255) DEFAULT NULL,
  `eventbadgepath` varchar(255) DEFAULT 'eventbadges/default.png',
  `eventcreator` varchar(36) DEFAULT NULL,
  `eventkey` varchar(255) DEFAULT NULL,
  `eventshortinfo` varchar(255) DEFAULT 'noevent.php',
  `views` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_participants`
--

CREATE TABLE `event_participants` (
  `participant_id` char(36) NOT NULL DEFAULT uuid(),
  `event_id` char(36) NOT NULL,
  `uid` char(36) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `joined_at` datetime NOT NULL DEFAULT current_timestamp(),
  `registered` tinyint(1) NOT NULL DEFAULT 1,
  `attendance_status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `existing_student_info`
--

CREATE TABLE `existing_student_info` (
  `student_id` int(11) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `program` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `program_code_backup` varchar(50) DEFAULT NULL,
  `program_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `msg_id` bigint(20) UNSIGNED NOT NULL,
  `channel_id` char(36) NOT NULL,
  `sender_uid` char(36) DEFAULT NULL,
  `body` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notif_id` bigint(20) UNSIGNED NOT NULL,
  `recipient_uid` char(36) NOT NULL,
  `channel_id` char(36) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `seen` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `program_code_mapping`
-- (See below for the actual view)
--
CREATE TABLE `program_code_mapping` (
`program_id` int(11)
,`code` varchar(10)
);

-- --------------------------------------------------------

--
-- Table structure for table `program_level`
--

CREATE TABLE `program_level` (
  `level_id` int(11) NOT NULL,
  `level_code` varchar(10) NOT NULL,
  `level_label` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program_list`
--

CREATE TABLE `program_list` (
  `program_id` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `program_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school`
--

CREATE TABLE `school` (
  `school_id` int(11) NOT NULL,
  `school_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_credentials`
--

CREATE TABLE `user_credentials` (
  `uid` varchar(36) NOT NULL,
  `fname` varchar(255) DEFAULT NULL,
  `mname` varchar(255) DEFAULT NULL,
  `lname` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `currboundtoken` varchar(64) DEFAULT '0',
  `emailverified` tinyint(1) DEFAULT 0,
  `attendedevents` longtext DEFAULT '[]',
  `creationtime` timestamp NULL DEFAULT current_timestamp(),
  `profilepicture` varchar(255) DEFAULT 'default.png',
  `password_reset_token` varchar(64) DEFAULT NULL,
  `password_reset_expiry` datetime DEFAULT NULL,
  `verification_code` varchar(255) DEFAULT NULL,
  `new_email` varchar(255) DEFAULT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `is_student` tinyint(1) NOT NULL DEFAULT 0,
  `user_level` tinyint(4) NOT NULL DEFAULT 0,
  `program_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_follows`
--

CREATE TABLE `user_follows` (
  `follower_uid` char(36) NOT NULL,
  `target_uid` char(36) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure for view `program_code_mapping`
--
DROP TABLE IF EXISTS `program_code_mapping`;

CREATE ALGORITHM=UNDEFINED DEFINER=`s21102134_palisade`@`localhost` SQL SECURITY DEFINER VIEW `program_code_mapping`  AS SELECT `program_list`.`program_id` AS `program_id`, 'BS CS' AS `code` FROM `program_list` WHERE `program_list`.`program_name` = 'Bachelor of Science in Computer Science'union all select `program_list`.`program_id` AS `program_id`,'BS IT' AS `code` from `program_list` where `program_list`.`program_name` = 'Bachelor of Science in Information Technology' union all select `program_list`.`program_id` AS `program_id`,'BSIT' AS `code` from `program_list` where `program_list`.`program_name` = 'Bachelor of Science in Information Technology' union all select `program_list`.`program_id` AS `program_id`,'BS IS' AS `code` from `program_list` where `program_list`.`program_name` = 'Bachelor of Science in Information Systems' union all select `program_list`.`program_id` AS `program_id`,'BS APPMATH' AS `code` from `program_list` where `program_list`.`program_name` = 'Bachelor of Science in Applied Mathematics' union all select `program_list`.`program_id` AS `program_id`,'BS ICT' AS `code` from `program_list` where `program_list`.`program_name` = 'Bachelor of Science in Information and Communications Technology'  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `channels`
--
ALTER TABLE `channels`
  ADD PRIMARY KEY (`channel_id`),
  ADD KEY `idx_channel_type` (`channel_type`),
  ADD KEY `idx_channel_rel` (`channel_type`,`related_id`);

--
-- Indexes for table `channel_members`
--
ALTER TABLE `channel_members`
  ADD PRIMARY KEY (`channel_id`,`uid`),
  ADD KEY `idx_member_uid` (`uid`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_name` (`department_name`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`eventid`),
  ADD KEY `eventcreator` (`eventcreator`);

--
-- Indexes for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD PRIMARY KEY (`participant_id`),
  ADD UNIQUE KEY `uniq_event_email` (`event_id`,`email`),
  ADD KEY `idx_event` (`event_id`),
  ADD KEY `idx_uid` (`uid`);

--
-- Indexes for table `existing_student_info`
--
ALTER TABLE `existing_student_info`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `program_id` (`program_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`msg_id`),
  ADD KEY `idx_channel_time` (`channel_id`,`created_at`),
  ADD KEY `idx_sender_uid` (`sender_uid`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notif_id`),
  ADD KEY `idx_recipient_seen` (`recipient_uid`,`seen`),
  ADD KEY `idx_notif_channel` (`channel_id`);

--
-- Indexes for table `program_level`
--
ALTER TABLE `program_level`
  ADD PRIMARY KEY (`level_id`),
  ADD UNIQUE KEY `level_code` (`level_code`);

--
-- Indexes for table `program_list`
--
ALTER TABLE `program_list`
  ADD PRIMARY KEY (`program_id`),
  ADD UNIQUE KEY `uq_level_name` (`level_id`,`program_name`),
  ADD KEY `fk_department` (`department_id`);

--
-- Indexes for table `school`
--
ALTER TABLE `school`
  ADD PRIMARY KEY (`school_id`);

--
-- Indexes for table `user_credentials`
--
ALTER TABLE `user_credentials`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `idx_is_student` (`is_student`);

--
-- Indexes for table `user_follows`
--
ALTER TABLE `user_follows`
  ADD PRIMARY KEY (`follower_uid`,`target_uid`),
  ADD KEY `idx_target` (`target_uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `msg_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notif_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `program_level`
--
ALTER TABLE `program_level`
  MODIFY `level_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `program_list`
--
ALTER TABLE `program_list`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `school`
--
ALTER TABLE `school`
  MODIFY `school_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `channel_members`
--
ALTER TABLE `channel_members`
  ADD CONSTRAINT `fk_cm_channel` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`channel_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cm_user` FOREIGN KEY (`uid`) REFERENCES `user_credentials` (`uid`) ON DELETE CASCADE;

--
-- Constraints for table `department`
--
ALTER TABLE `department`
  ADD CONSTRAINT `department_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `school` (`school_id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`eventcreator`) REFERENCES `user_credentials` (`uid`);

--
-- Constraints for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD CONSTRAINT `fk_ep_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`eventid`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ep_user` FOREIGN KEY (`uid`) REFERENCES `user_credentials` (`uid`) ON DELETE SET NULL;

--
-- Constraints for table `existing_student_info`
--
ALTER TABLE `existing_student_info`
  ADD CONSTRAINT `existing_student_info_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `program_list` (`program_id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_channel` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`channel_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_uid`) REFERENCES `user_credentials` (`uid`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_channel` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`channel_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`recipient_uid`) REFERENCES `user_credentials` (`uid`) ON DELETE CASCADE;

--
-- Constraints for table `program_list`
--
ALTER TABLE `program_list`
  ADD CONSTRAINT `fk_department` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_program_level` FOREIGN KEY (`level_id`) REFERENCES `program_level` (`level_id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_follows`
--
ALTER TABLE `user_follows`
  ADD CONSTRAINT `fk_follower_user` FOREIGN KEY (`follower_uid`) REFERENCES `user_credentials` (`uid`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_target_user` FOREIGN KEY (`target_uid`) REFERENCES `user_credentials` (`uid`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
