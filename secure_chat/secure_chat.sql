-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2025 at 10:07 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `secure_chat`
--

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `username` varchar(255) NOT NULL,
  `attempts` int(11) DEFAULT 0,
  `last_attempt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `encrypted_msg` text DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `encrypted_msg`, `timestamp`) VALUES
(80, 16, 17, 'MgsKBwoUDDgGCFAgAgIH', '2025-05-28 10:56:15'),
(81, 17, 16, '44Cg44Or44CS44CH44CJ', '2025-05-28 12:11:05'),
(82, 16, 17, 'I8KTCxUOwp4PKQIQWyxFTQsWFRkMKw8gwp5NFFUHJBJdOwNBFwzCsjkFGTQlwrpaHQcNGAxZVSU4BkRFwqJJHgJdFwZCP8OEJlUDE8OfVT9cQSsQFxwWIcK7ThE5NHFWVMKUERIKAwEAbQ4JVGnCgh4CQsKTTAzCpA8nHcKGC1YcOxw=', '2025-05-28 12:15:13'),
(83, 17, 16, 'IA0LA0YMDjhHB1AnRw==', '2025-05-28 12:15:46'),
(84, 16, 17, 'JxAYCA1VGCISREImSQAfVRBC', '2025-05-28 12:15:53'),
(85, 19, 17, 'ASAdaGcEPR0AHSYWEFw=', '2025-05-28 12:18:40'),
(86, 19, 17, 'AGUKISItcgwOBTlTHBcWBlY2HBxq', '2025-05-28 12:18:44'),
(87, 17, 19, 'ASxEJjUmflUYFThTAxobAlY8Cx80Ej04O2MoCRwNCw==', '2025-05-28 12:18:57'),
(88, 17, 19, 'CC4RZDMsPhQJUCYWGhUdFxggB1MiXTA4bjM0Ax8AWA1aVVAJNgMKBAFVDxM=', '2025-05-28 12:21:08'),
(89, 19, 17, 'ASREJi8oOw==', '2025-05-28 12:21:21'),
(90, 19, 17, '4KWd4KW1ROClvOCkhuCloeClrFlB4KWb4KSG4KWD4KS04KWZ4KWE4KWZ4KWGdOClgOCksuClmeCltWTgpYbgpabgpbHgpbjgpavgpZFJ4KSy4KWC4KSRWeClieCkjGY=', '2025-05-28 12:21:47'),
(91, 19, 16, 'KiAuWBg/HR4/DkkBBA4O', '2025-05-28 12:34:55'),
(92, 19, 16, 'IDIwQF13AAUz', '2025-05-28 12:34:57'),
(93, 16, 19, 'PCB5UVA2AQ==', '2025-05-28 12:35:34'),
(94, 16, 19, 'Kj8xUlUzHQgzBAUCBA==', '2025-05-28 12:35:38'),
(95, 16, 19, 'PyZ5WFk+GwV6AAgKUw==', '2025-05-28 12:35:41'),
(96, 19, 16, 'Kj8xUlUzHQgzBAUCBA==', '2025-05-28 12:35:49'),
(97, 19, 16, 'LRV5WFAyBBA/Vw==', '2025-05-28 12:35:51'),
(98, 16, 19, 'JTIwE1o/CQ0=', '2025-05-28 12:35:54'),
(99, 19, 16, 'JDg=', '2025-05-28 12:35:59'),
(100, 19, 16, '4KW84KSS4KWO4KSB4KSi4KSXSOClneCkmkjgpZHgpKTgpZngpZFH4KWj4KSf4KW/4KWjaeClj+ClnuClnuCkpFngpY3gpJHgpY7gpb94Q+ClpuCkmWvgpbLgpabgpbLgpI/gpaPgpYrgpLNR4KWh4KSe4KWUc+CkvuClleClnnfgpZHgpKbgpbJI4KWs4KWE4KWT4KW6R+CliuClsOClpGfgpaTgpZfgpZbgpZLgpabgpZfgpbbgpa/gpZgY4KWi4KWP4KS34KW/Z1XgpZHgpZvgpZfgpJ7gpbngpLRR4KWh4KSe4KWUc+ClrOCltOCklHfgpYLgpKfgpa3gpKXgpYngpZ3gpa7gpbXgpKBt4KWw4KSZ4KWy4KW54KWW4KWFTOClieClh+ClpuCkkeClqOCknOClpEPgpa/gpangpa/gpLJK', '2025-05-28 12:36:52'),
(101, 16, 19, 'IzIxUhR3Dws1DwUGTAQGbT8tJmkLCQkOWSNOAll0CxswdA==', '2025-05-28 12:37:12'),
(102, 19, 16, '8KWjkw==', '2025-05-28 12:37:30'),
(103, 16, 19, 'ICo4E1AiCUQ4AAgK8KSZrfCmqY/wqpmW8K6pi1Y=', '2025-05-28 12:38:05'),
(105, 16, 19, '4KWG4KWt4KWRHxjgpbngpKDgpaZ64KWR4KWo4KWb4KWE4KWRR+CloeClluClsmfgpaHgpZDgpKHgpa5E4KWs4KWhD+ClkuCkreClsOClnVrwqYmJSg==', '2025-05-28 12:39:12'),
(106, 20, 19, 'CClYRg==', '2025-05-28 12:42:55'),
(107, 19, 20, 'CC5VDxAIGz1zPwskHkU=', '2025-05-28 12:43:47');

-- --------------------------------------------------------

--
-- Table structure for table `passkeys`
--

CREATE TABLE `passkeys` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `credential_id` varbinary(255) NOT NULL,
  `public_key` text NOT NULL,
  `sign_count` int(11) DEFAULT 0,
  `aaguid` varchar(36) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passkeys`
