-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 25, 2024 at 05:07 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ceit`
--

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `format` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `title`, `description`, `format`) VALUES
(4, 'Title sample for document 1', 'description', 'text'),
(7, 'Accessible for faculty', 'faculty only', 'PDF'),
(8, 'Accessible for both students and faculty', 'students and faculty', 'PDF');

-- --------------------------------------------------------

--
-- Table structure for table `document_versions`
--

CREATE TABLE `document_versions` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `version_number` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_versions`
--

INSERT INTO `document_versions` (`id`, `document_id`, `version_number`, `content`, `created_at`) VALUES
(1, 4, 1, 'content', '2024-07-24 02:33:05'),
(2, 7, 1, 'hahahahha', '2024-07-24 03:51:40'),
(3, 8, 1, 'ahhaha\r\n', '2024-07-24 03:52:54');

-- --------------------------------------------------------

--
-- Table structure for table `keywords`
--

CREATE TABLE `keywords` (
  `id` int(11) NOT NULL,
  `keyword` varchar(50) DEFAULT NULL,
  `documentId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `keywords`
--

INSERT INTO `keywords` (`id`, `keyword`, `documentId`) VALUES
(1, 'document', 4),
(5, 'faculty', 7);

-- --------------------------------------------------------

--
-- Table structure for table `level_of_access`
--

CREATE TABLE `level_of_access` (
  `id` int(11) NOT NULL,
  `documentId` int(11) DEFAULT NULL,
  `access` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `level_of_access`
--

INSERT INTO `level_of_access` (`id`, `documentId`, `access`) VALUES
(1, 4, 'Student'),
(2, 8, 'Both'),
(3, 7, 'Faculty');

-- --------------------------------------------------------

--
-- Table structure for table `pin_documents`
--

CREATE TABLE `pin_documents` (
  `id` int(11) NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `documentId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pin_documents`
--

INSERT INTO `pin_documents` (`id`, `userId`, `documentId`) VALUES
(1, 3, 8),
(2, 3, 8),
(3, 3, 8);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `fname` varchar(50) DEFAULT NULL,
  `mname` varchar(50) DEFAULT NULL,
  `lname` varchar(50) DEFAULT NULL,
  `email` varchar(250) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `fname`, `mname`, `lname`, `email`, `password`, `profile_photo`) VALUES
(1, 'Student', 'Cedrick', NULL, 'Mahinay', 'cedrickmahinay@email.com', 'mahinay123', 'default.png'),
(2, 'Student', 'Justine', NULL, 'Justine', 'just@ine.com', 'justine123', 'default.png'),
(3, 'Faculty', 'John', NULL, 'Doe', 'j@doe.com', 'jdoe', 'default.png');

-- --------------------------------------------------------

--
-- Table structure for table `users_faculty`
--

CREATE TABLE `users_faculty` (
  `id` int(11) NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `department` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_faculty`
--

INSERT INTO `users_faculty` (`id`, `userId`, `department`) VALUES
(1, 3, 'DIT');

-- --------------------------------------------------------

--
-- Table structure for table `users_student`
--

CREATE TABLE `users_student` (
  `id` int(11) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `student_number` int(11) DEFAULT NULL,
  `program` varchar(250) DEFAULT NULL,
  `department` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_student`
--

INSERT INTO `users_student` (`id`, `userID`, `student_number`, `program`, `department`) VALUES
(1, 1, 202108606, 'BS IT', 'DIT'),
(2, 2, 2023008606, 'BSCS', 'DIT');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_id` (`document_id`);

--
-- Indexes for table `keywords`
--
ALTER TABLE `keywords`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documentId` (`documentId`);

--
-- Indexes for table `level_of_access`
--
ALTER TABLE `level_of_access`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documentId` (`documentId`);

--
-- Indexes for table `pin_documents`
--
ALTER TABLE `pin_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`),
  ADD KEY `documentId` (`documentId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users_faculty`
--
ALTER TABLE `users_faculty`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `users_student`
--
ALTER TABLE `users_student`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userID` (`userID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `document_versions`
--
ALTER TABLE `document_versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `keywords`
--
ALTER TABLE `keywords`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `level_of_access`
--
ALTER TABLE `level_of_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pin_documents`
--
ALTER TABLE `pin_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users_faculty`
--
ALTER TABLE `users_faculty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users_student`
--
ALTER TABLE `users_student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD CONSTRAINT `document_versions_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`);

--
-- Constraints for table `keywords`
--
ALTER TABLE `keywords`
  ADD CONSTRAINT `keywords_ibfk_1` FOREIGN KEY (`documentId`) REFERENCES `documents` (`id`);

--
-- Constraints for table `level_of_access`
--
ALTER TABLE `level_of_access`
  ADD CONSTRAINT `level_of_access_ibfk_1` FOREIGN KEY (`documentId`) REFERENCES `documents` (`id`);

--
-- Constraints for table `pin_documents`
--
ALTER TABLE `pin_documents`
  ADD CONSTRAINT `pin_documents_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pin_documents_ibfk_2` FOREIGN KEY (`documentId`) REFERENCES `documents` (`id`);

--
-- Constraints for table `users_faculty`
--
ALTER TABLE `users_faculty`
  ADD CONSTRAINT `users_faculty_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`);

--
-- Constraints for table `users_student`
--
ALTER TABLE `users_student`
  ADD CONSTRAINT `users_student_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