--

INSERT INTO `passkeys` (`id`, `user_id`, `username`, `credential_id`, `public_key`, `sign_count`, `aaguid`, `created_at`) VALUES
(4, 16, 'Huzefa', 0xa03387526307e778db5b3b2df849028554306a4bed897de620bd8b610f66f933, '-----BEGIN PUBLIC KEY-----\nMFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE+2l9t/UJQClPCKiEgYRyyTi9FRzw\nlwjxq0ADba2nCT/a+cUPaf2yfbKptFYNQwBvL6M2DXL0uEj3+qYK00+wcA==\n-----END PUBLIC KEY-----\n', 0, '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0', '2025-05-28 12:53:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `profile_path` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `profile_path`) VALUES
(16, 'Huzefa', '$2y$10$cCw0Fr7TmQpuxgVhq.nsPOcGoDvUYWGt.tx3VOkOpzX0DxgAQzbbC', 'profile/profile_16_1748415824.jpg'),
(17, 'Mohamed', '$2y$10$MnfPn.k9XhyqmCd4FLbXT.Wvci2NaGLgPnUUhwmiLvKqTGblgkseO', 'profile/profile_17_1748414814.jpg'),
(19, 'Ali Azeem Parkar', '$2y$13$/DjB6BsKXREmWYCec/8Wy.Z5DedmFso9P3QQ8iMxkyztHciUbknV6', 'profile/profile_19_1748415036.jpg'),
(20, 'Anam', '$2y$10$3cQcNtlXJMKE.VcMTtamselcQ2VzsxkOAH1apd50TH9CPU.1olGLC', NULL),
(21, 'Ali', '$2y$10$N/QNMw9601TUvCXDUZfPB.nktS4fSF0llIgCi8O5TMFZpJOfYhvo6', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `messages_ibfk_1` (`sender_id`);

--
-- Indexes for table `passkeys`
--
ALTER TABLE `passkeys`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `passkeys`
--
ALTER TABLE `passkeys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
