-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 13, 2026 at 02:35 PM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u526627089_pressclubwell`
--

-- --------------------------------------------------------

--
-- Table structure for table `about_content`
--

CREATE TABLE `about_content` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 1,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `about_content`
--

INSERT INTO `about_content` (`id`, `title`, `description`, `image`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(7, 'Who We Are', 'The Press Club Welfare Worldwide Foundation is a non-profit organization dedicated to promoting the welfare and interests of journalists and media professionals worldwide. Our mission is to provide support, resources, and opportunities to enhance the skills, well-being, and professional growth of journalists, while promoting press freedom and excellence in journalism.\r\n', '68f889519d13c.jpg', 1, 'active', '2025-10-06 22:01:27', '2025-10-22 10:48:14'),
(8, 'Our Mission', 'Our mission is to empower journalists and media professionals by providing them with the necessary tools, training, and support to excel in their profession. We strive to create a community that fosters collaboration, innovation, and mutual support, and to promote a culture of press freedom, ethics, and excellence in journalism.\r\n', '68f8ab75a233f.jpg', 2, 'active', '2025-10-06 22:01:27', '2025-10-22 10:01:25'),
(9, 'Our Vision', 'Our vision is to be a leading global organization dedicated to promoting the welfare and interests of journalists and media professionals. We envision a world where journalists can work freely and safely, and where the media can thrive and contribute to a well-informed society.', '68f8abb27b46f.jpg', 3, 'active', '2025-10-06 22:01:27', '2025-10-22 10:02:26'),
(10, 'Our Core Values', '- Integrity: We uphold the highest standards of integrity, transparency, and accountability in our work.\r\n- Excellence: We strive for excellence in everything we do, and support others in achieving their full potential.\r\n- Collaboration: We believe in the power of collaboration and partnership, and work with others to achieve our goals.\r\n- Innovation: We encourage innovation, creativity, and experimentation, and support new ideas and approaches.\r\n- Press Freedom: We believe in the importance of press freedom, and work to promote and protect it worldwide.', '68f88bc82ce10.jpeg', 4, 'active', '2025-10-06 22:01:27', '2025-10-22 07:46:16'),
(11, 'Our Services and Programs - Part 1', '- Training and Capacity Building: We provide training and capacity-building programs for journalists and media professionals to enhance their skills and knowledge.\r\n- Networking and Community Building: We create opportunities for journalists and media professionals to connect, share experiences, and collaborate.\r\n- Welfare and Support: We provide support and resources to journalists and media professionals in need, including emergency assistance and mental health support.', '68f88beee618f.jpeg', 5, 'active', '2025-10-06 22:01:27', '2025-10-22 07:46:54'),
(12, 'Our Services and Programs - Part 2', '- Research and Advocacy: We conduct research and advocacy on issues affecting journalists and media professionals, and work to promote policies and practices that support press freedom and media development.\r\n- Awards and Recognition: We recognize and celebrate the achievements of journalists and media professionals through our awards and recognition programs.\r\n- Partnerships and Collaborations: We partner with other organizations and stakeholders to promote the interests of journalists and media professionals, and to support the development of the media sector.', '68f88c728d242.jpeg', 6, 'active', '2025-10-06 22:01:27', '2025-10-22 07:49:06'),
(13, 'Our Impact and Reach', 'Our work has a significant impact on the lives and careers of journalists and media professionals worldwide. We have:\r\n- Trained thousands of journalists and media professionals\r\n- Provided support to journalists in crisis\r\n- Promoted press freedom and media development worldwide', '68f88c9bcdefe.jpeg', 7, 'active', '2025-10-06 22:01:27', '2025-10-22 07:49:47'),
(14, 'Get Involved and Support Our Work', 'There are many ways to get involved and support our work, including:\r\n- Donating: Your donation can help us provide support and resources to journalists and media professionals.\r\n- Volunteering: We welcome volunteers who can help us with our work and programs.\r\n- Partnering: We partner with organizations and stakeholders who share our mission and values.\r\n- Spreading the Word: Help us spread the word about our work and mission.\r\n\r\nTogether, we can make a difference in the lives of journalists and media professionals, and contribute to a more informed and just society.', '68f88d00d7518.jpg', 8, 'active', '2025-10-06 22:01:27', '2025-10-22 07:51:28');

-- --------------------------------------------------------

--
-- Table structure for table `advertisements`
--

CREATE TABLE `advertisements` (
  `id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `advertisements`
--

INSERT INTO `advertisements` (`id`, `image`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(5, 'ad_690f2718d174e7.60183850.jfif', 0, 'active', '2025-11-08 11:18:48', '2025-11-08 11:18:48'),
(7, 'ad_690f277681e8b2.29808335.jfif', 0, 'active', '2025-11-08 11:20:22', '2025-11-08 11:20:22'),
(8, 'ad_690f278f7ed7f2.44210969.jfif', 0, 'active', '2025-11-08 11:20:47', '2025-11-08 11:20:47'),
(9, 'ad_690f27aae9c150.51714783.jfif', 0, 'active', '2025-11-08 11:21:14', '2025-11-08 11:21:14'),
(10, 'ad_690f27e90ba1a3.71148156.jfif', 0, 'active', '2025-11-08 11:22:17', '2025-11-08 11:22:17'),
(11, 'ad_690f280d8dce94.65757310.jfif', 0, 'active', '2025-11-08 11:22:53', '2025-11-08 11:22:53'),
(12, 'ad_690f28298df986.52690389.jfif', 0, 'active', '2025-11-08 11:23:21', '2025-11-08 11:23:21'),
(13, 'ad_690f284da70158.37082795.jfif', 0, 'active', '2025-11-08 11:23:57', '2025-11-08 11:23:57');

-- --------------------------------------------------------

--
-- Table structure for table `affiliations`
--

CREATE TABLE `affiliations` (
  `id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `affiliations`
--

INSERT INTO `affiliations` (`id`, `image`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(3, 'affil_68f8a2834dfa30.54353977.jpg', 0, 'active', '2025-10-22 09:23:15', '2025-10-22 09:23:15'),
(7, 'affil_68f8a64cb04cb4.36406226.jpg', 0, 'active', '2025-10-22 09:39:24', '2025-10-22 09:39:24'),
(8, 'affil_68f8a659322342.57098557.jpg', 0, 'active', '2025-10-22 09:39:37', '2025-10-22 09:39:37'),
(9, 'affil_68f8a668dfbe23.47791884.jpg', 0, 'active', '2025-10-22 09:39:52', '2025-10-22 09:39:52'),
(10, 'affil_68f8a674bd8883.90826005.jpg', 0, 'active', '2025-10-22 09:40:04', '2025-10-22 09:40:04'),
(13, 'affil_68f8a6b2591010.79590984.jpg', 0, 'active', '2025-10-22 09:41:06', '2025-10-22 09:41:06'),
(14, 'affil_68f8a6c2281308.90428979.jpg', 0, 'active', '2025-10-22 09:41:22', '2025-10-22 09:41:22'),
(15, 'affil_68f8a6cc80f609.06906871.jpg', 0, 'active', '2025-10-22 09:41:32', '2025-10-22 09:41:32'),
(16, 'affil_68f8a6d8e883f9.62042909.jpg', 0, 'active', '2025-10-22 09:41:44', '2025-10-22 09:41:44'),
(18, 'affil_68f8aab65192c3.62231881.jpg', 0, 'active', '2025-10-22 09:58:14', '2025-10-22 09:58:14');

-- --------------------------------------------------------

--
-- Table structure for table `appreciation_certificates`
--

CREATE TABLE `appreciation_certificates` (
  `id` int(11) NOT NULL,
  `certificate_no` varchar(20) NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `training_duration_number` int(11) NOT NULL,
  `training_duration_unit` enum('days','months','years') NOT NULL,
  `issue_date` date NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `training_name` varchar(255) NOT NULL DEFAULT 'Training Program'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `awards_list`
--

CREATE TABLE `awards_list` (
  `id` int(11) NOT NULL,
  `award_name` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `awards_list`
--

INSERT INTO `awards_list` (`id`, `award_name`, `status`, `created_at`) VALUES
(17, 'Iron Man of India Award', 'active', '2025-12-05 07:45:22'),
(18, 'Bharat Iron Lady of India Award', 'active', '2025-12-05 07:45:40'),
(19, 'Lifetime Achievement Award', 'active', '2025-12-05 07:45:59'),
(20, 'MBR APJ Kalam Award', 'active', '2025-12-05 07:46:19'),
(21, 'Business Icon Award', 'active', '2025-12-05 07:46:42'),
(22, 'National Education Icon Award', 'active', '2025-12-05 07:49:50'),
(23, 'Bharat Excellence Leadership Award', 'active', '2025-12-05 07:50:16'),
(24, 'Bharat Nari Shakti Award', 'active', '2025-12-05 07:50:36'),
(25, 'International Seva Ratan Award', 'active', '2025-12-05 07:50:52'),
(26, 'National Icon Award', 'active', '2025-12-05 07:51:11'),
(27, 'National Shri Maharana Partap Award', 'active', '2025-12-05 07:54:01'),
(28, 'Bharat MBR Dr Bhim Rao Ambedkar Award', 'active', '2025-12-05 07:54:29'),
(29, 'Bharat MBR Subhash Chandra Bose Award', 'active', '2025-12-05 07:54:53'),
(30, 'National MBR Bhamashah Award', 'active', '2025-12-05 07:55:16'),
(31, 'Indian Kala Bhaskar Award', 'active', '2025-12-05 07:55:35'),
(32, 'National Kala Ratan Award', 'active', '2025-12-05 07:55:54'),
(33, 'National Excellence Leadership Award', 'active', '2025-12-05 07:56:24'),
(34, 'Bharat Excellence Business Award', 'active', '2025-12-05 07:56:42'),
(35, 'International Excellence in Education Award', 'active', '2025-12-05 07:56:59'),
(36, 'Bharat Best/Young/Social Entrepreneur Award', 'active', '2025-12-05 07:57:16'),
(37, 'Indian Woman Power Award', 'active', '2025-12-05 07:57:34'),
(38, 'Bharat Rani Laxmi Bai Award', 'active', '2025-12-05 07:57:52'),
(39, 'National Best Business Man/Business Women Award', 'active', '2025-12-05 07:58:19'),
(40, 'Bharat Woman Entrepreneur Award', 'active', '2025-12-05 07:58:46'),
(41, 'International Extraordinary Author/Writer/Kid Award', 'active', '2025-12-05 07:59:05'),
(42, 'National Best Innovative Leadership Award', 'active', '2025-12-05 07:59:21'),
(43, 'National Youth Icon Award', 'active', '2025-12-05 07:59:39'),
(44, 'National Global Excellence Award', 'active', '2025-12-05 11:10:58'),
(45, 'International Global Artist Award', 'active', '2025-12-05 11:11:49'),
(46, 'Bharat Global Socialist Award', 'active', '2025-12-05 11:12:08'),
(47, 'Indian Global Nobel Award', 'active', '2025-12-05 11:12:33'),
(48, 'National Global Educator Award', 'active', '2025-12-05 11:12:50'),
(49, 'International Global Bravery Award', 'active', '2025-12-05 11:13:12'),
(50, 'Bharat Global Iron Lady Award', 'active', '2025-12-05 11:13:36'),
(52, 'Indian Global Iron Man Award', 'active', '2025-12-05 11:15:18'),
(53, 'National Global Business Award', 'active', '2025-12-05 11:15:36'),
(54, 'International Global Genius Award', 'active', '2025-12-05 11:15:58'),
(55, 'Bharat Global Fashion Icon Award', 'active', '2025-12-05 11:16:13'),
(56, 'Indian Global Beauty Pageant/Queen Award', 'active', '2025-12-05 11:16:36'),
(57, 'National Global Youth Icon Award', 'active', '2025-12-05 11:16:55'),
(58, 'International Global Human Rights Activist Award', 'active', '2025-12-05 11:17:11'),
(59, 'Bharat Global Miss Pageant Award', 'active', '2025-12-05 11:17:25'),
(60, 'Indian Global Kids Icon Award', 'active', '2025-12-05 11:17:55'),
(61, 'National Global Power of Women Award', 'active', '2025-12-05 11:18:22'),
(62, 'International Global Female Anchor/Model/Artist/Actor Award', 'active', '2025-12-05 11:18:40'),
(63, 'Bharat Global Business Women Award', 'active', '2025-12-05 11:18:56'),
(64, 'National Global Author/Writer Award', 'active', '2025-12-05 11:19:11'),
(65, 'International Global Glamour Award', 'active', '2025-12-05 11:19:24'),
(66, 'Bharat Golden Globe Award', 'active', '2025-12-05 11:19:37'),
(67, 'Indian Global Brilliant Kid Award', 'active', '2025-12-05 11:19:54'),
(68, 'National Global Yoga Award', 'active', '2025-12-05 11:20:10'),
(69, 'International Global Singer Award', 'active', '2025-12-05 11:20:22'),
(70, 'Bharat Global Dancer Award', 'active', '2025-12-05 11:20:36'),
(71, 'National Best Achiever Award', 'active', '2025-12-05 11:20:50'),
(72, 'International Young Achiever Award', 'active', '2025-12-05 11:21:04'),
(73, 'Bharat India Bravery Award', 'active', '2025-12-05 11:21:17'),
(74, 'Indian The Phoenix Award', 'active', '2025-12-05 11:21:32'),
(75, 'National The Shining Star Award', 'active', '2025-12-05 11:21:45'),
(76, 'International Man/Woman of the Year Award', 'active', '2025-12-05 11:22:00'),
(77, 'Bharat Best Glamour Award', 'active', '2025-12-05 11:22:15'),
(78, 'Indian Best Glamour Women Award', 'active', '2025-12-05 11:22:30'),
(79, 'National Best Inspiration Award', 'active', '2025-12-05 11:22:52'),
(80, 'International Multi-Talented Award', 'active', '2025-12-05 11:23:12'),
(81, 'Bharat The Diamond Award', 'active', '2025-12-05 11:23:28'),
(82, 'Indian Achievers Excellence Award', 'active', '2025-12-05 11:23:46'),
(83, 'National Most Desirable Award', 'active', '2025-12-05 11:23:59'),
(84, 'International Best Leadership Award', 'active', '2025-12-05 11:24:14'),
(85, 'Bharat The Moral Hero Award', 'active', '2025-12-05 11:24:25'),
(86, 'Indian Outstanding Contribution to Society Award', 'active', '2025-12-05 11:24:44'),
(87, 'National Best Social Activist Award', 'active', '2025-12-05 11:25:23'),
(88, 'International Best Volunteer Award', 'active', '2025-12-05 11:25:34'),
(89, 'Bharat Frontline Medical Hero Award', 'active', '2025-12-05 11:25:44'),
(90, 'Indian Backline Medical Hero Award', 'active', '2025-12-05 11:25:56'),
(91, 'National Education Hero Award', 'active', '2025-12-05 11:26:10'),
(92, 'International Delivery Hero Award', 'active', '2025-12-05 11:26:28'),
(93, 'Bharat Public Service Hero Award', 'active', '2025-12-05 11:26:41'),
(94, 'Indian Covid Warrior Award', 'active', '2025-12-05 11:26:54'),
(95, 'National Best Artist Award', 'active', '2025-12-05 11:27:05'),
(96, 'International Best Dancer Award', 'active', '2025-12-05 11:27:18'),
(97, 'Bharat Best Singer Award', 'active', '2025-12-05 11:27:33'),
(98, 'Indian Best Playback Singer Award', 'active', '2025-12-05 11:27:47'),
(99, 'National Best Rock Vocal Performance Award', 'active', '2025-12-05 11:28:00'),
(100, 'International Best Pop Artist Award', 'active', '2025-12-05 11:28:20'),
(101, 'Bharat Best Vocalist Award', 'active', '2025-12-05 11:29:26'),
(102, 'Indian Best Choreographer Award', 'active', '2025-12-05 11:29:38'),
(103, 'National Best Journalist Award', 'active', '2025-12-05 11:29:52'),
(104, 'International Best Photo Journalist Award', 'active', '2025-12-05 11:30:12'),
(106, 'Bharat Media Hero Award', 'active', '2025-12-05 11:31:04'),
(107, 'Indian Digital Man/Woman Award', 'active', '2025-12-05 11:31:18'),
(108, 'National The Braniac Award', 'active', '2025-12-05 11:31:36'),
(109, 'International The Eccentric Performer Award', 'active', '2025-12-05 11:31:49'),
(110, 'Bharat Little Master Award', 'active', '2025-12-05 11:32:00'),
(111, 'Indian Young Professional Award', 'active', '2025-12-05 11:32:11'),
(112, 'National Best Reciter Award', 'active', '2025-12-05 11:32:26'),
(113, 'International Spontaneous Reciter Award', 'active', '2025-12-05 11:32:39'),
(114, 'Bharat Most Eminent Senior Citizen Award', 'active', '2025-12-05 11:32:50'),
(115, 'Indian Best Astrologer Award', 'active', '2025-12-05 11:33:03'),
(116, 'National Best Writer Award', 'active', '2025-12-05 11:33:15'),
(117, 'International Best Philanthropist Award', 'active', '2025-12-05 11:33:32'),
(118, 'Bharat Emerging Scientist Award', 'active', '2025-12-05 11:33:41'),
(119, 'Indian Innovative Leadership Award', 'active', '2025-12-05 11:33:52'),
(120, 'National Best Achievers&apos; Award', 'active', '2025-12-05 11:34:03'),
(121, 'International Honorary Doctor Award', 'active', '2025-12-05 11:34:21'),
(122, 'Bharat Global Award', 'active', '2025-12-05 11:34:46'),
(123, 'Indian Unique Award', 'active', '2025-12-05 11:35:03'),
(124, 'National Woman Power Award', 'active', '2025-12-05 11:35:14'),
(125, 'International Best Female Anchors/Model/Artist/Film/Industry Award', 'active', '2025-12-05 11:35:26'),
(126, 'Bharat Beauty Queen Award', 'active', '2025-12-05 11:35:38'),
(127, 'Indian Diamond Queen Award', 'active', '2025-12-05 11:35:50'),
(128, 'National Woman Entrepreneur Award', 'active', '2025-12-05 11:36:01'),
(129, 'National Medal of Distinction Award', 'active', '2025-12-05 11:36:14'),
(130, 'International Most Inspiring Teacher Award', 'active', '2025-12-05 11:36:29'),
(131, 'Bharat Teaching Excellence Award', 'active', '2025-12-05 11:36:44'),
(132, 'Indian Dynamic Teacher Award', 'active', '2025-12-05 11:36:56'),
(133, 'National Best Teacher Award', 'active', '2025-12-05 11:37:09'),
(134, 'International Innovation in Education Award', 'active', '2025-12-05 11:37:20'),
(135, 'Bharat Best Faculty Award', 'active', '2025-12-05 11:37:31'),
(136, 'Indian Best Researcher Award', 'active', '2025-12-05 11:37:45'),
(137, 'National Young Engineer of the Year Award', 'active', '2025-12-05 11:37:58'),
(138, 'International Special Recognition Award', 'active', '2025-12-05 11:38:08'),
(139, 'National Creative Business Award', 'active', '2025-12-05 11:38:21'),
(140, 'International Dynamic Business Award', 'active', '2025-12-05 11:38:32'),
(141, 'Bharat Best Technology Award', 'active', '2025-12-05 11:38:45'),
(142, 'Indian Fastest Growing Small Business Award', 'active', '2025-12-05 11:39:22'),
(143, 'National Innovative Business Award', 'active', '2025-12-05 11:39:36'),
(144, 'International Best Entrepreneur Award', 'active', '2025-12-05 11:39:50'),
(145, 'Bharat Emerging Entrepreneur Award', 'active', '2025-12-05 11:40:05'),
(146, 'Indian Excellence Digital Marketing Award', 'active', '2025-12-05 11:40:17'),
(147, 'National Marketing Excellence Award', 'active', '2025-12-05 11:40:28'),
(148, 'International Best E-commerce Business Award', 'active', '2025-12-05 11:40:46'),
(149, 'National Super Grasping &amp; Memory Power Kid Award', 'active', '2025-12-05 11:40:58'),
(150, 'International Incredible Memory Power Award', 'active', '2025-12-05 11:41:10'),
(151, 'Bharat Brilliant Kid Award', 'active', '2025-12-05 11:41:23'),
(152, 'Indian An Artistic Girl Award – Drawing, Painting, Acting &amp; Animation', 'active', '2025-12-05 11:41:36'),
(153, 'National Youngest Master of G.K. Award', 'active', '2025-12-05 11:41:49'),
(154, 'Bharat Elastic Girl Award', 'active', '2025-12-05 11:42:20'),
(155, 'Indian Best Story Teller Award', 'active', '2025-12-05 11:42:36'),
(156, 'National Super Talented Toddler Award', 'active', '2025-12-05 11:42:56'),
(157, 'International Amazing Kid Award', 'active', '2025-12-05 11:43:09'),
(158, 'HONORARY DOCTORATE AWARD', 'active', '2025-12-09 10:34:57'),
(0, 'Dr. B.R. Amedkar Award', 'active', '2026-03-30 09:23:46');

-- --------------------------------------------------------

--
-- Table structure for table `award_letters`
--

CREATE TABLE `award_letters` (
  `id` int(11) NOT NULL,
  `letter_no` varchar(50) NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `recipient_address` text NOT NULL,
  `award_title` varchar(255) NOT NULL,
  `field_of_contribution` varchar(255) NOT NULL,
  `issue_date` date NOT NULL,
  `ceremony_date` date NOT NULL,
  `ceremony_location` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_details`
--

CREATE TABLE `bank_details` (
  `id` int(11) NOT NULL,
  `environment` enum('local','live') NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `ifsc_code` varchar(50) NOT NULL,
  `qr_code_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bank_details`
--

INSERT INTO `bank_details` (`id`, `environment`, `bank_name`, `account_name`, `account_number`, `ifsc_code`, `qr_code_image`, `created_at`, `updated_at`) VALUES
(0, 'live', 'Indian Bank', 'PRESSCLUB WELFARE WORLDWIDE FOUNDATION', '7922922257', 'IDIB000S619', '68e436483fa6b.PNG', '2025-10-06 16:05:37', '2025-10-06 21:36:08'),
(0, 'local', 'ssdfsd', 'sdffsdfsd', '54654564665', 'dsfsdfsd546', '68e3f0220bc5a.png', '2025-10-06 16:36:50', '2025-10-06 16:36:50');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `excerpt` text DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `campaign_date` date DEFAULT NULL,
  `raised_amount` decimal(10,2) DEFAULT 0.00,
  `target_amount` decimal(10,2) NOT NULL,
  `status` enum('active','inactive','completed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `camps`
--

CREATE TABLE `camps` (
  `id` int(11) NOT NULL,
  `program` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `father_name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `class` varchar(50) NOT NULL,
  `amount` decimal(10,2) DEFAULT 0.00,
  `payment_method` enum('online','offline','free') DEFAULT 'free',
  `place` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `order_id` varchar(100) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed') DEFAULT 'pending',
  `camp_id` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `status`, `created_at`) VALUES
(1, 'EDUCATION', 'active', '2026-03-16 11:18:41'),
(2, 'Education,Social Justice, Media And Sports', 'active', '2026-03-30 09:26:05');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `certificate_type` varchar(100) NOT NULL,
  `certificate_no` varchar(50) NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `post_name` varchar(255) NOT NULL,
  `event_or_reason` text NOT NULL,
  `issue_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `certificate_type`, `certificate_no`, `recipient_name`, `post_name`, `event_or_reason`, `issue_date`, `end_date`, `photo_path`, `status`, `created_at`, `updated_at`, `user_id`) VALUES
(3, 'Appreciation', 'PCWWF00001', 'PRESSCLUB WELFARE WORLDWIDE FOUNDATION', 'National Director', 'good', '2025-10-08', '0000-00-00', '', 'active', '2025-10-08 07:56:02', '2025-10-08 07:56:02', 1),
(4, 'Appointment', 'PCWWF00002', 'DHEERAJ SEHGAL', 'State President', 'Appointing As State President', '2025-10-09', '2026-10-09', '', 'active', '2025-10-09 05:36:38', '2025-10-09 05:36:38', 2),
(5, 'Appointment', 'PCWWF00003', 'S.VELAVAN', 'Press Member', 'APPOITING AS PRESS MEMBER', '2025-10-09', '2026-10-09', '', 'active', '2025-10-09 06:04:00', '2025-10-09 06:04:00', 3),
(6, 'Appointment', 'PCWWF00004', 'S.VELAVAN', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-09', '2026-10-09', '', 'active', '2025-10-09 06:12:08', '2025-10-09 06:12:08', 3),
(7, 'Appointment', 'PCWWF00005', 'Santanu Das', 'MEMBER', 'APPOINTING AS MEMBER', '2025-10-09', '2026-10-09', '', 'active', '2025-10-09 06:20:17', '2025-10-09 06:20:17', 4),
(8, 'Appointment', 'PCWWF00006', 'parag Gada', 'MEMBER', 'APPONTING AS MEMBER', '2025-10-09', '2025-10-09', '', 'active', '2025-10-09 06:29:15', '2025-10-09 06:29:15', 5),
(9, 'Appointment', 'PCWWF00007', 'Anand kishor', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-09', '2026-10-09', '', 'active', '2025-10-09 06:44:19', '2025-10-09 06:44:19', 6),
(12, 'Appointment', 'PCWWF00010', 'Dr.Bhaskar shukla', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-09', '0000-00-00', '', 'active', '2025-10-09 07:06:27', '2025-10-09 07:06:27', 8),
(13, 'Appointment', 'PCWWF00011', 'Arabindo sahu', 'MEMBER', 'APPOTING AS MEMBER', '2025-10-09', '2026-10-09', '', 'active', '2025-10-09 07:07:27', '2025-10-09 07:07:27', 7),
(14, 'Appointment', 'PCWWF00012', 'Shazaib Bashir Parkar', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-09', '2026-10-09', '', 'active', '2025-10-09 07:23:12', '2025-10-09 07:23:12', 9),
(15, 'Appointment', 'PCWWF00013', 'KUMAR GOVINDHASAMY', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-09', '2026-10-09', '', 'active', '2025-10-09 07:37:54', '2025-10-09 07:37:54', 10),
(16, 'Appointment', 'PCWWF00014', 'Elvis Lalthangzuala', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-09', '2026-10-09', '', 'active', '2025-10-09 07:49:46', '2025-10-09 07:49:46', 11),
(17, 'Appointment', 'PCWWF00015', 'Beerbal', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-09', '2026-10-09', '', 'active', '2025-10-09 08:00:55', '2025-10-09 08:00:55', 12),
(18, 'Appointment', 'PCWWF00016', 'Shubham Rastogi', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-09', '2026-10-09', '', 'active', '2025-10-09 10:06:39', '2025-10-09 10:06:39', 13),
(19, 'Appointment', 'PCWWF00017', 'Irshad Ahamd', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-09', '2026-10-09', '', 'active', '2025-10-09 10:23:38', '2025-10-09 10:23:38', 14),
(20, 'Appointment', 'PCWWF00018', 'MOHOMMAD AJAZ', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-09', '2026-10-09', '', 'active', '2025-10-09 10:31:17', '2025-10-09 10:31:17', 15),
(21, 'Appointment', 'PCWWF00019', 'SANJEEV KUMAR SUMAN', 'MEMBER', 'APPOINTING AS MEMBER', '2025-10-09', '2026-10-09', '', 'active', '2025-10-09 11:54:50', '2025-10-09 11:54:50', 16),
(22, 'Appointment', 'PCWWF00020', 'SAMIR KUMAR PRADHAN', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-10', '2026-10-10', '', 'active', '2025-10-10 05:32:18', '2025-10-10 05:32:18', 18),
(23, 'Appointment', 'PCWWF00021', 'Thakur Vinod Singh', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-10', '2026-10-09', '', 'active', '2025-10-10 06:04:03', '2025-10-10 06:04:03', 19),
(24, 'Appointment', 'PCWWF00022', 'VIJAY SIROHI', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-09', '2026-10-09', '', 'active', '2025-10-10 06:20:18', '2025-10-10 06:20:18', 20),
(25, 'Appointment', 'PCWWF00023', 'Anil kumar N', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-09', '2026-10-09', '', 'active', '2025-10-10 06:30:27', '2025-10-10 06:30:27', 21),
(26, 'Appointment', 'PCWWF00024', 'SANJEEV KUMAR SUMAN', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-10', '2026-10-10', '', 'active', '2025-10-10 06:44:27', '2025-10-10 06:44:27', 16),
(27, 'Appointment', 'PCWWF00025', 'SWAMINATHAN TRICHIRAPALLI GANESAN', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-10', '2026-10-10', '', 'active', '2025-10-10 06:57:49', '2025-10-10 06:57:49', 22),
(28, 'Appointment', 'PCWWF00026', 'LUKESH NARESH AGARWAL', 'MEMBER', 'AAPOITMENT AS MEMBER', '2025-10-10', '0000-00-00', '', 'active', '2025-10-10 07:20:04', '2025-10-10 07:20:04', 23),
(29, 'Appointment', 'PCWWF00027', 'Emmanuel John', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-10', '2026-10-10', '', 'active', '2025-10-10 07:30:10', '2025-10-10 07:30:10', 24),
(30, 'Appointment', 'PCWWF00028', 'SHAILENDRA MALVIY', 'MEMBER', 'APPOITING  AS MEMBER', '2025-10-10', '2026-10-10', '', 'active', '2025-10-10 07:39:08', '2025-10-10 07:39:08', 25),
(31, 'Appointment', 'PCWWF00029', 'VARGHESE B J', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-10', '2026-10-10', '', 'active', '2025-10-10 09:16:37', '2025-10-10 09:16:37', 27),
(32, 'Appointment', 'PCWWF00030', 'Suank Bujahi', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-10', '2026-10-10', '', 'active', '2025-10-10 09:26:04', '2025-10-10 09:26:04', 28),
(33, 'Appointment', 'PCWWF00031', 'GAMEPALLY KRISHNAMURTHY', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-10', '2026-10-10', '', 'active', '2025-10-10 09:37:41', '2025-10-10 09:37:41', 29),
(34, 'Appointment', 'PCWWF00032', 'RAJU RAM', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-10', '2026-10-10', '', 'active', '2025-10-10 10:51:42', '2025-10-10 10:51:42', 30),
(35, 'Appointment', 'PCWWF00033', 'Balaji Suresh', 'MEMBER', 'APPOINTING AS MEMBER', '2025-10-10', '2026-10-10', '', 'active', '2025-10-10 11:00:40', '2025-10-10 11:00:40', 31),
(36, 'Appointment', 'PCWWF00034', 'Trilochan Barik', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-11', '2026-10-11', '', 'active', '2025-10-11 06:13:02', '2025-10-11 06:13:02', 46),
(37, 'Appointment', 'PCWWF00035', 'Rajesh PG', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-12', '2026-10-12', '', 'active', '2025-10-12 05:21:41', '2025-10-12 05:21:41', 47),
(38, 'Appointment', 'PCWWF00036', 'Mahendran PM', 'MEMBER', 'APPOINTING AS MEMBER', '2025-10-12', '2026-10-12', '', 'active', '2025-10-12 05:30:40', '2025-10-12 05:30:40', 48),
(39, 'Appointment', 'PCWWF00037', 'parag Gada', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-12', '2026-10-12', '', 'active', '2025-10-12 07:25:11', '2025-10-12 07:25:11', 5),
(40, 'Appointment', 'PCWWF00038', 'Patel Jaimit Manojbhai', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-13', '2026-10-13', '', 'active', '2025-10-13 11:24:00', '2025-10-13 11:24:00', 49),
(41, 'Appointment', 'PCWWF00039', 'R.Praveen Kumar', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-14', '2025-10-14', '', 'active', '2025-10-14 06:33:51', '2025-10-14 06:33:51', 50),
(42, 'Appointment', 'PCWWF00040', 'M Sakthivel', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-15', '2026-10-15', '', 'active', '2025-10-15 05:34:37', '2025-10-15 05:34:37', 51),
(43, 'Appointment', 'PCWWF00041', 'Binaya Kumar Mallik', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-16', '2026-10-16', '', 'active', '2025-10-16 04:54:47', '2025-10-16 04:54:47', 53),
(44, 'Appointment', 'PCWWF00042', 'Hari Om Verma', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-17', '2026-10-17', '', 'active', '2025-10-17 06:25:35', '2025-10-17 06:25:35', 55),
(45, 'Appointment', 'PCWWF00043', 'Hari Om Verma', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-17', '2026-10-17', '', 'active', '2025-10-17 08:30:07', '2025-10-17 08:30:07', 55),
(46, 'Appointment', 'PCWWF00044', 'Raghavendra Raju R', 'MEMBER', 'APPOITING  AS MEMBER', '2025-10-21', '2025-10-21', '', 'active', '2025-10-21 05:29:38', '2025-10-21 05:29:38', 70),
(47, 'Appointment', 'PCWWF00045', 'Raghavendra Raju R', 'DISTRICT PRESIDENT', 'APPOITING AS DISTRICT PRESIDENT', '2025-10-21', '2026-10-21', '', 'active', '2025-10-21 05:46:31', '2025-10-21 05:46:31', 70),
(48, 'Appointment', 'PCWWF00046', 'Santanu Das', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-21', '2026-10-21', '', 'active', '2025-10-21 07:09:30', '2025-10-21 07:09:30', 4),
(49, 'Appointment', 'PCWWF00047', 'Balaji Suresh', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-22', '2026-07-14', '', 'active', '2025-10-22 07:52:27', '2025-10-22 07:52:27', 31),
(50, 'Appointment', 'PCWWF00048', 'A M ARUN VIJAY', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-22', '2026-10-21', '', 'active', '2025-10-22 10:40:59', '2025-10-22 10:40:59', 107),
(51, 'Appointment', 'PCWWF00049', 'SHUBHANKAR GHOSH', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-24', '2026-10-23', '', 'active', '2025-10-24 07:31:41', '2025-10-24 07:31:41', 153),
(52, 'Appointment', 'PCWWF00050', 'KOUSHIK SARKAR.', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-24', '2026-10-23', '', 'active', '2025-10-24 07:38:53', '2025-10-24 07:38:53', 148),
(53, 'Appointment', 'PCWWF00051', 'VINAYAK CHITLANGI', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-24', '2026-10-23', '', 'active', '2025-10-24 08:03:10', '2025-10-24 08:03:10', 154),
(54, 'Appointment', 'PCWWF00052', 'VINAYAK CHITLANGI', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-24', '2026-10-23', '', 'active', '2025-10-24 08:07:13', '2025-10-24 08:07:13', 154),
(55, 'Appointment', 'PCWWF00053', 'VINAYAK CHITLANGI', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-24', '2026-10-23', '', 'active', '2025-10-24 08:08:26', '2025-10-24 08:08:26', 154),
(56, 'Appointment', 'PCWWF00054', 'SANTOSH KUMAR SAHU', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-25', '2026-10-24', '', 'active', '2025-10-25 04:52:29', '2025-10-25 04:52:29', 155),
(57, 'Appointment', 'PCWWF00055', 'Rishi S Patela', 'MEMBER', 'APPOITING  AS MEMBER', '2025-10-25', '2026-10-24', '', 'active', '2025-10-25 07:09:30', '2025-10-25 07:09:30', 156),
(58, 'Appointment', 'PCWWF00056', 'GOPAL POPATBHAI PRAJAPATI', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-25', '2026-10-24', '', 'active', '2025-10-25 11:53:51', '2025-10-25 11:53:51', 157),
(59, 'Appointment', 'PCWWF00057', 'GOPAL POPATBHAI PRAJAPATI', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-25', '2026-10-24', '', 'active', '2025-10-25 12:46:27', '2025-10-25 12:46:27', 157),
(60, 'Appointment', 'PCWWF00058', 'KISHOR KUMAR SINHA', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-28', '2026-10-27', '', 'active', '2025-10-28 04:56:26', '2025-10-28 04:56:26', 158),
(61, 'Appointment', 'PCWWF00059', 'MIRZA ILIYASBAIG AMINBAIG', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-28', '2026-10-27', '', 'active', '2025-10-28 05:34:29', '2025-10-28 05:34:29', 159),
(62, 'Appointment', 'PCWWF00060', 'SAKTHIVEL PERIYASAMY', 'MEMBER', 'APPOITING  AS MEMBER', '2025-10-29', '2026-10-28', '', 'active', '2025-10-29 12:09:49', '2025-10-29 12:09:49', 161),
(63, 'Appointment', 'PCWWF00061', 'SHAIK ABDULLA.', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-30', '2026-10-29', '', 'active', '2025-10-30 05:18:43', '2025-10-30 05:18:43', 163),
(65, 'Appointment', 'PCWWF00063', 'DILIP KUMAR', 'MEMBER', 'APPOITING AS MEMBER', '2025-10-30', '2026-10-29', '', 'active', '2025-10-30 05:52:37', '2025-10-30 05:52:37', 162),
(67, 'Appointment', 'PCWWF00065', 'PANDITHURAI S', 'MEMBER', 'APPOINTING AS MEMBER', '2025-11-01', '2026-11-01', '', 'active', '2025-11-01 07:30:24', '2025-11-01 07:30:24', 164),
(71, 'Appointment', 'PCWWF00069', 'Gautam kumar', 'MEMBER', 'need', '2025-11-03', '2026-11-03', '', 'active', '2025-11-03 10:11:21', '2025-11-03 10:11:21', 166),
(73, 'Appointment', 'PCWWF00071', 'jayabalaji T', 'MEMBER', 'need', '2025-11-03', '2026-11-03', '', 'active', '2025-11-03 10:37:08', '2025-11-03 10:37:08', 167),
(74, 'Appointment', 'PCWWF00072', 'Tadepu veera venkata naga bhavani sankar', 'MEMBER', 'need', '2025-11-03', '2026-11-03', '', 'active', '2025-11-03 11:17:07', '2025-11-03 11:17:07', 168),
(76, 'Appointment', 'PCWWF00074', 'Dr.k. Loganathan', 'MEMBER', 'need', '2025-11-03', '0000-00-00', '', 'active', '2025-11-03 12:06:10', '2025-11-03 12:06:10', 165),
(77, 'Appointment', 'PCWWF00075', 'P valsalan', 'MEMBER', 'MEMBER', '2025-11-05', '2026-11-05', '', 'active', '2025-11-05 05:36:18', '2025-11-05 08:00:41', 169),
(78, 'Appointment', 'PCWWF00076', 'A.MAJEED KHOSHI', 'MEMBER', 'MEMBER', '2025-11-07', '2026-11-07', '', 'active', '2025-11-07 07:36:04', '2025-11-07 07:36:04', 171),
(79, 'Appointment', 'PCWWF00077', 'A.MAJEED KHOSHI', 'MEMBER', 'MEMBER', '2025-11-07', '2026-11-07', '', 'active', '2025-11-07 07:37:15', '2025-11-07 07:37:15', 171),
(80, 'Appointment', 'PCWWF00078', 'A.MAJEED KHOSHI', 'MEMBER', 'MEMBER', '2025-11-07', '2026-11-07', '', 'active', '2025-11-07 07:46:51', '2025-11-07 07:46:51', 171),
(81, 'Appointment', 'PCWWF00079', 'KISHAN RAI CHOUDHARY', 'MEMBER', 'MEMBER', '2025-11-07', '2026-11-07', '', 'active', '2025-11-07 09:50:34', '2025-11-07 09:50:34', 172),
(82, 'Appointment', 'PCWWF00080', 'Ankur Bansal', 'MEMBER', 'MEMBER', '2025-11-07', '2026-11-07', '', 'active', '2025-11-07 10:48:19', '2025-11-07 10:48:19', 173),
(83, 'Appointment', 'PCWWF00081', 'Ankur Bansal', 'MEMBER', 'MEMBER', '2025-11-07', '2026-11-07', '', 'active', '2025-11-07 12:52:55', '2025-11-07 12:52:55', 173),
(84, 'Appointment', 'PCWWF00082', 'KISHAN RAI CHOUDHARY', 'MEMBER', 'MEMBER', '2025-11-08', '2026-11-07', '', 'active', '2025-11-08 10:05:59', '2025-11-08 10:05:59', 172),
(85, 'Appointment', 'PCWWF00083', 'Buddesab S S', 'MEMBER', 'MEMBER', '2025-11-10', '2026-11-10', '', 'active', '2025-11-10 04:51:10', '2025-11-10 04:51:10', 177),
(86, 'Appointment', 'PCWWF00084', 'SHAMSHAD BEGAUM', 'MEMBER', 'MEMBER', '2025-11-13', '2026-11-13', '', 'active', '2025-11-13 10:29:07', '2025-11-13 10:29:07', 181),
(87, 'Participation', 'PCWWF00085', 'ABDUL ATIQUE ABDUL RAFIQUE SHEIKH', 'MEMBER', 'SUNILLL', '2025-11-11', '0026-02-11', '', 'active', '2025-11-13 10:31:09', '2025-11-13 10:31:09', 131),
(88, 'Achievement', 'PCWWF00086', 'A.MAJEED KHOSHI', 'MEMBER', 'MNJJJJK', '0020-11-11', '2025-11-11', '', 'active', '2025-11-13 10:33:34', '2025-11-13 10:33:34', 171),
(89, 'Birthday Wishes', 'PCWWF00087', 'A M ARUN VIJAY', '', 'MEMBER', '2025-11-11', '2026-12-11', '', 'active', '2025-11-13 10:36:07', '2025-11-13 10:36:07', 107),
(90, 'Appointment', 'PCWWF00088', 'Siraj Anwar', 'MEMBER', 'MEMBER', '2025-11-13', '2026-11-13', '6915beeb815a7.jfif', 'active', '2025-11-13 11:20:11', '2025-11-13 11:20:11', 182),
(91, 'Appointment', 'PCWWF00089', 'Siraj Anwar', 'MEMBER', 'MEMBER', '2025-11-13', '2026-11-13', '', 'active', '2025-11-13 11:21:19', '2025-11-13 11:21:19', 182),
(92, 'Appointment', 'PCWWF00090', 'Siraj Anwar', 'MEMBER', 'MEMBER', '2025-11-13', '2026-11-13', '', 'active', '2025-11-13 11:23:47', '2025-11-13 11:23:47', 182),
(93, 'Appointment', 'PCWWF00091', 'Siraj Anwar', 'MEMBER', 'MEMBER', '2025-11-13', '2026-11-13', '', 'active', '2025-11-13 11:28:17', '2025-11-13 11:28:17', 182),
(94, 'Appointment', 'PCWWF00092', 'Md.Siraj Anwar', 'MEMBER', 'MEMBER', '2025-11-13', '2026-11-13', '', 'active', '2025-11-13 11:37:22', '2025-11-13 11:37:22', 174),
(95, 'Appreciation', 'PCWWF00093', 'Md.Siraj Anwar', 'MEMBER', 'MEMBER', '2025-11-13', '2026-11-13', '', 'active', '2025-11-13 11:40:22', '2025-11-13 11:40:22', 174),
(96, 'Appointment', 'PCWWF00094', 'RAGHUNATH P', 'MEMBER', 'press', '2025-11-18', '2026-11-18', '', 'active', '2025-11-18 10:30:49', '2025-11-18 10:30:49', 211),
(97, 'Appointment', 'PCWWF00095', 'SUMAN CHAKRABORTY', 'MEMBER', 'PRESS', '2025-11-19', '2026-11-19', '', 'active', '2025-11-19 05:55:15', '2025-11-19 05:55:15', 212),
(98, 'Appointment', 'PCWWF00096', 'Dr. SREEKUMAR D MENON', 'MEMBER', 'MEMBER', '2025-11-20', '2026-11-20', '', 'active', '2025-11-20 10:17:45', '2025-11-20 10:17:45', 213),
(99, 'Appointment', 'PCWWF00097', 'SYED JOHN PASHA', 'MEMBER', 'MEMBER', '2025-11-20', '2026-11-20', '', 'active', '2025-11-20 10:27:07', '2025-11-20 10:27:07', 214),
(100, 'Appointment', 'PCWWF00098', 'RADHESHYAM MISHRA', 'MEMBER', 'MEMBER', '2025-11-25', '2026-11-25', '', 'active', '2025-11-25 10:47:52', '2025-11-25 10:47:52', 215),
(101, 'Appointment', 'PCWWF00099', 'M. ALAGARSAMY', 'MEMBER', 'MEMBER', '2025-11-25', '2026-11-25', '', 'active', '2025-11-25 11:03:15', '2025-11-25 11:03:15', 216),
(102, 'Appointment', 'PCWWF00100', 'M. ALAGARSAMY', 'MEMBER', 'Member', '2025-11-26', '2025-11-25', '', 'active', '2025-11-26 06:19:22', '2025-11-26 06:19:22', 216),
(103, 'Appointment', 'PCWWF00101', 'M. ALAGARSAMY', 'MEMBER', 'membere', '2025-11-26', '2025-11-26', '', 'active', '2025-11-26 06:20:54', '2025-11-26 06:20:54', 216),
(104, 'Appointment', 'PCWWF00102', 'Chevva Sundara Reddy', 'MEMBER', 'member', '2025-11-26', '2026-11-25', '', 'active', '2025-11-26 06:36:15', '2025-11-26 06:36:15', 217),
(105, 'Appointment', 'PCWWF00103', 'Reena Dillu', 'MEMBER', 'member', '2025-11-26', '2026-11-25', '', 'active', '2025-11-26 07:17:36', '2025-11-26 07:17:36', 218),
(106, 'Appointment', 'PCWWF00104', 'M. ALAGARSAMY', 'MEMBER', 'member', '2025-11-26', '2026-11-25', '', 'active', '2025-11-26 07:40:38', '2025-11-26 07:42:40', 216),
(107, 'Appointment', 'PCWWF00105', 'Mohd Iqbal kohli', 'MEMBER', 'member', '2025-11-27', '2026-11-26', '', 'active', '2025-11-27 05:25:55', '2025-11-27 05:25:55', 219),
(108, 'Appointment', 'PCWWF00106', 'DR.M.MOHAN', 'MEMBER', 'Member', '2025-12-01', '2026-11-30', '', 'active', '2025-12-01 05:46:15', '2025-12-01 05:46:15', 222),
(109, 'Appointment', 'PCWWF00107', 'M Ramalingeswara Rao', 'MEMBER', 'Member', '2025-12-10', '2026-12-10', '', 'active', '2025-12-10 06:37:25', '2025-12-10 06:37:25', 223),
(110, 'Appointment', 'PCWWF00108', 'M Ramalingeswara Rao', 'MEMBER', 'Member', '2025-12-11', '2026-12-11', '', 'active', '2025-12-10 06:39:20', '2025-12-10 06:39:20', 223),
(111, 'Appointment', 'PCWWF00109', 'Dr. P Kumar', 'MEMBER', 'MEMBER', '2025-12-14', '2026-12-14', '', 'active', '2025-12-14 09:16:22', '2025-12-14 09:16:22', 224),
(112, 'Appointment', 'PCWWF00110', 'Dr.K.Chinnaiah', 'MEMBER', 'MEMBER', '2025-12-18', '2026-12-18', '', 'active', '2025-12-18 06:31:59', '2025-12-18 06:31:59', 225),
(113, 'Appointment', 'PCWWF00111', 'Om Prakesh Sharma', 'MEMBER', 'Member', '2025-12-19', '2026-12-19', '', 'active', '2025-12-19 06:54:14', '2025-12-19 06:54:14', 226),
(114, 'Appointment', 'PCWWF00112', 'BALASUBRAMANI AYYANAR', 'MEMBER', 'MEMBER', '2025-12-28', '2026-12-28', '69510b744d2f0.jpg', 'active', '2025-12-28 10:50:28', '2025-12-28 10:50:28', 227),
(115, 'Appointment', 'PCWWF00113', 'PURNIMA', 'MEMBER', 'member', '2026-01-05', '2027-01-05', '', 'active', '2026-01-05 10:04:18', '2026-01-05 10:04:18', 229),
(116, 'Appointment', 'PCWWF00114', 'SK SOYEAD ALI', 'MEMBER', 'MEMBER', '2026-01-05', '2027-01-05', '', 'active', '2026-01-05 10:24:29', '2026-01-05 10:24:29', 230),
(117, 'Appointment', 'PCWWF00115', 'JYOTI HAZARIKA', 'MEMBER', 'MEMBER', '2026-01-05', '2027-01-05', '', 'active', '2026-01-05 10:42:25', '2026-01-05 10:42:25', 231),
(118, 'Appointment', 'PCWWF00116', 'Amit zutshi', 'Press Member', 'Member', '2026-01-06', '2027-01-06', '', 'active', '2026-01-06 06:55:42', '2026-01-06 06:55:42', 228),
(119, 'Appointment', 'PCWWF00117', 'Amit zutshi', 'Press Member', 'Member', '2026-01-06', '2027-01-06', '', 'active', '2026-01-06 06:57:36', '2026-01-06 06:57:36', 228),
(120, 'Appointment', 'PCWWF00118', 'JYOTI HAZARIKA', 'Press Member', 'Member', '2026-01-06', '2027-01-06', '', 'active', '2026-01-06 07:05:07', '2026-01-06 07:05:07', 231),
(121, 'Appointment', 'PCWWF00119', 'NABA JYOTI HAZARIKA', 'Press Member', 'Member', '2026-01-06', '2027-01-06', '', 'active', '2026-01-06 07:09:55', '2026-01-06 07:09:55', 231),
(122, 'Appointment', 'PCWWF00120', 'SK SOYEAD ALI', 'Press Member', 'Member', '2026-01-06', '2027-01-06', '', 'active', '2026-01-06 07:48:40', '2026-01-06 07:48:40', 230),
(123, 'Appointment', 'PCWWF00121', 'KHOKAN MOLLA', 'Press Member', 'Member', '2026-01-06', '2027-01-06', '', 'active', '2026-01-07 07:26:49', '2026-01-08 05:40:05', 232),
(124, 'Appointment', 'PCWWF00122', 'AMIT NARESH RAJWANI', 'MEMBER', 'MEMBER', '2026-01-15', '2027-01-15', '', 'active', '2026-01-16 04:32:43', '2026-01-16 04:32:43', 220),
(125, 'Appointment', 'PCWWF00123', 'Jitesh Arvindbhi Vyas', 'Press Member', 'Member', '2026-01-16', '2027-01-16', '', 'active', '2026-01-16 05:08:01', '2026-01-17 09:34:46', 233),
(126, 'Appointment', 'PCWWF00124', 'Jitesh Arvindbhai Vyas', 'Press Member', 'Member', '2026-01-16', '2027-01-16', '', 'active', '2026-01-17 09:36:02', '2026-01-17 09:36:02', 233),
(127, 'Appointment', 'PCWWF00125', 'Anitha Chinnala', 'MEMBER', 'Member', '2026-01-19', '2027-01-19', '', 'active', '2026-01-19 06:22:18', '2026-01-19 06:22:18', 234),
(128, 'Appointment', 'PCWWF00126', 'Rajib Das', 'Press Member', 'Member', '2026-01-19', '2027-01-19', '', 'active', '2026-01-19 09:18:32', '2026-01-19 09:18:32', 235),
(129, 'Appointment', 'PCWWF00127', 'Sushanta Kumar Das', 'Press Member', 'Member', '2026-01-21', '2027-01-21', '', 'active', '2026-01-21 06:38:14', '2026-01-21 06:38:14', 237),
(130, 'Appointment', 'PCWWF00128', 'Sachin Oberoi', 'Press Member', 'Member', '2026-01-21', '2027-01-21', '', 'active', '2026-01-21 09:16:19', '2026-01-21 09:16:19', 238),
(131, 'Appointment', 'PCWWF00129', 'MUNGLA WILLIAM', 'Press Member', 'appointment', '2026-01-22', '2027-01-22', '', 'active', '2026-01-22 10:42:55', '2026-01-22 10:42:55', 239),
(132, 'Appointment', 'PCWWF00130', 'Hakeem Mohd Haroon Sahab', 'MEMBER', 'APPOITMENT', '2026-01-27', '2026-01-27', '', 'active', '2026-01-27 07:57:50', '2026-01-27 07:57:50', 240),
(133, 'Appointment', 'PCWWF00131', 'Tusar Kanti Barman', 'Press Member', 'APPOINTMENT', '2026-01-31', '2027-01-31', '', 'active', '2026-01-31 10:27:10', '2026-01-31 10:48:37', 241),
(134, 'Appointment', 'PCWWF00132', 'PRASENJIT DUTTA', 'Press Member', 'APPOINTMENT', '2026-01-31', '2027-01-31', '', 'active', '2026-01-31 10:41:10', '2026-01-31 10:41:10', 242),
(135, 'Appointment', 'PCWWF00133', 'Suniel Thadani', 'Press Member', 'Member', '2026-02-02', '2027-02-02', '', 'active', '2026-02-02 09:40:23', '2026-02-02 09:40:23', 243),
(136, 'Appointment', 'PCWWF00134', 'RAJESH SHANTILAL BORUNDIYA', 'MEMBER', 'MEMBER', '2026-02-02', '2027-02-02', '', 'active', '2026-02-02 11:39:56', '2026-02-02 11:39:56', 244),
(137, 'Appointment', 'PCWWF00135', 'Digambar Sitaram Tayade', 'MEMBER', 'MEMBER', '2026-02-02', '2027-02-02', '', 'active', '2026-02-02 11:55:33', '2026-02-02 11:55:33', 245),
(138, 'Appointment', 'PCWWF00136', 'Soumen Ray.', 'Press Member', 'APPOITMENT', '2026-02-03', '2027-02-03', '', 'active', '2026-02-03 05:18:10', '2026-02-03 05:18:10', 246),
(139, 'Appointment', 'PCWWF00137', 'DR. RAJIB KUMAR MOHOTTO', 'Press Member', 'APPOITMENT', '2026-02-03', '2027-02-03', '', 'active', '2026-02-03 05:26:40', '2026-02-03 05:26:40', 247),
(140, 'Appointment', 'PCWWF00138', 'RUKHSAR AHMED', 'Press Member', 'APPOITMENT', '2026-02-03', '2027-02-03', '', 'active', '2026-02-03 05:33:44', '2026-02-03 05:33:44', 248),
(141, 'Appointment', 'PCWWF00139', 'SUDHEER SHARMA ACHARYA', 'Press Member', 'APPOITMENT', '2026-02-03', '2027-02-03', '', 'active', '2026-02-03 05:41:56', '2026-02-03 05:41:56', 249),
(142, 'Appointment', 'PCWWF00140', 'BABLU MARANDI', 'Press Member', 'APPOITMENT', '2026-02-03', '2027-02-03', '', 'active', '2026-02-03 05:52:34', '2026-02-03 05:52:34', 250),
(143, 'Appointment', 'PCWWF00141', 'PRASENJIT DUTTA', 'Press Member', 'APPOITMENT', '2026-02-03', '2027-02-03', '', 'active', '2026-02-03 06:08:52', '2026-02-03 06:08:52', 242),
(144, 'Appointment', 'PCWWF00142', 'PRABHESH MISHRA', 'Press Member', 'APPOITMENT', '2026-02-05', '2027-02-05', '', 'active', '2026-02-05 05:39:11', '2026-02-05 05:39:11', 251),
(145, 'Appointment', 'PCWWF00143', 'KIMMY ARORA', 'Press Member', 'APPOITMENT', '2026-02-05', '2027-02-05', '', 'active', '2026-02-05 10:44:19', '2026-02-05 10:44:19', 252),
(146, 'Appointment', 'PCWWF00144', 'CHNDRA PRAKASH KUSHWAHA', 'Press Member', 'APPOITMENT', '2026-02-05', '2027-02-05', '', 'active', '2026-02-05 11:03:54', '2026-02-05 11:03:54', 253),
(147, 'Appointment', 'PCWWF00145', 'BHARAT BHUSAN ROY', 'Press Member', 'APPOITMENT', '2026-02-05', '2027-02-05', '', 'active', '2026-02-05 11:20:56', '2026-02-05 11:20:56', 254),
(148, 'Appointment', 'PCWWF00146', 'BHARAT BHUSAN ROY', 'Press Member', 'APPOITMENT', '2026-02-05', '2027-02-05', '', 'active', '2026-02-05 11:36:18', '2026-02-05 11:45:31', 254),
(149, 'Appointment', 'PCWWF00147', 'MOHAMMAD IRFAN', 'Press Member', 'APPOITMENT', '2026-02-06', '2027-02-06', '', 'active', '2026-02-06 05:29:38', '2026-02-06 05:29:38', 255),
(150, 'Appointment', 'PCWWF00148', 'SUDIDA PADMA', 'Press Member', 'APPOITMENT', '2026-02-06', '2027-02-06', '', 'active', '2026-02-06 05:53:13', '2026-02-06 05:53:13', 256),
(151, 'Appointment', 'PCWWF00149', 'Uttam Nagoraoji Bramhawade', 'Press Member', 'APPOITMENT', '2026-02-06', '2027-02-06', '', 'active', '2026-02-06 11:03:20', '2026-02-06 11:03:20', 258),
(152, 'Appointment', 'PCWWF00150', 'JUGAL KISHOR AACHERA', 'Active Member', 'APPOITMENT', '2026-02-16', '2027-02-16', '', 'active', '2026-02-16 06:18:45', '2026-02-16 06:18:45', 259),
(153, 'Appointment', 'PCWWF00151', 'Minesh Choksi', 'Active Member', 'APPOITMENT', '2026-02-17', '2027-02-16', '', 'active', '2026-02-17 05:38:57', '2026-02-17 05:38:57', 260),
(154, 'Appointment', 'PCWWF00152', 'Adnan Sayed', 'Active Member', 'APPOITMENT', '2026-02-18', '2027-02-17', '', 'active', '2026-02-18 05:19:03', '2026-02-18 05:19:03', 261),
(155, 'Appointment', 'PCWWF00153', 'VISHNU SOPANRAO CHAPKE', 'Active Member', 'APPOITMENT', '2026-02-18', '2027-02-17', '', 'active', '2026-02-18 05:45:14', '2026-02-18 05:45:14', 262),
(156, 'Appointment', 'PCWWF00154', 'MAHENDRA SINGH', 'Active Member', 'APPOITMENT', '2026-02-18', '2027-02-18', '', 'active', '2026-02-18 06:02:03', '2026-02-18 06:02:03', 263),
(157, 'Appointment', 'PCWWF00155', 'KRISHAN KUMAR', 'Active Member', 'APPOITMENT', '2026-02-18', '2027-02-18', '', 'active', '2026-02-18 06:09:14', '2026-02-18 06:09:14', 264),
(158, 'Appointment', 'PCWWF00156', 'NAZIR AHMAD WANI', 'Active Member', 'APPOITMENT', '2026-02-18', '2027-02-18', '', 'active', '2026-02-18 06:19:17', '2026-02-18 06:19:17', 265),
(159, 'Appointment', 'PCWWF00157', 'KRISHAN KUMAR', 'Active Member', 'APPOITMENT', '2026-02-18', '2027-02-18', '', 'active', '2026-02-18 06:25:23', '2026-02-18 06:25:23', 264),
(160, 'Appointment', 'PCWWF00158', 'Riyaz ahmad mugloo', 'Active Member', 'APPOITMENT', '2026-02-18', '2026-02-18', '', 'active', '2026-02-18 06:55:59', '2026-02-18 06:55:59', 266),
(161, 'Appointment', 'PCWWF00159', 'MOHAMMAD NASIR MOHAMMAD', 'Active Member', 'APPOITMENT', '2026-02-18', '2027-02-18', '', 'active', '2026-02-18 07:02:45', '2026-02-18 07:02:45', 267),
(162, 'Appointment', 'PCWWF00160', 'Samar Mandal', 'Active Member', 'APPOITMENT', '2026-02-18', '2027-02-18', '', 'active', '2026-02-18 07:13:54', '2026-02-18 07:13:54', 268),
(163, 'Appointment', 'PCWWF00161', 'MUBASHIR HASSAN', 'Active Member', 'APPOITMENT', '2026-02-18', '2027-02-18', '', 'active', '2026-02-18 07:25:24', '2026-02-18 07:25:24', 269),
(164, 'Appointment', 'PCWWF00162', 'RAJIB DUTTA', 'Active Member', 'APPOITMENT', '2026-02-18', '2027-02-18', '', 'active', '2026-02-18 07:36:19', '2026-02-18 07:36:19', 270),
(165, 'Appointment', 'PCWWF00163', 'Minesh Choksi', 'Active Member', 'APPOITMENT', '2026-02-18', '2027-02-18', '', 'active', '2026-02-18 07:40:13', '2026-02-18 07:40:13', 260),
(166, 'Appointment', 'PCWWF00164', 'RAJIB DUTTA', 'Active Member', 'APPOITMENT', '2026-02-18', '2027-02-18', '', 'active', '2026-02-18 07:44:00', '2026-02-18 07:44:00', 270),
(167, 'Appointment', 'PCWWF00165', 'Adnan Sayed', 'Active Member', 'APPOITMENT', '2026-02-18', '2027-02-18', '', 'active', '2026-02-18 07:53:27', '2026-02-18 07:53:27', 261),
(0, 'Appointment', 'PCWWF00166', 'WELSI NALLARATHNAM J', 'Active Member', 'appoitment', '2026-02-21', '2027-02-20', '', 'active', '2026-02-21 05:23:03', '2026-02-21 05:23:03', 271),
(0, 'Appointment', 'PCWWF00167', 'KRISHNAKANT', 'Active Member', 'AAPPOITMENT', '2026-02-23', '2027-02-22', '', 'active', '2026-02-23 05:22:02', '2026-02-23 05:22:02', 272),
(0, 'Appointment', 'PCWWF00168', 'RAJ KUMAR SHARMA', 'Active Member', 'appoitment', '2026-02-23', '2027-02-22', '', 'active', '2026-02-23 05:49:03', '2026-02-23 05:49:03', 273),
(0, 'Appointment', 'PCWWF00169', 'SUNITI LASKAR', 'Active Member', 'APPOITMENT', '2026-02-24', '2027-02-24', '', 'active', '2026-02-24 05:17:52', '2026-02-24 05:17:52', 275),
(0, 'Appointment', 'PCWWF00170', 'DR. PATEL, HIRENBHAI. MAYUR BHAI', 'Active Member', 'APPOITMENT', '2026-02-24', '2026-02-24', '', 'active', '2026-02-24 05:30:01', '2026-02-24 05:30:01', 276),
(0, 'Appointment', 'PCWWF00171', 'Ekanath Dnyandev Kamble', 'Active Member', 'APPOITMENT', '2026-02-24', '2026-02-24', '', 'active', '2026-02-24 05:44:23', '2026-02-24 05:44:23', 278),
(0, 'Appointment', 'PCWWF00172', 'SAMIN SEKH', 'Active Member', 'APPOITMENT', '2026-02-24', '2026-02-24', '', 'active', '2026-02-24 05:59:07', '2026-02-24 05:59:07', 274),
(0, 'Appointment', 'PCWWF00173', 'Prof. Dr. Chintamani Rathore', 'Active Member', 'appoitment', '2026-02-26', '2027-02-25', '', 'active', '2026-02-26 05:33:08', '2026-02-26 05:33:08', 279),
(0, 'Appointment', 'PCWWF00174', 'SAMIN SEKH', 'Active Member', 'appoitment', '2026-02-26', '2027-02-26', '', 'active', '2026-02-26 05:40:17', '2026-02-26 05:40:17', 274),
(0, 'Appointment', 'PCWWF00175', 'SUSHIL KUMAR', 'Active Member', 'APPOITMENT', '2026-02-28', '2026-02-28', '', 'active', '2026-02-28 06:11:32', '2026-02-28 06:11:32', 280),
(0, 'Appointment', 'PCWWF00176', 'Md Firoz Alam', 'Active Member', 'appoitment', '2026-03-08', '2027-03-08', '', 'active', '2026-03-08 07:34:45', '2026-03-08 07:34:45', 281),
(0, 'Appointment', 'PCWWF00177', 'Pankaj Kumar Jain', 'Active Member', 'APPOITMENT', '2026-03-10', '2027-03-10', '', 'active', '2026-03-10 06:32:33', '2026-03-10 06:32:33', 282),
(0, 'Appointment', 'PCWWF00178', 'Dr.sakaldip paswan', 'Active Member', 'APPOITMENT', '2026-03-11', '2027-03-11', '', 'active', '2026-03-11 09:35:59', '2026-03-11 09:35:59', 283),
(0, 'Appointment', 'PCWWF00166', 'Rakesh Yadav', 'Active Member', 'APPOINTMENT', '2026-03-13', '2027-03-13', '', 'active', '2026-03-13 09:52:33', '2026-03-13 09:52:33', 284),
(0, 'Appointment', 'PCWWF00166', 'Suresh Sharma', 'Active Member', 'APPOITMENT', '2026-03-14', '2027-03-14', '', 'active', '2026-03-14 09:59:31', '2026-03-14 09:59:31', 285),
(0, 'Appointment', 'PCWWF00166', 'MADANJEET SINGH', 'Active Member', 'APPOITMENT', '2026-03-18', '2027-03-18', '', 'active', '2026-03-18 11:33:26', '2026-03-18 11:33:26', 286),
(0, 'Appointment', 'PCWWF00166', 'BALJINDER SINGH', 'Active Member', 'APPOITMENT', '2026-03-18', '2027-03-18', '', 'active', '2026-03-18 11:43:44', '2026-03-18 11:43:44', 287),
(0, 'Appointment', 'PCWWF00166', 'VIMAL TRIPATHI', 'Active Member', 'APPOITMENT', '2026-03-19', '2027-03-18', '', 'active', '2026-03-19 05:35:02', '2026-03-19 05:35:02', 288),
(0, 'Appointment', 'PCWWF00166', 'RAMESH DAS', 'Active Member', 'APPOITMENT', '2026-03-19', '2027-03-18', '', 'active', '2026-03-19 05:42:15', '2026-03-19 05:42:15', 289),
(0, 'Appointment', 'PCWWF00166', 'AHSAN AHMAD ANSARI', 'Active Member', 'APPOITMENT', '2026-03-24', '2027-03-24', '', 'active', '2026-03-24 10:17:31', '2026-03-24 10:17:31', 291),
(0, 'Appointment', 'PCWWF00166', 'Afiqa Ali', 'Active Member', 'APPOITMENT', '2026-03-24', '2026-03-24', '', 'active', '2026-03-24 10:54:16', '2026-03-24 10:54:16', 292),
(0, 'Appointment', 'PCWWF00166', 'VIMAL TRIPATHI', 'Active Member', 'APPOITMENT', '2026-03-24', '2027-03-24', '', 'active', '2026-03-24 11:03:23', '2026-03-24 11:03:23', 288),
(0, 'Appointment', 'PCWWF00166', 'RADHE KRISHAN VERMA', 'Active Member', 'APPOITMENT', '2026-03-24', '2027-03-24', '', 'active', '2026-03-24 11:22:39', '2026-03-24 11:22:39', 294),
(0, 'Appointment', 'PCWWF00166', 'Mukund Shankar Kamble', 'Active Member', 'APPOITMENT', '2026-03-24', '2027-03-24', '', 'active', '2026-03-24 11:26:04', '2026-03-24 11:26:04', 293),
(0, 'Appointment', 'PCWWF00166', 'Ghulam Mohd Lone', 'Active Member', 'APPOITMENT', '2026-03-24', '2027-03-24', '', 'active', '2026-03-24 11:35:50', '2026-03-24 11:35:50', 295),
(0, 'Appointment', 'PCWWF00166', 'VIKRAM SINGH', 'Active Member', 'APPOITMENT', '2026-03-27', '2027-03-26', '', 'active', '2026-03-27 05:12:31', '2026-03-27 05:12:31', 296),
(0, 'Appointment', 'PCWWF00166', 'Mohammad Aslam Khan', 'Active Member', 'APPOITMENT', '2026-03-27', '2027-03-26', '', 'active', '2026-03-27 05:22:34', '2026-03-27 05:22:34', 297),
(0, 'Appointment', 'PCWWF00166', 'BISWAJEET MOHAPATRA', 'Active Member', 'APPOITMENT', '2026-03-27', '2027-03-26', '', 'active', '2026-03-27 05:34:57', '2026-03-27 05:34:57', 298),
(0, 'Appointment', 'PCWWF00166', 'DIXIT KISHORKUMAR SHRIDHAR', 'Active Member', 'APPOITMENT', '2026-03-27', '2027-03-26', '', 'active', '2026-03-27 05:45:29', '2026-03-27 05:45:29', 299),
(0, 'Appointment', 'PCWWF00166', 'Shiv Kumar Baghel', 'Active Member', 'APPOITMENT', '2026-03-27', '2027-03-26', '', 'active', '2026-03-27 05:52:23', '2026-03-27 05:52:23', 300),
(0, 'Appointment', 'PCWWF00166', 'Afiqa Ali', 'Active Member', 'APPOITMENT', '2026-03-27', '2027-03-26', '', 'active', '2026-03-27 05:54:37', '2026-03-27 05:54:37', 292),
(0, 'Appointment', 'PCWWF00166', 'Ghulam Mohd Lone', 'Active Member', 'APPOITMENT', '2026-03-27', '2027-03-26', '', 'active', '2026-03-27 05:57:36', '2026-03-27 05:57:36', 295),
(0, 'Appointment', 'PCWWF00166', 'RITIK KUMAR', 'District President', 'APPOITMENT', '2026-03-30', '2027-03-29', '', 'active', '2026-03-30 05:27:19', '2026-03-30 05:27:19', 301),
(0, 'Appointment', 'PCWWF00166', 'Alok Kumar', 'Active Member', 'APPOITMENT', '2026-03-30', '2027-03-29', '', 'active', '2026-03-30 05:46:29', '2026-03-30 05:46:29', 302),
(0, 'Appointment', 'PCWWF00166', 'MOHAMMEDRAFEEQ', 'Active Member', 'APOOITMENT', '2026-03-30', '2027-03-30', '', 'active', '2026-03-30 06:05:00', '2026-03-30 06:05:00', 303),
(0, 'Appointment', 'PCWWF00166', 'VIJAY KUMAR JAISWAL', 'Active Member', 'APPOITMENT', '2026-03-30', '2027-03-30', '', 'active', '2026-03-30 06:13:52', '2026-03-30 06:13:52', 304),
(0, 'Appointment', 'PCWWF00166', 'NILAMCHANDR NARAYAN WAKCHAURE', 'Active Member', 'APPOITMENT', '2026-03-30', '2027-03-30', '', 'active', '2026-03-30 06:25:40', '2026-03-30 06:25:40', 305),
(0, 'Appointment', 'PCWWF00166', 'SATENDRA RAI', 'Active Member', 'APPOITMENT', '2026-03-30', '2027-03-30', '', 'active', '2026-03-30 06:31:49', '2026-03-30 06:31:49', 306),
(0, 'Appointment', 'PCWWF00166', 'ASHWINI ABHIJEET KULKARNI', 'Active Member', 'APPOITMENT', '2026-04-01', '2027-04-01', '', 'active', '2026-04-01 06:22:06', '2026-04-01 06:22:06', 307),
(0, 'Appointment', 'PCWWF00166', 'Uttam Maheswari k', 'Active Member', 'APPOITMENT', '2026-04-01', '2027-04-01', '', 'active', '2026-04-01 07:56:08', '2026-04-01 07:56:08', 308),
(0, 'Appointment', 'PCWWF00166', 'LAJPAT RAI', 'Active Member', 'APPOITMENT', '2026-04-04', '2027-04-03', '', 'active', '2026-04-04 05:07:43', '2026-04-04 05:07:43', 309),
(0, 'Appointment', 'PCWWF00166', 'Uttam Maheswari k', 'Active Member', 'APPOITMENT', '2026-04-06', '2027-04-05', '', 'active', '2026-04-06 05:30:53', '2026-04-06 05:30:53', 308),
(0, 'Appointment', 'PCWWF00166', 'MAHABOOB ALI ADVANI', 'Active Member', 'APPOITMENT', '2026-04-08', '2027-04-07', '', 'active', '2026-04-08 05:11:37', '2026-04-08 05:11:37', 310);

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','in_progress','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `congratulations_certificates`
--

CREATE TABLE `congratulations_certificates` (
  `id` int(11) NOT NULL,
  `certificate_no` varchar(20) NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `recipient_address` text NOT NULL,
  `certificate_date` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_info`
--

CREATE TABLE `contact_info` (
  `id` int(11) NOT NULL,
  `info_type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','resolved') DEFAULT 'pending',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `mobile`, `email`, `topic`, `description`, `created_at`, `status`, `updated_at`) VALUES
(0, 'Canchi sathya prasad', '9043722941', 'canchics@gmail.com', 'Membership', 'Membership card expired on 05.02.2026', '2026-02-13 11:55:43', 'pending', NULL),
(0, 'Raymonddek', '3223724272', 'no.reply.KennethSmith@gmail.com', 'A novel new approach to advertising.', 'Good morning! pressclubwelfareworldwidefoundation.org \r\n \r\nDid you know that it is possible to send appeal lawfully and legitimately? \r\nWhen such business proposals are sent, no personal data is used, and messages are sent to forms specifically designed to receive messages and appeals securely. As Feedback Forms messages are taken into great consideration, they won&apos;t be classified as spam. \r\nWe give you a chance to experience our service for free. \r\nWe are able to dispatch up to 50,000 messages on your behalf. \r\n \r\nThe cost of sending one million messages is $59. \r\n \r\nThis message was automatically generated. \r\n \r\nContact us. \r\nTelegram - https://t.me/FeedbackFormEU \r\nWhatsApp - +375259112693 \r\nWhatsApp  https://wa.me/+375259112693 \r\nWe only use chat for communication.', '2026-02-14 19:07:15', 'pending', NULL),
(0, 'NikitaOccaf', '4180671075', 'nikitafofanov46@gmail.com', 'Grant for Accessibility Improvements', 'Hi! Hope your day is going smoothly. \r\n \r\nHello, I offer financial backing to quality websites. Your platform appears worthy of consideration. Would you like to discuss possibilities? Please contact me on WhatsApp +447401141794', '2026-02-18 03:10:15', 'pending', NULL),
(0, 'AndrewNic', '7738426455', 'no.reply.MarkGirard@gmail.com', 'Your emails are guaranteed to be delivered.', 'Good morning! pressclubwelfareworldwidefoundation.org \r\n \r\nDid you know that it is possible to send letter absolutely legally? \r\nWhen such requests are sent, no personal data is used, and messages are sent to forms specifically designed to receive messages and appeals securely. Seen as significant, messages sent by means of Feedback Forms are not labeled as spam. \r\nTrу out our service without paying a dіme! \r\nOn your behalf, we can dispatch up to 50,000 messages. \r\n \r\nThe cost of sending one million messages is $59. \r\n \r\nThis offer is automatically generated. \r\n \r\nContact us. \r\nTelegram - https://t.me/FeedbackFormEU \r\nWhatsApp - +375259112693 \r\nWhatsApp  https://wa.me/+375259112693 \r\nWe only use chat for communication.', '2026-02-20 00:00:32', 'pending', NULL),
(0, 'vijay sirohi', '8168879095', 'vjsirohi1980@gmail.com', 'no information regarding my registration id card', 'respected sir/madam\r\n\r\nsir i am registered on your website https://pressclubwelfareworldwidefoundation.org/ you are sent my id card online but you are not deliver my hard kit include sticker, id card with ribbon id  mic long time and not information on your website and when i call you. your team is not responding please send me solution as soon as possible\r\n\r\nThank you', '2026-02-23 06:46:28', 'pending', NULL),
(0, 'NANDIRAJU RAJA HANUMANTHA RAO', '9290842472', 'raja.hanumantharao@gmail.com', 'website My Photo display issue', 'Dear sir/Madam My ID number PCWWF/50952. My id place my image not showing i am complaint to many times because your not responding. so please check and correctly setting my id image possession on our website.', '2026-02-24 06:42:07', 'pending', NULL),
(0, 'Jorgeelell', '4101682600', 'tomaszlech80@yahoo.com', 'Financing from €1M to €25M', 'We offer financing and refinancing solutions for projects, businesses, and private individuals. \r\n \r\nWe are not angel investors and we operate with full transparency. \r\n \r\nOwn capital is mandatory for minimum 10% from the total requested ! \r\n \r\nLoan amounts range from €1 million to €25 million, with terms up to 15 years. \r\n \r\nInterest rates vary between 3% and 3.6%, depending on the amount and duration. \r\n \r\nIf you are seeking reliable funding, we are ready to assist. \r\n \r\nFor more information, please contact us: \r\nEmail: info@venelpark.nl \r\nPhone: +31 629 106 017', '2026-03-25 21:20:37', 'pending', NULL),
(0, 'Olivier Gabriel Balzac', '0051170876', 'projectoffice111@gmail.com', 'Ich würde Ihre Antwort in diesem Fall sehr schätzen, da es dringend ist.', 'Gunten Tag, \r\nMein Name ist Olivier Gabriel Balzac, Ich habe Ihnen zuvor eine Nachricht bezüglich einer Transaktion in Höhe von 13, 5 Millionen US-Dollar gesendet, die mein verstorbener Kunde vor seinem plötzlichen Tod hinterlassen hat. \r\nIch melde mich noch einmal bei Ihnen, da ich nach Durchsicht Ihres Profils fest davon überzeugt bin, dass Sie die Transaktion sehr gut mit mir abwickeln können. \r\nWenn Sie interessiert sind, möchte ich darauf hinweisen, dass nach der Transaktion 10% dieses Geldes unter Wohltätigkeitsorganisationen aufgeteilt werden sollen, während die restlichen 90% zwischen uns aufgeteilt werden, also jeweils 45%. \r\nDiese Transaktion ist zu 100% risikofrei. Bitte antworten Sie mir so schnell wie möglich, um weitere Erläuterungen zur Transaktion zu erhalten, meine E-mail: info@balzacavocate.com \r\nHochachtungsvoll, Ich hoffe von Ihnen so schnell wie möglich zu hören \r\nMit freundlichen Grüssen. \r\nOlivier Gabriel Balzac, \r\nRechtsanwalt. \r\nPhone. +33 756 850 084 \r\nE-mail: info@balzacavocate.com', '2026-03-26 13:41:40', 'pending', NULL),
(0, 'NAERTREGE430312NERTHRTYHR', '5184254854', 'obalfnqq@polosmail.com', 'TOTUTYJ430312TIGFHFGER', 'MERTHYTJTJ430312MARTHHDF', '2026-03-30 20:58:42', 'pending', NULL),
(0, 'NARYTHY1503949NERTHRTYHR', '4263343378', 'avtxktjc@fringmail.com', 'TOHHRT1503949TIGFHFGER', 'MERTYHRTHYHT1503949MAVNGHJTH', '2026-04-01 05:14:03', 'pending', NULL),
(0, 'NARYTHY1232458NEWETREWT', '2368118523', 'amzllzeh@tacoblastmail.com', 'TOTUTYJ1232458TIGFNMYUM', 'MEJTYJY1232458MARETRYTR', '2026-04-09 23:19:58', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `disclaimer_content`
--

CREATE TABLE `disclaimer_content` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) NOT NULL,
  `section_content` text NOT NULL,
  `section_icon` varchar(100) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `disclaimer_content`
--

INSERT INTO `disclaimer_content` (`id`, `section_title`, `section_content`, `section_icon`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'General Information', 'The information provided by Press Club Welfare Worldwide Foundation on its website, application forms, and communications is for general informational and registration purposes only.', 'fa-info-circle', 1, 'active', '2025-12-02 17:20:16', NULL),
(2, 'Application Outcome Disclaimer', 'Submission of a membership or nomination application or payment of any associated fee does **not guarantee** approval or the granting of any membership or award. The decision rests solely with the Foundation\'s governing body and evaluation committee, and their decision is final. No correspondence regarding the selection criteria or results will be entertained.', 'fa-times-circle', 2, 'active', '2025-12-02 17:20:16', NULL),
(3, 'Event and Ceremony Disclaimer', 'The Foundation reserves the right to modify or cancel the date, venue, or format of any event, ceremony, or program due to circumstances beyond its control (e.g., force majeure, governmental restrictions). The Foundation will not be liable for any costs incurred by members/recipients (e.g., travel, accommodation) due to such changes.', 'fa-calendar-times', 3, 'active', '2025-12-02 17:20:16', NULL),
(4, 'Verification Disclaimer', 'The Foundation relies on the honesty and accuracy of the information provided by applicants. However, the Foundation does not warrant the completeness or accuracy of any achievement, bio, or document submitted by a third party. Applicants are fully responsible for the veracity of their submitted claims.', 'fa-user-shield', 4, 'active', '2025-12-02 17:20:16', NULL),
(5, 'Limitation of Liability', 'The Foundation, its directors, and affiliates shall not be liable for any direct, indirect, incidental, or consequential damages resulting from the use of its services or reliance on any information provided.', 'fa-balance-scale', 5, 'active', '2025-12-02 17:20:16', NULL),
(6, 'Intellectual Property', 'All copyrighted materials, ID card designs, award designs, and content are the sole property of the Foundation and protected by law.', 'fa-copyright', 6, 'active', '2025-12-02 17:20:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `father_name` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text NOT NULL,
  `pan_card` varchar(20) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `order_id` varchar(100) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  `payment_method` enum('online','offline') NOT NULL,
  `user_id` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'User who sent the email',
  `recipient_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` enum('sent','failed') DEFAULT 'sent',
  `sent_at` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `event_time`, `location`, `image`, `status`, `created_at`) VALUES
(18, 'Community Charity Run', 'A 5K run to raise funds for local education programs. All ages welcome! Enjoy post-run refreshments and entertainment.', '2025-12-01', '07:30:00', 'Marine Drive, Mumbai, India', NULL, 'active', '2025-10-21 17:08:28'),
(0, 'Shatabdi Bhawan Cuttack Odisha', 'AWARDS CEREMONY', '2026-06-07', '03:06:00', 'Shatabdi Bhawan Cuttack Odisha', 'event_69d9df1b3773a.jpeg', 'active', '2026-04-11 05:41:47'),
(0, 'GANDHI BHAWAN,KUMARAKRUPA ROAD,(NEAR SHIVANANDA CIRCLE)KUMARA PARK EAST,SESHADRIPURAM,BENGALURU,560001', 'AWARDS CEREMONY', '2026-05-24', '03:06:00', 'GANDHI BHAWAN,KUMARAKRUPA ROAD,(NEAR SHIVANANDA CIRCLE)KUMARA PARK EAST,SESHADRIPURAM,BENGALURU,560001', 'event_69d9df746ee5c.jpg', 'active', '2026-04-11 05:43:16'),
(0, 'Volagiri Kalamandir 107,Netaji Subhas Road, Kadamtala, Howrah', 'AWARDS CEREMONY', '2026-05-31', '03:06:00', 'Volagiri Kalamandir 107,Netaji Subhas Road, Kadamtala, Howrah', 'event_69d9dfc81f415.jpg', 'active', '2026-04-11 05:44:40'),
(0, 'SARDAR VALLABH BHAI PATEL POLYTECHNIC BHOPAL M.P', 'AWARDS CEREMONY', '2026-06-28', '03:06:00', 'SARDAR VALLABH BHAI PATEL POLYTECHNIC BHOPAL M.P', 'event_69d9e022ae36b.jpg', 'active', '2026-04-11 05:46:10');

-- --------------------------------------------------------

--
-- Table structure for table `footer_settings`
--

CREATE TABLE `footer_settings` (
  `id` int(11) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `footer_settings`
--

INSERT INTO `footer_settings` (`id`, `section_name`, `title`, `content`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(5, 'about_ndf', 'About Us', 'Press Club Welfare Worldwide Foundation is a registered non-profit organization dedicated to supporting journalists, media professionals, and press freedom globally. Registration Number: U89000DL2024NPL437018. We focus on journalist welfare, media rights protection, professional development, and community support for media workers worldwide. Our aim is to promote press freedom, journalistic integrity, and sustainable development in the media industry. <a href=\"aboutus.php\">Read More</a>', 1, 'active', '2025-09-27 02:13:00', '2025-10-06 19:16:27'),
(6, 'contact_us', 'Contact Us', '<ul class=\"footer-links\">\r\n\r\n<li><i class=\"fas fa-map-marker-alt\"></i> Mahabir Nagar, New Delhi-110018 India</li>\r\n\r\n<li><i class=\"fas fa-phone\"></i> +91 9479073208</li>\r\n\r\n<li><i class=\"fas fa-envelope\"></i> <a href=\"mailto:help@pressclubwelfareworldwidefoundation.org\">help@pressclubwelfareworldwidefoundation.org</a></li>\r\n\r\n<li><i class=\"fas fa-globe\"></i> <a href=\"https://www.pressclubwelfareworldwidefoundation.org/\" target=\"_blank\">pressclubwelfareworldwidefoundation.org</a></li>\r\n\r\n</ul>', 2, 'active', '2025-09-27 02:13:00', '2025-10-07 06:30:00'),
(7, 'quick_links', 'Quick Links', '<ul class=\"footer-links\">\r\n\r\n<li><a href=\"index.php\"><i class=\"fas fa-home\"></i> Home</a></li>\r\n\r\n<li><a href=\"about.php\"><i class=\"fas fa-info-circle\"></i> About Us</a></li>\r\n\r\n<li><a href=\"contact-us.php\"><i class=\"fas fa-phone\"></i> Contact Us</a></li>\r\n\r\n<li><a href=\"donation-form.php\"><i class=\"fas fa-heart\"></i> Donate</a></li>\r\n\r\n<li><a href=\"users-apply-form.php\"><i class=\"fas fa-users\"></i> Membership</a></li>\r\n\r\n</ul>', 3, 'active', '2025-09-27 02:13:00', '2025-10-07 06:30:00'),
(8, 'our_work', 'Our Work', '<ul class=\"footer-links\">\r\n\r\n<li><i class=\"fas fa-newspaper\"></i> Journalist Welfare & Support</li>\r\n\r\n<li><i class=\"fas fa-shield-alt\"></i> Press Freedom Protection</li>\r\n\r\n<li><i class=\"fas fa-graduation-cap\"></i> Media Professional Development</li>\r\n\r\n<li><i class=\"fas fa-gavel\"></i> Legal Aid for Media Workers</li>\r\n\r\n<li><i class=\"fas fa-users\"></i> Community Building & Networking</li>\r\n\r\n</ul>', 4, 'active', '2025-09-27 02:13:00', '2025-10-07 06:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `image`) VALUES
(7, 'gallery_68e790b685ee6.jpg'),
(8, 'gallery_68e790daef37e.jpg'),
(9, 'gallery_68e791380b0a6.jpg'),
(10, 'gallery_68e7915e6219e.jpg'),
(11, 'gallery_68e79175708df.jpg'),
(12, 'gallery_68e79191e4f71.jpg'),
(13, 'gallery_68e791afae29c.jpg'),
(14, 'gallery_68e791c761b51.jpg'),
(15, 'gallery_68e792117aa6e.jpg'),
(16, 'gallery_68e79244bfbe8.jpg'),
(17, 'gallery_68e7926182352.jpg'),
(18, 'gallery_68e7927834242.jpg'),
(19, 'gallery_68e7928d063d9.jpg'),
(20, 'gallery_68e792a2dbd67.jpg'),
(21, 'gallery_68e792bed1cdc.jpg'),
(22, 'gallery_68e792d650a9f.jpg'),
(23, 'gallery_68e792ecc0142.jpg'),
(24, 'gallery_68e793082b494.jpg'),
(25, 'gallery_68e7931e6f9f9.jpg'),
(26, 'gallery_68e79331dcc23.jpg'),
(27, 'gallery_68e79349efe0e.jpg'),
(28, 'gallery_68e7935eab668.jpg'),
(29, 'gallery_68e793804936d.jpg'),
(30, 'gallery_68e793b530b7c.jpg'),
(31, 'gallery_68e793c8f269b.jpg'),
(32, 'gallery_68e793dd85060.jpg'),
(33, 'gallery_68e793f054643.jpg'),
(34, 'gallery_68e7940420b73.jpg'),
(35, 'gallery_68e79417359e2.jpg'),
(36, 'gallery_68e794301189d.jpg'),
(37, 'gallery_68e794495c850.jpg'),
(38, 'gallery_68e7945db99bf.jpg'),
(39, 'gallery_68e79471b2d04.jpg'),
(40, 'gallery_68e79485aa319.jpg'),
(41, 'gallery_68e794993f536.jpg'),
(42, 'gallery_68e794af2d04d.jpg'),
(43, 'gallery_68e794c3ce710.jpg'),
(44, 'gallery_68e794d976a9a.jpg'),
(45, 'gallery_68e794f28a839.jpg'),
(46, 'gallery_68e795098e5fc.jpg'),
(47, 'gallery_68e7951ec8e37.jpg'),
(48, 'gallery_68e795374134e.jpg'),
(49, 'gallery_68e7954ca75c1.jpg'),
(50, 'gallery_68e7956608eac.jpg'),
(51, 'gallery_68e7957fd4d77.jpg'),
(52, 'gallery_68e7959934fe9.jpg'),
(53, 'gallery_68e795aeba4d4.jpg'),
(54, 'gallery_68e795cd7c373.jpg'),
(55, 'gallery_68e7962776f79.jpg'),
(56, 'gallery_68e7963e940bd.jpg'),
(57, 'gallery_68e796564ab54.jpg'),
(58, 'gallery_68e7966ae9241.jpg'),
(59, 'gallery_68e7968141d5b.jpg'),
(60, 'gallery_68e7969e8940a.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `generated_id_cards`
--

CREATE TABLE `generated_id_cards` (
  `id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `id_card_file` varchar(255) DEFAULT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `honorary_awards`
--

CREATE TABLE `honorary_awards` (
  `id` int(11) NOT NULL,
  `award_no` varchar(50) NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `profession` varchar(255) DEFAULT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `pincode` varchar(20) DEFAULT NULL,
  `award_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `venue` varchar(255) NOT NULL,
  `award_date` date NOT NULL,
  `registration_no` varchar(100) NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `honorary_awards`
--

INSERT INTO `honorary_awards` (`id`, `award_no`, `recipient_name`, `mobile`, `email`, `gender`, `profession`, `qualification`, `address`, `state`, `city`, `pincode`, `award_name`, `category`, `content`, `venue`, `award_date`, `registration_no`, `photo_path`, `status`, `created_at`, `updated_at`) VALUES
(0, 'HA000001', 'Dr. ANURAG MISHRA', '09437254840', 'sarada@gmail.com', 'Male', '', '', 'NEAR SSVM,NANDAPUR ROAD SEMLIGUDA KORAPUT 764036', 'Odisha', 'Koraput', '764036', 'Bharat Excellence Business Award', 'EDUCATION', 'Dr. David Uttamkumar Rajarao has been awarded the Best Education Award in the category of International Educational Award for his outstanding contributions to the field of education. With a career spanning over three decades, Dr. Rajarao has made significant contributions to the advancement of education through his innovative teaching methods, research, and leadership. He has been recognized for his exceptional dedication and commitment towards shaping the minds of young learners, and for his role in promoting quality education globally. His passion for education has not only impacted the lives of his students, but also inspired many others to pursue excellence in the field. This prestigious award serves as a testament to Dr. Rajarao&apos;s invaluable contributions towards education and his unwavering dedication to improving the educational landscape on an international level. It is a well-deserved recognition of his hard work and unwavering efforts in promoting education and shaping the leaders of tomorrow.', 'GANJAM KALA PARISHAD, GANJAM, BERHAMPUR, GANJAM, ODISHA / 29TH DEC 2025', '2026-03-16', 'PCWWF/2026/0001', 'file_69b7e732984651.39032982.jfif', 'active', '2026-03-16 11:19:14', NULL),
(0, 'HA000002', 'Dr. David Uttamkumar Rajarao', '', '', 'Male', '', '', '', '', '', '', 'Dr. B.R. Amedkar Award', 'Education,Social Justice, Media And Sports', 'Dr. David Uttamkumar Rajarao is a renowned figure in the field of education, social justice, media and sports. He has dedicated his life to promoting these areas and bringing positive changes in society. His efforts have not gone unnoticed, as he has recently been honored with the prestigious Dr. B.R. Ambedkar award in recognition of his outstanding contributions. This award, named after the esteemed Indian social reformer and politician Dr. B.R. Ambedkar, is given to individuals who have made significant achievements in the fields of education, social justice, media and sports. Dr. Rajarao&amp;apos;s dedication and hard work in these areas have had a profound impact on society, making him a deserving recipient of this prestigious award. He serves as an inspiration for others to work towards creating a better and more equitable world for all.', 'Volagiri Kalamandir 107,Netaji Subhas Road, Kadamtala, Howrah', '2026-03-30', 'PCWWF/2026/0002', 'file_69ca4243996b19.94499824.jfif', 'active', '2026-03-30 09:28:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `membership_designations`
--

CREATE TABLE `membership_designations` (
  `id` int(11) NOT NULL,
  `membership_type` enum('active','gram_panchayat','block','tehsil','district','mandal','state','national') NOT NULL,
  `designation` varchar(255) NOT NULL,
  `designation_hindi` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `membership_designations`
--

INSERT INTO `membership_designations` (`id`, `membership_type`, `designation`, `designation_hindi`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'active', 'Active Member', 'Active Member', 1, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(2, 'gram_panchayat', 'Village Coordinator', 'Village Coordinator', 1, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(3, 'gram_panchayat', 'Village Representative', 'Village Representative', 2, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(4, 'gram_panchayat', 'Lead Volunteer', 'Lead Volunteer', 3, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(5, 'gram_panchayat', 'Women Empowerment Incharge', 'Women Empowerment Incharge', 4, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(6, 'gram_panchayat', 'Education Coordinator', 'Education Coordinator', 5, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(7, 'gram_panchayat', 'Health Facilitator', 'Health Facilitator', 6, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(8, 'gram_panchayat', 'Agriculture Support Officer', 'Agriculture Support Officer', 7, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(9, 'gram_panchayat', 'Youth Cell Coordinator', 'Youth Cell Coordinator', 8, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(10, 'gram_panchayat', 'Data Collector', 'Data Collector', 9, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(11, 'gram_panchayat', 'Public Relations Officer', 'Public Relations Officer', 10, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(12, 'gram_panchayat', 'Legal Aid Assistant', 'Legal Aid Assistant', 11, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(13, 'gram_panchayat', 'Fundraising Assistant', 'Fundraising Assistant', 12, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(14, 'gram_panchayat', 'Training Assistant', 'Training Assistant', 13, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(15, 'gram_panchayat', 'Digital Services Volunteer', 'Digital Services Volunteer', 14, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(16, 'gram_panchayat', 'Sanitation Volunteer', 'Sanitation Volunteer', 15, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(17, 'gram_panchayat', 'Child Development Worker', 'Child Development Worker', 16, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(18, 'gram_panchayat', 'Grievance Redressal Officer', 'Grievance Redressal Officer', 17, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(19, 'gram_panchayat', 'Community Meeting Coordinator', 'Community Meeting Coordinator', 18, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(20, 'block', 'Block Coordinator', 'Block Coordinator', 1, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(21, 'block', 'Block Incharge', 'Block Incharge', 2, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(22, 'block', 'Block Representative', 'Block Representative', 3, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(23, 'block', 'Block Officer', 'Block Officer', 4, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(24, 'block', 'Block Assistant', 'Block Assistant', 5, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(25, 'block', 'Block Media Incharge', 'Block Media Incharge', 6, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(26, 'block', 'Women Incharge (Block)', 'Women Incharge (Block)', 7, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(27, 'block', 'Education Coordinator (Block)', 'Education Coordinator (Block)', 8, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(28, 'block', 'Health Assistant (Block)', 'Health Assistant (Block)', 9, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(29, 'block', 'Youth Representative (Block)', 'Youth Representative (Block)', 10, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(30, 'block', 'Agriculture Worker (Block)', 'Agriculture Worker (Block)', 11, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(31, 'block', 'Training Incharge (Block)', 'Training Incharge (Block)', 12, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(32, 'block', 'Data Assistant (Block)', 'Data Assistant (Block)', 13, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(33, 'block', 'Promotion Incharge (Block)', 'Promotion Incharge (Block)', 14, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(34, 'block', 'Development Assistant (Block)', 'Development Assistant (Block)', 15, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(35, 'block', 'Child Welfare Incharge (Block)', 'Child Welfare Incharge (Block)', 16, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(36, 'block', 'Grievance Officer (Block)', 'Grievance Officer (Block)', 17, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(37, 'tehsil', 'Tehsil Coordinator', 'Tehsil Coordinator', 1, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(38, 'tehsil', 'Tehsil Incharge', 'Tehsil Incharge', 2, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(39, 'tehsil', 'Tehsil Vice President', 'Tehsil Vice President', 3, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(40, 'tehsil', 'Tehsil Secretary', 'Tehsil Secretary', 4, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(41, 'tehsil', 'Women Incharge (Tehsil)', 'Women Incharge (Tehsil)', 5, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(42, 'tehsil', 'Education Incharge (Tehsil)', 'Education Incharge (Tehsil)', 6, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(43, 'tehsil', 'Health Coordinator (Tehsil)', 'Health Coordinator (Tehsil)', 7, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(44, 'tehsil', 'Youth Coordinator (Tehsil)', 'Youth Coordinator (Tehsil)', 8, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(45, 'tehsil', 'Publicity & Promotion Incharge', 'Publicity & Promotion Incharge', 9, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(46, 'tehsil', 'Agriculture Advisor (Tehsil)', 'Agriculture Advisor (Tehsil)', 10, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(47, 'tehsil', 'Training Incharge (Tehsil)', 'Training Incharge (Tehsil)', 11, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(48, 'tehsil', 'Legal Advisor (Tehsil)', 'Legal Advisor (Tehsil)', 12, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(49, 'tehsil', 'Child Welfare Officer (Tehsil)', 'Child Welfare Officer (Tehsil)', 13, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(50, 'tehsil', 'Grievance Redressal Officer (Tehsil)', 'Grievance Redressal Officer (Tehsil)', 14, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(51, 'tehsil', 'Media Incharge (Tehsil)', 'Media Incharge (Tehsil)', 15, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(52, 'tehsil', 'Technical Assistant (Tehsil)', 'Technical Assistant (Tehsil)', 16, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(53, 'tehsil', 'Digital Services Coordinator (Tehsil)', 'Digital Services Coordinator (Tehsil)', 17, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(54, 'tehsil', 'Development Officer (Tehsil)', 'Development Officer (Tehsil)', 18, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(55, 'district', 'District President', 'District President', 1, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(56, 'district', 'Youth Wing President', 'Youth Wing President', 2, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(57, 'district', 'District Vice President', 'District Vice President', 3, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(58, 'district', 'Women Wing President', 'Women Wing President', 4, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(59, 'district', 'Youth Wing Vice President', 'Youth Wing Vice President', 5, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(60, 'district', 'Women Wing Vice President', 'Women Wing Vice President', 6, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(61, 'district', 'General Secretary', 'General Secretary', 7, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(62, 'district', 'Secretary', 'Secretary', 8, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(63, 'district', 'Research Officer', 'Research Officer', 9, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(64, 'district', 'District Coordinator', 'District Coordinator', 10, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(65, 'district', 'District Incharge', 'District Incharge', 11, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(66, 'district', 'District Director', 'District Director', 12, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(67, 'district', 'District Development Officer', 'District Development Officer', 13, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(68, 'district', 'District Program Officer', 'District Program Officer', 14, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(69, 'district', 'Women Incharge', 'Women Incharge', 15, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(70, 'district', 'Education Officer', 'Education Officer', 16, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(71, 'district', 'Health Coordinator', 'Health Coordinator', 17, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(72, 'district', 'Training Officer', 'Training Officer', 18, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(73, 'district', 'Legal Advisor', 'Legal Advisor', 19, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(74, 'district', 'Monitoring Officer', 'Monitoring Officer', 20, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(75, 'district', 'Grievance Redressal Officer', 'Grievance Redressal Officer', 21, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(76, 'mandal', 'Mandal President', 'Mandal President', 1, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(77, 'mandal', 'Mandal Coordinator', 'Mandal Coordinator', 2, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(78, 'mandal', 'Mandal Incharge', 'Mandal Incharge', 3, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(79, 'mandal', 'Mandal Vice President', 'Mandal Vice President', 4, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(80, 'mandal', 'Women Head (Mandal)', 'Women Head (Mandal)', 5, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(81, 'mandal', 'Youth Head (Mandal)', 'Youth Head (Mandal)', 6, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(82, 'mandal', 'Health Coordinator (Mandal)', 'Health Coordinator (Mandal)', 7, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(83, 'mandal', 'Education Coordinator (Mandal)', 'Education Coordinator (Mandal)', 8, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(84, 'mandal', 'Training Incharge (Mandal)', 'Training Incharge (Mandal)', 9, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(85, 'mandal', 'Media Incharge (Mandal)', 'Media Incharge (Mandal)', 10, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(86, 'mandal', 'Development Officer (Mandal)', 'Development Officer (Mandal)', 11, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(87, 'mandal', 'Digital Coordinator (Mandal)', 'Digital Coordinator (Mandal)', 12, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(88, 'mandal', 'Grievance Officer (Mandal)', 'Grievance Officer (Mandal)', 13, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(89, 'state', 'State President', 'State President', 1, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(90, 'state', 'State Director', 'State Director', 2, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(91, 'state', 'State Coordinator', 'State Coordinator', 3, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(92, 'state', 'State Secretary', 'State Secretary', 4, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(93, 'state', 'State Vice President', 'State Vice President', 5, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(94, 'state', 'State Program Head', 'State Program Head', 6, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(95, 'state', 'State Representative', 'State Representative', 7, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(96, 'state', 'Women Head (State)', 'Women Head (State)', 8, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(97, 'state', 'Education Head (State)', 'Education Head (State)', 9, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(98, 'state', 'Health Head (State)', 'Health Head (State)', 10, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(99, 'state', 'Youth Head (State)', 'Youth Head (State)', 11, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(100, 'state', 'Training Head (State)', 'Training Head (State)', 12, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(101, 'state', 'Media & Publicity Head', 'Media & Publicity Head', 13, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(102, 'state', 'Financial Controller', 'Financial Controller', 14, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(103, 'state', 'Legal Advisor', 'Legal Advisor', 15, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(104, 'state', 'Project Head', 'Project Head', 16, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(105, 'state', 'Technical Advisor', 'Technical Advisor', 17, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(106, 'state', 'Monitoring & Evaluation Officer', 'Monitoring & Evaluation Officer', 18, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(107, 'state', 'Child & Women Welfare Head', 'Child & Women Welfare Head', 19, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(108, 'state', 'State Liaison Officer', 'State Liaison Officer', 20, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(109, 'state', 'State Incharge', 'State Incharge', 21, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(110, 'national', 'National President', 'National President', 1, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(111, 'national', 'National Vice President', 'National Vice President', 2, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(112, 'national', 'National General Secretary', 'National General Secretary', 3, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(113, 'national', 'National Secretary', 'National Secretary', 4, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(114, 'national', 'National Coordinator', 'National Coordinator', 5, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(115, 'national', 'National Director', 'National Director', 6, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(116, 'national', 'National Women Head', 'National Women Head', 7, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(117, 'national', 'National Youth Head', 'National Youth Head', 8, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(118, 'national', 'National Education Head', 'National Education Head', 9, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(119, 'national', 'National Health Head', 'National Health Head', 10, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(120, 'national', 'National Training Head', 'National Training Head', 11, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(121, 'national', 'National Media Head', 'National Media Head', 12, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(122, 'national', 'National Technical Advisor', 'National Technical Advisor', 13, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(123, 'national', 'National Project Head', 'National Project Head', 14, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(124, 'national', 'National Promotion & Publicity Head', 'National Promotion & Publicity Head', 15, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(125, 'national', 'National Legal Advisor', 'National Legal Advisor', 16, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(126, 'national', 'National Monitoring Officer', 'National Monitoring Officer', 17, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(127, 'national', 'National Evaluation Head', 'National Evaluation Head', 18, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(128, 'national', 'National Financial Controller', 'National Financial Controller', 19, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(129, 'national', 'National Child & Women Welfare Head', 'National Child & Women Welfare Head', 20, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(130, 'national', 'National Liaison Officer', 'National Liaison Officer', 21, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(131, 'national', 'National Incharge', 'National Incharge', 22, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(132, 'gram_panchayat', 'Community Outreach Volunteer', 'Community Outreach Volunteer', 19, 'active', '2025-07-29 01:31:00', '2025-07-29 01:31:00'),
(133, 'block', 'City President', 'City President', 10, 'active', '2025-07-31 04:25:51', '2025-07-31 04:25:51');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `excerpt` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','draft') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news_activities`
--

CREATE TABLE `news_activities` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` enum('news','activity','announcement') DEFAULT 'news',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nominations`
--

CREATE TABLE `nominations` (
  `id` int(11) NOT NULL,
  `registration_id` varchar(20) NOT NULL COMMENT 'Unique ID starting with GHDAF',
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `category` varchar(100) DEFAULT NULL COMMENT 'e.g. Education, Social Work',
  `award` varchar(255) DEFAULT NULL COMMENT 'Award category nominated for',
  `venue` varchar(255) DEFAULT NULL COMMENT 'Preferred ceremony venue',
  `profession` varchar(255) DEFAULT NULL,
  `about` text DEFAULT NULL COMMENT 'Brief bio / About the nominee',
  `talent` text DEFAULT NULL COMMENT 'Key achievements and talent details',
  `gender` varchar(50) DEFAULT NULL,
  `qualification` varchar(255) DEFAULT NULL COMMENT 'Educational qualification',
  `address` text DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL COMMENT 'Stores City/District',
  `pincode` varchar(20) DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT 0 COMMENT 'Online ceremony preference',
  `is_offline` tinyint(1) DEFAULT 0 COMMENT 'Offline ceremony preference',
  `is_ecert` tinyint(1) DEFAULT 0 COMMENT 'E-Certificate preference',
  `profile_image` varchar(255) DEFAULT NULL COMMENT 'Profile picture filename',
  `document_one` varchar(255) DEFAULT NULL COMMENT 'ID Proof document',
  `document_two` varchar(255) DEFAULT NULL COMMENT 'Educational Certificate',
  `document_three` varchar(255) DEFAULT NULL COMMENT 'Achievement Proof 1',
  `document_four` varchar(255) DEFAULT NULL COMMENT 'Achievement Proof 2',
  `user_type` varchar(50) DEFAULT 'member' COMMENT 'Default user type for nominations',
  `status` varchar(50) DEFAULT 'pending' COMMENT 'pending, approved, rejected',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Award nominations table - stores all nomination applications';

--
-- Dumping data for table `nominations`
--

INSERT INTO `nominations` (`id`, `registration_id`, `name`, `email`, `mobile`, `category`, `award`, `venue`, `profession`, `about`, `talent`, `gender`, `qualification`, `address`, `state`, `district`, `pincode`, `is_online`, `is_offline`, `is_ecert`, `profile_image`, `document_one`, `document_two`, `document_three`, `document_four`, `user_type`, `status`, `created_at`, `updated_at`) VALUES
(0, 'GHDAF60511', 'Ravikumar SHINDE', 'ravirajshinde2222@gmail.com', '7057248811', 'पत्रकार', 'National Best Journalist Award', 'ANNE CENTENARY LIBRARY GANDHI MAHDAPAM RD, SURYA NAGAR , KOTTURPURAM, CHENNAI, TAMIL NADU DATE- 12TH JANUARY 2026', '', '', '', 'Male', '', 'Dhondparon\r\nDhondparon', 'Maharashtra', 'Ahmednagar', '413205', 1, 0, 0, NULL, NULL, NULL, NULL, NULL, 'member', 'pending', '2026-03-20 05:17:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `objectives`
--

CREATE TABLE `objectives` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `office_bearers`
--

CREATE TABLE `office_bearers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ourworks`
--

CREATE TABLE `ourworks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ourworks`
--

INSERT INTO `ourworks` (`id`, `name`, `content`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Education Initiative', '<p>We provide quality education to underprivileged children, helping them build a better future through knowledge and skills development.</p>', '', '2025-11-01 06:20:47', '2025-11-01 06:20:47'),
(2, 'Healthcare Program', '<p>Our healthcare program ensures access to medical services and health education for communities in need, promoting overall wellness.</p>', '', '2025-11-01 06:20:47', '2025-11-01 06:20:47'),
(3, 'Women Empowerment', '<p>We empower women through skill training, entrepreneurship support, and advocacy for gender equality and women\'s rights.</p>', '', '2025-11-01 06:20:47', '2025-11-01 06:20:47');

-- --------------------------------------------------------

--
-- Table structure for table `our_ideals`
--

CREATE TABLE `our_ideals` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'Title in Hindi/English',
  `description` text DEFAULT NULL COMMENT 'Description about the ideals',
  `image` varchar(255) NOT NULL COMMENT 'Image filename',
  `father_name` varchar(255) DEFAULT NULL COMMENT 'Father name',
  `mother_name` varchar(255) DEFAULT NULL COMMENT 'Mother name',
  `father_birth_date` date DEFAULT NULL COMMENT 'Father birth date',
  `mother_birth_date` date DEFAULT NULL COMMENT 'Mother birth date',
  `father_death_date` date DEFAULT NULL COMMENT 'Father death date (if applicable)',
  `mother_death_date` date DEFAULT NULL COMMENT 'Mother death date (if applicable)',
  `message` text DEFAULT NULL COMMENT 'Inspirational message',
  `sort_order` int(11) DEFAULT 1 COMMENT 'Display order',
  `status` enum('active','inactive') DEFAULT 'active' COMMENT 'Status',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Table for storing ideals/parents information';

-- --------------------------------------------------------

--
-- Table structure for table `participations`
--

CREATE TABLE `participations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `city` varchar(255) DEFAULT NULL,
  `is_ngo` enum('yes','no') NOT NULL,
  `ngo_id` varchar(255) DEFAULT NULL,
  `donation_detail` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `participation_certificates`
--

CREATE TABLE `participation_certificates` (
  `id` int(11) NOT NULL,
  `certificate_no` varchar(20) NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `issue_date` date NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `partners`
--

CREATE TABLE `partners` (
  `id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL COMMENT 'Partner website URL',
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `partners`
--

INSERT INTO `partners` (`id`, `image`, `website`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(13, 'partner_69300ead197106.07204249.jfif', 'www.pressclubwelfareworldwidefoundation.org', 0, 'active', '2025-12-03 10:19:25', '2025-12-03 10:41:04'),
(14, 'partner_69300ee66e0f47.33811451.jfif', 'www.womensprotectionandempowermentworldwidefoundation.org', 0, 'active', '2025-12-03 10:20:22', '2025-12-03 10:20:22'),
(15, 'partner_69300f1b791c36.04145928.jfif', 'www.indianawardsceremony.org', 0, 'active', '2025-12-03 10:21:15', '2025-12-03 10:21:15'),
(16, 'partner_69300f7c2590b7.80475059.jfif', 'https://worldwidebusinessawardsceremony.com/', 0, 'active', '2025-12-03 10:22:52', '2025-12-03 10:22:52'),
(17, 'partner_6930127a02afa1.14239041.jfif', 'www.worldcharitywelfarefoundation.org', 0, 'active', '2025-12-03 10:35:38', '2025-12-03 10:35:38'),
(18, 'partner_693012b01c38c0.74301596.jfif', 'www.worldcharityawardsceremony.org', 0, 'active', '2025-12-03 10:36:32', '2025-12-03 10:36:32'),
(19, 'partner_693012e835e041.22611312.jfif', 'www.pressclubwelfareworldwidefoundation.org', 0, 'active', '2025-12-03 10:37:28', '2025-12-03 10:37:28'),
(20, 'partner_69301349b24259.15086132.jfif', 'www.honorarydoctorateawards.org', 0, 'active', '2025-12-03 10:39:05', '2025-12-03 10:39:44');

-- --------------------------------------------------------

--
-- Table structure for table `president_message`
--

CREATE TABLE `president_message` (
  `id` int(11) NOT NULL,
  `president_name` varchar(255) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `privacy_policy_content`
--

CREATE TABLE `privacy_policy_content` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) NOT NULL,
  `section_content` text NOT NULL,
  `section_icon` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `privacy_policy_content`
--

INSERT INTO `privacy_policy_content` (`id`, `section_title`, `section_content`, `section_icon`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Our Commitment to Privacy', 'Press Club Welfare Worldwide Foundation is committed to protecting the privacy and personal data of all members, nominees, and stakeholders. This Privacy Policy details the information we collect, how we use it specifically for membership and award processes, and how we protect your data.', 'fa-user-shield', 1, 'active', '2025-12-02 17:20:16', NULL),
(2, 'Information We Collect', 'We collect: Personal Data (Name, Contact, Email, Address, Profession, Qualification, Gender); Membership/Application Data (Membership Category, Award Applied For, Achievements/Bio, Documentation, Profile Photo, Press Card Details); Financial Data (Payment/Transaction details for fees - handled securely by payment gateways; we do not store complete card details). This data is collected explicitly for the purpose of validating membership/nomination and processing awards.', 'fa-database', 2, 'active', '2025-12-02 17:20:16', NULL),
(3, 'Purpose of Data Use', 'Your information is used strictly for: Membership/Nomination Validation and Vetting; Communication regarding your application status, ceremony logistics, and results; Generating ID cards, certificates, and awards with your name, photo, and achievement content; Archival and record-keeping required by law; Promotional and Recognition Purposes (with explicit or implied consent upon submission, relating to sharing successful member/nominee stories).', 'fa-cogs', 3, 'active', '2025-12-02 17:20:16', NULL),
(4, 'Information Sharing', 'We share personal information only when necessary: Service Providers (Payment processors, IT vendors, Courier services for certificates/ID cards); Legal Requirements (when mandated by law or court order). We do not sell or rent personal information to third parties for marketing purposes. By accepting membership or an award, you acknowledge that your name, award title, and general achievement summary may be publicized for the purpose of promoting the Foundation.', 'fa-share-alt', 4, 'active', '2025-12-02 17:20:16', NULL),
(5, 'Data Security', 'We employ industry-standard security measures (SSL, secured servers, access controls) to protect your data from unauthorized access, loss, or misuse. We follow secure archiving practices for membership and nomination documents.', 'fa-lock', 5, 'active', '2025-12-02 17:20:16', NULL),
(6, 'Your Rights and Choices', 'You have the right to request access to and correction of your personal data. You may object to the processing of your data for promotional purposes beyond the core membership/award issuance. Contact us to exercise these rights.', 'fa-user-check', 6, 'active', '2025-12-02 17:20:16', NULL),
(7, 'Data Retention', 'We retain membership, nomination and award recipient data for a period necessary for legal, auditing, and archival purposes, typically 7 years or as required by Indian law.', 'fa-calendar-alt', 7, 'active', '2025-12-02 17:20:16', NULL),
(8, 'Contact Information', 'For privacy-related questions, please use the primary contact information provided on the Foundation website.', 'fa-phone-alt', 8, 'active', '2025-12-02 17:20:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','completed','upcoming') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pwa_icons`
--

CREATE TABLE `pwa_icons` (
  `id` int(11) NOT NULL,
  `icon_set_name` varchar(255) NOT NULL,
  `icon_size` varchar(20) NOT NULL,
  `icon_path` varchar(500) NOT NULL,
  `icon_type` varchar(50) DEFAULT 'image/png',
  `is_maskable` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pwa_settings`
--

CREATE TABLE `pwa_settings` (
  `id` int(11) NOT NULL,
  `app_name` varchar(255) NOT NULL DEFAULT 'Uma Foundation Charitable Trust',
  `short_name` varchar(50) NOT NULL DEFAULT 'UFCT',
  `description` text DEFAULT 'Uma Foundation Charitable Trust - Serving humanity with compassion and dedication',
  `theme_color` varchar(7) DEFAULT '#667eea',
  `background_color` varchar(7) DEFAULT '#ffffff',
  `current_icon_set` varchar(255) DEFAULT NULL,
  `start_url` varchar(255) DEFAULT '/',
  `display` enum('standalone','fullscreen','minimal-ui','browser') DEFAULT 'standalone',
  `orientation` enum('any','natural','landscape','portrait') DEFAULT 'portrait',
  `is_enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `razorpay_config`
--

CREATE TABLE `razorpay_config` (
  `id` int(11) NOT NULL,
  `environment` enum('local','live') NOT NULL,
  `razorpay_key_id` varchar(255) NOT NULL,
  `razorpay_key_secret` varchar(255) NOT NULL,
  `webhook_secret` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `razorpay_orders`
--

CREATE TABLE `razorpay_orders` (
  `id` int(11) NOT NULL,
  `order_id` varchar(100) NOT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `amount` int(11) NOT NULL COMMENT 'Amount in paisa',
  `currency` varchar(3) NOT NULL DEFAULT 'INR',
  `receipt` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'created',
  `payment_status` varchar(50) DEFAULT NULL,
  `razorpay_data` text DEFAULT NULL,
  `payment_data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recent_activities`
--

CREATE TABLE `recent_activities` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `activity_date` date NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refund_policy_content`
--

CREATE TABLE `refund_policy_content` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) NOT NULL,
  `section_content` text NOT NULL,
  `section_icon` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `refund_policy_content`
--

INSERT INTO `refund_policy_content` (`id`, `section_title`, `section_content`, `section_icon`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Policy Overview', 'This Refund Policy applies to all fees paid for Membership Registration, Nomination Applications, Processing Fees, and related services for the Press Club Welfare Worldwide Foundation. Please read this policy carefully before submitting any payments.', 'fa-undo-alt', 1, 'active', '2025-12-02 17:20:16', NULL),
(2, 'General Non-Refundable Policy', 'All Membership Fees, Nomination Application Fees and Processing Fees are **non-refundable**. Fees cover the administrative costs associated with vetting, evaluation, verification, ID card generation, and processing your application/membership, regardless of the final outcome (approved or rejected).', 'fa-times-circle', 2, 'active', '2025-12-02 17:20:16', NULL),
(3, 'Exceptions (Refundable Circumstances)', 'Refunds will only be considered in the following exceptional circumstances:\n\n1. **Duplicate Payment:** If a member/nominee is charged more than once for the same transaction/service due to a technical error.\n2. **Organizational Cancellation:** If the Foundation is forced to cancel the entire membership program or award cycle without providing an alternative option.\n3. **Incorrect Charge:** If the payment amount debited is incorrect due to a system error (e.g., charged ₹10,000 instead of the advertised ₹1,000 fee).', 'fa-check-circle', 3, 'active', '2025-12-02 17:20:16', NULL),
(4, 'Refund Request Process', 'Refund requests for the limited exceptional circumstances mentioned above must be submitted in writing via email to the official Foundation contact email within **7 days** of the payment date or cancellation announcement. Requests must include the member/nominee\'s name, registration ID, transaction details, amount paid, and clear evidence of the exceptional circumstance.', 'fa-list-ol', 4, 'active', '2025-12-02 17:20:16', NULL),
(5, 'Processing and Timeline', 'Approved refunds will be processed back to the original payment method. Processing time is typically 10-20 business days, depending on banking procedures. The refund amount may be subject to deduction of non-recoverable payment gateway transaction fees.', 'fa-money-check-alt', 5, 'active', '2025-12-02 17:20:16', NULL),
(6, 'Contact for Refund Inquiries', 'For all refund inquiries, please contact the Foundation\'s administrative office directly.', 'fa-phone-square-alt', 6, 'active', '2025-12-02 17:20:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(0, 'membership_price_active', '999', '2026-04-08 05:42:03'),
(0, 'membership_price_gram_panchayat', '1444', '2026-04-08 05:46:52'),
(0, 'membership_price_block', '1999', '2026-04-08 05:46:52'),
(0, 'membership_price_tehsil', '2444', '2026-04-08 05:46:52'),
(0, 'membership_price_district', '2999', '2026-04-08 05:46:52'),
(0, 'membership_price_mandal', '3444', '2026-04-08 05:46:52'),
(0, 'membership_price_state', '9999', '2026-04-08 05:46:52'),
(0, 'membership_price_national', '19999', '2026-04-08 05:46:52');

-- --------------------------------------------------------

--
-- Table structure for table `shankaracharya_content`
--

CREATE TABLE `shankaracharya_content` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `position` varchar(255) DEFAULT NULL,
  `tenure_start` varchar(100) DEFAULT NULL,
  `birth_date` varchar(100) DEFAULT NULL,
  `birth_place` varchar(255) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipping_policy_content`
--

CREATE TABLE `shipping_policy_content` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) NOT NULL,
  `section_content` text NOT NULL,
  `section_icon` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_policy_content`
--

INSERT INTO `shipping_policy_content` (`id`, `section_title`, `section_content`, `section_icon`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Policy Overview', 'This Delivery Policy governs the distribution of physical ID cards, awards, certificates, and related documentation to the members and recipients of the Press Club Welfare Worldwide Foundation.', 'fa-truck', 1, 'active', '2025-12-02 17:20:16', NULL),
(2, 'Delivery of Physical Materials', 'Physical materials (ID cards, awards, medals, framed certificates) will be handed over to the recipient either in person during official events or dispatched via secured courier if the recipient chooses remote receipt or cannot attend in person.', 'fa-shipping-fast', 2, 'active', '2025-12-02 17:20:16', NULL),
(3, 'E-Certificate/E-Card Delivery', 'Members who opt for E-Certificate or E-ID Card will receive a high-resolution digital copy via email within 7 working days following approval or confirmation of receipt of supporting documents.', 'fa-envelope-open-text', 3, 'active', '2025-12-02 17:20:16', NULL),
(4, 'Shipping Charges and Responsibility', 'Standard domestic courier charges for physical materials may be included in the registration fee or charged separately, depending on the membership/award type. The recipient is responsible for providing a complete and correct shipping address. The Foundation is not liable for non-delivery or loss due to incorrect address submission by the recipient.', 'fa-rupee-sign', 4, 'active', '2025-12-02 17:20:16', NULL),
(5, 'Damage and Claims', 'Recipients must inspect materials upon receipt. Claims for physical damage during transit must be reported to the Foundation within 48 hours of delivery, along with photographic evidence, to qualify for a replacement. Replacement logistics and costs will be handled on a case-by-case basis.', 'fa-box-open', 5, 'active', '2025-12-02 17:20:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `site_config`
--

CREATE TABLE `site_config` (
  `id` int(11) NOT NULL,
  `site_title` varchar(255) NOT NULL,
  `site_subtitle` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `meta_author` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone1` varchar(20) DEFAULT NULL,
  `phone2` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `working_hours` varchar(100) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `footer_logo` varchar(255) DEFAULT NULL,
  `header_logo` varchar(255) DEFAULT NULL,
  `site_icon` varchar(255) DEFAULT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `map_embed_url` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `active_style` varchar(255) DEFAULT 'style.css'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_config`
--

INSERT INTO `site_config` (`id`, `site_title`, `site_subtitle`, `meta_title`, `meta_description`, `meta_keywords`, `meta_author`, `address`, `phone1`, `phone2`, `email`, `working_hours`, `facebook_url`, `twitter_url`, `instagram_url`, `youtube_url`, `footer_logo`, `header_logo`, `site_icon`, `website_url`, `map_embed_url`, `updated_at`, `active_style`) VALUES
(1, 'Press Club Welfare Worldwide Foundation', 'Supporting journalists and media professionals globally', 'Press Club Welfare Worldwide Foundation - Global Media Support', 'Press Club Welfare Worldwide Foundation is a registered organization supporting journalists and media professionals worldwide. Registration Number: U89000DL2024NPL437018', 'Press Club Welfare Worldwide Foundation, journalists, media professionals, press club, media welfare, PCWWF', 'Press Club Welfare Worldwide Foundation', 'Mahabir Nagar, New Delhi-110018 India', '9040898333', NULL, 'pressclubwelfareworldwide@gmail.com', '24hrs', 'https://www.pressclubwelfareworldwidefoundation.org/', 'https://www.pressclubwelfareworldwidefoundation.org/', 'https://www.pressclubwelfareworldwidefoundation.org/', 'https://www.pressclubwelfareworldwidefoundation.org/', '68e4140cac8a7.png', '68e4140cacdd6.png', '68e4140cad34b.png', 'https://www.pressclubwelfareworldwidefoundation.org', NULL, '2025-11-13 10:50:51', 'style.css');

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

CREATE TABLE `sliders` (
  `id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sliders`
--

INSERT INTO `sliders` (`id`, `image`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(12, 'slider_68e79876a1dc99.18714239.jfif', 0, 'active', '2025-10-09 11:11:50', '2025-10-09 11:11:50'),
(13, 'slider_68e7988f362004.65681174.jfif', 0, 'active', '2025-10-09 11:12:15', '2025-10-09 11:12:15'),
(15, 'slider_68e798bae8b101.65196546.jfif', 0, 'active', '2025-10-09 11:12:58', '2025-10-09 11:12:58'),
(16, 'slider_68e7999d65c147.95343803.jpg', 0, 'active', '2025-10-09 11:16:45', '2025-10-09 11:16:45'),
(19, 'slider_68e79a17f3cad9.01803389.jpg', 0, 'active', '2025-10-09 11:18:48', '2025-10-09 11:18:48'),
(20, 'slider_69cfa4bb3899f6.98001607.jpeg', 0, 'active', '2025-10-09 11:21:28', '2026-04-03 11:30:03');

-- --------------------------------------------------------

--
-- Table structure for table `smtp_settings`
--

CREATE TABLE `smtp_settings` (
  `id` int(11) NOT NULL,
  `host` varchar(255) NOT NULL,
  `port` int(5) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `encryption` enum('ssl','tls') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `smtp_settings`
--

INSERT INTO `smtp_settings` (`id`, `host`, `port`, `username`, `password`, `encryption`) VALUES
(1, 'smtp.hostinger.com', 465, 'info@pressclubwelfareworldwidefoundation.org', '*4fRt1*x/xHO', 'ssl');

-- --------------------------------------------------------

--
-- Table structure for table `social_media`
--

CREATE TABLE `social_media` (
  `id` int(11) UNSIGNED NOT NULL,
  `type` enum('image','video','post') NOT NULL DEFAULT 'post',
  `content_file` varchar(255) DEFAULT NULL,
  `link` text DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sponsors`
--

CREATE TABLE `sponsors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sponsors`
--

INSERT INTO `sponsors` (`id`, `name`, `designation`, `photo`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(7, 'MATHUKUMALLI VENKATA PHANI BHUSHAN', 'SOCIAL WORKER', 'sponsor_690f234b24cad9.78994281.jfif', 1, 'active', '2025-11-08 11:02:35', '2025-11-08 11:06:23'),
(8, 'GANESH NANDLAL THAKARE', 'SOCIAL WORKER', 'sponsor_690f2398aef048.34974241.jfif', 2, 'active', '2025-11-08 11:03:52', '2025-11-08 11:06:06'),
(9, 'DR. USHMA HARENDRA UDESHI', 'DOCTORATE IN SOCIAL SERVICE', 'sponsor_690f240e84c745.12863388.jfif', 3, 'active', '2025-11-08 11:05:50', '2025-11-08 11:05:50'),
(10, ',Irshad Ahamd', 'SOCIAL WORKER', 'sponsor_690f24f393c835.91889517.jfif', 4, 'active', '2025-11-08 11:09:39', '2025-11-08 11:09:39'),
(11, 'S PANDIAN', 'SOCIAL WORKER', 'sponsor_690f2537937572.62706158.jfif', 5, 'active', '2025-11-08 11:10:47', '2025-11-08 11:10:47'),
(12, 'Anup Inamdar', 'DOCTORATE IN SOCIAL SERVICE', 'sponsor_690f2582c0eff6.75402946.jfif', 6, 'active', '2025-11-08 11:12:02', '2025-11-08 11:12:02'),
(13, 'MAHESH PRASAD MISHRA', 'SOCIAL WORKER', 'sponsor_690f25be834283.21480308.jfif', 7, 'active', '2025-11-08 11:13:02', '2025-11-08 11:13:02'),
(14, 'YAWER RASOOL', 'SOCIAL WORKER', 'sponsor_690f2600584815.12948423.jfif', 8, 'active', '2025-11-08 11:14:08', '2025-11-08 11:14:08'),
(15, 'TAHSINAH FAIZ', 'SOCIAL WORKER', 'sponsor_690f26662bb9c2.52043844.jfif', 9, 'active', '2025-11-08 11:15:50', '2025-11-08 11:15:50'),
(16, 'VIVEK DHALLAM', 'DOCTORATE IN SOCIAL SERVICE', 'sponsor_690f28a14c7be9.61027575.jfif', 10, 'active', '2025-11-08 11:25:21', '2025-11-08 11:25:21');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `area_of_work` varchar(255) DEFAULT NULL COMMENT 'Area of Working',
  `image` varchar(255) DEFAULT NULL COMMENT 'Photo path',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Contact No',
  `sort_order` int(11) DEFAULT 0,
  `member_type` varchar(50) NOT NULL DEFAULT 'management',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `name`, `designation`, `area_of_work`, `image`, `phone`, `sort_order`, `member_type`, `status`, `created_at`, `updated_at`) VALUES
(3, 'Anita Sharma', 'Relationship manager', 'MADHY PRADESH', 'team_6947b6adc9d26.jpg', '91 9340546517', 0, 'management', 'active', '2025-12-21 08:58:21', '2025-12-21 12:08:31'),
(4, 'Pranati das', 'Relationship manager', 'ODISHA', 'team_6947de1538655.jpeg', '72056 35391', 0, 'management', 'active', '2025-12-21 08:59:50', '2025-12-21 11:48:43'),
(5, 'Simran bagh', 'Relationship manager', 'ODISHA', 'team_6947b740ac57c.jpg', '9861744922', 0, 'management', 'active', '2025-12-21 09:00:48', '2025-12-21 11:41:06'),
(6, 'Theyseen', 'Relationship manager', 'ALL INDIA', 'team_6954c8688c00b.jpeg', '9390314175', 0, 'management', 'active', '2025-12-21 09:01:34', '2025-12-31 06:53:28'),
(7, 'Banita mishra', 'Relationship manager', 'ODISHA', 'team_6947b79ab5b6c.jpg', '+91 78558 17072', 0, 'management', 'active', '2025-12-21 09:02:18', '2025-12-21 11:40:16'),
(8, 'Ankita ray', 'Relationship manager', 'Kolkata  West Bengal', 'team_6947c61b236a2.jpg', '7735078867', 0, 'management', 'active', '2025-12-21 09:02:58', '2025-12-21 11:39:15'),
(12, 'PALAK SHARMA', 'Relationship manager', 'MADHY PRADESH', 'team_6947dfdac50d8.jpeg', '91 6260025449', 0, 'management', 'active', '2025-12-21 11:52:24', '2025-12-21 12:07:46');

-- --------------------------------------------------------

--
-- Table structure for table `terms_conditions_content`
--

CREATE TABLE `terms_conditions_content` (
  `id` int(11) NOT NULL,
  `section_title` varchar(255) NOT NULL,
  `section_content` text NOT NULL,
  `section_icon` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `terms_conditions_content`
--

INSERT INTO `terms_conditions_content` (`id`, `section_title`, `section_content`, `section_icon`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Acceptance of Terms', 'By accessing and utilizing the membership services, nomination services, website, and resources provided by Press Club Welfare Worldwide Foundation, you acknowledge and agree to be bound by these Terms and Conditions. These terms govern your application for membership, nominations, registration, participation in award ceremonies, and all related interactions. If you do not agree with these terms, please cease use of our services immediately. Continued engagement constitutes acceptance of any modified terms.', 'fa-check-circle', 1, 'active', '2025-12-02 17:20:16', NULL),
(2, 'Eligibility and Application Process', 'The Foundation grants memberships and awards based on merit, achievement, and adherence to specified criteria. Applications must be submitted using the official online form with complete and truthful information. The submission of an application does not guarantee approval or grant of membership/award. The Foundation reserves the right to verify all submitted details and documents. Any misrepresentation or fraudulent information will result in immediate disqualification and forfeiture of any fees paid. Eligibility criteria, fees, and documentation requirements are specified on the official application page and are subject to change without prior notice.', 'fa-user-check', 2, 'active', '2025-12-02 17:20:16', NULL),
(3, 'Membership and Award Ceremonies', 'Approved members and award recipients will be formally notified via email and/or phone call. Participation in physical or virtual ceremonies (if applicable) requires adherence to the rules and schedules provided by the Foundation. The Foundation reserves the right to change the date, venue, or format of any event due to logistical or unforeseen circumstances. Failure to attend or participate in the prescribed manner may result in the membership/award being withheld or cancelled. All travel, accommodation, and personal expenses related to attending physical events are the sole responsibility of the member/recipient.', 'fa-trophy', 3, 'active', '2025-12-02 17:20:16', NULL),
(4, 'Fees and Payment Terms', 'Membership fees, nomination processing fees, registration fees, and any optional service charges (e.g., ID card courier charges) must be paid in full at the time of application or registration as specified. All fees are non-refundable, except as explicitly detailed in the Refund Policy due to payment errors or organizational cancellation. Payments must be made through the authorized payment channels provided on the website. The Foundation is not responsible for errors or issues arising from third-party payment gateways.', 'fa-credit-card', 4, 'active', '2025-12-02 17:20:16', NULL),
(5, 'Intellectual Property and Usage Rights', 'All content on the website, including logos, ID card designs, award templates, and text, belongs to the Foundation. By submitting achievement details, bios, or photographs, you grant the Foundation a perpetual, royalty-free license to use, reproduce, publish, and display such materials for promotional, archival, and ceremonial purposes, including printing on ID cards, certificates, awards and display on the website/social media. Membership credentials, award titles, and seals must not be misused or altered by the holder. Misuse of credentials or titles in a manner that misrepresents their nature may lead to revocation.', 'fa-copyright', 5, 'active', '2025-12-02 17:20:16', NULL),
(6, 'Limitation of Liability', 'To the fullest extent permitted by law, the Foundation, its directors, and staff shall not be liable for any direct, indirect, incidental, or consequential damages arising from your membership/nomination process, inability to receive membership/award, or participation in events. The Foundation is not responsible for losses due to postal errors, technical failures, or non-attendance at events. Our total liability for any claim shall not exceed the fees paid by you to the Foundation in connection with the specific membership/nomination.', 'fa-exclamation-triangle', 6, 'active', '2025-12-02 17:20:16', NULL),
(7, 'Governing Law and Jurisdiction', 'These Terms and Conditions shall be governed by the laws of India. Any disputes arising shall be subject to the exclusive jurisdiction of the courts in the city where the Foundation\'s registered office is located.', 'fa-balance-scale-right', 7, 'active', '2025-12-02 17:20:16', NULL),
(8, 'Contact Information', 'For questions or issues regarding these Terms, please refer to the contact details provided on our official website.', 'fa-envelope', 8, 'active', '2025-12-02 17:20:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `rating` int(11) DEFAULT 5,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `topbar_settings`
--

CREATE TABLE `topbar_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_certificates`
--

CREATE TABLE `training_certificates` (
  `id` int(11) NOT NULL,
  `certificate_no` varchar(30) NOT NULL,
  `trainee_name` varchar(255) NOT NULL,
  `trainee_id` varchar(100) DEFAULT NULL,
  `training_program_id` int(11) DEFAULT NULL,
  `training_name` varchar(255) NOT NULL,
  `certificate_type` varchar(50) DEFAULT 'technical_training',
  `training_start_date` date NOT NULL,
  `training_end_date` date NOT NULL,
  `duration_hours` int(11) DEFAULT 0,
  `trainer_name` varchar(255) DEFAULT NULL,
  `grade_achieved` varchar(10) DEFAULT NULL,
  `score_percentage` decimal(5,2) DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('active','inactive','expired') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_programs`
--

CREATE TABLE `training_programs` (
  `id` int(11) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `program_code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `duration_hours` int(11) DEFAULT 0,
  `duration_days` int(11) DEFAULT 0,
  `category` varchar(100) DEFAULT NULL,
  `level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `prerequisites` text DEFAULT NULL,
  `learning_objectives` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `mobile` varchar(15) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `dob` date NOT NULL,
  `sdw_type` enum('S/O','D/O','W/O') NOT NULL,
  `sdw_name` varchar(255) NOT NULL,
  `profession` varchar(100) DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `aadhar` varchar(12) NOT NULL,
  `state` varchar(100) NOT NULL,
  `district` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `pincode` varchar(6) NOT NULL,
  `membership_type` enum('active','gram_panchayat','block','tehsil','district','mandal','state','national') NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `aadhar_front` varchar(255) DEFAULT NULL,
  `aadhar_back` varchar(255) DEFAULT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `order_id` varchar(100) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `payment_method` enum('online','offline') DEFAULT NULL,
  `registration_id` varchar(20) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `valid_until` date DEFAULT NULL,
  `user_type` enum('member','coordinator','admin') DEFAULT 'member',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `working_area` text DEFAULT NULL,
  `id_card_photo` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `valid_from` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `mobile`, `gender`, `dob`, `sdw_type`, `sdw_name`, `profession`, `designation`, `blood_group`, `aadhar`, `state`, `district`, `address`, `pincode`, `membership_type`, `profile_image`, `aadhar_front`, `aadhar_back`, `payment_id`, `order_id`, `payment_proof`, `payment_method`, `registration_id`, `status`, `valid_until`, `user_type`, `created_by`, `created_at`, `working_area`, `id_card_photo`, `updated_at`, `valid_from`) VALUES
(1, 'AMAR KUMAR BAGH', 'pressclubwelfareworldwide@gmail.com', '$2y$10$jipJADUdR87/SgS3I9KQtOsckFw33.O.hdZ./N4k1bpHbF3Arq4yi', '9040898333', 'Male', '1986-10-05', 'D/O', 'Father', 'Self Business', 'National President', 'B+', '743143848676', 'Delhi', 'South Delhi', 'DELHI INDIA', '110002', 'national', '697702c0301cb.jfif', '68dec071e5b17.png', '68dec071e6104.png', 'pay_789', NULL, '6977015b01022.jfif', '', 'PCWWF/00001', 'approved', '2027-09-20', 'admin', 1, '2025-07-28 07:08:45', 'Cachar', 'id_card_1_1758108456.png', '2025-09-17 11:27:36', '2026-01-26'),
(3, 'S.VELAVAN', 'svelavan69@gmail.com', '$2y$10$v2ZmWHvoV2I/YaacbYJGpuWIZgW45OmoZ7.tE3fwbkJxNiX59IzBq', '9943645308', 'Male', '1989-06-02', 'S/O', 'SEKAR', 'Self Business', 'MEMBER', '', '287391814120', 'Puducherry', 'PUDUCHERRY', '20, KUTTAI STREET VADHANUR (PUDUCHERRY)', '605501', 'active', '68e74fe19c2eb.jfif', NULL, NULL, '12548946', NULL, NULL, 'offline', 'PCWWF/00002', 'approved', '2026-10-09', 'member', 1, '2025-10-09 06:02:09', '', NULL, NULL, '2025-10-09'),
(4, 'Santanu Das', 'santanu2528@gmail.com', '$2y$10$/6vdkfd9hHLTanvOJafWAOHF3puLlPXuLqKrcaH6xv./iQBOny/nm', '9123016431', 'Male', '1997-09-18', 'S/O', 'SHANKAR DAS', 'Self Business', 'MEMBER', '', '916412561657', 'West Bengal', 'North 24 Parganas', 'Suckchar Girja Ek Ford Road, P.O - Suckchar, P.S - Khardaha', '700115', 'active', '68e755ad1f28d.jfif', NULL, NULL, '12548945', NULL, NULL, 'offline', 'PCWWF/00003', 'approved', '2026-10-09', 'member', 1, '2025-10-09 06:19:18', '', NULL, NULL, '2025-10-09'),
(5, 'parag Gada', 'paraggada1981@gmail.com', '$2y$10$yn/1Hi35PdEICiH1HZ5Egu03VXwOqS5YyAMwC9weU3WrkOhdqY0dG', '9892431244', 'Male', '1985-06-03', 'S/O', 'JAYANTILA GADA', 'Self Business', 'MEMBER', '', '275144474365', 'Maharashtra', 'Mumbai City', '1003 Batul house \r\nD .N SINGH ROAD \r\nHATIBAUG \r\nLOVELANE \r\nMAZGEON MUMBAI 10', '400010', 'active', '68eb566c83fbe.jpg', NULL, NULL, '125489454', NULL, NULL, 'offline', 'PCWWF/00004', 'approved', '2026-10-09', 'member', 1, '2025-10-09 06:25:48', '', NULL, NULL, '2025-10-09'),
(6, 'Anand kishor', 'anandseema0657@gmail.com', '$2y$10$3e18/M24HwI8a5.mUy.6YuZISW2KNjcCp/nZI5o9o1y0AeXDKcaQS', '8709795876', 'Male', '1991-07-11', 'S/O', 'NAND KISHOR', '', 'MEMBER', '', '677047141596', 'Jharkhand', 'Jamshedpur', 'Patel nagar sunder hatu basti chhota govindpur jamshedpur jharkhand near Durga mandir PIN code 831015', '831015', 'active', '68e759931f318.jfif', NULL, NULL, '125489453', NULL, NULL, 'offline', 'PCWWF/00005', 'approved', '2026-10-09', 'member', 1, '2025-10-09 06:43:31', '', NULL, NULL, '2025-10-09'),
(7, 'Arabindo sahu', 'arabindosahu687@gmail.com', '$2y$10$ValJJ.Qgvb54zXN363e2RukG4Uju4tUzORc7K7CgS16PmHA6FUIQ.', '9337609995', 'Male', '1989-09-06', 'S/O', 'MADHUSUDAN SAHU', '', 'MEMBER', '', '330966870396', 'Odisha', 'Nabarangpur', 'Teli street, nabarangpur, odisha,pin- 764059', '764059', 'active', '68e75c06ab54c.jfif', NULL, NULL, '1254894569', NULL, NULL, 'offline', 'PCWWF/00006', 'approved', '2026-10-09', 'member', 1, '2025-10-09 06:53:58', '', NULL, NULL, '2025-10-09'),
(8, 'Dr.Bhaskar shukla', 'bhasker2shukla@yahoo.co.in', '$2y$10$SkzetWeAj3m1QNz3lfEYEO2uiux1SflUEYCRCMYG137ycRTp33kLO', '9426525045', 'Male', '1987-05-20', 'S/O', 'ARAVINDCHANDRA', '', 'MEMBER', '', '907229817837', 'Gujarat', 'Gandhinagar', 'plot no.2/b, sector 2/ a, gandhhinagar...382007 Gujarat', '382007', 'active', '68e75e970153f.jfif', NULL, NULL, '1254894565', NULL, NULL, 'offline', 'PCWWF/00007', 'approved', '2026-10-09', 'member', 1, '2025-10-09 07:04:55', '', NULL, NULL, '2025-10-09'),
(9, 'Shazaib Bashir Parkar', 'shazaib@gmail.com', '$2y$10$/yDHIFDTLYa5YKGqX/cGQO7iAhDMw.O5ll8eaKj88bCP8M864jyP.', '8767297665', 'Male', '1984-09-06', 'S/O', 'Bashir Abdul Gafoor Parkar', '', 'MEMBER', '', '836498949335', 'Maharashtra', 'Ratnagiri', 'Atpost Pewe , Tal: Mandangad ,Dist :- Ratnagiri', '415214', 'active', '68e76250a649e.jfif', NULL, NULL, '125489465', NULL, NULL, 'offline', 'PCWWF/00008', 'approved', '2026-10-09', 'member', 1, '2025-10-09 07:20:48', '', NULL, NULL, '2025-10-09'),
(10, 'KUMAR GOVINDHASAMY', 'kumar@gmail.com', '$2y$10$xFXjMZqOdypDVGDRb78yTOce5XUrvamy5Suplgk/xVIZa5Vwnvamq', '7502288360', 'Male', '1984-06-03', 'S/O', 'GOVINDHASAMY', '', 'MEMBER', '', '764572069790', 'Tamil Nadu', 'Tiruppur', 'allavanthan steet pallalipalayam tmpoondi tirupur', '641652', 'active', '68e765d50c59b.jfif', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00009', 'approved', '2026-10-09', 'member', 1, '2025-10-09 07:35:49', '', NULL, NULL, '2025-10-09'),
(11, 'Elvis Lalthangzuala', 'elvishnialum@gmail.com', '$2y$10$ETNPKS.J5OkCTAtZKhnA4OfApHAQ16UxFsZ1GL4H6wudEiTpqPGgi', '9436176670', 'Male', '1987-06-03', 'S/O', 'LALTHANGZUALA', '', 'MEMBER', '', '856401095637', 'Mizoram', 'Lawngtlai', 'Lawngtlai Council Veng', '796891', 'active', '68e768d04398d.jfif', NULL, NULL, '125489464', NULL, NULL, 'offline', 'PCWWF/00010', 'approved', '2026-10-09', 'member', 1, '2025-10-09 07:48:32', '', NULL, NULL, '2025-10-09'),
(12, 'Beerbal', 'beerbals62@gmail.com', '$2y$10$BA5Mls76obfwt5sphiFoqOfOKTnMxSQ/pYG3bu4dtian.XmoEIVgq', '9917771554', 'Male', '1983-01-02', 'S/O', 'HARI SINGH', '', 'MEMBER', '', '493897654768', 'Uttar Pradesh', 'Etawah', 'Village Nagla Mahasukh post Niloi Jaswantnagar Dist. Etawah', '206245', 'active', '68e76b5fb90a7.jfif', NULL, NULL, '125489455', NULL, NULL, 'offline', 'PCWWF/00011', 'approved', '2026-10-09', 'member', 1, '2025-10-09 07:59:27', '', NULL, NULL, '2025-10-09'),
(13, 'Shubham Rastogi', 'shubham.rastogi1990@gmail.com', '$2y$10$4xySML6aXX.slJHsiJyp8OQy3pwO5exP4R5brDk4H9mE7aLRjWmK2', '7003259523', 'Male', '1985-06-02', 'S/O', 'RANJAN RASTOGI', '', 'MEMBER', '', '765487012084', 'West Bengal', 'Howrah', '301/1 G.T.Road belur howrah. Abs North Plaza', '711202', 'active', '68e788ee7a095.jfif', NULL, NULL, '12548945696', NULL, NULL, 'offline', 'PCWWF/00012', 'approved', '2026-10-09', 'member', 1, '2025-10-09 10:05:34', '', NULL, NULL, '2025-10-09'),
(14, 'Irshad Ahamd', 'Irshad933693@gmail.com', '$2y$10$ThSfPxeAI5bj.Ygai8NY.exfMRAd1yVXf57GT7/Asm.b.GKQ5WW4K', '9336930606', 'Male', '1988-02-08', 'S/O', 'Late Nishar Ahmad', '', 'MEMBER', '', '607335769625', 'Uttar Pradesh', 'Varanasi', 'N 12/227 Near Auto Stand Dharara Bajardihan', '221109', 'active', '68e78ceac5b59.jfif', NULL, NULL, '12548945', NULL, NULL, 'offline', 'PCWWF/00013', 'approved', '2026-10-09', 'member', 1, '2025-10-09 10:22:34', '', NULL, NULL, '2025-10-09'),
(15, 'MOHOMMAD AJAZ', 'ajazbawa8888@gmail.com', '$2y$10$8s03jHsXbs5/Nyuk91.Z..XkRD8ifIj2holzlXny1ZdDfw.K6S4Vi', '9552458460', 'Male', '1898-01-06', 'S/O', 'mohommad taher', '', 'MEMBER', '', '795512852700', 'Maharashtra', 'Akola', 'rampeer Nagar Khair Mohammed plot juna Shahar Akola', '444001', 'active', '68e78eb9c41c7.jfif', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00014', 'approved', '2026-10-09', 'member', 1, '2025-10-09 10:30:17', '', NULL, NULL, '2025-10-09'),
(16, 'SANJEEV KUMAR SUMAN', 'sanjeev@gmail.com', '$2y$10$AkNf8bIzGYsY6JYov/n/N.d4maWWys1Xlu4RGCBtoS5r6gZZ6znTu', '8865913614', 'Male', '1989-05-10', 'S/O', 'RAMACHANDRA SUMAN', '', 'MEMBER', '', '555197554219', 'Uttar Pradesh', 'Bareilly', 'BADAUN ROAD SHANTI ROAD BIHAR COLONY NEAR RADHA KRISHNA MANDIR SHUBNASH NAGAR', '243001', 'active', '68e7a259ac173.jfif', NULL, NULL, '1254894565', NULL, NULL, 'offline', 'PCWWF/00015', 'approved', '2026-10-09', 'member', 1, '2025-10-09 11:54:01', '', NULL, NULL, '2025-10-09'),
(18, 'SAMIR KUMAR PRADHAN', 'samirkumarpradhan@gmail.com', '$2y$10$yLPwhVaatAoapwmoWUlDQuwQkYaBXnBqX3596dppASRVRt/RzJ2rC', '8658253831', 'Male', '1993-03-20', 'S/O', 'SURENDRA PRADHAN', '', 'MEMBER', '', '519608189905', 'Odisha', 'Kendrapara', 'Jaganathpur Odisha', '754246', 'active', '68e89991b3511.jpeg', NULL, NULL, '12548946', NULL, '68e89991b3747.jpeg', 'offline', 'PCWWF/00016', 'approved', '2026-10-09', 'member', 1, '2025-10-10 05:28:49', '', NULL, NULL, '2025-10-09'),
(19, 'Thakur Vinod  Singh', 'thakurvinodsigh@gmail.com', '$2y$10$MmQKqxV4r1q38iSk0Qq0z.HLf7AXJMp.d33ZAzlfX9osbLaK5Qw.W', '9885615902', 'Male', '1964-06-10', 'S/O', 'Thakur Raghunath Singh', '', 'MEMBER', '', '667617140056', 'Andhra Pradesh', 'Hyderabad', '1-7-1013/1 Harinagar Zamistanpur', '500020', 'active', '68e8a10522095.jfif', NULL, NULL, '12548945696', NULL, '68e8a10522317.jfif', 'offline', 'PCWWF/00017', 'approved', '2026-10-09', 'member', 1, '2025-10-10 06:00:37', '', NULL, NULL, '2025-10-09'),
(20, 'VIJAY SIROHI', 'laxmikantnimbasonawane@gmail.com', '$2y$10$Hth3XGcojESiHWcgcG899ee3ALKfIsfe1CafxFlZXjbECeo3OnKYy', '8168879095', 'Male', '1981-05-10', 'S/O', 'HARI KRISHAN SIROHI', '', 'MEMBER', '', '329661842571', 'Haryana', 'Kaithal', 'House Number 519 Behind Anandpur Ashram Ward N umber 4 Siwan Gate', '136027', 'active', '68e8a4dfe5201.jfif', NULL, NULL, '12548945696', NULL, '68e8a4dfe556e.jfif', 'offline', 'PCWWF/00018', 'approved', '2026-10-09', 'member', 1, '2025-10-10 06:17:03', '', NULL, NULL, '2025-10-09'),
(21, 'Anil kumar N', 'anilkumarn@gmail.com', '$2y$10$f7OW6v/PxgAxGUhNR1/PauMB76mxWqKSKbjYi3R.jt6S6tNAKRv66', '8217470072', 'Male', '1992-05-28', 'S/O', 'Bhaskar N', '', 'MEMBER', '', '525520944302', 'Tamil Nadu', 'Chennai', '94/97 Dr Ambedkar Nagar 2nd Street Ayanavaram', '600023', 'active', '68e8a762e556a.jfif', NULL, NULL, '1254894569', NULL, NULL, 'offline', 'PCWWF/00019', 'approved', '2026-10-09', 'member', 1, '2025-10-10 06:27:46', '', NULL, NULL, '2025-10-09'),
(22, 'SWAMINATHAN TRICHIRAPALLI GANESAN', 'swaminathantrichirapalliganesan@gmail.com', '$2y$10$aVAIHh36QbekGxvnRz3WaOmAJhzhMkWp7J/izzPIlSUiVACLMzW4m', '7904081457', 'Male', '1959-04-18', 'S/O', 'GANESAN TRICHIRAPALLI', '', 'MEMBER', '', '871522562573', 'Tamil Nadu', 'Triuchirapalli', '48/114 North Andar Street', '620002', 'active', '68e8adc0183de.jfif', NULL, NULL, '12548945696', NULL, NULL, 'offline', 'PCWWF/00020', 'approved', '2026-10-10', 'member', 1, '2025-10-10 06:54:56', '', NULL, NULL, '2025-10-10'),
(23, 'LUKESH NARESH AGARWAL', 'lukeshnareshagarwal@gmail.com', '$2y$10$Ze003ydbrAlJ5fTbzIf1guHvCcz12FUlol/b51Z04nhrWghGa2eGC', '9881408313', 'Male', '1976-12-10', 'S/O', 'SHANKAR AGARWAL', '', 'MEMBER', '', '920962813021', 'Maharashtra', 'Pune', '119 Kalas Alandi Road Kalas Near R And DE Compeny', '411015', 'active', '68e8b30963fd3.jfif', NULL, NULL, '1254894569', NULL, NULL, 'offline', 'PCWWF/00021', 'approved', '2026-10-10', 'member', 1, '2025-10-10 07:17:29', '', NULL, NULL, '2025-10-10'),
(24, 'Emmanuel John', 'emmanueljohn@gmail.com', '$2y$10$OuOqZxoblkMFMQ5AG5ZKG.N/xmaTmg9OV5YO5tzUJWudAr4PgjwLC', '9981650048', 'Male', '1985-10-07', 'S/O', 'Peter John', '', 'MEMBER', '', '605294215285', 'Madhya Pradesh', 'Tikamgarh', 'Ward No 11 Renj Ke Pass  Palera', '472221', 'active', NULL, NULL, NULL, '1254894569', NULL, NULL, 'offline', 'PCWWF/00022', 'approved', '2026-10-10', 'member', 1, '2025-10-10 07:27:48', '', NULL, NULL, '2025-10-10'),
(25, 'SHAILENDRA MALVIY', 'shailendramalviy@gmail.com', '$2y$10$piJnVV4avgB0Hw5tMlHRrO7lguKLaAGf5c6zibajVFNDqh3tNGdIu', '9630989266', 'Male', '1999-12-06', 'S/O', 'ROOPSINGH  MALVIY', '', 'MEMBER', '', '644522531210', 'Madhya Pradesh', 'Narsinghpur', 'Mu  Post  Sahawan Sali Choka Road', '487881', 'active', '68e8b7d74f166.jfif', NULL, NULL, '1254894565', NULL, NULL, 'offline', 'PCWWF/00023', 'approved', '2026-10-10', 'member', 1, '2025-10-10 07:36:20', '', NULL, NULL, '2025-10-10'),
(26, 'ARUN CHANDRAN B', 'arjunchandranb@gmail.com', '$2y$10$N.ODk.BJ45Fux/yA8FMAx.dznudRNFjwfr5JfQSuxU9oEa976V8Ey', '9539510694', 'Male', '2025-10-10', 'S/O', 'BALACHANDRAN NAIR P', '', 'MEMBER', '', '865497723794', 'Kerala', 'Thiruvananthapuram', 'Kamala  Mandiram Venniyoor Po Vizhinjam', '695523', 'active', '68e8bb87a458a.jfif', NULL, NULL, '12548946', NULL, NULL, 'offline', 'PCWWF/00024', 'approved', '2026-10-10', 'member', 1, '2025-10-10 07:53:43', '', NULL, NULL, '2025-10-10'),
(27, 'VARGHESE B J', 'admin@gmail.com', '$2y$10$sF.3WccAzRO80dXZ9RC29OBDbMULve1dRHo6Qu8eJCLM8cxtusBsO', '6394643690', 'Male', '1966-02-16', 'S/O', 'RAM BJ', '', 'MEMBER', '', '259813427380', 'Kerala', 'Thrissur', 'Bharanikulam Mala Pallipuram  Po  Pallippuram', '680732', 'active', '68e8cea51969b.jfif', NULL, NULL, '1254894569', NULL, NULL, 'offline', 'PCWWF/00025', 'approved', '2026-10-10', 'member', 1, '2025-10-10 09:15:17', '', NULL, NULL, '2025-10-10'),
(28, 'Suank Bujahi', 'suankbujahi@gmail.com', '$2y$10$Ckflt0dYa8xTzezJ67mUmODC3X.n3cWC/pb66tSOlRfnmn1M46ad2', '9872464679', 'Male', '1966-02-16', 'S/O', 'RAJESH KUMAR', '', 'MEMBER', '', '947271316420', 'Punjab', 'Amritsar', '41, Gali No. 1, Jagdambey Colony, Majitha Road,', '143001', 'active', '68e8d091245a1.jfif', NULL, NULL, '12548945696', NULL, NULL, 'offline', 'PCWWF/00026', 'approved', '2026-10-10', 'member', 1, '2025-10-10 09:23:29', '', NULL, NULL, '2025-10-10'),
(29, 'GAMEPALLY KRISHNAMURTHY', 'gamepallykrishnamurthy@gmail.com', '$2y$10$EXn6OrwbzpmnOC9Wb1SC/Ozp/bHpvm1sKFZTTHv/mt5LR1RbOOTjS', '5421369875', 'Male', '1983-10-17', 'S/O', 'GARNEPALLI MALLAIAH', '', 'MEMBER', '', '648956208961', 'Andhra Pradesh', 'Medak', 'H No 2-36 Jagadevpur Mandal Teegul Narsapur Po', '502301', 'active', '68e8d31ad7b4c.jfif', NULL, NULL, '12548946', NULL, NULL, 'offline', 'PCWWF/00027', 'approved', '2026-10-10', 'member', 1, '2025-10-10 09:34:18', '', NULL, NULL, '2025-10-10'),
(30, 'RAJU RAM', 'rajuram@gmail.com', '$2y$10$CwZupzVrSNQC2kmDlTXX/uPlOEkFaMXx7zU3momsq9jmrimP2WTIW', '6379064055', 'Male', '1996-07-02', 'S/O', 'SANWALA RAM', '', 'MEMBER', '', '450631782862', 'Rajasthan', 'Jalore', 'Hadetar', '343041', 'active', '68e8e464219ad.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00028', 'approved', '2026-10-10', 'member', 1, '2025-10-10 10:48:04', '', NULL, NULL, '2025-10-10'),
(31, 'Balaji Suresh', 'aakashbalaji@gmail.com', '$2y$10$uscbw1XnRY7XfT5dN8cnbeC8faMl9VVN2uqFRIt1dT01swiV.mvrW', '9840035105', 'Male', '1964-05-04', 'S/O', 'SUBHASH', '', 'MEMBER', '', '778477720464', 'Tamil Nadu', 'Chennai', '443,Pushpanjali  Apartments 2, Thirumangalam  Road Chennai 600049', '600049', 'active', '68e8e6fc03bad.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00029', 'approved', '2026-07-14', 'member', 1, '2025-10-10 10:59:08', '', NULL, NULL, '2025-07-15'),
(32, 'JAMI SIVA SANKAR', 'jamisivasankar@gmail.com', '$2y$10$xp28RcyZ/tSBnJoSIS3rq.bTp5e89f6uRTeGxYn0GDwWDoy5XXV6C', '7093369174', 'Male', '1990-06-10', 'S/O', 'RAMCHNADRA', '', 'MEMBER', '', '257964435958', 'Andhra Pradesh', 'Visakhapatnam', '22-121-74/1 Dayal Nagar Pedagantyada', '530044', 'active', '68e8eb34abe73.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00030', 'approved', '2026-10-10', 'member', 1, '2025-10-10 11:17:08', '', NULL, NULL, '2025-10-10'),
(33, 'SACHIN SINGH', 'sachinsing@gmail.com', '$2y$10$4wu03jvn22coKxP63AG41O4N6NbqQQ..6QbS6C38qauCtQy4/C5m6', '6263669182', 'Male', '2001-07-18', 'S/O', 'NARENDRA SINGH', '', 'MEMBER', '', '592433680860', 'Chhattisgarh', 'Bijapur', 'kush vaha 351 Ward 10 Gram Sanjay Para Bhairamgarh', '494450', 'active', '68e8ed5f812c1.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00031', 'approved', '2026-10-10', 'member', 1, '2025-10-10 11:26:23', '', NULL, NULL, '2025-10-10'),
(34, 'N NARENDRA BABU', 'nnarendrababu@gmail.com', '$2y$10$ocO/XmDfxQ1wtJ84aj1SDO5v2JOQTfWpja8QRWTnsRUvKzuyJEtWu', '9008625512', 'Male', '1975-05-03', 'S/O', 'NARASAPPA', '', 'MEMBER', '', '900026268189', 'Karnataka', 'Shimoga', '1st Cross Narasimha Badavane Vinoba Nagar', '577201', 'active', '68e8eeeda3c13.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00032', 'approved', '2026-10-10', 'member', 1, '2025-10-10 11:33:01', '', NULL, NULL, '2025-10-10'),
(35, 'RAM KRISHNAN V', 'ramkrishnanv@gmail.com', '$2y$10$NPy.NrpRG8NUxFIeVQO99u8FOrlWdM85KAIl7M5Bg.GAGLm0BFx2.', '9947108981', 'Male', '1985-10-01', 'S/O', 'VELAUTHAN ACHARI', '', 'MEMBER', '', '992874226235', 'Kerala', 'Alappuzha', 'kollan parambil kandiyoor thattarambalam po mavelikkara', '690103', 'active', '68e8f1aac5cc6.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00033', 'approved', '2026-10-10', 'member', 1, '2025-10-10 11:44:42', '', NULL, NULL, '2025-10-10'),
(36, 'PEDDONI KRISHNA', 'peddonikrishna@gmail.com', '$2y$10$YZ2jeIztUQ/W0iWfzmKxXuCATyU6HsEvpUhznwjftGO2Q.bftdbna', '9848540320', 'Male', '1986-08-10', 'S/O', 'SANWALA', '', 'MEMBER', '', '933924913814', 'Andhra Pradesh', 'Mahabub', 'Balanagar  Mandal Gouthapur Po Balanagar', '509202', 'active', '68e8f33d9a885.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00034', 'approved', '2026-10-10', 'member', 1, '2025-10-10 11:51:25', '', NULL, NULL, '2025-10-10'),
(37, 'RAJA MUKHERJEE', 'rajamukherjee@gmail.com', '$2y$10$jJOG8mRUQ.EuJ47Hgyrj2.vd5m7hhw2jHmnCCbBqStqqjSBzf6Hku', '9830836804', 'Male', '1977-01-05', 'S/O', 'MADHUSUDAN MUKHERJEE', '', 'MEMBER', '', '302318083822', 'West Bengal', 'Kolkata', '3/9B Mukherjee Para Lanenn  Kalighat', '700026', 'active', '68e8f49d7c4ef.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00035', 'approved', '2026-10-10', 'member', 1, '2025-10-10 11:57:17', '', NULL, NULL, '2025-10-10'),
(38, 'RAJ KUMAR SINGARIYA', 'rajkumarsingariya@gmail.com', '$2y$10$U23XLYch5lfhwGB1qk5LKOwbyOmWTxgbKb4IBVQFg2qMKHzgQbxhq', '9602705750', 'Male', '1992-07-31', 'S/O', 'SARVAN KUMAR  SINGARIYA', '', 'MEMBER', '', '344644488071', 'Rajasthan', 'Sikar', 'Dansroli', '332742', 'active', '68e8f5e830716.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00036', 'approved', '2026-10-10', 'member', 1, '2025-10-10 12:01:57', '', NULL, NULL, '2025-10-10'),
(39, 'RENUGOPAL S', 'renugopals@gmail.com', '$2y$10$F73WLFdN8bfi7ThGKth.xuoefi8j5MauapuJNuvQ8LUX9D7ArofiG', '9626399968', 'Male', '1973-05-03', 'S/O', 'SUBRAMANI', '', 'MEMBER', '', '988393560179', 'Tamil Nadu', 'Vellore', 'Poonthottam  India Nagar Perumugai', '632009', 'active', '68e8f73f54fc5.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00037', 'approved', '2026-10-10', 'member', 1, '2025-10-10 12:08:31', '', NULL, NULL, '2025-10-10'),
(40, 'GANESH NANDLAL THAKARE', 'ganeshnandlalthakare@gmail.com', '$2y$10$lDJ2oCXhoCmEo9ig0qPeZudeKVzMndctGkoWnCyEfg/0aA/eI6hJS', '9657508645', 'Male', '1987-07-14', 'S/O', 'SANWALA', '', 'MEMBER', '', '343463846279', 'Maharashtra', 'Nashik', 'Welapur VTC Niphad  Po Lasalgaon', '422306', 'active', '68e8f8b7b8688.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00038', 'approved', '2026-10-10', 'member', 1, '2025-10-10 12:14:47', '', NULL, NULL, '2025-10-10'),
(41, 'LLAIYARAJA', 'llaiyaraja@gmail.com', '$2y$10$4A0PHcsCFfR4wD80d5.VFOntAH5gtNJVu4C.H0YgVUfAy/w9kSzrO', '7448684868', 'Male', '1982-09-07', 'S/O', 'DEVAN', '', 'MEMBER', '', '563491214027', 'Tamil Nadu', 'Tiruvallur', 'No  20  Gandhi Streel Mogappair', '600037', 'active', '68e8fb326fc63.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00039', 'approved', '2026-10-10', 'member', 1, '2025-10-10 12:25:22', '', NULL, NULL, '2025-10-10'),
(42, 'Usman', 'usman@gmail.com', '$2y$10$O.NzjjWmPdOaRDH8d0Jfr.hd9zEa7X9Lkv/8MeESp6j7Od6ZqIEwu', '9755303843', 'Male', '1981-06-30', 'S/O', 'Chand', '', 'MEMBER', '', '920812554546', 'Madhya Pradesh', 'Dhar', '72 Ranan Pratap Marg Bdnawar', '454660', 'active', '68e8fcd9b99d7.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00040', 'approved', '2026-10-10', 'member', 1, '2025-10-10 12:32:25', '', NULL, NULL, '2025-10-10'),
(43, 'PRITHIV RAJ F', 'Punithaprithivraj@gmail.com', '$2y$10$1A8AZQyWr4Kd62UwZ1BcFer4Z0CcQOf3A5cvJbCZLxbLUYVL/bANm', '9626053626', 'Male', '1999-04-15', 'S/O', 'Francls', '', 'MEMBER', '', '288876615059', 'Tamil Nadu', 'Vellore', '2/138 Pazhaiya Manai Keezhchendathur melpatti Past', '635805', 'active', '68e8ffb69ddc3.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00041', 'approved', '2026-10-10', 'member', 1, '2025-10-10 12:44:38', '', NULL, NULL, '2025-10-10'),
(44, 'SANTOSH KUMAR', 'santoshkumar@gmail.com', '$2y$10$SFi8mBYrM/0Kod5znPfkpe5s1EBkuDECyw.jMkfUm1B8yQHcHJ.Ra', '9650543190', 'Male', '1990-02-06', 'S/O', 'SUSHIL JHA', '', 'MEMBER', '', '602040297136', 'Delhi', 'Nagafgarh', 'H No 213/1 Laxmi Vihar B- Block Nangloi Road', '110043', 'active', '68e901a552a0e.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00042', 'approved', '2026-10-10', 'member', 1, '2025-10-10 12:52:53', '', NULL, NULL, '2025-10-10'),
(45, 'Dr Ushma Harendra Udeshi', 'ushmau1973@gmail.com', '$2y$10$6R6mjHWErF/gf43V4uoVte6ZV41XSv0TcHS6ssL8I4KpSeYXFkWFi', '5847965248', 'Female', '1996-06-19', 'S/O', 'SANWALA', '', 'MEMBER', '', '344644488071', 'Gujarat', 'Anand', '8,Harikunj socity Behind jain derasar Surya mandir Road. Borsad', '388540', 'active', '68e9e360dce19.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00043', 'approved', '2026-10-11', 'member', 1, '2025-10-11 04:56:00', '', NULL, NULL, '2025-10-11'),
(46, 'Trilochan Barik', 'trilochanbarik9070@gmail.com', '$2y$10$v6O8bLbSrm6eVvkWF/gOX.bYnVOmzAo39S5kbf174fUZjtjO7MtA.', '7894703898', 'Male', '1981-04-04', 'S/O', 'Bhramarbar Barik', '', 'MEMBER', '', '236614799032', 'Odisha', 'Khordha', 'At=Gopinathpur, p/o-Balugaon', '752030', 'active', '68e9f4ec956f8.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00044', 'approved', '2026-10-11', 'member', 1, '2025-10-11 06:10:52', '', NULL, NULL, '2025-10-11'),
(47, 'Rajesh  PG', 'rajeshpg@gmail.com', '$2y$10$pPKEf3pPeX66VaSBUScrI.tfSSjRN.2esTCouBn.a6Hwe6MdkP/yW', '9567590839', 'Male', '1977-04-05', 'S/O', 'Gopal', '', 'MEMBER', '', '997206669061', 'Kerala', 'Thrissur', 'PorathikkattilHouse SM Lane Pullazhi Po Manakkody', '680012', 'active', '68eb3a67d9e34.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00045', 'approved', '2026-10-12', 'member', 1, '2025-10-12 05:19:35', '', NULL, NULL, '2025-10-12'),
(48, 'Mahendran PM', 'mahendrapm@gmail.com', '$2y$10$yqKcIvi/BLQ.pQLrMaEdF.K/LDD8tCrOFRkB9MU8AmcuRvM07w3dK', '9048007009', 'Male', '1986-06-25', 'S/O', 'Mani PM', '', 'MEMBER', '', '507105577589', 'Kerala', 'Thrissur', 'House Peringanoor Peramangalam', '680545', 'active', '68eb3cae4e8b2.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00046', 'approved', '2026-10-12', 'member', 1, '2025-10-12 05:29:18', '', NULL, NULL, '2025-10-12'),
(49, 'Patel Jaimit Manojbhai', 'jaimitptl9805@gmail.com', '$2y$10$xyHVTH8hSiDdFSMOkFmJweavVZ3SDJ3QUsiO0.IxirhYZbSRq3LD.', '3847848991', 'Male', '2006-08-09', 'S/O', 'Manojbhai Patel', '', 'MEMBER', '', '935786568092', 'Gujarat', 'Ahmedabad', '93/2 Patel Vas VTC Vinzol', '382445', 'active', '68ece07e8f63a.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00047', 'approved', '2026-10-13', 'member', 1, '2025-10-13 11:20:30', '', NULL, NULL, '2025-10-13'),
(51, 'M Sakthivel', 'editorsakthivel7@gmail.com', '$2y$10$UwH9IG3B6oDsfDn7n4m9au/X7JeyQj.hXsOUqHF4foDodQIOS7qGq', '9787576858', 'Male', '1988-05-19', 'S/O', 'Mookkan', '', 'MEMBER', '', '607734785180', 'Tamil Nadu', 'Salem', '54 Kalaivanar Street', '636141', 'active', '68ef31f2c09fd.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00049', 'approved', '2026-10-15', 'member', 1, '2025-10-15 05:32:34', '', NULL, NULL, '2025-10-15'),
(52, 'Vinayak R Harijan', 'vinayakrharijan@gmail.com', '$2y$10$9WZgPq6hCy0CxJN2QoVYsul6xkN31nzzqpEmv.MM7grPOdOndUB6S', '8618118695', 'Male', '1996-06-19', 'S/O', 'Hari', '', 'MEMBER', '', '344644488071', 'Karnataka', 'Dharwad', '78 Chamundeshwari Nagar Near Raj Nagar', '580032', 'active', '68f078ee2c316.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00050', 'approved', '2026-10-14', 'member', 1, '2025-10-16 04:47:42', '', NULL, NULL, '2025-10-14'),
(53, 'Binaya Kumar Mallik', 'mallikbinaya2@gmail.com', '$2y$10$Pp2vxDbUFA0S58CNNCPPjOQLpDj7DYQaImR6YmEr.p1v9OVss2Kee', '9938855455', 'Male', '1981-01-17', 'S/O', 'Rama Chandra Mallik', '', 'MEMBER', '', '877450092611', 'Odisha', 'Cuttack', 'Singhanathpur Patapur', '754008', 'active', '68f07a2c3b658.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00051', 'approved', '2026-01-16', 'member', 1, '2025-10-16 04:53:00', '', NULL, NULL, '2025-10-16'),
(54, 'MD FIROZ KHAN', 'mdfirozkhan@gmail.com', '$2y$10$a/8Nr5UrM5oO8rzsFpKU8O2LuV4/XoYoTz3PyN6AYuGsYWpc4PQ8a', '9832989898', 'Male', '1990-06-19', 'S/O', 'SANWALA', 'Self Business', 'State President', '', '450631782862', 'West Bengal', 'Kolkata', 'Islampur Near Masjid Asansol', '713301', 'state', '68f0815ba48cd.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00052', 'approved', '2026-10-15', 'coordinator', 1, '2025-10-16 05:23:39', '', NULL, NULL, '2025-10-16'),
(55, 'Hari Om Verma', 'Hoverma3087@gmail.com', '$2y$10$kagt9TBdoRZe/WxYenSMnuQEPH1nnv20Frr1ULaBDsqdArNUnF9/2', '8127697178', 'Male', '1968-08-20', 'S/O', 'Gaya Prasad', '', 'MEMBER', '', '752601272575', 'Uttar Pradesh', 'Kanpur Nagar', '30/87 Maheshwari Mohal', '208001', 'active', '68f1e0d4dba37.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00053', 'approved', '2026-10-17', 'member', 1, '2025-10-17 06:23:16', '', 'id_card_55_1760682216.png', '2025-10-17 06:23:36', '2025-10-17'),
(56, 'KAMALKUMAR KALIDAS SOLANKI', 'kamalkumar@gmail.com', '$2y$10$XStaxYBEsiOdSBrFFedCwe2fc1mqGZSLhjLB6K0ghh14XAV0LbBJm', '9998371203', 'Male', '1989-09-05', 'S/O', 'SURESH', '', 'MEMBER', '', '844068897105', 'Gujarat', 'Ahmedabad', 'block no 139/3332 gujarat housing socity meghani nagar', '380016', 'active', '68f34252b0fde.jpeg', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00054', 'approved', '2026-10-18', 'member', 1, '2025-10-18 07:31:30', '', NULL, NULL, '2025-10-18'),
(57, 'PRASANT KUMAR KAR', 'prasant@gmail.com', '$2y$10$S6c4Rf/rKpAZnpGizWBKs.eCq3fT3lBvMFYJvtCX34ZKKK81W6QXu', '9337234223', 'Male', '1985-06-02', 'S/O', 'KAR', '', 'MEMBER', '', '844068897105', 'Odisha', 'Rayagada', 'at padmapur,rayagada', '765025', 'active', '68f343e4bbd0a.jpeg', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00055', 'approved', '2026-10-18', 'member', 1, '2025-10-18 07:37:51', '', NULL, NULL, '2025-10-18'),
(58, 'VIJAYAKUMAR RP', 'vijayakumar@gmail.com', '$2y$10$3LwljZ4AjXQ6vyX./pBF/OENKcJKf/lzNcWSPi6aquj7lXG1kl6bi', '9884436624', 'Male', '1984-06-02', 'S/O', 'RP', '', 'MEMBER', '', '844068897105', 'Tamil Nadu', 'Chennai', 'NO 330/108 LOYDS ROAD (NEAR RELIANCE)ROYAPETAI CHENNAI', '600014', 'active', '68f34564633ce.jpeg', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00056', 'approved', '2026-10-18', 'member', 1, '2025-10-18 07:44:36', '', NULL, NULL, '2025-10-18'),
(59, 'Dr. Lakshmanbhai Virabhai Vankar', 'lakshmanbhaivirabhaivankar@gmail.com', '$2y$10$FFN28uGGHD4QvSEnigzPLOTrwh/ya/kOMmZv.rqj4WTSdZWwOrQsS', '9824989926', 'Male', '1990-06-19', 'S/O', 'Gaya Prasad', '', 'MEMBER', '', '344644488071', 'Gujarat', 'Mahisagar.', 'At &amp; Po. Movasa, Ta. Santrampur', '389260', 'active', '68f355e420f59.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00057', 'approved', '2026-10-18', 'member', 1, '2025-10-18 08:55:00', '', NULL, NULL, '2025-10-18'),
(60, 'SARATHY S', 'sarathys@gmail.com', '$2y$10$33nBadWD.IObZvwUESSI..Idu6zUzcPC9bkYu/Dd8U2jkOy2BfR/W', '9944324508', 'Male', '1983-04-05', 'S/O', 'SAMIVEL', '', 'MEMBER', '', '752601272575', 'Tamil Nadu', 'Vellore', '74 New Balavinayagar Kovil Street Gudiyatham', '632602', 'active', NULL, NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00058', 'approved', '2026-10-18', 'member', 1, '2025-10-18 09:28:27', '', NULL, NULL, '2025-10-18'),
(61, 'RENUKA GAUTAM DILIPBHAI', 'renukagautamdilipbhai@gmail.com', '$2y$10$65srCmdJBZ4UIX0ueoYbtucYU31c0H6h6d0dlG/6WsSphdeF3Ep6C', '9824214443', 'Male', '1993-02-23', 'S/O', 'BAROT DILIPBHAI', '', 'Training Head (State)', '', '513846085030', 'Gujarat', 'Rajkot', 'B -11 Ratnam Villa Bunglos Arihant Nagar 1 Jamanagar Road Bihind Ami', '360006', 'state', '68f35fd926ed8.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00059', 'approved', '2026-10-18', 'member', 1, '2025-10-18 09:37:29', '', NULL, NULL, '2025-10-18'),
(62, 'ARIPAKA NAGA VENKATA PRASAD', 'aripakanagavenkataprasad@gmail.com', '$2y$10$aUbU6dI1UtPNd937vC75DeCnbmUqK2Ip8lBYfK..VpHl9ND/9iBJS', '9912694457', 'Male', '1975-07-22', 'S/O', 'ARIPAKA SATYANARAYANA', '', 'MEMBER', '', '731315051968', 'Andhra Pradesh', 'East Godavari', 'Ragam Peta Relli Street VTC Kakinada', '533001', 'active', '68f361bc17bef.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00060', 'approved', '2026-10-18', 'member', 1, '2025-10-18 09:45:32', '', NULL, NULL, '2025-10-18'),
(63, 'DASARI BABU RAJENDRA KUMAR', 'dasaribaburajendrakumar@gmail.com', '$2y$10$G2Vujj6jKI/RaLHFQ3kF.OyuyNfKI2U87abioffW7GS9ZUYAA4Hoq', '9035721353', 'Male', '1985-06-15', 'S/O', 'THIRUMALAIAH', '', 'MEMBER', '', '532521394201', 'Karnataka', 'Bengaluru', 'no 107 1st floor janardhan reddy 1st  a cross near anuman temple', '560068', 'active', NULL, NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00061', 'approved', '2026-10-18', 'member', 1, '2025-10-18 09:52:38', '', NULL, NULL, '2025-10-18'),
(64, 'AKARSHA G', 'akarshag@gmail.com', '$2y$10$AOoQfmu2NRYSiMPAn/ZumeiKoc55eNIdKbZpL1hSiBEbIOZI2ViY6', '7892438253', 'Male', '1995-11-16', 'S/O', 'GURUMURTHY', '', 'MEMBER', '', '567672071060', 'Karnataka', 'Kolar', 'MALUR TALUK VTC CHIKKATHIRUPATHI', '563160', 'active', '68f364ebf18ac.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00062', 'approved', '2026-10-18', 'member', 1, '2025-10-18 09:59:07', '', NULL, NULL, '2025-10-18'),
(65, 'KALEPALLI JOHN WESLEY', 'kalepallijohnwesly@gmail.com', '$2y$10$kffZGLQi8EriqZB7tBPeB..Yjbe2tGHoyGch1F4pIqG.U9SIOdOyS', '9346444465', 'Male', '1986-10-08', 'S/O', 'KALEPALLI RAJAIAH', '', 'MEMBER', '', '573863312143', 'Andhra Pradesh', 'East Godavari', '1-153 markandeyapuram thammavaram po apsp camp', '533005', 'active', '68f367b5b99d0.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00063', 'approved', '2026-10-10', 'member', 1, '2025-10-18 10:11:01', '', NULL, NULL, '2025-10-18'),
(66, 'PATEL KISHAN KUMAR SHANKARLAL', 'patelkishankumarshankarlal@gmail.com', '$2y$10$iS3LERJlGyecvcbD8Qo5AeCVZjMC/ThegXl0DhJkGjx9SnCs.l9UK', '9624693624', 'Male', '1994-06-12', 'S/O', 'SHANKARLAL PATEL VADI', '', 'MEMBER', '', '273268123485', 'Gujarat', 'Jamnagar', 'KALAVAD', '361160', 'active', '68f3695a1be84.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00064', 'approved', '2026-10-18', 'member', 1, '2025-10-18 10:18:02', '', NULL, NULL, '2025-10-18'),
(67, 'Dr.Bhaskar shukla', 'bhaskarshukla@gmail.com', '$2y$10$57etKzCGlrMSv.FcrqZfBeRb3LFzuX6d7ow.KOFdexWapst1NeDQO', '8144044678', 'Male', '1963-03-02', 'S/O', 'ARVINDCHANDRA', '', 'MEMBER', '', '907229817837', 'Gujarat', 'Gandhinagar', 'plot no.2/b, sector 2/ a, gandhhinagar.', '382007', 'active', '68f36bf2b8b72.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00065', 'approved', '2025-10-18', 'member', 1, '2025-10-18 10:29:06', '', NULL, NULL, '2025-10-18'),
(68, 'Shazaib Bashir Parkar', '', '$2y$10$ZFPsK81OWXWEROVeHP9pq.oh3Tj45VGbh9NhXHP3D5F1UjyHMYNju', '6371896656', 'Male', '2003-11-28', 'S/O', 'SANWALA', '', 'MEMBER', '', '344644488071', 'Maharashtra', 'Ratnagiri', 'Atpost Pewe , Tal: Mandangad', '415214', 'active', '68f36f1fafdeb.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00066', 'approved', '2026-10-18', 'member', 1, '2025-10-18 10:42:39', '', NULL, NULL, '2025-10-18'),
(69, 'MEHABOOB ALI MB', 'mehaboob@gmail.com', '$2y$10$2rWFmD6vOJYcyY94HelJ/udQo65t7JEcYU04lUXca7YNuKo/Q6O9a', '9845387771', 'Male', '1984-12-20', 'S/O', 'KAR', '', 'State President', '', '844068897105', 'Karnataka', 'Bengaluru (Bangalore) Rural', 'SHARADHA NAGAR NEW TOWN NORTH YALAHANKA', '560007', 'active', '68f370e1bd2ac.jpeg', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00067', 'approved', '2026-10-18', 'member', 1, '2025-10-18 10:50:09', '', NULL, NULL, '2025-10-18'),
(70, 'Raghavendra Raju R', 'raghuraju5558@gmail.com', '$2y$10$YtyeMa7WC9kPjPmwau67DeATmLZggbDaGwvl4y1EHmYh.9JcSi8ze', '9742985558', 'Male', '1985-04-02', 'S/O', 'P Rama Raju R', '', 'DISTRICT PRESIDENT', '', '499414749129', 'Karnataka', 'Bangalore', 'No 86 Naskar Building Green House 21 st Cross', '560036', 'active', '68f719d61600e.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00068', 'approved', '2025-10-21', 'member', 1, '2025-10-21 05:27:50', '', NULL, NULL, '2025-10-21'),
(71, 'Dharmendra Kumar Rathore', 'Dharmendrakumarrathore@gmail.com', '$2y$10$r.b5PTnj9HqS9l6mLAihzehZko3GHPLR6BqxUx6azAv87ECnygJH.', '9516281213', 'Male', '1993-06-19', 'S/O', 'Gaya Prasad', '', 'MEMBER', '', '450631782862', 'Madhya Pradesh', 'Sehore', 'Netaji house bas stand kalapipal post Bordi kalan tehsil ichhawar', '764036', 'active', '68f72f0dd9923.jpg', NULL, NULL, '', NULL, '68f72f0dd9c02.jpg', 'offline', 'PCWWF/00069', 'approved', '2026-10-21', 'member', 1, '2025-10-21 06:58:21', '', NULL, NULL, '2025-10-21'),
(72, 'Sandip Kusha Patil', 'sandipkushapatil@gmail.com', '$2y$10$C87w4Nkn3Wv4pcpFN/dkYuAElnd9fnZQTEy9hZzb6.NOjtWZroh6e', '9029158619', 'Male', '1991-06-19', 'S/O', 'ARVINDCHANDRA', '', 'MEMBER', '', '752601272575', 'Maharashtra', 'Thane', 'Daighar Gaon  veshi mata mandir', '421204', 'active', '68f7309ea522a.jpg', NULL, NULL, '', NULL, '68f7309ea555b.jpg', 'offline', 'PCWWF/00070', 'approved', '2026-10-21', 'member', 1, '2025-10-21 07:05:02', '', NULL, NULL, '2025-10-21'),
(73, 'R.Praveen Kumar', 'rpraveenkumar@gmail.com', '$2y$10$CmkfOOgTPqUzX9jKsJlQrua4eowqRDqiYVvB0WwRkXNAgR.Sinph2', '6371896645', 'Male', '1990-02-10', 'S/O', 'Gaya Prasad', '', 'MEMBER', '', '992874226235', 'Andhra Pradesh', 'Eluru', 'CHR palem, main road Lingapalem mandal,', '764036', 'active', '68f753dc891c5.jpg', NULL, NULL, '', NULL, '68f753dc8952e.jpg', 'offline', 'PCWWF/00071', 'approved', '2026-10-21', 'member', 1, '2025-10-21 09:35:24', '', NULL, NULL, '2025-10-21'),
(74, 'Baibhav kumar singh', 'baibhav@gmail.com', '$2y$10$JSp7Qhcm42EqmsC8qpS//eeSsvubAemQs8Yb2It/umlQv3upOOPQS', '6203797974', 'Male', '1986-06-02', 'S/O', 'Prabhash kumar singh', '', 'MEMBER', '', '844068897105', 'Bihar', 'Muzaffarpur', 'Rajput tola. Muzaffarpur bihar.\r\n842002. Muzaffarpur.', '842002', 'active', '68f75ba438278.jpeg', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00072', 'approved', '2026-10-21', 'member', 1, '2025-10-21 10:08:36', '', NULL, NULL, '2025-10-21'),
(75, 'Usmangani Yunus Dhamasker', 'usmangani@gmail.com', '$2y$10$ZE0duGNFTwX/DDcDoi.JferrFBkn1wTJx9QLhlEXdYH/ugQLZYpS.', '9136790640', 'Male', '1987-04-06', 'S/O', 'KAR', '', 'MEMBER', '', '844068897105', 'Maharashtra', 'Mumbai City', 'room no 7 dhamaskar house abdullah painter compound opp leela hotel behind Novotel al nizami darbar gali a/k road', '400059', 'active', '68f75dc698314.jpeg', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00073', 'approved', '2026-10-21', 'member', 1, '2025-10-21 10:17:42', '', NULL, NULL, '2025-10-21'),
(76, 'Inder Kumar Jairamani', 'inderkumarjairamani@gmail.com', '$2y$10$v0JTy.ulXpoCUFur65BST.DqW0OUFScZ/ds.Gke0ZjXM.92gAS/OW', '9214629999', 'Male', '1990-06-19', 'S/O', 'Tikam chand Jairamani', '', 'MEMBER', '', '752601272575', 'Rajasthan', 'Jaipur', '32/19 Swarn Path Mansarovar', '302020', 'active', '68f7654288776.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00074', 'approved', '2026-10-21', 'member', 1, '2025-10-21 10:49:26', '', NULL, NULL, '2025-10-21'),
(77, 'manoj kumar P', 'manojkumarp@gmail.com', '$2y$10$wKdIcEOANOFTB3hJlbDLv.LZBJ3SxZfzn4J2DEkNg237y5kgMAez.', '9600106942', 'Male', '2000-06-18', 'S/O', 'Geetha Prem Kumar', '', 'MEMBER', '', '752601272575', 'Tamil Nadu', 'Chennai', 'No 1 Manimegalai 1st Street Pallikaranai', '600100', 'active', '68f76711a48b7.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00075', 'approved', '2026-10-21', 'member', 1, '2025-10-21 10:56:37', '', NULL, NULL, '2025-10-21'),
(78, 'Mrutynjay G. Hiremath', 'mrutynjay@gmail.com', '$2y$10$opF9rzTiylrzvXJ9Huos4u7kDO7FEmsI8mXktuSgeQBHPJh3NolEy', '9448469789', 'Male', '1985-06-03', 'S/O', 'SURESH', '', 'MEMBER', '', '844068897105', 'Karnataka', 'Bagalkot', 'A/P : Malapur  (Mudhol)', '603112', 'active', '68f7693eee64c.jpeg', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00076', 'approved', '2026-10-21', 'member', 1, '2025-10-21 11:06:38', '', NULL, NULL, '2025-10-21'),
(79, 'Mohammad Mizanur Alam', 'mohammadmizanuralam@gmail.com', '$2y$10$ityLhICEAVu/hfml9EsRCuOLyVjUjDqK0YtAPVLENouP7z6XU.GIa', '8638232870', 'Male', '1990-07-10', 'S/O', 'Ahammad Ali Miah', '', 'MEMBER', '', '257964435958', 'Goa', 'North Goa', 'House No 340/2 Morrod Sangolda', '403511', 'active', '68f76bb58c797.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00077', 'approved', '2026-10-21', 'member', 1, '2025-10-21 11:16:59', '', NULL, NULL, '2025-10-21'),
(80, 'Narinder Kumar', 'narinderkumar@gmil.com', '$2y$10$X0pobJFnH8QjQOXGV5df5u0oEfStHxlN3b.NlloZnZGL0lE2RaYRS', '8847257288', 'Male', '1990-05-18', 'S/O', 'Sh Nazar Ram', '', 'MEMBER', '', '907229817837', 'Punjab', 'Shahid Bhagat Singh Nagar', 'V.P.O Happowal Teh Banga', '144505', 'active', '68f76e49138b8.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00078', 'approved', '2026-10-21', 'member', 1, '2025-10-21 11:27:11', '', NULL, NULL, '2025-10-21'),
(81, 'Raa Dea Karabhari', 'raadeakarabhari@gmail.com', '$2y$10$zhH0SAutc43eCSafWWb1I.ttRnfAKiU8hCkEkWkuxOqAEALi0UTze', '7760433113', 'Male', '1990-06-18', 'S/O', 'Devaraj Karabhari', '', 'MEMBER', '', '257964435958', 'Karnataka', 'Gadag', 'Ashok Road Health Camp Betageri', '582102', 'active', '68f76fbe8dd18.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00079', 'approved', '2026-10-21', 'member', 1, '2025-10-21 11:32:49', '', NULL, NULL, '2025-10-21'),
(82, 'Radha Raman Sharma', 'radharamansharma@gmail.com', '$2y$10$YoQMJlshFLeLbWDD.TcTB.N.Z3ELY8L1tMDuznbEYWxXYGPJ4rgrW', '9161011112', 'Male', '1991-06-19', 'S/O', 'Gaya Prasad', '', 'MEMBER', '', '907229817837', 'Uttar Pradesh', 'Kanpur', '107/3/121 Vijay Nagar  Thane Kakadeo', '208005', 'active', '68f77189777b1.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00080', 'approved', '2026-10-21', 'member', 1, '2025-10-21 11:40:21', '', NULL, NULL, '2025-10-21'),
(83, 'Chandan Singh', 'chandrasingh@gmail.com', '$2y$10$yPoXyOuwrTJFPKKcGR8uTuK7nvJwHa8cDaa8BZcmswPfOSesBlSDm', '9014185405', 'Male', '1990-02-18', 'S/O', 'Shyam Singh', '', 'MEMBER', '', '992874226235', 'Uttarakhand', 'Almora', 'Vill  Bachkande Post Syunani', '263625', 'active', '68f774f79572e.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00081', 'approved', '2026-10-21', 'member', 1, '2025-10-21 11:46:42', '', NULL, NULL, '2025-10-21'),
(84, 'Sharad Mishra', 'sharadmishra@gmail.com', '$2y$10$QozCgWO.DEd8yX6QtMgYyuptFvO1SubvhTmiTIubqTIcFNF43JFBO', '9202708221', 'Male', '1990-03-18', 'S/O', 'SANWALA RAM', '', 'MEMBER', '', '992874226235', 'Madhya Pradesh', 'Indore', '1 CLOTH MARKAT HOSPITALPARISAR DHAR ROAD NEAR GANGWAL BUS STAND', '452002', 'active', '68f7779c28ba7.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00082', 'approved', '2026-10-21', 'member', 1, '2025-10-21 12:05:46', '', NULL, NULL, '2025-10-21'),
(85, 'Rabindra Kumar', 'rabindrakumar@gmail.com', '$2y$10$q73ZZck9CsLFxr8R7oiAteWLr9s1wTCqQ4DNy4ydi8qkuCUNY0Ei6', '8093013800', 'Male', '1990-06-19', 'S/O', 'Narendra Kumar', '', 'MEMBER', '', '450631782862', 'Odisha', 'Puri', 'Nabalkaleibar Road Near Arjun Das Clinic', '752002', 'active', '68f77b182e7db.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00083', 'approved', '2026-10-21', 'member', 1, '2025-10-21 12:22:48', '', NULL, NULL, '2025-10-21'),
(86, 'Dr Vaishnavi Premchandani', 'deevaish.15@gmail.com', '$2y$10$53byuwWwFS.iQMut67wvN.xmTZUutv5/VLm4cv1luU9DRqTPs/gJq', '9833947506', 'Female', '1977-11-02', 'W/O', 'Deepak Premchandani', 'Self Business', 'MEMBER', 'O+', '581702658445', 'Maharashtra', 'Mumbai Suburban', '602/dunhill Building 6th floor Dr Ambedkar road khar west Mumbai  opp Bon Bon Medical store', '400052', 'active', '68f77bcec7f44.jpg', '68f77bcec819a.jpg', '68f77bcec858a.jpg', '', NULL, '68f77bcec89b1.jpg', 'offline', 'PCWWF/00084', 'approved', '2026-10-21', 'member', NULL, '2025-10-21 12:25:50', 'Social worker', NULL, NULL, '2025-10-21'),
(87, 'Vinita Minda', 'vinitaminda@gmail.com', '$2y$10$u1vcrYa5Qe3MgO6Z1TEhkejMtVR91462XLhj/.UwbYmGn.nkV4Yji', '9829917500', 'Male', '1990-06-19', 'S/O', 'Kailash Chandra', '', 'MEMBER', '', '450631782862', 'Rajasthan', 'Pratapgarh', 'Pratapgarh', '312605', 'active', '68f77d882efaf.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00085', 'approved', '2026-10-21', 'member', 1, '2025-10-21 12:33:12', '', NULL, NULL, '2025-10-21'),
(88, 'BASABVARAJ SABU CHAVAN', 'basabvarajsabuchavan@gmail.com', '$2y$10$s3iQe1i5mRQQyEm39CkCC.4WePlFICH2l6UStwvKj7l7c806Wp1pa', '8197001999', 'Male', '1990-04-18', 'S/O', 'Gaya Prasad', '', 'MEMBER', '', '450631782862', 'Karnataka', 'Chikballapur', 'jyothirlinga nilaya anjanadri badavane beside  golden glems', '562101', 'active', '68f77f212bfe0.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00086', 'approved', '2026-10-21', 'member', 1, '2025-10-21 12:40:01', '', NULL, NULL, '2025-10-21'),
(89, 'Prasenjit Diha', 'prasenjitdiha@gmail.com', '$2y$10$7D98DAA0.LNlm/.fUo040epU2gVj6T7lB8sUvBqOcw.vgMvNe3hMq', '7602020903', 'Male', '1991-06-19', 'S/O', 'Arati Diha', '', 'MEMBER', '', '257964435958', 'West Bengal', 'West Burdwan', 'Ispat pally B zone durgapur 5', '713205', 'active', '68f781471a571.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00087', 'approved', '2026-10-21', 'member', 1, '2025-10-21 12:49:11', '', NULL, NULL, '2025-10-21'),
(90, 'VIKRANT NARAYAN KENE', 'vkrantnarayankene@gmail.com', '$2y$10$2j5oTLKWonC4304DRQ401emJIHXXOY9YJ7AzVW9S.Bz4rt6564jMe', '7030289827', 'Male', '1993-05-19', 'S/O', 'SMITA', '', 'MEMBER', '', '450631782862', 'Maharashtra', 'Thane', 'F N 02/03 Shivam Complex E Wing Kalyan bhiwandi road kongaon', '421311', 'active', '68f783689e896.jpg', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00088', 'approved', '0000-00-00', 'member', 1, '2025-10-21 12:58:16', '', NULL, NULL, '0000-00-00'),
(91, 'Amith Govind', 'crimefile2012@gmail.com', '$2y$10$6ClaM/xfErDkTbtu9TIkW.9yQjbThoB4Nb/G5tbb1TdbvxXIO5yOC', '9916609666', 'Male', '1982-10-08', 'S/O', 'Mudalagiri', 'Self Business', 'MEMBER', 'A+', '569293848972', 'Karnataka', 'Bengaluru (Bangalore) Urban', 'No. 46, 3rd floor, 5th Cross, Mahadeshwara Niagara, Herohalli, Tenginatotha, Bengaluru', '560091', 'active', '68f83600d37a8.jpg', '68f83600d3b29.jpg', '68f83600d3d32.jpg', '', NULL, '68f83600d4574.jpg', 'offline', 'PCWWF/00089', 'approved', '0000-00-00', 'member', NULL, '2025-10-22 01:40:16', 'Bengaluru', NULL, NULL, '0000-00-00'),
(92, 'BAJRANG LAL  BAJAJ', 'bajranglalbajaj@gmail.com', '$2y$10$VJXgUZb8TZpEFfUPp1K/UuhuIQk/OOZdqv1retREM1l/acxdFMgLe', '7020153490', 'Male', '1990-02-20', 'S/O', 'SANWALA', '', 'MEMBER', '', '344644488071', 'Goa', 'North Goa', 'Socorro Garden Block D Flat No 101 &amp; AMP', '403501', 'active', '68f86254e5b91.jpg', NULL, NULL, '12548945', NULL, NULL, 'offline', 'PCWWF/00090', 'approved', '2026-10-22', 'member', 1, '2025-10-22 04:49:24', '', NULL, NULL, '2025-10-22'),
(93, 'INDU SHARMA', 'indusharma@gmail.com', '$2y$10$WMrXJKVm6FZsuyStsoDCMum4WeYUgSbiNy3KhNnAIaE58UIy/ekAy', '9419821007', 'Female', '1994-06-19', 'S/O', 'ARVINDCHANDRA', '', 'MEMBER', '', '257964435958', 'Jammu and Kashmir', 'Jammu', 'H No 118 Panjtrirhi', '180001', 'active', '68f86637739a5.jpg', NULL, NULL, '12548945', NULL, NULL, 'offline', 'PCWWF/00091', 'approved', '2026-10-22', 'member', 1, '2025-10-22 05:05:59', '', NULL, NULL, '2025-10-22'),
(94, 'SIVAPRASAD BL', 'sivaprasadbl@gmail.com', '$2y$10$HdppGa1gS2Rzj/1/7bZ6feEbOHIIlLEBJ/jQLgSSYNLSgJeXjBIYq', '9447345668', 'Male', '1989-06-19', 'S/O', 'S BABY', '', 'MEMBER', '', '233626087707', 'Kerala', 'Thiruvananthapuram', '15/1210 kukkiliyal ane dpi thycaud post', '695014', 'active', '68f8684b09c20.jpg', NULL, NULL, '12548945', NULL, NULL, 'offline', 'PCWWF/00092', 'approved', '2026-10-22', 'member', 1, '2025-10-22 05:14:51', '', NULL, NULL, '2025-10-22'),
(95, 'PRAMOD KUMAR TAPRE', 'pramodkumartapre@gmail.com', '$2y$10$YpGIMB.pGx.MqDBtv4lbXu3ajNgayZyCfxkUo4UWEDDouSb3PAxpu', '9284421727', 'Male', '1984-04-15', 'S/O', 'SANWALA RAM', '', 'MEMBER', '', '975333446286', 'Maharashtra', 'Pune', 'La-Salette Society, Flat No- B-602, Kirtane Baugh, Magarpatta-Mundhwa Bypass', '411036', 'active', '68f869853abe2.jpg', NULL, NULL, '12548945', NULL, NULL, 'offline', 'PCWWF/00093', 'approved', '2026-10-22', 'member', 1, '2025-10-22 05:20:05', '', NULL, NULL, '2025-10-22'),
(96, 'REPAKULA NARESH', 'repakulanaresh@gmail.com', '$2y$10$vxggueX1YwmBbNZ2thxK9eazTdKIC5cJbkaPlwNEdyaEOmtLysTV.', '9652003017', 'Male', '1990-06-24', 'S/O', 'NAGESHWARA RAO', '', 'MEMBER', '', '793117471560', 'Telangana', 'Suryapet', '5-14/A BC Colony Ananthagiri Mandalam', '508206', 'active', '68f86b6d3a467.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00094', 'approved', '2026-10-22', 'member', 1, '2025-10-22 05:28:13', '', NULL, NULL, '2025-10-22'),
(97, 'SHAMALABHAI PADAMAJI KAPADI', 'shamalabhaipadamajikapadi@gmail.com', '$2y$10$f8Zjp8Ij8jsoxZP.0XCgI.M73AVuEi9/iLIfGKS.n5OW9rjh9Nq7C', '9909746610', 'Male', '1979-01-01', 'S/O', 'SANWALA', '', 'MEMBER', '', '235403609802', 'Gujarat', 'Banaskantha', 'Kuda Kapadi Vas Ta Lakhani', '385535', 'active', '68f86d1071ac1.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00095', 'approved', '2026-10-22', 'member', 1, '2025-10-22 05:35:03', '', NULL, NULL, '2025-10-22'),
(98, 'BASAWESHWARA M', 'basaweshwaram@gmail.com', '$2y$10$h3j8AKKgbA0NikiPHHBKZ.PzrsDy2eP3WH./k7qErzbnSGZACcARi', '8431302018', 'Male', '2000-01-26', 'S/O', 'MALIU MADAPPA', '', 'MEMBER', '', '407913702326', 'Karnataka', 'Bangalore', '386 1ST CROSS BEUR ROAD BEHIND CMC', '560068', 'active', '68f8709c17cdd.jpg', NULL, NULL, '12548945', NULL, NULL, 'offline', 'PCWWF/00096', 'approved', '2026-10-22', 'member', 1, '2025-10-22 05:50:20', '', NULL, NULL, '2025-10-22'),
(99, 'Tarini Prasad Behera', 'tariniprasadbehera@gmail.com', '$2y$10$RdgLkb0lnWmyx2I0S9FVeO4Zsg/R9/Wl/vTM8iFDob35FWL6wCmQe', '7978959069', 'Male', '1996-05-19', 'S/O', 'SANWALA', '', 'MEMBER', '', '257964435958', 'Odisha', 'Keonjhar', 'tarini prasad behera at barik sahi anandapur', '758021', 'active', '68f8759fa2962.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00097', 'approved', '2026-10-22', 'member', 1, '2025-10-22 06:11:43', '', NULL, NULL, '2025-10-22'),
(100, 'ANUP INAMDAR', 'anupinamdar@gmail.com', '$2y$10$0NmzF5EssF6jKUUOgAAMPeEv/g1UW22MotAhl2FHqQDPVLr6HaDxS', '9422090089', 'Male', '1964-02-21', 'S/O', 'Gaya Prasad', '', 'MEMBER', '', '433393275151', 'Maharashtra', 'Thane', 'FLAT 1006 FLOOR 10 PARSHWANATH GALAXY CHS KASARVADAVLI', '400615', 'active', '68f877254b105.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00098', 'approved', '2026-10-22', 'member', 1, '2025-10-22 06:18:13', '', NULL, NULL, '2025-10-22'),
(101, 'KUNA KUMAR SAHOO', 'kunakumarsahoo@gmail.com', '$2y$10$x3kGA1LvJirn2DNz6/dmF.afqVHox/3MWsCyq1CatDsxs5sz5spyW', '9437149645', 'Male', '1975-06-18', 'S/O', 'SANWALA', '', 'MEMBER', '', '450631782862', 'Odisha', 'Angul', 'kendubaniasahi l.t.i bypass road hatatota talcher', '759100', 'active', '68f8799baae06.jpg', NULL, NULL, '12548945', NULL, NULL, 'offline', 'PCWWF/00099', 'approved', '2026-10-22', 'member', 1, '2025-10-22 06:28:43', '', NULL, NULL, '2025-10-22'),
(102, 'VIVEK DHALLAM', 'vivekdhallam@gmail.com', '$2y$10$Oen0obKQMl96ONcbW05kwuN.qlw/jXN/MxwwKHLYrbKrOmur2KsfK', '9419137701', 'Male', '1981-11-02', 'S/O', 'BALDEV RAJ', '', 'MEMBER', '', '587051952853', 'Jammu and Kashmir', 'Jammu', 'B NO 30 H NO 111 W NO 9 PO BISHNAH VTC BISHNA', '181132', 'active', '68f87a9b663e4.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00100', 'approved', '2026-10-22', 'member', 1, '2025-10-22 06:32:59', '', NULL, NULL, '2025-10-22'),
(103, 'VEMULAKONDA SUBRAMANYARAJA SRIDHAR', 'vemulakondasubramanyarajasridhar@gmail.com', '$2y$10$OgNIPuewP1gNhEpJAEUe2..yZHz/u2fs.zmLkzLm1D.D05nALmLNu', '9985094522', 'Male', '1973-07-21', 'S/O', 'SANWALA', '', 'MEMBER', '', '260621789858', 'Andhra Pradesh', 'Vizianagaram', '42-138 AGRAHARM STREET HEAD POST OFFICE VTC BOBBILI', '535558', 'active', '68f8815178571.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00101', 'approved', '2026-06-16', 'member', 1, '2025-10-22 07:01:37', '', NULL, NULL, '2025-06-17'),
(104, 'JASPAL SINGH NINDI', 'jaspalsinhghnindi@gmail.com', '$2y$10$6XVV8gqluHVIoF8lugDNCepfORgYpjFFGeqoj7ZAfdt2tblbDz4lC', '7009251431', 'Male', '1966-01-01', 'S/O', 'CHANAN SINGH', '', 'MEMBER', '', '399515120357', 'Punjab', 'Shaheed Bhagat Singh', 'KAHMA PO KAHMA', '144512', 'active', '68f883f7aed01.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00102', 'approved', '2026-08-26', 'member', 1, '2025-10-22 07:12:55', '', NULL, NULL, '2025-08-27'),
(105, 'DABHI  DASHARATHBHAI JORUBHAI', 'dabhidasharathbhaijorubhai@gmail.com', '$2y$10$lcr3faFrA1OfNgWvw3jZZudboUrg7w5eEizDJQn7wLjLvWzBe3SWu', '7862802709', 'Male', '1985-01-01', 'S/O', 'SANWALA', '', 'MEMBER', '', '752601272575', 'Gujarat', 'Ahmedabad', 'kalyangadh ta bavla jee', '382240', 'active', '68f8b13cae1a2.jpg', NULL, NULL, '12548945', NULL, NULL, 'offline', 'PCWWF/00103', 'approved', '2026-07-02', 'member', 1, '2025-10-22 10:26:04', '', NULL, NULL, '2025-07-03'),
(106, 'TAHSINAH FAIZ', 'tahsinahfaiz@gmail.com', '$2y$10$VwAUbkIo8AhuQnU9oCDSkO45uFOJFSQ0QWNBsbyWj4K4euoHegpoS', '9435537829', 'Female', '1979-11-22', 'S/O', 'SANWALA', '', 'MEMBER', '', '607099341749', 'Assam', 'Jorhat', '039 VIBGYOR HOUSE &amp; 039  OPP', '785001', 'active', '68f8b323ce1c7.jpg', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00104', 'approved', '2026-06-17', 'member', 1, '2025-10-22 10:34:11', '', NULL, NULL, '2025-06-18'),
(107, 'A M ARUN VIJAY', 'arun23kgf@gmail.com', '$2y$10$SHgWODqYaaeKCTJmKuGMfe5hnpyzv8PfnatXXwtMiZWZyH2ST8rmm', '8453995623', 'Male', '1986-06-10', 'S/O', 'ANBARASAN', '', 'MEMBER', '', '450631782862', 'Karnataka', 'Bangarapet Kolar', '# 299 NEAR FLOOR MILL BOWERIALPET', '563122', 'active', '68f8b44a9e8f6.jpg', NULL, NULL, '12548945', NULL, NULL, '', 'PCWWF/00105', 'approved', '2026-10-21', 'member', 1, '2025-10-22 10:39:06', '', 'id_card_107_1761129566.png', '2025-10-22 10:39:26', '2025-10-22');
INSERT INTO `users` (`id`, `name`, `email`, `password`, `mobile`, `gender`, `dob`, `sdw_type`, `sdw_name`, `profession`, `designation`, `blood_group`, `aadhar`, `state`, `district`, `address`, `pincode`, `membership_type`, `profile_image`, `aadhar_front`, `aadhar_back`, `payment_id`, `order_id`, `payment_proof`, `payment_method`, `registration_id`, `status`, `valid_until`, `user_type`, `created_by`, `created_at`, `working_area`, `id_card_photo`, `updated_at`, `valid_from`) VALUES
(108, 'SADIK ALI', 'sadikali@gmail.com', '$2y$10$RkPBwQG7XdMOb5YSB5jTBeud24G4Giy5wykOJQpB2nXg0kWcsto3a', '7067590371', 'Male', '1980-09-02', 'S/O', 'Gaya Prasad', '', 'MEMBER', '', '257964435958', 'Madhya Pradesh', 'Dindori', 'pata subkhar warde no 1 dindori mp', '491990', 'active', '68f8b71eb2f4d.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00106', 'approved', '2026-07-15', 'member', 1, '2025-10-22 10:51:10', '', NULL, NULL, '2025-07-16'),
(109, 'YAWER RASOOL', 'yawerrasool@gmail.com', '$2y$10$.FAlJO2fGjR3OSj.psNa..POJfwx/02qnxM16MCp2X9qitlXHG396', '9419253359', 'Male', '1994-11-22', 'S/O', 'HAFIZULLAH KHURWANI', '', 'MEMBER', '', '450631782862', 'Jammu and Kashmir', 'Doda', '029 mohalla irfan abad near masjid sah', '182202', 'active', '68f8b94bb8379.jpg', NULL, NULL, '12548945', NULL, NULL, 'offline', 'PCWWF/00107', 'approved', '2026-07-01', 'member', 1, '2025-10-22 11:00:27', '', NULL, NULL, '2025-07-02'),
(110, 'PRAMOD MANSINGRAO PATIL', 'pramodmansingraopatil@gmail.com', '$2y$10$EUbyiGYkEBZUR3H9DIWcR.lneTbq.s45kMhVTeyMK4fAhgh0exkTq', '9923477676', 'Male', '1985-04-12', 'S/O', 'SANWALA RAM', '', 'MEMBER', '', '485528871842', 'Maharashtra', 'Satare', 'shivaji  chouk kapil tal karad', '415124', 'active', '68f8bc4412774.jpg', NULL, NULL, '12548945', NULL, NULL, 'offline', 'PCWWF/00108', 'approved', '2026-07-13', 'member', 1, '2025-10-22 11:13:08', '', NULL, NULL, '2025-07-14'),
(111, 'VILAAS SHRIRAM MURTEE', 'vilaasshirirammurtee@gmail.com', '$2y$10$Rf9uTqLRSdfPrd7jo3qc..hv9tJORuoHhKzkHaZNvOeR5IHeH5.4a', '9922755052', 'Male', '1969-05-07', 'S/O', 'SANWALA', '', 'MEMBER', '', '344644488071', 'Maharashtra', 'Mumbai City', 'B62 VALLABH APARTMENT SEJAL PARK LINK ROAD  GOREGAON', '400104', 'active', '68f8c195f0fe0.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00109', 'approved', '2026-07-16', 'member', 1, '2025-10-22 11:35:49', '', NULL, NULL, '2025-07-17'),
(112, 'S K ANARUL HAQUE', 'skanarulhaque@gmail.com', '$2y$10$q32s4oVVf4GIFVI1fImQXOvxfIC7LSRX9qffvdYhfLofs1ZAhnkyy', '7001549135', 'Male', '1983-03-10', 'S/O', 'SK MOMEZZAD', '', 'MEMBER', '', '752601272575', 'West Bengal', 'Bankura', 'vill  kumbhasthal po salda ps joypur', '722122', 'active', '68f8c2e57023f.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00110', 'approved', '2026-07-16', 'member', 1, '2025-10-22 11:41:25', '', NULL, NULL, '2025-07-17'),
(113, 'BHARAT BHUSHAN SINGLA', 'bharatbhushansingla@gmail.com', '$2y$10$d1G2NDoVvpERikKiIdCZJuO9V0Goz82i8MuMkSC2QcfQQ47QwL2py', '9872075248', 'Male', '1990-06-19', 'S/O', 'SANWALA', '', 'MEMBER', '', '257964435958', 'Punjab', 'Barnala', 'kothi no 306 aastha enclave', '148101', 'active', '68f8c44b9d8ac.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00111', 'approved', '2026-08-14', 'member', 1, '2025-10-22 11:47:23', '', NULL, NULL, '2025-08-15'),
(114, 'RENJU R', 'nunchukrenju@gmail.com', '$2y$10$CWtVBC4sEDeF6/gUkTo8Kuj7u3DomZb3OcLVq3QC3vLA7Am7GOuwW', '9061792578', 'Male', '1988-05-26', 'S/O', 'Gaya Prasad', '', 'Youth Head (State)', '', '450631782862', 'Kerala', 'Kollam', 'Nedumkathil, \r\nCheruseribhagom, chavara p, o', '691583', 'state', '68f8c5e2d1aea.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00112', 'approved', '2026-07-19', 'member', 1, '2025-10-22 11:54:10', '', NULL, NULL, '2025-07-20'),
(115, 'AMIT KUMAR PAHWA', 'amitkumarpahwa@gmail.com', '$2y$10$Xf80umbr07/C7ClLMwOzyeTA57bz1nMKpew3hfWH4rbpuoxCAqZGy', '9009500080', 'Male', '1982-06-07', 'S/O', 'SANWALA', '', 'MEMBER', '', '752601272575', 'Madhya Pradesh', 'Indore', '98/1/8 lasudia mori a b road dewas naka', '453771', 'active', '68f8c713995a2.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00113', 'approved', '2026-07-17', 'member', 1, '2025-10-22 11:59:15', '', NULL, NULL, '2025-07-18'),
(116, 'S PANDIYAN', 'spandiyan@gmai9l.com', '$2y$10$Kq7b8fl8eERj.xTngRhNiuEAjGFu9BTkJtawOwqkWdC1csyHJSuzC', '9443116197', 'Male', '1965-05-30', 'S/O', 'SINGARAVELU', '', 'MEMBER', '', '257964435958', 'Tamil Nadu', 'Nagapattinam', '3/47 WEST STREET THIRUPPUGALUR', '609704', 'active', '68f8c9112c221.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00114', 'approved', '2026-05-29', 'member', 1, '2025-10-22 12:07:45', '', NULL, NULL, '2025-05-30'),
(117, 'INDUR ARUN', 'indurarun@gmail.com', '$2y$10$klEhM2D6APDO.oI3REZMz.gLyiigqqLx2VxrM2oijEJEQPxdc.r8C', '9700307258', 'Male', '1972-10-20', 'S/O', 'Gaya Prasad', '', 'MEMBER', '', '257964435958', 'Telangana', 'Nizamabad', '5-6 105/1 dwaraka nagr panchami mess', '503001', 'active', '68f8cae1b96a5.jpg', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00115', 'approved', '2026-07-15', 'member', 1, '2025-10-22 12:15:29', '', NULL, NULL, '2025-07-16'),
(118, 'MAHABOOBVALI SHAIK', 'mahaboobvalishaik@gmail.com', '$2y$10$7sFaw1g7KZfCCpSz7zumA.evQnpSJdcnq/3h4Cmwtnq8962pLzYjq', '8506820334', 'Male', '1982-06-10', 'S/O', 'ISMAEL', '', 'MEMBER', '', '450631782862', 'Andhra Pradesh', 'Prakasam', '1-203-42 E-3 MAHAMMAD NAGAR POWER  OFFICE', '523316', 'active', '68f8cd85e9735.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00116', 'approved', '0000-00-00', 'member', 1, '2025-10-22 12:26:45', '', NULL, NULL, '0000-00-00'),
(119, 'MATHUKUMALLI VENKATA PHANI BHUSHAN', 'mathukumallivenkataphanibhushan@gmail.com', '$2y$10$iCR4SA30OTBVhKrwsYM7m.y2X4OBXrES/w1uLjOfo8CsvgAWCE7QK', '9441589980', 'Male', '1958-11-01', 'S/O', 'LATE MS T SAI', '', 'MEMBER', '', '752601272575', 'Uttar Pradesh', 'Varanasi', 'B .13/4 SONARPURA', '221001', 'active', '68f8cf4c9ad53.jpg', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00117', 'approved', '2026-07-09', 'member', 1, '2025-10-22 12:34:20', '', NULL, NULL, '2025-07-10'),
(120, 'RAJAT  TALUKDAR', 'rajatalukdar@gmail.com', '$2y$10$NGuQSp1qdHzSc4lVfSUkxOqmueKQeP5hpN2bwPbBmquekR7hzzyRS', '8420784909', 'Male', '1990-12-08', 'S/O', 'Gaya Prasad', '', 'MEMBER', '', '257964435958', 'West Bengal', 'North 24 Parganas', 'R/;AA 23 RAGHUNATH PO DB NAGAR VIP ROAD KOLKATA', '700059', 'active', '68f8d3708459e.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00118', 'approved', '2026-07-21', 'member', 1, '2025-10-22 12:52:00', '', NULL, NULL, '2025-07-22'),
(121, 'MAHESH PRASAD  MISHRA', 'maheshprasadmishra@gmail.com', '$2y$10$LNFQMA4pyXNDWhKggV7vpOtQjhlWahUSiAVzH25uDLOqCs3zglGYG', '7000065821', 'Male', '1990-05-18', 'S/O', 'SANWALA', 'Self Business', 'National Health Head', 'B+', '257964435958', 'Madhya Pradesh', 'Bhopal', '186- C MAHABALI NAGAR KOLAR ROAD', '462042', 'national', '68f8d4b44d795.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00119', 'approved', '2026-07-10', 'member', 1, '2025-10-22 12:57:24', '', NULL, NULL, '2025-07-11'),
(122, 'SHUBHANK LALL', 'shubhanklall@gmail.com', '$2y$10$qP9TxRkTxBpkGZrbwwu.memPE24x/SSmigpWBDHcMQMRmTjUhFwEK', '9405316808', 'Male', '1994-05-01', 'S/O', 'Balakdas gajbhiye', '', 'Active Member', '', '450631782862', 'Maharashtra', 'Nagpur', 'plot no 17/21 dr ambedkar marg near kamal chwk l', '440017', 'active', '68f8d69ed5645.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00120', 'approved', '2026-07-25', 'member', 1, '2025-10-22 13:05:34', '', NULL, NULL, '2025-07-26'),
(123, 'Gh. Qadir Beidar', 'ghqadirbeidar@gmail.com', '$2y$10$li8A9sBW3D26BXyxFjDbKekM3VMsiN0NHnVG2jW4v2jI.sbI4FR4u', '7006017313', 'Male', '1963-03-20', 'S/O', 'GH AHMAD BHAT', '', 'MEMBER', '', '257964435958', 'Jammu and Kashmir', 'Budgam', 'ALAMDAAR CLONY CHAHRI SHARIEF', '191112', 'active', '68f9c0b8a590c.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00121', 'approved', '2026-05-24', 'member', 1, '2025-10-23 05:44:24', '', NULL, NULL, '2025-05-25'),
(124, 'SAYYED MOHD ANEES', 'sayyedmohoanees@gmail.com', '$2y$10$lVsY3mOZx4ZNuSKgZPMNrebp9vFmPT14mZXj2ut4G2V14x1qB6goa', '9588965224', 'Male', '1963-06-19', 'S/O', 'Gaya Prasad', '', 'MEMBER', '', '450631782862', 'Uttar Pradesh', 'Etawah', 'no 217 katra purdal khan', '206001', 'active', '68f9c27ef34b5.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00122', 'approved', '2026-06-21', 'member', 1, '2025-10-23 05:51:58', '', NULL, NULL, '2025-06-22'),
(125, 'C.Anthoniraj', 'canthoniraj@gmail.com', '$2y$10$H7.xdKURqGgYrzgX3hXO9u8.wvsLSP/VEJj07DvfPdpNUmDrhHliy', '9944758003', 'Male', '1992-02-16', 'S/O', 'Gaya Prasad', '', 'MEMBER', '', '450631782862', 'Tamil Nadu', 'Tiruvannamalai', '.39 East street \r\n    Allikondapattu Village', '606811', 'active', '68f9c682b2582.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00123', 'approved', '2026-08-05', 'member', 1, '2025-10-23 06:09:06', '', NULL, NULL, '2025-08-06'),
(126, 'BHAVIK NARENDRA SHAH', 'bhaviknarendrashah@gmail.com', '$2y$10$Cnq.h.MCTj915VRV5wB63O.rk/fL8gTjA.tP74sQavI5hMQaHDKAG', '9004504066', 'Male', '1997-08-14', 'S/O', 'SANWALA', '', 'MEMBER', '', '752601272575', 'Maharashtra', 'Mumbai City', 'cottage prabhat colony', '400055', 'active', '68f9ca9a3d46a.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00124', 'approved', '2026-08-14', 'member', 1, '2025-10-23 06:26:34', '', NULL, NULL, '2025-08-15'),
(127, 'BLESSON  GEORGE KURIAN', 'blessongeorgekurian@gmail.com', '$2y$10$Sb0KfrlZzTDai0/vAQcQiep8WGcOsGBG39iLi0DBnDK2U3BYtRYJK', '8589854389', 'Male', '1999-10-27', 'S/O', 'BOBBY G KURIAN', '', 'MEMBER', '', '344644488071', 'Kerala', 'Kottayam', 'perumpuzhakunnel manarcad', '686019', 'active', '68f9d5646d68e.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00125', 'approved', '2026-08-14', 'member', 1, '2025-10-23 07:12:36', '', NULL, NULL, '2025-08-15'),
(128, 'SIKILAMETLA ARUN KUMAR', 'sikilametlaarunkumar@gmail.com', '$2y$10$wC442fU3BpqpOnu2YoCsbuuK801pehf.PWlslw4r6B.kKYoIJ2SDW', '9154103239', 'Male', '1998-12-23', 'S/O', 'SANWALA', '', 'MEMBER', '', '257964435958', 'Telangana', 'Nalgonda', '19/50/A ayyappa nagar mandal devarakonda', '508248', 'active', '68f9d87b3ee0c.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00126', 'approved', '2026-08-30', 'member', 1, '2025-10-23 07:25:47', '', NULL, NULL, '2025-08-31'),
(129, 'K S DATTATRI', 'ksdattatri@gmail.com', '$2y$10$a5EHxzAfYJAwWC.GMpsQH.U9C7FLEZCl2izarJsSjO9flFqT.xOge', '5214564186', 'Male', '1990-03-06', 'S/O', 'SANWALA', '', 'MEMBER', '', '450631782862', 'Karnataka', 'Bengaluru Urban', '124/3 janani surveyor street', '560004', 'active', '68f9de661519e.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00127', 'approved', '2026-08-07', 'member', 1, '2025-10-23 07:51:02', '', NULL, NULL, '2025-08-08'),
(130, 'MR  SHYAM DIWAN', 'shyamdiwan@gmail.com', '$2y$10$DwskdXLQtNoNTIAq5z3APeNRsV2Oc9Xw/vSyvBatisTW8Le/nNT8m', '8770700178', 'Male', '1990-02-18', 'S/O', 'Gaya Prasad', '', 'MEMBER', '', '257964435958', 'Chhattisgarh', 'Bastar', 'vill pakhnakongera bhanpur', '494224', 'active', '68f9e0bb12b38.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00128', 'approved', '2026-08-07', 'member', 1, '2025-10-23 08:00:59', '', NULL, NULL, '2025-08-08'),
(131, 'ABDUL ATIQUE  ABDUL RAFIQUE SHEIKH', 'abdulatiqueabdulsheikh@gmail.com', '$2y$10$oaFr2R8qrdBTv7dYIUE4zOFN9DI72WcPhRQg2blw6dCpEbLtCKFCa', '8390383806', 'Male', '1990-06-19', 'S/O', 'SANWALA', '', 'MEMBER', '', '344644488071', 'Maharashtra', 'Yavatmal', 'PANDHARASHTRA', '445302', 'active', '68f9f2e3c75dd.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00129', 'approved', '0000-00-00', 'member', 1, '2025-10-23 09:18:27', '', NULL, NULL, '0000-00-00'),
(132, 'MUSHTAQ MUHSIN', 'mushtaqmuhsin@gmail.com', '$2y$10$gwCw8KTA.MjWhsg/2Y8RseLMFsBdk.RXWoySekuqfhJXyhcW.KC7m', '7768835598', 'Male', '1990-09-18', 'S/O', 'SANWALA', '', 'MEMBER', '', '992874226235', 'Maharashtra', 'Aurangabad', 'super furniture opp nutan colony mehri takiya', '431001', 'active', '68fa0484b1796.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00130', 'approved', '2026-07-08', 'member', 1, '2025-10-23 10:33:40', '', NULL, NULL, '2025-07-09'),
(133, 'Sunil  Bobal', 'sunilbobal@gmail.com', '$2y$10$63JiL2fm7L1vsWk0OkbatOc6f8cRqoOaCX8pWCimbLSlE9.1UgfiK', '8708099912', 'Male', '1990-02-06', 'S/O', 'SANWALA', '', 'MEMBER', '', '257964435958', 'Haryana', 'Karnal', 'h no 409 warde no 2 gram budha', '132001', 'active', '68fa059aad56b.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00131', 'approved', '2026-09-03', 'member', 1, '2025-10-23 10:38:18', '', NULL, NULL, '2025-09-04'),
(134, 'NANDA KISHORE JHA', 'nandakishorejha@gmail.com', '$2y$10$.O7E/lAL.79kjirEWA4RWOWXnJCwGXdmpYN0CzaSQ86ATfSRuTTtC', '9810727498', 'Male', '1964-11-15', 'S/O', 'SURYA KANT JHA', '', 'MEMBER', '', '450631782862', 'Uttar Pradesh', 'Ghaziabad', 'M 199B SECTOR 23 SANJAY NAGAR', '201002', 'active', '68fa06d56cdb3.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00132', 'approved', '2026-08-15', 'member', 1, '2025-10-23 10:43:33', '', NULL, NULL, '2025-08-16'),
(135, 'KRASHN KUMAR', 'krashankumar@gmail.com', '$2y$10$Mjnbk5vrPEw8dEpX7wTMGujYUrGGnj4UkwYR.TCnRtdwjj9nsEESK', '9174098780', 'Male', '2000-06-19', 'S/O', 'SANWALA', '', 'MEMBER', '', '450631782862', 'Madhya Pradesh', 'Dindori', 'house no 65 ward no 04 post khudiya vill khargahana', '481882', 'active', '68fa08f9a8f0e.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00133', 'approved', '2026-08-14', 'member', 1, '2025-10-23 10:52:41', '', NULL, NULL, '2025-08-15'),
(136, 'SHAMKANT EKNATH BHAWARIYA', 'shamkanteknathbhawariya@gmail.com', '$2y$10$EAlXL3QrWFN7XYzkx19lreMOSIPfadmtmo6DQ7xBfcOOFw23dDhfu', '7276505088', 'Male', '1990-06-20', 'S/O', 'Gaya Prasad', '', 'MEMBER', '', '450631782862', 'Maharashtra', 'Pune', 'a/p alandi dewachi omkar niwas sn 20/2 plot no 13 tal', '412105', 'active', '68fa0a851ccd7.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00134', 'approved', '2026-09-14', 'member', 1, '2025-10-23 10:59:17', '', NULL, NULL, '2025-09-15'),
(137, 'RIZWAN AHMEA DAV', 'rizwanahmeadav@gmail.com', '$2y$10$qJpZmAFbTg7e3Xt0mjEXY.U02jBRjjwOwjMVB2mbshDf2BAjqj/CK', '9998148196', 'Male', '1990-07-19', 'S/O', 'SANWALA', '', 'MEMBER', '', '257964435958', 'Gujarat', 'Panchmahal', 'near darussalam masjid godhara panchmahal', '389001', 'active', '68fa0de2cd5b7.jpg', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00135', 'approved', '2026-08-14', 'member', 1, '2025-10-23 11:13:38', '', NULL, NULL, '2025-08-15'),
(138, 'S RAGURAMAN', 'sraguraman@gmail.com', '$2y$10$qJWojzNbMEGC.sl9ACk6T.QEWq03WhVlc.Jd.yX..cCPreOXsiINS', '9080719919', 'Male', '1992-05-18', 'S/O', 'SANWALA', '', 'MEMBER', '', '752601272575', 'Tamil Nadu', 'Erode', '58 mkm street varanapuram bhavani taluk', '638301', 'active', '68fa1044b144e.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00136', 'approved', '2026-09-01', 'member', 1, '2025-10-23 11:23:48', '', NULL, NULL, '2025-09-02'),
(139, 'Naveen Kumar Nimbhore', 'naveenkumarnimbhore@gmail.com', '$2y$10$6XITMtb9kN3GNE4I4AMz5u14En5exxK/UBbnPc0jU9W4Hw/BFsJn2', '9753869997', 'Male', '1991-06-19', 'S/O', 'SANWALA', '', 'MEMBER', '', '450631782862', 'Madhya Pradesh', 'Burhanpur', 'lb 32 new indira colony', '450331', 'active', NULL, NULL, NULL, '', NULL, NULL, '', 'PCWWF/00137', 'approved', '2026-09-01', 'member', 1, '2025-10-23 11:30:08', '', NULL, NULL, '2025-09-02'),
(140, 'ASHA JAIN', 'ashajain@gmail.com', '$2y$10$134nvjwYBt/FPrI/cUzWu.eOgC/dWNo..lzkHCOeTbFUUDJwWShQa', '9079829211', 'Male', '1991-06-19', 'S/O', 'Deepak Premchandani', '', 'Women Head (State)', '', '257964435958', 'Rajasthan', 'Bhilwara', 'hukmi chand khand plot 3665 ma narbda', '311001', 'state', '68fa133cafe1c.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00138', 'approved', '2026-09-01', 'member', 1, '2025-10-23 11:36:28', '', NULL, NULL, '2025-09-01'),
(141, 'BILAL AHMED MIR', 'bilalahmedmir@gmail.com', '$2y$10$eyqsIS628.0r9s6jkln0Wu4nH1LdX2II75YNI7KwW0tmnxXyggG96', '7006338116', 'Male', '1990-06-19', 'S/O', 'SANWALA', '', 'MEMBER', '', '450631782862', 'Jammu and Kashmir', 'Srinagar', 'kathidareaze rainwari', '190003', 'active', '68fa14d34666d.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00139', 'approved', '2026-09-01', 'member', 1, '2025-10-23 11:43:15', '', NULL, NULL, '2025-09-01'),
(142, 'SANJAY KUMAR SHAH M', 'sanjaykumarshahm@gmail.com', '$2y$10$dkAwpFRCJF3U2PqsE26r5OIOAq5srPLuMZ5VjXx1CElkw62JysNjS', '9884017935', 'Male', '1009-06-19', 'S/O', 'SANWALA', '', 'MEMBER', '', '257964435958', 'Tamil Nadu', 'Kanchipuram', '5056 srimushnam flats a-3 1st floor ramnagar 5th street', '600091', 'active', '68fa16cc02c1b.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00140', 'approved', '2026-09-01', 'member', 1, '2025-10-23 11:51:40', '', NULL, NULL, '2025-09-01'),
(143, 'DIRAVATH SAI KIRAN NAIK', 'diravathsaikirannaik@gmail.com', '$2y$10$ie0LiiUqeWJ04GSIBYqGOufn9FKjgKnACIFtrw6SAEvRW3SmcHZxa', '8790225413', 'Male', '1990-07-19', 'S/O', 'Gaya Prasad', '', 'MEMBER', '', '752601272575', 'Telangana', 'Hyderabad', '11/51/33 s t colony kapra new banjara colony  commity hall ranga reddy', '500062', 'active', '68fa19ee3cad6.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00141', 'approved', '2026-08-14', 'member', 1, '2025-10-23 12:05:02', '', NULL, NULL, '2025-08-15'),
(144, 'MOHAMMAD ASARAF', 'mohammadasaraf@gmail.com', '$2y$10$tBXZRv5B06Zanld4bI1UaeECNbZOZrk1oxliV5SZlFQmkiwbyTLFm', '9413878797', 'Male', '1990-07-19', 'S/O', 'SANWALA', '', 'MEMBER', '', '450631782862', 'Rajasthan', 'Bundi', 'near ct square hotel noorani mohalla', '344022', 'active', '68fa1dad7d01a.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00142', 'approved', '2026-08-14', 'member', 1, '2025-10-23 12:21:01', '', NULL, NULL, '2025-08-15'),
(145, 'JITENDRA  JARIWALA', 'jitendrajariwala@gmail.com', '$2y$10$fq0pmA1U6dsc1lvx0UBtt.yooTbweVvZA4B6gEGHuTCy.inpoUuUK', '9825469070', 'Male', '1990-10-20', 'S/O', 'PRANLAL  PRAVINCHANDRA', '', 'MEMBER', '', '450631782862', 'Gujarat', 'Surat', '2/1475 hanuman street sagampura', '395002', 'active', '68fa24ff07fc6.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00143', 'approved', '2026-08-14', 'member', 1, '2025-10-23 12:52:15', '', NULL, NULL, '2025-08-15'),
(146, 'Ishtiaq Ahmed', 'ishtiaqahmed@gmail.com', '$2y$10$5wMop6Qu/0dPqC8YaGVqNOG/Irvb.PTUUbc8uwTXcksdiEEnKkuSW', '9739988384', 'Male', '1980-02-09', 'S/O', 'Mohmed Iqbal', '', 'MEMBER', '', '257964435958', 'Karnataka', 'Bengaluru Urban', '27/1 nethaji road cross frazer lown', '560005', 'active', '68fb0991c8709.jpg', NULL, NULL, '1254894569', NULL, NULL, 'offline', 'PCWWF/00144', 'approved', '2026-09-04', 'member', 1, '2025-10-24 05:07:29', '', NULL, NULL, '2025-09-05'),
(147, 'Gokul Gopalakrishnan', 'gokulgopalakrishnan@gmail.com', '$2y$10$A8k5Wi0rBsPzNi7amZLtPux6VjIdZLKhEkVB9dl3OIMKRM8FaPXGa', '8144044563', 'Male', '1994-09-06', 'S/O', 'Gopalakrishna', '', 'MEMBER', '', '257964435958', 'Kerala', 'Alappuzha', 'Menamoottil, house\r\nNeerattupuram, po,\r\nNeerattupuram,', '689571', 'active', NULL, NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00145', 'approved', '2026-07-17', 'member', 1, '2025-10-24 05:15:18', '', NULL, NULL, '2025-07-18'),
(148, 'KOUSHIK SARKAR.', 'dr.koushik@outlook.com', '$2y$10$0AplUh3o1mOY.EW8C9bWquQ4XOKMaUMspFzgWXFDHsaVQCKsT4UpG', '9434158863', 'Male', '1979-10-06', 'S/O', 'SANWALA', '', 'MEMBER', '', '450631782862', 'West Bengal', 'Nadia', '50.R.N.TAGORE ROAD(KADAMTAL) Krishnanagar, Nadia.', '741101', 'active', '68fb0dd5bf43c.jpg', NULL, NULL, '1254894569', NULL, NULL, 'offline', 'PCWWF/00146', 'approved', '0000-00-00', 'member', 1, '2025-10-24 05:25:41', '', NULL, NULL, '0000-00-00'),
(149, 'RAVI SHANKAR VYAS', 'ravishankarvyas@gmail.com', '$2y$10$6bg66HZfiG9reMQR9e4L6O0LjlnSbiksGkm2PaJikn03AR8UpmlQi', '7357362804', 'Male', '1990-06-18', 'S/O', 'SANWALA', '', 'MEMBER', '', '450631782862', 'Rajasthan', 'Bhilwara', 'rajes sykil amar pelece road ganghinagar', '311001', 'active', '68fb1c81c431a.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00147', 'approved', '2026-09-01', 'member', 1, '2025-10-24 06:28:17', '', NULL, NULL, '2025-09-01'),
(150, 'ABHIJIT MUKHERJEE', 'abhijitmukerjee@gmail.com', '$2y$10$hhWHp2rANZl9c5xH6XSYxOYnfwSydA0gvqlW/h64WF4BBwlw7DkL2', '8653146444', 'Male', '1990-06-18', 'S/O', 'SANWALA', '', 'MEMBER', '', '752601272575', 'West Bengal', 'Paschim Medinipur', 'RAGUNATH CHAWAK SRIPALLY 4B/4 CHELIDANGA ASANSOL SOUTH PASCHIM', '713304', 'active', '68fb1fe009cf4.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00148', 'approved', '2026-08-23', 'member', 1, '2025-10-24 06:42:40', '', NULL, NULL, '2025-08-24'),
(151, 'Mahesh M Demashetti', 'maheshmdemashetti@gmail.com', '$2y$10$gAahFM8lLmPqZqd0BJ1gJeUSZ9KLiH0Rbcz5WllbxF1SrNBR7otXy', '9916054391', 'Male', '1990-05-10', 'S/O', 'SANWALA', '', 'MEMBER', '', '257964435958', 'Karnataka', 'Bagalkote', 'NEAR TAHASILDER', '587313', 'active', NULL, NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00149', 'approved', '2026-08-28', 'member', 1, '2025-10-24 06:48:25', '', NULL, NULL, '2025-08-29'),
(152, 'HALEWADIMATH PRABHAYYA', 'halewadimathprabhayya@gmail.com', '$2y$10$kZgPYnqeIJEWB/a6Hxzp8.NvC5AJJUo0luhkIh9K1VgMGoZ7vV0Ru', '9886573108', 'Male', '1990-02-10', 'S/O', 'SANWALA', '', 'MEMBER', '', '450631782862', 'Karnataka', 'Haveri', 'HOLABASAVESHWAR NAGAR HAUNSBHAVI', '581109', 'active', '68fb22460611e.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00150', 'approved', '2026-08-28', 'member', 1, '2025-10-24 06:52:54', '', NULL, NULL, '2025-08-29'),
(153, 'SHUBHANKAR GHOSH', 'rumashubhankar@gmail.com', '$2y$10$ycmgf73M7LNT1Eqk5.euHeQ4OIXCSUcpI4qhx7fTEtVnKkVyESHAi', '9474548535', 'Male', '1968-01-12', 'S/O', 'SANWALA', '', 'MEMBER', '', '211220972470', 'West Bengal', 'Birbhum', 'Bolpur', '731204', 'active', '68fb2aa53d45f.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00151', 'approved', '2026-10-23', 'member', 1, '2025-10-24 07:28:37', '', NULL, NULL, '2025-10-24'),
(154, 'VINAYAK CHITLANGI', 'vinayakchitlangi@gmail.com', '$2y$10$DDPXidaiRVi.v6.eSFubMOZRZuxmH6vanKySMQUEWY6Jjbshw9XRi', '7297078101', 'Male', '1983-12-01', 'S/O', 'SANWALA', '', 'MEMBER', '', '450631782862', 'Rajasthan', 'Bikaner', 'TEHSIL ROAD NOKHA', '334803', 'active', '68fb31f43248e.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00152', 'approved', '2026-10-23', 'member', 1, '2025-10-24 07:59:48', '', NULL, NULL, '2025-10-24'),
(155, 'SANTOSH KUMAR SAHU', 'santoshkumarsahu@gmail.com', '$2y$10$OBm/YHVml/MnHVbvG1V1gOseWBvVopZD2VSO.7tyVEcS0U3eMfd3u', '9030571314', 'Male', '1986-07-28', 'S/O', 'SAHU GANESH KUMAR', '', 'MEMBER', '', '257964435958', 'Andhra Pradesh', 'Srikakulam', '3 -186 M THOTURU M MANDAPALLI ICHCHAPURAM', '532312', 'active', '68fc56b6db408.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00153', 'approved', '2026-10-24', 'member', 1, '2025-10-25 04:48:54', '', NULL, NULL, '2025-10-25'),
(156, 'Rishi S Patela', 'rishispatela@gmail.com', '$2y$10$mUtCNtvUlGabq9LnaXOqGuMJECn9DEfEm.KEgMJ9618k33LLHsEIu', '7349537169', 'Male', '1991-05-19', 'S/O', 'SANWALA', '', 'MEMBER', '', '257964435958', 'Karnataka', 'Hassan', 'Brain Academy,  \r\nCanara Bank Road\r\nKeralapura Ramanathapuram hobli, Arakalagudu taluk', '573136', 'active', '68fc77195fbcf.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00154', 'approved', '2026-10-24', 'member', 1, '2025-10-25 07:07:05', '', NULL, NULL, '2025-10-25'),
(157, 'GOPAL POPATBHAI PRAJAPATI', 'gopalprajapati979@gmail.com', '$2y$10$Mp2ifcZ87YAQDO1cPi2M.uaWFpVqSsT2rXpiECGrnduXg8Eg6L9IK', '9510777143', 'Male', '1993-05-24', 'S/O', 'POPATBHAI NATHABHAI PRAJAPATI', '', 'Health Head (State)', '', '257964435958', 'Gujarat', 'Ahmedabad', '293 RANCHHODRAYNAGAR 3 JAGATPUR ROAD CHANDLODIA', '382481', 'state', '68fcb9eb7ae9d.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00155', 'approved', '2026-10-24', 'member', 1, '2025-10-25 11:52:11', '', NULL, NULL, '2025-10-25'),
(158, 'KISHOR KUMAR SINHA', 'dr.kksinha66@gmail.com', '$2y$10$SfUJXr.c0uI0tQcqZuWrDOed8Rpog2t6orIFm8FynZRnpTuGahs5u', '9431560836', 'Male', '1966-01-10', 'S/O', 'RAJNANDAN PRASAD SINHA', '', 'MEMBER', '', '450631782862', 'Jharkhand', 'Garhwa', 'VILL BELCHAMPA POST BELCHAMPA PS GARHWA WARD NO 3 BELCHAMPA', '822124', 'active', '69004c807f9a8.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00156', 'approved', '2026-10-27', 'member', 1, '2025-10-28 04:54:24', '', NULL, NULL, '2025-10-28'),
(159, 'MIRZA ILIYASBAIG AMINBAIG', 'mirzailiyasbaigaminbaig@gmail.com', '$2y$10$wqeXzp.roQMAK2tdVKBSq.sqILyFQWBVKHfq7qroFFPlHU8qSlLai', '7420032767', 'Male', '2000-12-09', 'S/O', 'MIRZA AMINBAIG', '', 'MEMBER', '', '257964435958', 'Maharashtra', 'Jalna', 'GAIBISHAVALI NAGAR BADNAPUR', '431202', 'active', '69005570b1519.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00157', 'approved', '2026-10-27', 'member', 1, '2025-10-28 05:32:32', '', 'id_card_159_1761629566.png', '2025-10-28 05:32:46', '2025-10-28'),
(160, 'BINGI KALYAN GOUD', 'jkgoud369@gmail.com', '$2y$10$1/bzKxfwzMOXvm6tJ8W4oeesFr/qlUFSR1dUywe1j9DP8e4MyIhk.', '9963230942', 'Male', '1979-10-29', 'S/O', 'BINGI BIKSHAPATHI GOUD', '', 'MEMBER', '', '257964435958', 'Andhra Pradesh', 'Vizianagaram', '2-7 6/2 BHARATH NAGAR NEAR S H K HOSPITAL UPPAL', '500039', 'active', '6900631e4cde6.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00158', 'approved', '2026-10-27', 'member', 1, '2025-10-28 06:30:54', '', NULL, NULL, '2025-10-28'),
(161, 'SAKTHIVEL PERIYASAMY', 'Svel53800@gmail.com', '$2y$10$D./ZVtUUoBhztbnydbXgC.Ev9SrwwQ.f5/kNU273ss8Vi11wkxZFy', '9442660354', 'Male', '1979-03-12', 'S/O', 'PERIYASAMY', '', 'MEMBER', '', '257964435958', 'Tamil Nadu', 'Pudukkottai', 'Black no 33 Door no 1534\r\nTamil Nadu urban habitat development board narimedu', '622005', 'active', '69020272633d9.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00159', 'approved', '2026-10-28', 'member', 1, '2025-10-29 12:02:58', '', NULL, NULL, '2025-10-29'),
(162, 'DILIP KUMAR', 'dksiddharth563@gmail.com', '$2y$10$viMwV.Mp6Cqyl7iSHWNMKOj..B9TJVQidImgQ7q3XP9y2r83DdDXe', '9982678596', 'Male', '1995-06-19', 'S/O', 'SANWALA', '', 'MEMBER', '', '450631782862', 'Rajasthan', 'Sirohi', 'VPO Gol Teh. &amp;amp; Distt', '307801', 'active', '6902f2fd5cbf8.jpg', NULL, NULL, '1254894569', NULL, NULL, 'offline', 'PCWWF/00160', 'approved', '2026-10-29', 'member', 1, '2025-10-30 05:09:17', '', NULL, NULL, '2025-10-30'),
(163, 'SHAIK ABDULLA.', 'shaikabdullahfarhan@gmail.com', '$2y$10$a7f30of69L2BZHve5ZBrwO50JR/8N9CGWI7k2mNu/qs5mKIDkr6gm', '9448666664', 'Male', '1987-03-17', 'S/O', 'SHAIK BAHADUR.', '', 'MEMBER', 'A+', '535516746870', 'Karnataka', 'Bengaluru Urban', 'NO.501.RAHMANJI.GUEST HOUSE,5TH CROSS MANJUNATH NAGAR KALKERE EXTENSION, ANDHRA LAYOUT. HOURSMAVU', '560043', 'active', '6902f4bd61f0d.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00161', 'approved', '2026-10-29', 'member', 1, '2025-10-30 05:16:45', '', NULL, NULL, '2025-10-30'),
(164, 'PANDITHURAI S', 'pandithuraitpm@gmail.com', '$2y$10$iiI3MrHQYAOD2oO/JMbOU.0fslH8hom2JndNomtY1BW.6gt0QXMSW', '9751847905', 'Male', '1984-05-28', 'S/O', 'SANWALA', '', 'State President', '', '450631782862', 'Tamil Nadu', 'Mayiladuthurai', '3/64 Padasalai street, Thennampattinam and post Sirkali Taluk', '609106', 'active', '69059632b5d45.jpg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/00162', 'approved', '2026-11-01', 'member', 1, '2025-11-01 05:10:10', '', NULL, NULL, '2025-11-01'),
(165, 'Dr.k. Loganathan', 'loganathan20a@gmail.com', '$2y$10$ZSrDVoza8skegtWWtx9FK.FO9kA79GoB0NJVxcUUE3unfWQGR61Ui', '9884037766', 'Male', '1971-12-01', 'S/O', 'D.k', '', 'MEMBER', 'B+', '304975269727', 'Tamil Nadu', 'Chennai', 'kolathur,tamilnadu,chennai,600099', '600099', 'active', '69085c2bac8e9.jpeg', '69085c2bacd95.jfif', NULL, '', NULL, NULL, '', 'PCWWF/00163', 'approved', '2026-11-03', 'member', 1, '2025-11-03 07:39:23', '', 'id_card_165_1762171076.png', '2025-11-03 11:57:56', '2025-11-03'),
(166, 'Gautam kumar', 'advocatebhaiyajee@gmail.com', '$2y$10$PZMsUhoKy7RyRxLMbhU5Huu6HQx7e7lzO7C4mf7vO3IRUmnRuWIBC', '7366966662', 'Male', '1966-03-21', 'S/O', 'Gopal kumar prasad', '', 'MEMBER', 'B+', '435313663562', 'Bihar', 'Khagaria', 'thana chowk,near gopal jewellers,khagaria,bihar,851204', '851204', 'active', '6908669dc33ef.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00164', 'approved', '2026-11-03', 'member', 1, '2025-11-03 08:23:57', '', 'id_card_166_1762159116.png', '2025-11-03 08:38:36', '2025-11-03'),
(167, 'jayabalaji T', 'jayabalaji1983@gmail.com', '$2y$10$Kz3pqN5I.cs9PDna1NTEEekyudeAFZ3qh0m40Z0Vc.NlakutDXF/.', '8925006975', 'Male', '1983-03-01', 'S/O', 'jaya', '', 'MEMBER', '', '455880811381', 'Tamil Nadu', 'Kanchipuram', 'thiruvengadam,3/114,main road,thriupulivanam,tamilnadu,603406', '603406', 'active', '690884c71c01a.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00165', 'approved', '2026-11-03', 'member', 1, '2025-11-03 10:32:39', '', 'id_card_167_1762166674.png', '2025-11-03 10:44:34', '2025-11-03'),
(168, 'Tadepu veera venkata naga bhavani sankar', 'shankar969696@gmail.com', '$2y$10$b5LMPdzQZ0HjYciz9KPECePNhqycFx/Y.nFOCVG7UYsd38214r78u', '9493801007', 'Male', '1993-04-26', 'S/O', 'Ravi babu', '', 'MEMBER', 'A+', '448305395479', 'Andhra Pradesh', 'Krishna', '5-143,jupudi sitharamyya bazar lakshmi takis center,vuyyuru,krishna', '521165', 'active', '69088eb0e7ec9.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00166', 'approved', '2026-11-03', 'member', 1, '2025-11-03 11:14:56', '', NULL, NULL, '2025-11-03'),
(170, 'P valsalan', 'valsanpanoil@gmail.com', '$2y$10$13/CCVXeUMjEdPgXZJyxCeh5tMj6hxEWqRjC9IK4HrFcwBw8HqN7e', '9645990501', 'Male', '1954-10-30', 'S/O', 'kanari', '', 'MEMBER', '', '273769721167', 'Kerala', 'Kannur', 'pandyala,kelalur,pinarayi,po-pinarayi,s.o-pinarayi,kannur,kerala,670741', '670741', 'active', '690b2dd57588a.jfif', '690b326a9591f.jfif', NULL, '', NULL, NULL, '', 'PCWWF/00167', 'approved', '2026-11-05', 'member', 1, '2025-11-05 10:27:56', '', 'id_card_170_1762339232.png', '2025-11-05 10:40:32', '2025-11-05'),
(171, 'A.MAJEED KHOSHI', 'TWYCROSS.IN@GMAIL.COM', '$2y$10$/14y025Fogk1QLv/LgXCQ.SAzCj7v3jCC.5d5QDQjWrzyDF2M2aJ.', '7736424142', 'Male', '1970-05-23', 'S/O', 'A.MAJEED KHOSHI', '', 'MEMBER', 'A+', '437811368704', 'Kerala', 'Ernakulam', 'THACHAVALLATH,PALLIKAVALA JUNCTION,THAIKKATTUKARA P O,ALUVA,ERNAKULAM,KERALA-683106', '683106', 'active', '690da10f528d7.jpg', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00168', 'approved', '2026-11-07', 'member', 1, '2025-11-07 07:34:39', '', NULL, NULL, '2025-11-07'),
(172, 'KISHAN RAI CHOUDHARY', 'panditji1556o@gmail.com', '$2y$10$r8HIskRU5nLxGKE4YW7MXerG/QxAmciQ4tjj30sp.kpdyoGVNorW2', '9810949523', 'Male', '1985-01-01', 'S/O', 'PURANJAN RAI CHOUDHARY', '', 'MEMBER', 'O+', '910485199986', 'Delhi', 'North West Delhi', 'B-349 Wazirpur industrial area shahid sukhdev nagar delhi,110052', '110052', 'active', '690dc093c10c0.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00169', 'approved', '2026-11-07', 'member', 1, '2025-11-07 09:49:07', '', NULL, NULL, '2025-11-07'),
(173, 'Ankur Bansal', 'ankbansal2016@gmail.com', '$2y$10$3LECOpkb1DqCJhi6nNkNiuBt.DkG/Zqazdpgi1er8thRZYAt75YUK', '8860170385', 'Male', '1985-11-06', 'S/O', 'Kali charan bansal', '', 'MEMBER', 'AB+', '414389125285', 'Delhi', 'North West Delhi', 'c-6/178 block C-6 keshav puram, vtc.:keshav puram,p.o-onkar nagar,sub dist-saraswati vihar,dist-north west delhi,state-delhi,110035', '110053', 'active', '690dce268082d.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/00170', 'approved', '2026-11-07', 'member', 1, '2025-11-07 10:47:02', '', NULL, NULL, '2025-11-07'),
(174, 'Md.Siraj Anwar', 'sirajanwar78692@gmail.com', '$2y$10$xXcpo1CVIMvN7FgotQKqgehvm4WdLAVMU41kGjOVgBFkXv6VY2ztm', '8827452000', 'Male', '1981-07-31', 'S/O', 'Md.Munir Ahmed', 'Self Business', 'MEMBER', 'O+', '299419539921', 'Chhattisgarh', 'Korba', 'Nicco Electricals Power Tools\r\nPlot no 17,T.P.Nagar\r\nKorba (Chhattisgarh)\r\nPin - 495677', '495677', 'active', '690f2a59aa481.jpeg', '690efd81c5768.jpg', '690efd81c5913.jpg', '', NULL, '690f2a59aa7ae.jpeg', 'offline', 'PCWWF/24118', 'approved', '0000-00-00', 'member', NULL, '2025-11-08 08:21:21', 'Korba (Chhattisgarh)', NULL, NULL, '2025-11-08'),
(175, 'B PAVAN KUMAR', 'pavank0105@gmail.com', '$2y$10$VVv7wVEEh9pATvZnGZcd7.FR3lDCm1BX2o9EOX1.HqmCmvBJIN3Ie', '9701821981', 'Male', '1982-08-02', 'S/O', 'B sriramulu', 'Self Business', 'Press Member', 'O+', '415099451942', 'Telangana', 'Hanamkonda', '2-8-413 RAGHAVENDRA NAGAR NGOS COLONY HANAMKONDA', '506001', 'active', '690f3d5174fa1.jpg', '690f3d517578c.jpg', '690f3d5175b20.jpg', NULL, NULL, '690f3d517621b.jpg', 'offline', 'PCWWF/70331', 'approved', NULL, 'member', NULL, '2025-11-08 12:53:37', 'Hanamkonda', NULL, NULL, '2025-11-08'),
(176, 'Nandiraju Raja Hanumantha Rao', 'raja.hanumantharao@gmail.com', '$2y$10$L0lWr/Eak/o97ShAdptmK.EwODcTyNZNfBwYBc0pVqjzy9cjslb1q', '9290842472', 'Male', '1985-07-22', 'S/O', 'Nandiraju Sri Hari Rao', 'Self Business', 'Press Member', 'O+', '349928037244', 'Andhra Pradesh', 'NTR', 'D.No.18-18, Kakulaiah Street,NTR District,AndhraPradesh. Pin Code 521185.', '521185', 'active', '690f7b4d1044c.jpg', '690f7b4d1063c.jpg', '690f7b4d1075d.jpg', NULL, NULL, '690f7b4d10971.jpg', 'offline', 'PCWWF/50952', 'approved', NULL, 'member', NULL, '2025-11-08 17:18:05', 'NTR District, Nandigama', 'id_card_176_1763052557.png', '2025-11-13 16:49:17', '2025-11-08'),
(177, 'Buddesab S S', 'aairabsp@gmail.com', '$2y$10$KiBbLQ.L7AgCo1DOdIh.X.HDWaGcLxKYCjTo77MeHOKWx33.DZM9C', '8970688749', 'Male', '1988-07-05', 'S/O', 'sopi sab', '', 'MEMBER', 'B+', '441466131054', 'Karnataka', 'Ballari', '74,Thirumala,kotturu road,Hagaribommanahalli, vijayanagar ,583212', '583212', 'active', '69116e79a6204.jpg', NULL, NULL, '', NULL, NULL, '', 'PCWWF/50953', 'approved', '2026-11-10', 'member', 1, '2025-11-10 04:47:53', '', NULL, NULL, '2025-11-10'),
(178, 'Rohan Lall Chowdhury', 'rohanchamp@gmail.com', '$2y$10$LguYy9NeQe4eJvIoAN0KO.HmUKA4nSVojvXcHMd3.5anRS0URQCv6', '9339820083', 'Male', '1980-03-28', 'S/O', 'Makhan Lall Chowdhury', 'Self Business', 'Press Member', 'O-', '284916264857', 'West Bengal', 'Kolkata', '12A Rangalal Street, Kidderpore', '700023', 'active', '6911d6398dcff.jpg', '6911d6398e10e.jpg', '6911d6398e33c.jpg', '', NULL, '6911d6398e819.jpg', 'offline', 'PCWWF/62854', 'approved', '0000-00-00', 'member', NULL, '2025-11-10 12:10:33', 'Kolkata', NULL, NULL, '2025-11-10'),
(179, 'SEKHARESH PAL', 'dipupal80018@gmail.com', '$2y$10$jm0X.5112ELw0UucayrPguo6Yy6zJsoC/XJ5.p9TM4MqLv8.A0K7O', '9153303340', 'Male', '1984-05-12', 'S/O', 'BIJOY KUMAR PAL', 'Private Job', 'MEMBER', 'O+', '575626600978', 'West Bengal', 'Birbhum', 'Krishnapur.Churor.Birbhum.731133.West Bangal', '731133', 'active', '6911e9f2a3b9c.jpg', '6911e9f2a3f9e.jpg', '6911e9f2a4aac.jpg', '', NULL, '6911e9f2a53b1.png', 'offline', 'PCWWF/17549', 'approved', '0000-00-00', 'member', NULL, '2025-11-10 13:34:42', 'WEST BENGAL.BIRBHUM.', NULL, NULL, '2025-11-10'),
(180, 'Ashuru Poul', 'ashuhriipaul@gmail.com', '$2y$10$DJRoiwupU31fbjUz14SAAeJiATjxItnFxaedrcSQWTo9uYVlvaRVK', '8119820174', 'Male', '1998-11-10', 'S/O', 'Kaikho Thomas', 'Private Job', 'Press Member', 'B+', '645643896147', 'Manipur', 'Senapati', 'Pudunamei village', '795150', 'active', '6911f3d3c78f6.jpg', '6911f3d3c7d7b.jpg', '6911f3d3c9187.jpg', NULL, NULL, '6911f3d3cae1c.png', 'offline', 'PCWWF/24635', 'approved', NULL, 'member', NULL, '2025-11-10 14:16:51', 'Medziphema, Chümokedima Nagaland', 'id_card_180_1775317424.png', '2026-04-04 15:43:44', '2025-11-10'),
(181, 'SHAMSHAD BEGAUM', 'shashbbegum@gmail.com', '$2y$10$r5GWNlvyFsd218pnNOh3duEV9BDvV2vbZfxsZeNAc6Z14.B9CkyjK', '9880841507', 'Female', '1986-06-01', 'W/O', 'K Dadapeer', 'Private Job', 'Press Member', '', '778267181363', 'Karnataka', 'Ballari', '1-100, krishnadevaray road, urdu shale, arali halli, \r\nVTC: hagaribommanahalli\r\nPO: hagaribommanahalli\r\nSub District: hagaribommanahalli\r\nDistrict: Bellary,\r\nState: Karnataka\r\nPIN- 583212', '583212', 'active', '691586c6a1761.jfif', '691586c6a1c11.jfif', NULL, '', NULL, NULL, '', 'PCWWF/24636', 'approved', '2026-11-15', 'member', 1, '2025-11-13 07:20:38', '', NULL, NULL, '2025-11-15'),
(182, 'Siraj Anwar', 'sirajanwarR78692@gmail.com', '$2y$10$LVAPa2Yd8VdUdxiFxx6FmeBQeP5NAJhGecowUQESzfXUJm9oi2Zk.', '7587404571', 'Male', '1981-07-31', 'S/O', 'munir ahamed', 'Private Job', 'MEMBER', '', '299419539921', 'Chhattisgarh', 'Korba', 'Nicco Electricals Power Tools  Plot no 17, T.P.Nagar  Korba (Chhattisgarh) Near Indian coffee house', '495677', 'active', '6915be5c91294.jfif', '6915be5c91757.jfif', '6915be5c918e7.jfif', '', NULL, NULL, '', 'PCWWF/24637', 'approved', '2026-11-13', 'member', 1, '2025-11-13 11:17:48', '', NULL, NULL, '2025-11-13'),
(184, 'DR.Karthickeyan', 'akarthickeyan83@gmail.com', '$2y$10$N4M1s62CGERQZHSCLOpCc.i9Kxl8.8XFERNAQGT9s5g9usmVDYWq2', '9940787548', 'Male', '1983-03-29', 'S/O', 'P.Alagarsamy', 'Self Business', 'Press Member', 'B+', '419469202576', 'Tamil Nadu', 'Madurai', '152 V Surendren Nagar 6th Street Ponmeni', '625016', 'active', '6915f0cbe6b5e.jpg', '6915f0cbe6ea0.jpg', '6915f0cbe70fd.jpg', '', NULL, '6915f0cbe775e.jpg', 'offline', 'PCWWF/40781', 'approved', '0000-00-00', 'member', NULL, '2025-11-13 14:52:59', 'Madurai', NULL, NULL, '2025-11-13'),
(185, 'SAMBIT KUMAR BAGSINGH', 'sambitbagsingh670@gmail.com', '$2y$10$stMwAC6ZAIXHi8xeWvSImeZQrfioCxjc1Kc0eyFXdZvMNYNWa2Say', '6372724071', 'Male', '1999-06-20', 'S/O', 'GOKULA NANDA BEHERA', 'Student', 'MEMBER', 'O+', '763291159304', 'Odisha', 'Khordha', 'AT/PO-PODADIHA DIST - KHORDHA', '752061', 'active', '6916093849116.jpg', '69160938493f5.jpg', '6916093849799.jpg', NULL, NULL, '691609384ab14.jpg', 'offline', 'PCWWF/37583', 'approved', NULL, 'member', NULL, '2025-11-13 16:37:12', 'Odisha', 'id_card_185_1763100085.png', '2025-11-14 06:01:25', '2025-11-13'),
(186, 'Abdul Majid', 'abdul345@gmail.com', '$2y$10$58UW6m/csRE3SwNfflTg7.ijGbBE4DmT/VxBds89iKsGZUKdWwRti', '9210682927', 'Male', '1968-05-01', 'S/O', 'Abdul majid', 'Private Job', 'MEMBER', '', '388268905979', 'Delhi', 'West Delhi', '2866 frist floor kucha chlan, 8 darya ganj, central delhi, central delhi', '110002', 'active', '6916c31322f33.jfif', '6916c3132328e.jfif', '6916c37255f4b.jfif', '', NULL, NULL, '', 'PCWWF/37584', 'approved', '2026-11-14', 'member', 1, '2025-11-14 05:50:11', '', NULL, NULL, '2025-11-14'),
(187, 'K.S.PERIYASAMY', 'pressperiyasamyk@gmail.com', '$2y$10$K.9TobHbY3qOCAcg1rRsGuaxqg3wu6XF6ps8NJwy7pgsFLZBQxVFu', '9488473844', 'Male', '1970-02-10', 'S/O', '. K. SAMBASIVAM', '', 'MEMBER', '', '958440891710', 'Tamil Nadu', 'Kallakurichi', '137.C. V.O.C. NAGAR. 2nd.CROSS STREET. KALLAKURICHI. PO, TK. KALLAKURICHI. DIST. PIN. 606213.', '606213', 'active', '6916c7e08207a.jfif', '6916c7e082376.jfif', '6916c7e0824bd.jfif', '', NULL, NULL, '', 'PCWWF/37585', 'approved', '2026-11-14', 'member', 1, '2025-11-14 06:10:40', '', NULL, NULL, '2025-11-14'),
(188, 'Dr. Vijay sirohi', 'vijaysirohi23@gmail.com', '$2y$10$UAiZYYLOpa.8Xw1Gno8Tku9A6oew5FzYtZ7TVQGdeKIMbViacr5OG', '8168879094', 'Male', '1981-09-21', 'S/O', 'HARI KRISHAN SIROHI', '', 'Education Officer', '', '329661842571', 'Haryana', 'Kaithal', 'House number 519, behind anandpur ashram, ward number 4 siwan gate, kaithal-136027', '136027', 'district', '6916d1a6d5d2b.jfif', '6916d1a6d617d.jfif', '6916d1a6d62a8.jfif', '', NULL, NULL, '', 'PCWWF/37586', 'approved', '2026-09-14', 'member', 1, '2025-11-14 06:52:22', '', NULL, NULL, '2025-09-15'),
(189, 'Vinod Ghisulal Solanki', 'vinodsolanki265@gmail.com', '$2y$10$2MmSv0WOMs9Ozkc2AHTq5uDV9Q.N1Moy4rMpm6xTCSUUJ1Ez04Ok6', '9226410828', 'Male', '1959-09-17', 'S/O', 'D.k', 'Private Job', 'MEMBER', '', '839948745440', 'Punjab', 'Barnala', 'Bramha Courts flat no 13/401 Sahaney Sujan Park Lullanagr Pune 411040 Third Wave Coffee lane', '411040', 'active', '6916d35fc96f8.jfif', '6916d35fc996d.jfif', NULL, '', NULL, NULL, '', 'PCWWF/37587', 'approved', '2026-11-13', 'member', 1, '2025-11-14 06:59:43', '', NULL, NULL, '2025-11-14'),
(190, 'Gangula Raj Kumar', 'rajkumar345@gmail.com', '$2y$10$wtFAxOZKRF5H3yVM1lidYu8zu2PeKwv2nv2WIq7MRD7eo7hU7VwpO', '6281394960', 'Male', '1998-06-13', 'S/O', 'Sailu', 'Self Business', 'MEMBER', '', '273769721168', 'Telangana', 'Mancherial', '2-12 , venkatraopet, venkatraopet, luxettipet, mancherial, telangana', '504215', 'active', '6916d6e810449.jfif', '6916d6e810806.jfif', NULL, '', NULL, NULL, '', 'PCWWF/37588', 'approved', '2026-09-03', 'member', 1, '2025-11-14 07:14:48', '', NULL, NULL, '2025-09-04'),
(191, 'ABHIJEET JENA', 'abhijeet345@gmail.com', '$2y$10$FFL/nQr.Ih0TTP75vBTkAu57gEmN8T5UZD43.LBeXr69f27bTlTyW', '9339770983', 'Male', '1981-03-12', 'S/O', 'jena', '', 'MEMBER', '', '748219398933', 'West Bengal', 'Kolkata', '188/1A, picnic garden road, tiljala, tiljala S.O , kalkata, west bengal', '700093', 'active', '6916d98676c5b.jfif', '6916d98676edd.jfif', '6916d986770a2.jfif', '', NULL, NULL, '', 'PCWWF/37589', 'approved', '0000-00-00', 'member', 1, '2025-11-14 07:25:58', '', NULL, NULL, '0000-00-00'),
(192, 'KRISHNA KUMAR SINGARAPU', 'singarapukittuu@gmail.com', '$2y$10$g/I5EmaQ4xR34mcs1ST0FuSZv8o7s4tz3sSBV.I2FyYb8v9W9zuAq', '9445115673', 'Male', '1973-06-04', 'S/O', 'lakman singarapu', 'Private Job', 'MEMBER', '', '414646657071', 'Tamil Nadu', 'Chennai', '12 CHINNA THAMBI MUDALI STREET, SOWCARPET. CHENNAI -600001 MOBILE NUMBER', '600001', 'active', '6916dad7cbeda.jfif', '6916dad7cc13c.jfif', '6916dad7cc3a1.jfif', '', NULL, NULL, '', 'PCWWF/37590', 'approved', '2026-06-16', 'member', 1, '2025-11-14 07:31:35', '', NULL, NULL, '2025-06-17'),
(193, 'KHATEEB HAKEEM AKHEEL AHMED', 'wafaahmed0558@gmail.com', '$2y$10$/ip4lg1.W.uLkSsW5hbxr.hdqscirU/KXnki2/HKSMM.BY6YH8oZ.', '7200174878', 'Male', '1982-09-06', 'S/O', 'Ahmed', '', 'MEMBER', '', '329661842579', 'Tamil Nadu', 'Tiruppur', '558 M.K.STREET FORT \r\nVANIYAMBADI 635 751, TIRUPPATUR DIST, TAMILNADU', '635751', 'active', '6916fba888d42.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/37591', 'approved', '2026-06-16', 'member', 1, '2025-11-14 09:51:36', '', NULL, NULL, '2025-06-17'),
(194, 'KRASHN KUMAR', 'krashnkumar3@gmail.com', '$2y$10$91Ee8C.RWmkyjcK56wE7kebg/75e8Exe1f3pQSN..YM7a90njzMUy', '9174098781', 'Male', '1998-07-04', 'S/O', 's .d', '', 'MEMBER', '', '964943534348', 'Madhya Pradesh', 'Dindori', 'HOUSE NUMBER 65 WARD NUMBER 04 POST KHUDIYA VILLAGE KHARGAHANA KHUDIYA', '481882', 'active', NULL, NULL, NULL, '', NULL, NULL, '', 'PCWWF/37592', 'approved', '2026-08-14', 'member', 1, '2025-11-14 10:02:43', '', NULL, NULL, '2025-08-15'),
(195, 'Dr. Se. Vengadesan', 'vengadesan67@gmail.com', '$2y$10$nCfM8ObceoIZDxWrRF55feQtRc5g2m4OD5U7aGR4Bu.mZDsCcdG6O', '9943810925', 'Male', '1974-05-17', 'S/O', 'selvaraj', '', 'MEMBER', '', '890932478029', 'Puducherry', 'Puducherry', '37 fourth cross street sakthi nagar pondicherry', '605013', 'active', '691701c012222.jfif', '691701c012872.jfif', NULL, '', NULL, NULL, '', 'PCWWF/37593', 'approved', '2026-02-25', 'member', 1, '2025-11-14 10:17:36', '', NULL, NULL, '2025-02-26'),
(196, 'Dr J Haridoss', 'harij19511@gmail.com', '$2y$10$Pj7waFgwzZ8XZzUetFi.S.32hNKEWEwSi6JeHoqOen.CebezPElK2', '9444054922', 'Male', '1951-01-19', 'S/O', 'Father Name', '', 'MEMBER', '', '963084961280', 'Tamil Nadu', 'Chennai', 'Komal crest potential  19 Cresent park Street  ( Natesan park back) T Nagar Chennai 600017', '600017', 'active', '6917033c99775.jfif', '6917033c99c2c.jfif', NULL, '', NULL, NULL, '', 'PCWWF/37594', 'approved', '2026-05-16', 'member', 1, '2025-11-14 10:23:56', '', NULL, NULL, '2025-06-17'),
(198, 'GOVINDA TRIPATHY', 'Govindatripathy10@gmail.com', '$2y$10$XG01DNjjtEg2uzAmrmS5auXqCsAo60GquLU1gWqKKc2Dvw9pkmcj.', '8480556478', 'Male', '1970-11-01', 'S/O', 'kumuda chandra tripathy', '', 'Secretary', '', '263268826118', 'Odisha', 'Nabarangpur', '0014, KEUTASTREET ,NABARANGAPUR', '764059', 'district', '691705173e154.jfif', '691705173e411.jfif', NULL, '', NULL, NULL, '', 'PCWWF/37596', 'approved', '2026-03-20', 'member', 1, '2025-11-14 10:31:51', '', NULL, NULL, '2025-03-21'),
(199, 'S.THIRUMAL', 'thirucdmm@gmail.com', '$2y$10$LaccV5QW5n7v6OHPN04LmOCuDSBf6oErTzi0ZG8kFdfhR1.KmWaKG', '9360556361', 'Male', '1985-05-01', 'S/O', 'singaravel', '', 'Health Coordinator', '', '313690713426', 'Tamil Nadu', 'Cuddalore', '38,MANA LANE,CHIDAMBARAM', '608001', 'district', '6917064fef132.jfif', '6917064fef338.jfif', NULL, '', NULL, NULL, '', 'PCWWF/37597', 'approved', '2026-11-13', 'member', 1, '2025-11-14 10:37:03', '', NULL, NULL, '2025-11-14'),
(200, 'Niraj kumar prjapat', 'niraj456@gmail.com', '$2y$10$R/qDwQZx22F.OJAkJv3dQeYG.Wn6n9mwZGpdVFQ6cKEAbNMn6jldi', '9893888743', 'Male', '1992-02-03', 'S/O', 'Bhanwar', '', 'MEMBER', '', '222469851210', 'Rajasthan', 'Pratapgarh', 'GOMANA darwaza lohara ka chok, waed 14 chhati sadhi PO: chhoti sadari', '312604', 'active', '6917080f570cb.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/37598', 'approved', '2026-11-13', 'member', 1, '2025-11-14 10:44:31', '', NULL, NULL, '2025-11-14'),
(201, 'PRAGADEESHWARAN', 'spwaranvipp@gmail.com', '$2y$10$owyUTv1kUkvKC3hWvHZacuOXCBRJTVgEHXi24IcE1gEiU7jcuULHW', '9942290254', 'Male', '1994-04-28', 'S/O', 'saravanan', '', 'MEMBER', '', '674684866566', 'Tamil Nadu', 'Pudukkottai', '3/596 SAMPATTIVIDUTHI 4 ROUD', '622301', 'active', '6917096476cf2.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/37599', 'approved', '2025-11-13', 'member', 1, '2025-11-14 10:50:12', '', NULL, NULL, '2025-11-14'),
(203, '|Jogesh chandra roy', 'jogeshchandra456@gmail.com', '$2y$10$JYKoNUV0Dj33dZKHCO.75eOfHQcWPXmL7vZiU9Qs4xA2M/9nd34ae', '9836187074', 'Male', '1969-01-25', 'S/O', 'Father Name', '', 'MEMBER', '', '652192004876', 'West Bengal', 'Purba Medinipur', '306,\r\nDurgachak new colony, \r\nHaldia municipality', '721602', 'active', '69170cf28d7eb.jfif', '69170cf28dc6d.jfif', NULL, '', NULL, NULL, '', 'PCWWF/37600', 'approved', '2026-05-31', 'member', 1, '2025-11-14 11:05:22', '', NULL, NULL, '2025-06-01'),
(204, 'Sarata chandra mahanty', 'drmohanty7@gmail.com', '$2y$10$8Pde9737FdBVVoeEN4J2V.yr//3UyHRYFqpAODiFqz0i3hBqgx24K', '7008220961', 'Male', '1961-10-14', 'D/O', 'jagannath', '', 'MEMBER', '', '687049970957', 'Delhi', 'New Delhi', 'Mahabir nagar new delhi', '110018', 'active', '69170f09702f1.jfif', '69170f0970757.jfif', NULL, '', NULL, NULL, '', 'PCWWF/37601', 'approved', '2026-01-19', 'member', 1, '2025-11-14 11:14:17', '', NULL, NULL, '2025-01-20'),
(205, 'Jaspreet singh', 'jaspreetsingh5242@gmail.com', '$2y$10$hf9OUGf9KrMzOicm4vczTePzBsnEFsAwiG6KwBNrjbQ0HwZRnHNVK', '9530531839', 'Male', '1989-04-11', 'S/O', 'Bhagwant singh', '', 'MEMBER', '', '479853902583', 'Punjab', 'Amritsar', 'house no 2540 amarpura ward no 75 ludhaina state punjab pincode 141008', '141008', 'active', '691712049d72e.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/37602', 'approved', '2026-04-10', 'member', 1, '2025-11-14 11:27:00', '', NULL, NULL, '2025-04-11'),
(206, 'DEEPAK KUMAR KARUA', 'deepakkumarkaruaa@gmail.com', '$2y$10$J2hWRrKiaUCkYTDvTtZqfuh7Req8lealtzcX54qTq21X8SV3OATPC', '9337226099', 'Male', '1995-06-26', 'S/O', 'Durga karua', '', 'MEMBER', '', '580631981442', 'Odisha', 'Kendujhar', 'C/O- DURGA KARUA  AT- SAHID NAGAR JODA  PS/PO-JODA 758034', '758034', 'active', '691713686a368.jfif', '691713686a6c7.jfif', NULL, '', NULL, NULL, '', 'PCWWF/37603', 'approved', '2026-06-16', 'member', 1, '2025-11-14 11:32:56', '', NULL, NULL, '2025-06-17'),
(207, 'Kailas Ganpatrao Kaninde', 'ganpatrao23@gmail.com', '$2y$10$2/p0PD99d.NDhk7CViFGRu661ayqJ/f0lURlNc0X8huce/WOa0ACG', '9767044993', 'Male', '1978-05-23', 'S/O', 'Ganpatrao Kaninde', '', 'MEMBER', '', '876395264895', 'Maharashtra', 'Mumbai Suburban', '188 Renapur Bhokar, \r\nBhokar Nanded\r\nMaharashtra', '431801', 'active', '691813e391c0e.jfif', '691813e392103.jfif', NULL, '', NULL, NULL, '', 'PCWWF/37604', 'approved', '2026-02-19', 'member', 1, '2025-11-15 05:47:15', '', NULL, NULL, '2025-02-20'),
(208, 'RAJU RAM', 'rajuramm@gmail.com', '$2y$10$DlPJ2eIWV9A00yPIrv8Kc.xuhymGpCdlUzsn1kh7NgSXTIcL1BH0m', '8490917067', 'Male', '1996-07-02', 'S/O', 'Sanwala ram', '', 'National President', '', '450631782862', 'Rajasthan', 'Jalore', 'SANWALA RAM HADETAR  JALORE RAJASTHAN 343041', '343041', 'national', '691814da34e09.jfif', '691814da35015.jfif', '691814da35341.jfif', '', NULL, NULL, '', 'PCWWF/37605', 'approved', '2026-09-13', 'member', 1, '2025-11-15 05:51:22', '', NULL, NULL, '2025-09-14'),
(209, 'Dr. Garima Ganeriwal', 'garimag00@gmail.com', '$2y$10$wnYRb//whBQ7XmL28hiJi.AHyGwhEMVpS3AFGp83hjvhG0X78DMsm', '9885366654', 'Female', '1989-11-12', 'S/O', 'Father Name', '', 'MEMBER', '', '987140964253', 'Telangana', 'Hyderabad', 'Shanti niketan Apartment  Flat no 103 Near Roastry Coffee House Venkat nagar Banjara hills Road no :14', '500034', 'active', '6918177477b93.jfif', NULL, '6918177477e3f.jfif', '', NULL, NULL, '', 'PCWWF/37606', 'approved', '2026-04-03', 'member', 1, '2025-11-15 06:02:28', '', NULL, NULL, '2025-04-04'),
(210, 'GOUTAM DUTTA', 'goutam6926@gmail.com', '$2y$10$l/b37Q6yY7uL8zrPip0HfOx86l8.5VjU/88EGXshHOdyNsA8eZE1m', '9433190923', 'Male', '1965-11-10', 'S/O', 'Late Haripada Dutta', 'Government Job', 'Press Member', 'AB+', '733058751264', 'West Bengal', 'Kolkata', 'P-12 VIII-M CIT SCHEME FLAT-B ULTADANGA KOLKATA', '700067', 'active', '6919cec75973b.jpg', '6919cec759b15.jpg', '6919cec75a698.jpg', NULL, NULL, '6919cec75b1e4.png', 'offline', 'PCWWF/03137', 'approved', NULL, 'member', NULL, '2025-11-16 13:16:55', 'West Bengal', 'id_card_210_1763919738.png', '2025-11-23 17:42:18', '2025-11-16');
INSERT INTO `users` (`id`, `name`, `email`, `password`, `mobile`, `gender`, `dob`, `sdw_type`, `sdw_name`, `profession`, `designation`, `blood_group`, `aadhar`, `state`, `district`, `address`, `pincode`, `membership_type`, `profile_image`, `aadhar_front`, `aadhar_back`, `payment_id`, `order_id`, `payment_proof`, `payment_method`, `registration_id`, `status`, `valid_until`, `user_type`, `created_by`, `created_at`, `working_area`, `id_card_photo`, `updated_at`, `valid_from`) VALUES
(211, 'RAGHUNATH P', 'kotesree27@gmail.com', '$2y$10$xQTMfD2AITL9xXMvseBmQOUWKCZ4XMXh3BIOBuhxwaX0fvDeSsFo.', '9535297720', 'Male', '1981-10-27', 'S/O', 'A PRAKASH MUDHLIYAR', 'Job', 'MEMBER', 'O+', '502233278947', 'Karnataka', 'Shivamogga', '[2:38 PM, 11/18/2025] .: S/O: A Prakash Mudhliyar,\r\n#189 mastambika nilaya,\r\n1st cross veenasharadha school down,\r\nsharavathi nagar,\r\nVTC: Shimoga,\r\nPO: Shimoga,\r\nSub District: Shimoga,\r\nDistrict: Shimoga,\r\nState: Karnataka,\r\nPIN Code: 577201', '577201', 'active', '691c493166c7f.jpg', '691c493166fbf.jpg', '691c4931671cc.jpg', '', NULL, NULL, '', 'PCWWF/03138', 'approved', '2026-11-18', 'member', 1, '2025-11-18 10:23:45', '1ST CROSS, SHARAVATHI NAGAR, SHIVAMOGGA', 'id_card_211_1763461722.png', '2025-11-18 10:28:42', '2025-11-18'),
(212, 'SUMAN CHAKRABORTY', 'sumanchawkk@gmail.com', '$2y$10$.Pd9QKgTflVmY74P2s1FN.4H.MfMb3R/0qTpfvBNfGk4tV6AGR..i', '9123618538', 'Male', '1977-02-18', 'S/O', 'GOURI SANKAR CHAKRABORTY', 'Job', 'MEMBER', 'O-', '663885806296', 'West Bengal', 'North 24 Parganas', 'S/O: Gouri Sankar Chakraborty, B.K.-121, SALTLAKE SECTOR- II, Bidhannagar(M), North 24 Parganas, West Bengal - 700091', '700091', 'active', '691d5b5455c8a.jpg', '691d5b5455ff7.jpg', '691d5b545612f.jpg', '', NULL, NULL, 'offline', 'PCWWF/03139', 'approved', '2026-11-19', 'member', 1, '2025-11-19 05:53:24', 'S/O: Gouri Sankar Chakraborty, B.K.-121, SALTLAKE SECTOR- II, Bidhannagar(M), North 24 Parganas, West Bengal - 700091', NULL, NULL, '2025-11-19'),
(213, 'Dr. SREEKUMAR D MENON', 'menonisavailable@gmail.com', '$2y$10$Id7cZtaAgm8yLw830HScD.llZcYmv6mXKG1Mcwfkdw6.vHQIOUHZS', '9446078970', 'Male', '1964-05-25', 'S/O', 'DAMODHARA MENON', 'Job', 'Technical Advisor', 'O-', '770209508881', 'Kerala', 'Kottayam', 'SAYOOJYA, SOUTH, PAMPADY P.O., Poothakuzhy, Kottayam, Kerala, 686521', '686521', 'state', '691eea5187534.jpg', '691eea518786c.jpg', '691eea5187a00.jpg', '', NULL, NULL, 'offline', 'PCWWF/03140', 'approved', '2026-11-20', 'member', 1, '2025-11-20 10:15:45', 'SAYOOJYA, SOUTH, PAMPADY P.O., Poothakuzhy, Kottayam, Kerala, 686521', NULL, NULL, '2025-11-20'),
(214, 'SYED JOHN PASHA', 'johnu.pasha@gmail.com', '$2y$10$sSmv6xaNXtaFPN3rp.e4euFM5uIjLR0kMLXFks.hp1jEwvoFejESS', '9959467817', 'Male', '1988-05-01', 'S/O', 'Syed Rahim', 'Job', 'MEMBER', 'O+', '636357290679', 'Telangana', 'Khammam', 'C/O Syed Rahim, 2-22, Ram Nagar, Ram Nagar, Peddireddygudem, Bhavannapalem, Khammam, Telangana - 507316', '507316', 'active', '691eec8ac5f4e.jpg', '691eec8ac623e.jpg', '691eec8ac63d3.jpg', '', NULL, NULL, 'offline', 'PCWWF/03141', 'approved', '2026-11-20', 'member', 1, '2025-11-20 10:25:14', 'C/O Syed Rahim, 2-22, Ram Nagar, Ram Nagar, Peddireddygudem, Bhavannapalem, Khammam, Telangana - 507316', NULL, NULL, '2025-11-20'),
(215, 'RADHESHYAM MISHRA', 'dir.rsmishra@gmail.com', '$2y$10$DT978xE50QzENQBHxqWzk.tQddE/LOagt6po1chQ9p/5lNA5g0oRO', '9437105278', 'Male', '1977-01-18', 'S/O', 'GOPINATH MISHRA', 'Job', 'MEMBER', 'O+', '498666924695', 'Odisha', 'Khordha', 'Flat No D-001 Lifestyle Orchid Apartment, Khandagiri Chandaka Road, Sundarpur, Khorda, Bhubaneswar, Odisha - 751024', '751024', 'active', '692588050ef97.jpg', '692588050f279.jpg', '692588050f426.jpg', '', NULL, NULL, 'offline', 'PCWWF/03142', 'approved', '2026-11-25', 'member', 1, '2025-11-25 10:42:13', 'Flat No D-001 Lifestyle Orchid Apartment, Khandagiri Chandaka Road, Sundarpur, Khorda, Bhubaneswar, Odisha - 751024', NULL, NULL, '2025-11-25'),
(216, 'M. ALAGARSAMY', 'Sbmtrust777@gmail.com', '$2y$10$agF1etWhSRqx/ukw91E0sOEoAF2a7pW9hJzBHNbbUqE4T1RmthHSK', '9042498777', 'Male', '1966-02-11', 'S/O', 'S.B. MOHAN', 'Job', 'Project Head', 'O+', '391165304360', 'Tamil Nadu', 'Virudhunagar', '12-3-25, PANDIYAN NAGAR, Kariapatti, Kariapatti, Virudhunagar, Tamil Nadu, 626106', '626106', 'state', '69258c9a23b7c.jpg', '69258c9a23dd4.jpg', '69258c9a23f92.jpg', '', NULL, NULL, 'offline', 'PCWWF/03143', 'approved', '2026-11-25', 'member', 1, '2025-11-25 11:01:46', '12-3-25, PANDIYAN NAGAR, Kariapatti, Kariapatti, Virudhunagar, Tamil Nadu, 626106', 'id_card_216_1764137595.png', '2025-11-26 06:13:15', '2025-11-26'),
(217, 'Chevva Sundara Reddy', 'sundarareddy1947@gmail.com', '$2y$10$OBNSdnl7mTjGJ8Ih8ATqReORUJk0FZGGo7rWIuOuO/qUkYDL9Ih56', '8106683973', 'Male', '1947-01-01', 'S/O', 'Chevva Sundara Reddy', '', 'MEMBER', '', '285741276941', 'Andhra Pradesh', 'Guntur', 'rajeev nagar colony, Gudemcheruvu, Cuddapah , jammalamdugu, andgra pradesh', '516434', 'active', '69269f2306e29.jfif', '69269f230705b.jfif', NULL, '', NULL, NULL, '', 'PCWWF/03144', 'approved', '2026-11-25', 'member', 1, '2025-11-26 06:33:07', '', NULL, NULL, '2025-11-26'),
(218, 'Reena Dillu', 'reenadillu345@gmail.com', '$2y$10$W5HlPg0cf1pRIQDvkgvAHey0DvVhuD.O0Xsk1bTcJLZAx1XptaQLS', '9617199019', 'Female', '1988-07-01', 'S/O', 'Father Name', '', 'Women Incharge', 'O+', '595748808328', 'Chhattisgarh', 'Bilaspur', 'C\\O Sunil kumar kiran, waed no 42, middle school near gram panchayet, devrikhurd, bilaspur, bilaspur PO: Deorikhurd', '495004', 'district', '6926a917238ce.jfif', '6926a91723d4d.jfif', NULL, '', NULL, NULL, '', 'PCWWF/03145', 'approved', '2026-11-25', 'member', 1, '2025-11-26 07:15:35', 'member', NULL, NULL, '2025-11-26'),
(219, 'Mohd Iqbal kohli', 'mohdiqbalkohli@gmail.com', '$2y$10$IDFNkQcjDVPQ10bgeFL0XeuWNYGzIuJ109qZ93k6K2NQZORHH9EsC', '9103040786', 'Male', '1991-12-11', 'S/O', 'Munir Hussain', '', 'MEMBER', 'A+', '927572860796', 'Jammu and Kashmir', 'Poonch', 'Gaarang, brachhar, punch, jammu ans kashmir', '185102', 'active', '6927e064c50b2.jfif', '6927df5ae7b93.jfif', NULL, '', NULL, NULL, '', 'PCWWF/03146', 'approved', '2026-11-26', 'member', 1, '2025-11-27 05:19:22', 'member', NULL, NULL, '2025-11-27'),
(220, 'AMIT NARESH RAJWANI', 'amitnrajwani@gmail.com', '$2y$10$2FN0AB9fNy80w1.6vl6e/.YDo1rwItEm/ajUpEverTe1yA1mZYMJ.', '9821012678', 'Male', '1981-12-10', 'S/O', 'Father Name', '', 'National Media Head', '', '303651467667', 'Maharashtra', 'Mumbai City', 'OM VALIKUNTH CHS LTB B WING FLAT NO 406 4TH FLOOR NEAR S V H SCHOOL SINDHI SOCIETY MUMBAI 400071', '400071', 'national', '6969bf0a49d12.jpeg', NULL, NULL, '', NULL, NULL, '', 'PCWWF/03147', 'approved', '2027-01-15', 'member', 1, '2025-11-28 06:38:17', 'OM VALIKUNTH CHS LTB B WING FLAT NO 406 4TH FLOOR NEAR S V H SCHOOL SINDHI SOCIETY MUMBAI 400071', NULL, NULL, '2026-01-15'),
(221, 'MANOJ KUMAR JAIN', 'manojkrjain4@gmail.com', '$2y$10$d3c0e2c8EHRpzwBpEO6lHusp9e5tHOOwhDyWsQK4KwO8aceTRO9P6', '9830183152', 'Male', '1965-10-16', 'S/O', 'paras kumar jain', '', 'National President', 'A+', '804727897908', 'West Bengal', 'Howrah', 'VIVEK VIHAR \r\n     PHASE --3\r\n     BLOCK --A-1,302.\r\n    493/C/A, G.T.ROAD,SOUTH\r\nSHIBPUR \r\nHOWRAH --711102.\r\nWEST -BENGAL.', '711102', 'national', NULL, NULL, NULL, '', NULL, NULL, '', 'PCWWF/03148', 'approved', '2026-11-26', 'member', 1, '2025-11-28 06:50:11', '', NULL, NULL, '2025-11-27'),
(222, 'DR.M.MOHAN', 'marimohan3r6@gmail.com', '$2y$10$qIajOhbeWMExQj0XBe6iN.CV1.z/CdkBWARQDSrcMZerrCVhrdZJ6', '8110001691', 'Male', '1971-09-03', 'S/O', 'Meri', '', 'MEMBER', 'AB+', '888546961272', 'Andhra Pradesh', 'Chittoor', 'Seetharamapeta, Sathyavbedu, Ambakam, Chittaar, Andhra pradesh', '617688', 'active', '692d2ab420474.jfif', '692d2ab4206e8.jfif', NULL, '', NULL, NULL, '', 'PCWWF/03149', 'approved', '2026-11-30', 'member', 1, '2025-12-01 05:42:12', '', NULL, NULL, '2025-12-01'),
(223, 'M Ramalingeswara Rao', 'mudigonda71@gmail.com', '$2y$10$PXJrBVTUc1CZMMH4E/1e2.uLRCpD5jEfwZNBHe9Z08Bcqccd8GL06', '8639477606', 'Male', '1971-05-19', 'S/O', 'Viswanadham', '', 'Monitoring Officer', 'O+', '225392769575', 'Andhra Pradesh', 'Guntur', 'Angalakuduru, guntur, Andhra pradesh 522211', '522211', 'district', '693914137bfb4.jfif', '693913a00cb82.jfif', NULL, '', NULL, NULL, '', 'PCWWF/03150', 'approved', '2026-12-11', 'member', 1, '2025-12-10 06:30:56', '', NULL, NULL, '2025-12-11'),
(224, 'Dr. P Kumar', 'ammuselvi5318@gmail.com', '$2y$10$9.1XdCDR.ZGuPYt2Hsg9pOUrgo6Y5prjQva8CgIjK40f2z4FFjhVu', '6369232994', 'Male', '1978-06-26', 'S/O', 'ponnusamy senjeevi goundar', 'Job', 'MEMBER', 'A+', '281083997028', 'Tamil Nadu', 'Vellore', 'Mukundarayapuram\r\nMukundarayapuram\r\nVellore\r\nTamil Nadu 632405', '632405', 'active', '693e7f1440502.jpeg', '693e7f144078e.jpeg', '693e7f1440bc5.jpeg', '', NULL, NULL, 'offline', 'PCWWF/03151', 'approved', '2026-12-14', 'member', 1, '2025-12-14 09:10:44', 'Mukundarayapuram\r\nMukundarayapuram\r\nVellore\r\nTamil Nadu 632405', NULL, NULL, '2025-12-14'),
(225, 'Dr.K.Chinnaiah', 'cvsthiru@gmail.com', '$2y$10$HXg1TTluq9HsF5.dLsbbZu5VJ8Ga6.Pr1S85YCM/60zhB07n9Dqji', '9444062697', 'Male', '1997-06-24', 'S/O', 'Kandasamy', 'Job', 'MEMBER', 'O+', '251882105448', 'Tamil Nadu', 'Chennai', '6/6, Rajivgandhi Street, Virugambakkam, Chennai', '600092', 'active', '69439f152ad22.jpeg', '69439f152b08e.jpeg', '69439f152b2da.jpeg', '', NULL, NULL, 'offline', 'PCWWF/03152', 'approved', '2026-12-18', 'member', 1, '2025-12-18 06:28:37', '6/6, Rajivgandhi Street, Virugambakkam, Chennai', NULL, NULL, '2025-12-18'),
(226, 'Om Prakesh Sharma', 'sharmaopsonologist@gmail.com', '$2y$10$PoYtYBFvsHjmPMbu94/N9Otm07lyyxrqtWgoxko6Jpr0UW0wBAlda', '9455979888', 'Male', '1947-07-14', 'S/O', 'Kedar Nath Sharma', 'Job', 'Project Head', 'B+', '979691653686', 'Uttar Pradesh', 'Varanasi', '8/180-R-124, RAJENDRA VIHAR COLONY, NEWADA, Varanasi', '221005', 'state', '6944f5f0b0dc6.jpeg', '6944f5f0b10de.jpeg', '6944f5f0b12b8.jpeg', '', NULL, NULL, '', 'PCWWF/03153', 'approved', '2026-12-19', 'member', 1, '2025-12-19 06:51:28', 'rajendra Vihar Colony, Newada Varanasi', NULL, NULL, '2025-12-19'),
(227, 'BALASUBRAMANI AYYANAR', 'Vidiyalbalu@gmail.Com', '$2y$10$pnWFH8JjBIYChOx2m0xskebqZWW68GTrU.f9TCK5MeQnrtUctvpoC', '8838801977', 'Male', '1967-06-05', 'S/O', 'AYYANAR', 'Job', 'MEMBER', 'B+', '776418329893', 'Tamil Nadu', 'Dindigul', '11-1-12k, 53/1, machoor, Pannaikadu, Adukkam, Adukkam, Dindigui, Tamil Nadu, 624101', '624101', 'active', '69510a8d2d704.jpg', '69510a8d2d9ca.jpg', '69510a8d2dbb2.jpg', '', NULL, NULL, 'offline', 'PCWWF/03154', 'approved', '2026-12-28', 'member', 1, '2025-12-28 10:46:37', '11-1-12k, 53/1, machoor, Pannaikadu, Adukkam, Adukkam, Dindigui, Tamil Nadu, 624101', NULL, NULL, '2025-12-28'),
(228, 'Amit zutshi', 'amzutshi968@gmail.com', '$2y$10$yNLKkTUorcl.YLNMjTIZLeL7BsSriEFPVw33BMwFayf632gtMkPkW', '9858027289', 'Male', '1982-05-07', 'S/O', 'Chaman lal zutshi', 'Self Business', 'Press Member', 'A+', '591176869034', 'Jammu and Kashmir', 'Jammu', 'Lane no 8 block no 35 flat no 3 Jagti nagrota jammu jkut pin code 181221', '181221', 'active', '695cb12545536.jpeg', '695cb1254599d.jpeg', '695cb12545c85.jpeg', '', NULL, '69552df427db3.jpg', 'offline', 'PCWWF/04439', 'approved', '2027-01-06', 'member', NULL, '2025-12-31 14:06:44', 'Jammu Kashmir', NULL, NULL, '2026-01-06'),
(229, 'PURNIMA', 'Purnimapandey3333@gmail.com', '$2y$10$GYf24e8oYqdVcfShIhmjgOhNeSY9bmTgw7MOu8W454Ac0FKiW.Hre', '9236592252', 'Female', '1985-07-12', 'D/O', 'pandey', 'Job', 'Active Member', 'O+', '684524211412', 'Uttar Pradesh', 'Lucknow', 'FLAT NO 305 FOURTH FLOOR, AJEET RESIDENCY, GULACHIN MANDIR KE SAAMNE, Vikas Nagar, PO: Vikas Nagar, DIST: Lucknow, Uttar Pradesh - 226022', '226022', 'active', '695b8c306b906.jpeg', '695b8c306bc29.jpeg', NULL, '', NULL, NULL, 'offline', 'PCWWF/04440', 'approved', '2027-01-05', 'coordinator', 1, '2026-01-05 10:02:24', 'FLAT NO 305 FOURTH FLOOR, AJEET RESIDENCY, GULACHIN MANDIR KE SAAMNE, Vikas Nagar, PO: Vikas Nagar, DIST: Lucknow, Uttar Pradesh - 226022', NULL, NULL, '2026-01-05'),
(230, 'SK SOYEAD ALI', 'sksoyedali@gmail.com', '$2y$10$8YCAN6BokXdPxbEK4kNyn.t4gXV/svrsG0m2Hil2COtCsV4umBjpS', '9167001434', 'Male', '1988-01-01', 'S/O', 'AMAN', 'Job', 'MEMBER', 'AB+', '693459520765', 'Odisha', 'Khordha', 'PLOT NO-C/8, SAHID NAGAR, ESHAN ECODRIVE PVT, LTD, BHUBANESWAR', '751007', 'active', '695cbdd8bcf66.jpeg', '695cbdd8bd22a.jpeg', '695cbdd8bd746.jpeg', '', NULL, NULL, 'offline', 'PCWWF/04441', 'approved', '2027-01-05', 'member', 1, '2026-01-05 10:23:08', 'PLOT NO-C/8, SAHID NAGAR, ESHAN ECODRIVE PVT. LTD, BHUBANESWAR, Saheednagar, Khorda', NULL, NULL, '2026-01-05'),
(231, 'NABA JYOTI HAZARIKA', 'nabajyoti6155@gmail.com', '$2y$10$uGn9qL7Yo6ZiYSTg6YD7zeAeSMZ8qoXkWAaU8TxpHQOrEQefqEaW6', '7002871387', 'Male', '1964-07-19', 'S/O', 'Puspendra Nath Hazarika', '', 'MEMBER', 'O+', '519452751478', 'Assam', 'Kamrup Metropolitan', 'House No 4, Pub Sarania Main Road, Near Rail Gate, P.S Chandmari, Silpukhuri, Kamrup Metro, Assam - 781003', '781003', 'active', '695b9561f0dcf.jpeg', '695b9561f149a.jpeg', '695b9561f17b8.jpeg', '', NULL, NULL, 'offline', 'PCWWF/04442', 'approved', '2027-01-05', 'member', 1, '2026-01-05 10:41:37', 'House No 4, Pub Sarania Main Road, Near Rail Gate, P.S Chandmari, Silpukhuri, Kamrup Metro, Assam - 781003', NULL, NULL, '2026-01-05'),
(232, 'KHOKAN MOLLA', 'aman.khokan@gmail.com', '$2y$10$ot.SwOqeIOt2Nelafptx/.qNCNeQsJAYLSctQf5ADXPLNRbwJfN4.', '9831154700', 'Male', '1985-09-01', 'S/O', 'Khokan Molla', 'Self Business', 'Press Member', 'O+', '693459520765', 'West Bengal', 'North 24 Parganas', 'Purbapara, Rajarhat Gopalpur(M) , North 24 Parganas West Baengal', '700157', 'active', '695e0a183e527.jfif', '695e0a183e85c.jfif', '695e0a183ea41.jfif', '', NULL, NULL, 'offline', 'PCWWF/04443', 'approved', '2027-01-06', 'member', 1, '2026-01-07 07:24:08', 'Purbapara , Rajarhat Gopalpur(M) , North 24 Parganas West Bengal', NULL, NULL, '2026-01-06'),
(233, 'Jitesh Arvindbhai Vyas', 'jituvyasjv0@gmail.com', '$2y$10$kcTXa0SLtB/ZEYS4LifpL.H3QMAembecHVgaTrhIRsWlOzOyOZE1C', '9727744880', 'Male', '1973-11-30', 'S/O', 'Arvindbhai', 'Job', 'Press Member', 'O+', '896957835214', 'Gujarat', 'Jamnagar', 'Nagar Chaklo Mukesh Londry Jamnagar, Gujarat', '361001', 'active', '6969c7283ac77.jpeg', '6969c7283b0a6.jpeg', '6969c7283b2dd.jpeg', '', NULL, NULL, 'offline', 'PCWWF/04444', 'approved', '2027-01-16', 'member', 1, '2026-01-16 05:05:44', 'Nagar Chaklo Mukesh Londry Jamnagar, Gujarat', NULL, NULL, '2026-01-16'),
(234, 'Anitha Chinnala', 'anitahrm3@gmail.com', '$2y$10$GsdWx1IEuZNLov48mztOv.rNNtyeWa34a7qVEEmfjETwrPJCJnaTy', '9949227381', 'Female', '1975-06-10', 'D/O', 'Ramulu', 'Job', 'Active Member', 'B+', '770992952786', 'Telangana', 'Hyderabad', '403,A1 - Block, Near info Sys-SEZ, Annojiguda, Secunderabad, Hyderabad', '500088', 'active', '696dccf4a060d.jpeg', '696dccf4a0a70.jpeg', '696dccf4a0c90.jpeg', '', NULL, NULL, 'offline', 'PCWWF/04445', 'approved', '2027-01-19', 'coordinator', 1, '2026-01-19 06:19:32', '403,A1 - Block, Near info Sys-SEZ, Annojiguda, Secunderabad, Hyderabad', NULL, NULL, '2026-01-19'),
(235, 'Rajib Das', 'rib.das007@gmail.com', '$2y$10$YHGvtXN9tJiAp9u8e8eQquHGhc4iiP2VUjAo9nGwztK/qvS2m/uWe', '9854369582', 'Male', '1978-04-02', 'S/O', 'Arvindbhai', 'Job', 'Press Member', 'O+', '619791832489', 'Assam', 'Nagaon', 'Paingaon Netaji Road , Nagaon, Assam', '782003', 'active', '696df664d3cf1.jpeg', '696df664d40ad.jpeg', NULL, '', NULL, NULL, 'offline', 'PCWWF/04446', 'approved', '2027-01-19', 'member', 1, '2026-01-19 09:16:20', 'Paingaon Netaji Road , Nagaon, Assam', NULL, NULL, '2026-01-19'),
(236, 'Viren kishor Desai', 'parshwareqltors@hotmail.com', '$2y$10$UNfXdRV1moCTCmIU3pM.we1OCSl5brypOxVpI3ZbIZ/MVjzYBOUsC', '9019723231', 'Male', '1986-06-03', 'S/O', 'Father Name', '', 'Press Member', '', '304975269727', 'Maharashtra', 'Ahmednagar', '202, Hs Coral zaver Road,near punjab national bank mulund west mumbai 400080', '400080', 'active', NULL, NULL, NULL, '', NULL, '69707222276a7.jfif', '', 'PCWWF/04447', 'approved', '2027-01-21', 'member', 1, '2026-01-21 06:28:50', '', NULL, NULL, '2026-01-21'),
(237, 'Sushanta Kumar Das', 'bapisworld.shila@gmail.com', '$2y$10$b1eElY3QvQMXcZ7AyWrIjuiU3t0/BZkwBL4FMmhSY9ZOvpw09cOpm', '9831164727', 'Male', '1966-01-02', 'S/O', 'Arvindbhai', 'Job', 'Press Member', 'B+', '653148381167', 'West Bengal', 'Howrah', 'Gopinath Chongdar Lane Haora (M.Corp), Howrah , West Bengal - 711101', '711101', 'active', '697073586aa9e.jpeg', '697073586af29.jpeg', NULL, '', NULL, NULL, 'offline', 'PCWWF/04448', 'approved', '2027-01-21', 'member', 1, '2026-01-21 06:34:00', 'Gopinath Chongdar Lane Haora (M.Corp), Howrah , West Bengal - 711101', NULL, NULL, '2026-01-21'),
(238, 'Sachin Oberoi', 'sirmourabhi@gmail.com', '$2y$10$.j8gzq0F1Va8IlWF03WmA.1NvGs5WqpvH6HQMwOA4cZoj/aih9Q.C', '9882262261', 'Male', '1990-07-09', 'S/O', 'Harbhajan Singh', 'Job', 'Press Member', 'B+', '368264720189', 'Himachal Pradesh', 'Sirmaur', 'House Number - 82711, Paonta Sahib (T), Sirmaur , Himachal Pradesh', '173025', 'active', '697098e0ef119.jpeg', '697098e0ef503.jpeg', '697098e0ef8ca.jpeg', '', NULL, NULL, 'offline', 'PCWWF/04449', 'approved', '2027-01-21', 'member', 1, '2026-01-21 09:12:51', 'Paonta Sahib (T), Sirmaur , Himachal Pradesh', NULL, NULL, '2026-01-21'),
(239, 'MUNGLA WILLIAM', 'munigalawilliam8@gmail.com', '$2y$10$RJPpKFklH2vYIGff7cPyPu/MOtOanvCSeowdxD37ID/TevqkGsTVG', '9985444773', 'Male', '1954-05-05', 'S/O', 'MUNIGALA', '', 'Press Member', 'O+', '934952811216', 'Telangana', 'Hanamkonda', '57-4-118,RAMPUR,KAZIPET,RAMPUR ,HANAMKONDA ,TELANGANA ,506151', '506151', 'active', '6971fae9a72a4.jpeg', NULL, NULL, '', NULL, NULL, '', 'PCWWF/04450', 'approved', '2027-01-22', 'member', 1, '2026-01-22 10:24:41', '', NULL, NULL, '2026-01-22'),
(240, 'Hakeem Mohd Haroon Sahab', 'hakeem@gmail.com', '$2y$10$x1UsXcUPl3Cv3kA7JjxZX.6ZHY.YqKSgu3VH8E.J9m9k1bD39LPfu', '6386266428', 'Male', '1989-05-02', 'S/O', 'Shabnam', '', 'Press Member', 'O+', '257964435958', 'Uttar Pradesh', 'Bahraich', 'New Galla Mandi \r\nKaiserganj Bahraich Uttar Pradesh \r\nPin code:- 271903', '271903', 'active', '69786fbcb0c3c.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/04451', 'approved', '2027-01-27', 'member', 1, '2026-01-27 07:55:45', '', NULL, NULL, '2026-01-27'),
(241, 'Tusar Kanti Barman', 'barmantusarkanti160@gmail.com', '$2y$10$9g/NWeg.vdknqxLjQczQC.Ac78T/LY2zX8TUOOR6CLig5D1Vim9LK', '9064482110', 'Male', '1989-05-02', 'S/O', 'AMAL BARMAN', '', 'Press Member', 'B+', '468215263136', 'West Bengal', 'Jalpaiguri', 'Vill-Purba Chakchaka, Post -Barobisha, PS-Kumargram, Dist-Jalpaiguri/Alipurduar,Pin-736207', '736207', 'active', '697dd8b653005.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/04452', 'approved', '2027-01-31', 'member', 1, '2026-01-31 10:25:58', '', NULL, NULL, '2026-01-31'),
(242, 'PRASENJIT DUTTA', 'Prsnjitdutta18@gmail.com', '$2y$10$FhYEPawiHGwRnncMzSU5Zu5I7d4R2u2xC6XUI28XwIpGNC6JI/bJa', '9365139903', 'Male', '1974-12-21', 'S/O', 'GOPENDRA KUMAR DUTTA', '', 'Press Member', 'B+', '763304530784', 'Assam', 'Hojai', 'GOBINDAPALLIY WORD NO 8 HOJAI ASSAM 782435', '782435', 'active', '697ddbfbc3c31.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/04453', 'approved', '2027-01-31', 'member', 1, '2026-01-31 10:39:55', '', NULL, NULL, '2026-01-31'),
(243, 'Suniel Thadani', 'sunilthadani150@gmail.com', '$2y$10$UrKOyqbSgzB.SDMriglJrujB/rMRUcyKkjJKLSR.pCmJ6lwPNVsmm', '7710041282', 'Male', '1964-04-09', 'S/O', 'Gullu Thadani', 'Job', 'Press Member', 'B+', '723543109511', 'Maharashtra', 'Mumbai City', '8,Floor-3RD,Lokumal, Gopi Tank Road , Citylight Cinema , Mahim , Mumbai , Mahim, Maharashtra, pin - 400016', '400016', 'active', '6980721ce62c7.jfif', '6980705fcfae5.jfif', '6980705fcfe50.jfif', '', NULL, NULL, 'offline', 'PCWWF/04454', 'approved', '2027-02-02', 'member', 1, '2026-02-02 09:37:35', '8,Floor-3RD,Lokumal, Gopi Tank Road , Citylight Cinema , Mahim , Mumbai , Mahim, Maharashtra, pin - 400016', NULL, NULL, '2026-02-02'),
(244, 'RAJESH SHANTILAL BORUNDIYA', 'dainikyavatmalchaufer00@gmail.com', '$2y$10$T4sG1aALZHiKL3/KdkNFZeOqBJuA1p8Vnrb0.Y8FbsASqD0BJpTwi', '9226204997', 'Male', '1970-07-27', 'S/O', 'SHANTILALJI BORUNDIYA', 'Job', 'MEMBER', 'A-', '398617574603', 'Maharashtra', 'Yavatmal', 'S/O Shantilalji Borundiya, near rani sati mandir, ward no 10 talab fail cotton market road, Yavatmal, Yavatmal, Maharashtra - 445001', '445001', 'active', '69808cb17fe91.jpeg', NULL, NULL, '', NULL, NULL, 'offline', 'PCWWF/04455', 'approved', '2027-02-02', 'member', 1, '2026-02-02 11:38:25', 'S/O Shantilalji Borundiya, near rani sati mandir, ward no 10 talab fail cotton market road, Yavatmal, Yavatmal, Maharashtra - 445001', NULL, NULL, '2026-02-02'),
(245, 'DR Digambar Sitaram Tayade', 'digambartayade5@gmail.com', '$2y$10$ymij605wU1Sv.FlxlOqwsOSRr/3zXRpYD4PEyxpj8zWtrD7yOMsAC', '9821267159', 'Male', '1954-09-09', 'S/O', 'SITARAM TAYADE', 'Job', 'MEMBER', 'AB+', '920167581744', 'Maharashtra', 'Thane', 'Regency Estate bldg no . 15/902 Dawadi gaon, near Venkatesh Petrol pump, Dombiwali east , Dist. Thane, Maharashtra \r\n421 203.', '421203', 'active', '69808fea1095c.jpeg', '69808fea10e6d.jpeg', NULL, '', NULL, NULL, 'offline', 'PCWWF/04456', 'approved', '2027-02-02', 'member', 1, '2026-02-02 11:52:10', 'Regency Estate bldg no . 15/902 Dawadi gaon, near Venkatesh Petrol pump, Dombiwali east , Dist. Thane, Maharashtra \r\n421 203.', NULL, NULL, '2026-02-02'),
(246, 'Soumen Ray.', 'Soumray1977@gmail.com', '$2y$10$MjD80YLoVUKMzEvUzSB4weKDzB4ca8.bcHQiiDw23lI7KfqEBxTai', '7483405454', 'Male', '1977-11-29', 'S/O', 'Subir Roy.', '', 'Press Member', 'A+', '791431200976', 'Karnataka', 'Bengaluru Rural', '#136.\r\n6th Cross. 5th Main. Rpc lay out. Vijaynagar.  Bangalore-560104', '560104', 'active', '698184c8e8ea8.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/04457', 'approved', '2027-02-03', 'member', 1, '2026-02-03 05:16:56', '', NULL, NULL, '2026-02-03'),
(247, 'DR. RAJIB KUMAR MOHOTTO', 'rkmohotta@gmail.com', '$2y$10$vZhUA4OwnMEH6CUAofJyW.1ka3vEfzAj85X21oiBVwxCwJ8wjlbhG', '8822039835', 'Male', '1980-06-18', 'S/O', 'SONURAM MOHOTTO', '', 'Press Member', 'B+', '812637202701', 'Assam', 'Charaideo', '2 NO SARUPATHER,MORANHAT,CHARAIDEO,ASSAM 785670', '785670', 'active', '698186dcf2fae.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/04458', 'approved', '2027-02-03', 'member', 1, '2026-02-03 05:25:48', '', NULL, NULL, '2026-02-03'),
(248, 'RUKHSAR AHMED', 'rukhsarnd@gmail.com', '$2y$10$kNwCo6n9Yi2rcNIKrDRqOu89x2iBLf6NCq7kA.817rYBHc/VRms0S', '9682349702', 'Male', '1990-11-20', 'S/O', 'MOHD', '', 'Press Member', 'A+', '337585749403', 'Jammu and Kashmir', 'Jammu', 'KADWAH,KHANAD,KHANED,UDHAMPUR,JAMMU AND KASHMIR,182128', '182128', 'active', '6981887ec22b2.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/04459', 'approved', '2027-02-03', 'member', 1, '2026-02-03 05:32:46', '', NULL, NULL, '2026-02-03'),
(249, 'SUDHEER SHARMA ACHARYA', 'acharyabca@gmail.com', '$2y$10$6LSDRHPqBHC/mBrmVSmW4OwY9E5yfa.3JrvLMwsv1hzItQF5WiSHW', '9826419439', 'Male', '1973-03-27', 'S/O', 'BHAGWAN SAHAY SHARMA', '', 'Press Member', 'O+', '417666148004', 'Madhya Pradesh', 'Morena', 'WARD NO 13,BHARATPURA ROAD,BRIGHT CAREER ACADEMY,AMBAH,MORENA,MADHYA PRADESH 476111', '476111', 'active', '69818a6f8d344.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/04460', 'approved', '2027-02-03', 'member', 1, '2026-02-03 05:41:03', '', NULL, NULL, '2026-02-03'),
(250, 'BABLU MARANDI', 'bablumarandi545@gmail.com', '$2y$10$tpeZmGJ55O.yKkIAI5VzX.ruxOT8TikKZH1tF9a5/5AbMpCSYq3im', '7004524903', 'Male', '1992-10-06', 'S/O', 'MARKUS MARANDI', '', 'Press Member', 'O+', '778788463492', 'Bihar', 'Araria', 'DEORIA,PAIK TOLA,ARARIA,BIHAR 854325', '854325', 'active', '69818ca4edc2c.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/04461', 'approved', '2027-02-03', 'member', 1, '2026-02-03 05:50:28', '', NULL, NULL, '2026-02-03'),
(251, 'PRABHESH MISHRA', 'mishraprabhesh20@gmail.com', '$2y$10$oXMZFBigVaL1lxz39DT95e6N5zdHadcQcrtEDuEf1h8o1qkD.zbui', '9303393190', 'Male', '1982-10-02', 'S/O', 'JANDARAN MISHRA', '', 'Press Member', 'B+', '858499262239', 'Chhattisgarh', 'Surajpur', 'WARD NO 8, BHATGAON,SURAJPUR,CHHATTISGARH,497235', '497235', 'active', '69842c5ad20c8.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/04462', 'approved', '2027-02-05', 'member', 1, '2026-02-05 05:36:26', '', NULL, NULL, '2026-02-05'),
(252, 'KIMMY ARORA', 'kimmy@gmail.com', '$2y$10$d6qe5fbOq/L/vmb2qWJlLeqBrYE5/Nkp.BsUwGD2O9HlCAkKRwc1C', '9888203111', 'Male', '1983-03-22', 'S/O', 'RAKESH ARORA', '', 'Press Member', 'O+', '580971814817', 'Punjab', 'Sangrur', 'GURU TEG BHADUR COLONY MALERKOTLA,148023', '148023', 'active', '6984701654744.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/04463', 'approved', '2027-02-05', 'member', 1, '2026-02-05 10:24:49', '', NULL, NULL, '2026-02-05'),
(253, 'CHNDRA PRAKASH KUSHWAHA', 'chandra@gmail.com', '$2y$10$lLcSH79oGZM6mwVczTUrWOi0GHAtXGhjrRFB0NrOCDuN6.soQmtQC', '9151158796', 'Male', '1993-01-02', 'S/O', 'JOGESHWAR', '', 'Press Member', 'A+', '367173329600', 'Uttar Pradesh', 'Hardoi', '82 mandai bilgram hardoi,uttar pradesh, 241301', '241301', 'active', '6984788107a4f.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/04464', 'approved', '2027-02-05', 'member', 1, '2026-02-05 11:01:21', '', NULL, NULL, '2026-02-05'),
(254, 'BHARAT BHUSAN ROY', 'bharat@gmail.com', '$2y$10$aAMInbACfiLnlXNhn..tC.VOpyOBNXvZYEhJrNu5jS69S3A4cWCH6', '8908579332', 'Male', '1976-08-16', 'S/O', 'BHUSAN', '', 'Press Member', 'AB+', '263175478665', 'Odisha', 'Cuttack', 'RUCHILINE MADHUPATANA,CUTTACK SADAR,753010', '753010', 'active', '6984808e28a7a.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/04465', 'approved', '2027-02-05', 'member', 1, '2026-02-05 11:17:30', '', NULL, NULL, '2026-02-05'),
(255, 'MOHAMMAD IRFAN', 'sonusamrat30otc@gmal.com', '$2y$10$WXSw.2EHGKlm0jpIv/NVrOlQwPYERw1pmHe1gXssWHtopMpgYHpZC', '7737405053', 'Male', '1990-10-31', 'S/O', 'MUNNA', '', 'Press Member', 'B-', '934912902971', 'Rajasthan', 'Ajmer', 'WARD NO 22 NEW BHATTA COLONI KEKRI,AJMER RAJASTHAN 305404', '305404', 'active', '69857bdc9a520.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/04466', 'approved', '2027-02-06', 'member', 1, '2026-02-06 05:27:56', '', NULL, NULL, '2026-02-06'),
(256, 'SUDIDA PADMA', 'sudida@gmail.com', '$2y$10$bCIQfN5V7OsV50AVylkyeud3mIxWe84Pc6Ta2HdU15q7QqAIdIenq', '8688197114', 'Female', '1980-03-16', 'W/O', 'SUDIDA', '', 'Press Member', 'O+', '384836799497', 'Telangana', 'Warangal', 'MACHILI BAZAR,THAUSANDPILLER TEMPLE BACK HANAMKONDA, WARANGAL,TELANGANA,506001', '506001', 'active', '69858154ad72d.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/04467', 'approved', '0000-00-00', 'member', 1, '2026-02-06 05:51:16', '', NULL, NULL, '2026-02-06'),
(258, 'Uttam Nagoraoji Bramhawade', 'uttambramhanwade@gmal.com', '$2y$10$c/XGlwgL5QcW3zlvV7.jGOakqQ01GFVooxRJDnBTctlafIj0d.X62', '9284070433', 'Male', '1977-12-29', 'S/O', 'Uttam Nagoraraoji Bramhanwade', '', 'Press Member', 'O+', '582045585903', 'Maharashtra', 'Amravati', 'At post,Taluka Nandgao khandeshwar, district,Amravati, pincode 444708 ,Bastand parisar', '444701', 'active', '6985ca3d48b71.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/04469', 'approved', '2027-02-06', 'member', 1, '2026-02-06 11:02:21', '', NULL, NULL, '2026-02-06'),
(259, 'JUGAL KISHOR AACHERA', 'Jugalaachhera@gmail.com', '$2y$10$LhTGro02hB6pXo6iUYjaLeOt7GhrFWa/oPaK3C8CoDx8Tn3PpRwL.', '9950703486', 'Male', '1980-11-08', 'S/O', 'TARACHAND AACHERA', '', 'Active Member', 'B+', '348061872416', 'Rajasthan', 'Ajmer', 'WARD NO 7, KHAIGADH,RAJASHTHAN,AJMER,305404', '305404', 'active', 'file_6989710ae35c49.88280825.jfif', NULL, NULL, '', NULL, NULL, '', 'GHDAF40282', 'approved', '2027-02-09', 'member', 1, '2026-02-09 05:30:50', '', NULL, NULL, '2026-02-09'),
(260, 'Minesh Choksi', 'mineshchoksi@gmail.com', '$2y$10$FEsVOJ5pZRkeZtfuVsDl2.41rwyYTR5GkxKXoBAOvSAb8J5KF.Rsm', '9904474337', 'Male', '1985-05-21', 'S/O', 'SANWALA', '', 'Active Member', '', '966388291174', 'Uttar Pradesh', 'Varanasi', '363, Pushpanjali Baikunth, Phase 2, Vrindavan - 281121 U. P.', '281121', 'active', 'file_69956ca2b63a61.38954634.jfif', NULL, NULL, '', NULL, NULL, '', 'GHDAF82657', 'approved', '2027-02-16', 'member', 1, '2026-02-16 06:56:01', '', NULL, NULL, '2026-02-16'),
(261, 'Adnan Sayed', 'sayedaddy86@gmail.com', '$2y$10$ixWzKFVZQUmetnJAb8q1D.jBRnZWadW0NF0g3z7ezFyLCeiOzFz/u', '9082781083', 'Male', '2002-01-11', 'S/O', 'shahbuddin', '', 'Active Member', 'B+', '510241180733', 'Maharashtra', 'Mumbai City', 'Sanjivani chs 55/403 pmgp colony near mercury school mankhurd west 400043', '400043', 'active', 'file_69940174466889.96314710.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/67904', 'approved', '2027-02-16', 'member', 1, '2026-02-17 05:49:40', '', NULL, NULL, '2026-02-16'),
(262, 'VISHNU SOPANRAO CHAPKE', 'vishnu@gmail.com', '$2y$10$UBZ6vOVZskfo2QLxYEfIFOr/bZOZSYGn5nRGGJmdsFhQkKzg2eInO', '9881524491', 'Male', '1982-06-12', 'W/O', 'VISHNU CHAPKE', '', 'Active Member', 'AB+', '512582786709', 'Maharashtra', 'Parbhani', 'BUILDING NO 33 A BRAHMA VISHNU APARTMENT KAREGAON,PARBHANI,MAHARASHTRA 431402', '431402', 'active', 'file_699551ac596d20.57209631.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/19545', 'approved', '2027-02-17', 'member', 1, '2026-02-18 05:44:12', '', NULL, NULL, '2026-02-17'),
(263, 'MAHENDRA SINGH', 'mahendrasingh@gmail.com', '$2y$10$ViNP9gScbwXsziQIGXcySOiFaQvECV7VwO/IR7dcewHqf/nnNtU3i', '8923477475', 'Male', '1979-02-02', 'S/O', 'MAAN', '', 'Active Member', 'A+', '356759276425', 'Uttarakhand', 'Champawat', 'KON BHUMWARI,CHAMPAWAT,BHUMBARI,UTTARKHAND,262528', '262528', 'active', 'file_699554dbd78ad7.39895029.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/41869', 'approved', '2027-02-17', 'member', 1, '2026-02-18 05:57:47', '', NULL, NULL, '2026-02-17'),
(264, 'KRISHAN KUMAR', 'krishan@gmail.com', '$2y$10$MHF5avGOItK1UbkAXyAvZO0H7Fty2eY/Ct2NatiWerk.exZNzbtAO', '9815037587', 'Male', '1977-02-06', 'S/O', 'RAKHA RAM', '', 'Active Member', 'B+', '991625845866', 'Punjab', 'Patiala', 'H NO 81/1,AMAMGARH,SAMANA,PATIALA,PUNJAB,147101', '147101', 'active', 'file_69955a28af5b86.60824811.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/22880', 'approved', '2027-02-18', 'member', 1, '2026-02-18 06:07:51', '', NULL, NULL, '2026-02-18'),
(265, 'NAZIR AHMAD WANI', 'Sahilnazirkps@gmail.com', '$2y$10$IQKhivLLyMoMgbGjXw8uIukJ7Rv/sRoo8JuWyzPn7SxCIuTIeN3VG', '9906845504', 'Male', '1988-05-14', 'S/O', 'AB AZIZ WANI', '', 'Active Member', 'O+', '429747916181', 'Jammu and Kashmir', 'Jammu', 'SINGH PORA BARAMULA,JAMMU AND KASHMIR 193121', '193121', 'active', 'file_6995597ab2c241.66526043.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/56861', 'approved', '2027-02-18', 'member', 1, '2026-02-18 06:17:30', '', NULL, NULL, '2026-02-18'),
(266, 'Riyaz ahmad mugloo', 'Riyazhighway07@gmail.com', '$2y$10$ol4jNVVlSUV.PQyYYSsK9OIRi62g5ZBAecYfGr1Ai6JC97SYy28a6', '9906641484', 'Male', '1977-12-07', 'S/O', 'Gh mohd mugloo', '', 'Active Member', 'A+', '240301181625', 'Jammu and Kashmir', 'Baramulla', 'GANAI HAMAM,BARAMULA,JAMMU AND KASHMIR,193101', '193101', 'active', 'file_69956239aa8b53.45808233.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/86571', 'approved', '2027-02-18', 'member', 1, '2026-02-18 06:54:49', '', NULL, NULL, '2026-02-18'),
(267, 'MOHAMMAD NASIR MOHAMMAD', 'nasirtagale@gmail.com', '$2y$10$TrESKw.3dQhpJHerv2fOp.qoPEA.HqXJSdMpkm4Fse8IJ7R4N1BqG', '9822651763', 'Male', '1974-02-02', 'S/O', 'MOHAMMAD YASIN', '', 'Active Member', '', '366723441026', 'Maharashtra', 'Nanded', 'TAGALE,MOMINPURA,NEAR URDU SCHOOL,TQ KINWAT,NANDED,MAHARASHTRA,431804', '431804', 'active', 'file_699563c232d163.31818585.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/26453', 'approved', '2027-02-18', 'member', 1, '2026-02-18 07:01:22', '', NULL, NULL, '2026-02-18'),
(268, 'Samar Mandal', 'newsno1bhutniallindiadigital@gmail.com', '$2y$10$ovUYholDJEut3Pz3WaWmz.NDbkxEQp0807sKdm6vSYWN05G5Z/k4C', '8170855907', 'Male', '1993-03-04', 'S/O', 'BALLI', '', 'Active Member', 'O+', '231181757143', 'West Bengal', 'Malda', 'Po - BHUTNI Ps-Bhutni district -Malda state West Bengal Kolkata country India', '732203', 'active', 'file_699566250112d2.41589380.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/13434', 'approved', '2026-02-18', 'member', 1, '2026-02-18 07:11:33', '', NULL, NULL, '2026-02-18'),
(269, 'MUBASHIR HASSAN', 'Truthofkashmirnewsnetwork@gmail.com', '$2y$10$aDNNO54PN/Ex5BrdA7GMoOzsVayZ8Fdd7lJ1qCTvjX8NDPzXSCaBa', '4516346465', 'Male', '1980-03-03', 'S/O', 'SHIEKH GH HASSAN', '', 'Active Member', 'O+', '386442998690', 'Jammu and Kashmir', 'Pulwama', 'DADASRA,BATIPORA,DADH SARA,PULWAMA,JAMMU AND KASHMIR 192123', '192123', 'active', 'file_699568f42e72e2.35064257.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/21965', 'approved', '2027-02-18', 'member', 1, '2026-02-18 07:23:32', '', NULL, NULL, '2026-02-18'),
(270, 'RAJIB DUTTA', 'rajibduttaphotography@gmail.com', '$2y$10$ronKTjp0D7wCFB6IuSs3Rupo3OogoSEmuLS80PF1M2Mq2gDfqYW7i', '7005375046', 'Male', '1991-02-09', 'S/O', 'NIRU DUTTA', '', 'Active Member', 'O+', '283283930246', 'Nagaland', 'Dimapur', 'A.G. SCHOOL ROAD,BANK COLONY,WARD 5,DIMAPUR,NAGALAND 797112', '797112', 'active', 'file_69956bb7d4a957.54287147.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/31773', 'approved', '2027-02-18', 'member', 1, '2026-02-18 07:35:19', '', NULL, NULL, '2026-02-18'),
(271, 'WELSI NALLARATHNAM J', 'rathnam2014@gmail.com', '$2y$10$Vdl4f3iXwQLGOPyOXzDa5u25CXYsQaoYCq9GDUfA.LZ4nD6WEmL6q', '1234526265', 'Male', '1966-05-18', 'S/O', 'BHUSAN', '', 'Active Member', 'B+', '931093324223', 'Tamil Nadu', 'Cuddalore', 'gandhi nagar sithera road,mel bhubangiri,cuddalore tamilnadu,608601', '608601', 'active', 'file_699940df7bc110.96472044.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/48765', 'approved', '2027-02-20', 'member', 1, '2026-02-21 05:21:35', '', NULL, NULL, '2026-02-20'),
(272, 'KRISHNAKANT', 'rajjaiswal.g.life@gmail.com', '$2y$10$0P6EI6D4RADwWdLSTveKieSlV5q9Eb3dWxssTO.icYpPxbZCzOMUS', '8965804268', 'Male', '1987-06-14', 'S/O', 'Punamchand jaiswal', '', 'Active Member', 'O+', '908479018609', 'Madhya Pradesh', 'Dhar', 'RAMKHENDA,DHAR,MADHYA PARADESH,454660', '454660', 'active', 'file_699be3ccc07766.25887570.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/74438', 'approved', '2027-02-22', 'member', 1, '2026-02-23 05:21:16', '', NULL, NULL, '2026-02-22'),
(273, 'RAJ KUMAR SHARMA', 'rajkumar@gmail.com', '$2y$10$9vxnI0OaeiafKZRuAZjO/uk/TxwkM9gzy4pupvhzXTFIjSWWsM7De', '9211231599', 'Male', '1971-01-15', 'S/O', 'MUNSHI LAL SHARMA', '', 'Active Member', 'A+', '788933273636', 'Uttar Pradesh', 'Hapur', 'sakti nagar swarg ashram road,hapur,uttar pradesh,245101', '245101', 'active', 'file_699be9fcbff8b5.31506081.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/14651', 'approved', '2027-02-22', 'member', 1, '2026-02-23 05:47:40', '', NULL, NULL, '2026-02-22'),
(274, 'SAMIN SEKH', '', '$2y$10$Jy5RAiMpaULJi5ZbDBw2Fup1OQGHBRa6J67jUaPALSN.dV0UJCenu', '9333735424', 'Male', '1986-01-01', 'S/O', 'Sk Muhammad Ali', '', 'Active Member', 'B+', '596370417343', 'West Bengal', 'Birbhum', 'TANTKHAND,BARSHUL,BARDDHAMAN,WEST BENGAL,713124', '713124', 'active', 'file_699bebfe348ae0.14391510.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/97230', 'approved', '2027-02-22', 'member', 1, '2026-02-23 05:56:14', '', NULL, NULL, '2026-02-22'),
(275, 'SUNITI LASKAR', 'suniti@gmail.com', '$2y$10$yhQ0yONjn2yFjRW.UVwacuDej8eb8sXMkHuSJV92TUqKAK4sKqy06', '9678435101', 'Male', '1972-02-29', 'S/O', 'ISWAR', '', 'Active Member', 'O+', '419485387822', 'Assam', 'Kamrup', 'PARALIGURI,KAMPUR,NAGAON,ASSAM,782426', '782426', 'active', 'file_699d33efd580a5.34647027.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/54532', 'approved', '2027-02-24', 'member', 1, '2026-02-24 05:15:27', '', NULL, NULL, '2026-02-24'),
(276, 'DR. PATEL, HIRENBHAI. MAYUR BHAI', 'anitaupatel778@gmail.com', '$2y$10$2a/qh147qqT7/4nuErALFesGS3eNz49FtknLlO2y1ICmM3IJ/iGxy', '9998485785', 'Male', '1985-04-25', 'S/O', 'SANWALA', '', 'Active Member', 'O+', '980769451976', 'Gujarat', 'Anand', 'A 34 nilkanth tenement, near by shathiya party plots. Av roda. Anand... 388001', '388001', 'active', 'file_699d371e16be57.83918071.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/49654', 'approved', '2026-02-24', 'member', 1, '2026-02-24 05:29:02', '', NULL, NULL, '2026-02-24'),
(277, 'Rambabu chouhan', 'rambabuchouhan@gmail.com', '$2y$10$8owDPaLe31HoLy.ZlvWeZuXD93Yonaa1HNBnSF5X6KXOkcP1CrmwW', '9907900491', 'Male', '1981-03-11', 'S/O', 'BHUSAN', '', 'Active Member', 'O+', '592799321375', 'Madhya Pradesh', 'Rajgarh', 'ward no. 11 Masjid ke pas chhapiheda, \r\n‎Thashil:-Khilchipur\r\n‎Disti:-Rajgarh mp ‎Pin-465689', '465689', 'active', 'file_699d393570e7a1.34655691.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/86018', 'approved', '2027-02-24', 'member', 1, '2026-02-24 05:37:57', '', NULL, NULL, '2026-02-24'),
(278, 'Ekanath Dnyandev Kamble', 'eknath.kamble40@gmail.com', '$2y$10$q4MT7gTd4/ZqUalNiqNH0OvvqyjUPkOC2ehdwO5hMlhdF.YJxvqZ6', '9763340370', 'Male', '1973-06-06', 'S/O', 'kanari', '', 'Active Member', 'A+', '336059006896', 'Maharashtra', 'Sangli', 'A/p Kille Mochnider Gad\r\nTal - Walwa Dist-sangli Pin 415302', '415302', 'active', 'file_699d3a8baab130.91494315.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/46661', 'approved', '2026-02-24', 'member', 1, '2026-02-24 05:43:39', '', NULL, NULL, '2026-02-24'),
(279, 'Prof. Dr. Chintamani Rathore', 'chintamanir1962@gmail.com', '$2y$10$d0skDWKXvPFmRzTcIHUz0uML0UwuWL5wu11t6XBHbKbpPADw62AYi', '8871917502', 'Male', '1962-04-03', 'S/O', 'Shri Moolchand Ji Rathore', '', 'Active Member', 'A+', '617168108677', 'Madhya Pradesh', 'Ujjain', '15, Tatya Tope Marg, Freeganj\r\nUjjain, Madhya Pradesh – 456010', '456010', 'active', 'file_699fda7d406597.14098039.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/77301', 'approved', '2027-02-26', 'member', 1, '2026-02-26 05:30:37', '', NULL, NULL, '2026-02-26'),
(280, 'SUSHIL KUMAR', 'sushilsharma3200@gmail.com', '$2y$10$jwB/yPi8AIAEalHvpfu0bOcswMlq6eGJpFb6u5GjRtbr4aBbYiG5C', '8168223200', 'Male', '1980-02-02', 'S/O', 'SANWALA', '', 'Active Member', 'A+', '778060194790', 'Haryana', 'Palwal', '358/2,MOHAN ,NAGAR ,NEAR RAILYWAY SATATION,PALWAL,121102', '121102', 'active', 'file_69a286da29d093.01472439.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/01979', 'approved', '2027-02-28', 'member', 1, '2026-02-28 06:10:34', '', NULL, NULL, '2026-02-28'),
(281, 'Md Firoz Alam', 'Katihartodaysnews@gmail.com', '$2y$10$vxqcMkFxLD4l4uHvdIjOv.d2L.LYC2qP51Cch3SnK06w9JdqgvHSS', '9199563931', 'Male', '1992-10-16', 'S/O', 'kanari', '', 'Active Member', 'AB+', '719582708995', 'Bihar', 'Katihar', ': S/O Md. Samsul, Village - Nirpur,\r\nPost Panikanta, Andabad,\r\nKatihar, Bihar, 854112', '854112', 'active', 'file_69ad2662bdc755.15088537.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/53774', 'approved', '2027-03-09', 'member', 1, '2026-03-08 07:33:54', '', NULL, NULL, '2026-03-09'),
(282, 'Pankaj Kumar Jain', 'pankaj@gmail.com', '$2y$10$q9B0DNlPY25S478fByo8oONcPn.eXniBbs2zz0T101N3iXi5ia9re', '6376976234', 'Male', '1980-03-22', 'W/O', 'SANWALA', '', 'Active Member', 'AB+', '725186715375', 'Rajasthan', 'Udaipur', '14, Bajrang Marg,\r\nOutside Chandpole, Ginwa,\r\nUdaipur, Rajasthan – 313001', '313001', 'active', 'file_69afbabd93b759.41712959.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/98358', 'approved', '2027-03-10', 'member', 1, '2026-03-10 06:31:25', '', NULL, NULL, '2026-03-10'),
(283, 'Dr.sakaldip paswan', 'sakaldip@gmail.com', '$2y$10$JE7SkM7uyCfi3npofLGjDuxvzw31Kb2zigyGlR0xUo4C0c.9AWk5y', '9931642703', 'Male', '1985-02-06', 'S/O', 'kali paswan', '', 'Active Member', 'B+', '638719956038', 'Bihar', 'Jamui', 'illage jit jhingoi, post office, jeet jhingoi, p.s khaira, district jamui, bihar, pin code 811317', '811317', 'active', 'file_69b13739e85d91.81991740.jfif', NULL, NULL, '', NULL, NULL, '', 'PCWWF/21933', 'approved', '2027-03-11', 'member', 1, '2026-03-11 09:34:49', '', NULL, NULL, '2026-03-11'),
(284, 'Rakesh Yadav', 'rakeshrakesh99476@gmail.com', '$2y$10$0iuRn7rUP91eNHyAXhNFmenAWDe/FEOyxrrN1ApDp7AeAzIaLR26u', '8269428644', 'Male', '1980-06-20', 'S/O', 'Shri Ramdulare Yadav', '', 'Active Member', 'B+', '245320104627', 'Madhya Pradesh', 'Indore', 'Jain mandir ke piche, 15/A,\r\nSukhliya, Indore, Indore,\r\nMadhya Pradesh - 452010', '452010', 'active', 'file_69b266f479f267.31903177.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF88238', 'approved', '2027-03-12', 'member', 1, '2026-03-12 07:10:44', '', NULL, NULL, '2026-03-12'),
(285, 'Suresh Sharma', 'sureshsharma@gmail.com', '$2y$10$OoRd3H/4Cs3hJpSqPk92CuSMFw0h68T5qKTyXEAG4JmPSW08PAKCG', '9829051637', 'Male', '1953-01-02', 'S/O', 'kanari', '', 'Active Member', 'A+', '858851329221', 'Rajasthan', 'Jaipur', '181\r\nHeera Nagar\r\nDOM Ajmer Road\r\nJaipur\r\nHeerapura Jaipur Jaipur\r\nRajasthan 302021', '302021', 'active', 'file_69b531298e1e82.44292253.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF25035', 'approved', '2027-03-14', 'member', 1, '2026-03-14 09:58:01', '', NULL, NULL, '2026-03-14'),
(286, 'MADANJEET SINGH', 'madanjeet@gmail.com', '$2y$10$JQMT4nyY9XlsSwDLuBIw4e2r0WsULvSoOFekyDZWngPrHXm.Kd6O.', '9915327633', 'Male', '1953-11-05', 'S/O', 'BHAGWANT RAM', '', 'Active Member', '', '534781973074', 'Punjab', 'Sahibzada Ajit Singh Nagar', 'SHIVALIK HOMES,SECTOR 127,KHARAR,SAS NAGAR,PUNJAB,140301', '140301', 'active', 'file_69ba8d251bb520.53697871.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF17822', 'approved', '2027-03-18', 'member', 1, '2026-03-18 11:31:49', '', NULL, NULL, '2026-03-18'),
(287, 'BALJINDER SINGH', 'Ubsfilms@gmail.com', '$2y$10$ub9jPHl8xRQO79HOusLHVeC0iGwvEZA4.tGNgiP1eD263BB0MOzpu', '9914740606', 'Male', '1966-12-10', 'S/O', 'BHAG SINGH', '', 'Active Member', 'B+', '806614056283', 'Punjab', 'Ludhiana', 'HOUSE NO 13227/1,GOPAL NAGAR,WARD NO 8 TIBBA ROAD,BASTI JODHEWAL,LUDHIANA', '141007', 'active', 'file_69ba8f9cc64152.22339904.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF26871', 'approved', '2027-03-18', 'member', 1, '2026-03-18 11:42:20', '', NULL, NULL, '2026-03-18'),
(288, 'VIMAL TRIPATHI', 'vimal@gmail.com', '$2y$10$wxxIRAq/TIz.Svig6Kwk9OFft69OxI/tq/4hrcmfK1UAKsqcBIEFW', '9425179351', 'Male', '1991-09-30', 'S/O', 'OM PRAKASH TRIPATHI', '', 'Active Member', 'O+', '239645369749', 'Madhya Pradesh', 'Sidhi', 'MAKAN NO.265,GRAAM-RAMPUR,SIDHI,MADHYA PRADESH 486661', '486661', 'active', 'file_69bb8a486f1179.72772419.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF03000', 'approved', '2027-03-18', 'member', 1, '2026-03-19 05:31:52', '', NULL, NULL, '2026-03-18'),
(289, 'RAMESH DAS', 'ramesh@gmail.com', '$2y$10$NHwxvYbnEH6nBNjt6IixL.1UZRxC1FBXg22K5NWI69VuVq.BSPe5K', '9394199433', 'Male', '1965-08-02', 'S/O', 'PARAMESWAR DAS', '', 'Active Member', 'B+', '888344854848', 'Assam', 'Barpeta', 'SARUGATI,CHAMUA GATI,BAKSA,SARUPETA,ASSAM,781318', '781318', 'active', 'file_69bb8c82135a75.41964877.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF64534', 'approved', '2027-03-18', 'member', 1, '2026-03-19 05:41:22', '', NULL, NULL, '2026-03-18'),
(290, 'Shinde ravikumar Pradip', 'ravirajshinde2222@gmail.com', '$2y$10$Q71Q2Lkwb0gLhZEPHafa5OSwZ9pNFIlQa7tV6OUA0r2Fa09AyW5S6', '7057248811', 'Male', '1991-07-21', 'S/O', 'Pradip shinde', 'Farmer', 'Block Coordinator', 'A+', '668624618065', 'Maharashtra', 'Ahmednagar', 'At post dhondpargon \r\nJamkhed ahiyanargar', '413205', 'block', 'file_69bc962bbcedc8.25564175.png', 'file_69bc962bbd5500.42794988.jpg', 'file_69bc962bbe7254.04232282.jpg', NULL, NULL, 'file_69bc962bbea8c8.62708694.jpg', 'offline', 'PCWWF/76283', 'pending', NULL, 'member', NULL, '2026-03-20 00:34:51', 'Jamkhed', NULL, NULL, '2026-03-20'),
(291, 'AHSAN AHMAD ANSARI', 'ansari0107@gmail.com', '$2y$10$JNoUj6jEhetMiuIIAck9Bex9u6i9gSTYirwItXrKaUZnUY7z0NrBm', '9926393786', 'Male', '1966-07-02', 'S/O', 'Suleman Ansari', '', 'Active Member', 'O+', '512861462545', 'Madhya Pradesh', 'Jabalpur', 'House No 42,\r\nJawanra Ward No 05,\r\nTensil Sinora, Sihora,\r\nShahpura, Jabalpur\r\nMadhya Pradesh - 483225', '483225', 'active', 'file_69c2645948bb26.56799470.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF82450', 'approved', '2027-03-24', 'member', 1, '2026-03-24 10:15:53', '', NULL, NULL, '2026-03-24'),
(292, 'Afiqa Ali', 'Afiqaali@gmail.com', '$2y$10$pZ1COGgsPQddpTP88viO6exqV66Rvw6LoDmPJGaszXjQ5NQ9AOeZe', '7006708054', 'Female', '1980-04-04', 'S/O', 'SAYED ABDUL', '', 'Active Member', 'A+', '644774052616', 'Jammu and Kashmir', 'Budgam', 'CHEAK NO 1,BADRI NATH,BADGAM,JAMMU AND KASHMIR,19113', '191113', 'active', 'file_69c61b7e768df4.78618223.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF41831', 'approved', '2027-03-24', 'member', 1, '2026-03-24 10:51:30', '', NULL, NULL, '2026-03-24'),
(293, 'Mukund Shankar Kamble', 'shankar@gmail.com', '$2y$10$H.nyVLZQf36xJmfpagvtZu/a/HGhB6l6n7l5dB8CyKQb6KGlKbtcq', '9819678813', 'Male', '1974-03-26', 'S/O', 'Shankar Kamble', '', 'Active Member', 'O+', '511341515376', 'Maharashtra', 'Raigad', 'A Type Sector-13, Chawl No.13, Room no.8,,\r\nVTC: New Panvel,\r\nDistrict: Raigarh,\r\nState: Maharashtra,\r\nPIN Code: 410206,', '410206', 'active', 'file_69c27238a516c2.25030240.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF42114', 'approved', '2027-03-24', 'member', 1, '2026-03-24 11:15:04', '', NULL, NULL, '2026-03-24'),
(294, 'RADHE KRISHAN VERMA', 'radhe@gmail.com', '$2y$10$jRL62eC78dFcotdkCsalk.5dwpowaaqMjNh3GVUYcqckUI9u7aZju', '9418944209', 'Male', '1952-02-06', 'S/O', 'CHHOTA RAM', '', 'Active Member', '', '620223787953', 'Himachal Pradesh', 'Mandi', 'POST OFFICE PANDOH TEHSIL SADAR MANDI,HIMANCHAL PRADESH,175124', '175124', 'active', 'file_69c273c7e35857.87278489.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF77581', 'approved', '2026-03-24', 'member', 1, '2026-03-24 11:21:43', '', NULL, NULL, '2026-03-24'),
(295, 'Ghulam Mohd Lone', 'ghulam@gmail.com', '$2y$10$VD1sCTKvgssZzUkZwXzAXOkHBPpHr/sEe6qs8hbnUSblD4PUXfXP.', '9149525881', 'Male', '1978-02-21', 'S/O', 'Abdul Salam lone', '', 'Active Member', 'O+', '730642723297', 'Jammu and Kashmir', 'Pulwama', 'PULWAMA JAMMU AND KASHMIR,192301', '192301', 'active', 'file_69c276c3cc84a2.05988935.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF16660', 'approved', '2027-03-24', 'member', 1, '2026-03-24 11:34:27', '', NULL, NULL, '2026-03-24'),
(296, 'VIKRAM SINGH', 'singhjournalistvikram@gmail.com', '$2y$10$G1NXEXRW.woWfd3.ivMHme.HQ7XAtlJiJuNPEQWO/d.vUUADBE1qu', '9468091563', 'Male', '1977-10-28', 'S/O', 'HANS RAJ', '', 'Active Member', 'B+', '634969347076', 'Haryana', 'Yamunanagar', '208,CHITTA MANDIR ROAD,MADHU COLONY,JAGADHRI,YAMUNA NAGAR,HARYANA,135001', '135001', 'active', 'file_69c6117d4fc942.95299528.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF74787', 'approved', '2027-03-26', 'member', 1, '2026-03-27 05:11:25', '', NULL, NULL, '2026-03-26'),
(297, 'Mohammad Aslam Khan', 'aslamkhan64nov@gmail.com', '$2y$10$WHKsRQ3X14cObp8KJfk.xeRVegYr1InbqQT8wK5GCwCRecbRAOUcC', '9052825410', 'Male', '1964-11-28', 'S/O', 'MOHD KHAN', '', 'Active Member', 'AB+', '988922069409', 'Telangana', 'Hyderabad', '5-4-115,MURLIDHAR BAGH,ABIDS,NAMPALLY,HYDERABAD,ANDRA PADESH,500001', '500001', 'active', 'file_69c613ea91cc65.74667455.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF55157', 'approved', '2027-03-26', 'member', 1, '2026-03-27 05:21:46', '', NULL, NULL, '2026-03-26'),
(298, 'BISWAJEET MOHAPATRA', 'biswajeetmohapatra510@gmail.com', '$2y$10$KMfena4.TAuUYpGi5/NW/e4ynqVS1k2WpukDSxZIGm5hCXn4ZX08a', '7978698515', 'Male', '1982-09-08', 'S/O', 'BRUNDHABAN MOHAPATRA', '', 'Active Member', 'AB+', '806587376500', 'Odisha', 'Cuttack', 'QTR NO 7,MARRIED NURSING,HOSTEL,SCB MEDICAL COLLEGE CAMPUS,CUTTACK SARDAR,MEDICAL,COLLEGE,ODISHA,753007', '573007', 'active', 'file_69c6169472fd86.04827110.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF81776', 'approved', '2027-03-26', 'member', 1, '2026-03-27 05:33:08', '', NULL, NULL, '2026-03-26'),
(299, 'DIXIT KISHORKUMAR SHRIDHAR', 'Kishordixit0007@gmail.com', '$2y$10$2qlwk19JxF/ihNbSetitEu3S1f97nL1pFi5.wb7pr1PL8ocoWo9i.', '9822280533', 'Male', '1970-11-16', 'S/O', 'SHRI NIRMOL', '', 'Active Member', 'O+', '256736314057', 'Maharashtra', 'Nashik', 'SHIVAJI NAGAR CHINCHKHED ROAD PIMPALGAON BASWANT,MAHARASTRA,422209', '422209', 'active', 'file_69c6194a8eb807.34856190.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF15748', 'approved', '2027-03-26', 'coordinator', 1, '2026-03-27 05:44:42', '', NULL, NULL, '2026-03-26'),
(300, 'Shiv Kumar Baghel', 'shiv620@gmail.com', '$2y$10$N9c8GMLEmXKFEmU4heiXzutSaWf2GFNFPfFoyqELzhgptgSTtVq2m', '6266482819', 'Male', '1966-08-26', 'S/O', 'Bhukhan Lal Baghel', '', 'Active Member', 'B+', '451806397559', 'Chhattisgarh', 'Bilaspur', 'ward no-5.\r\nbaniya para,\r\nratanpur,\r\nVTC: Ratanpur (NP),\r\nPO: Ratanpur,\r\nSub District: Kota\r\nDistrict: Bilaspor,\r\nState: Chhanisgarh,\r\nPIN Code: 495442.', '495442', 'active', 'file_69c61ad766d103.51782155.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF71636', 'approved', '2027-03-26', 'member', 1, '2026-03-27 05:51:19', '', NULL, NULL, '2026-03-26'),
(301, 'RITIK KUMAR', 'ritikkumarasdfg@gmail.com', '$2y$10$IxxPPTosr2ZYHXnMQMR1B.EipRavBj9evAfzEWdZeyt2ryUkOrg2K', '9696136462', 'Male', '2005-07-15', 'S/O', 'kanari', '', 'District President', 'B+', '800797676886', 'Uttar Pradesh', 'Jalaun', 'VILL DIKAULI JAGIR POST JAYGHA JALAUN U.P 285124', '285124', 'district', 'file_69ca095c932525.05755154.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF88255', 'approved', '2027-03-29', 'coordinator', 1, '2026-03-30 05:25:48', '', NULL, NULL, '2026-03-29'),
(302, 'Alok Kumar', 'alokk8019@gmail.com', '$2y$10$SlUGNz9Rz/cX5s3s3mAQ1e6KU5Sur29ZMNUdwgKcVCvB1VPIjd2Mq', '9308797732', 'Male', '1970-01-06', 'S/O', 'SANWALA', '', 'Active Member', 'O+', '579942271179', 'Bihar', 'Patna', 'Near Durga Mata Mandir,\r\nChandmari Road, Road No. 5,\r\nKankarbagh, Patna,\r\nBihar – 800020', '800020', 'active', 'file_69ca0defadd979.33598835.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF87928', 'approved', '2027-03-29', 'member', 1, '2026-03-30 05:45:19', '', NULL, NULL, '2026-03-29');
INSERT INTO `users` (`id`, `name`, `email`, `password`, `mobile`, `gender`, `dob`, `sdw_type`, `sdw_name`, `profession`, `designation`, `blood_group`, `aadhar`, `state`, `district`, `address`, `pincode`, `membership_type`, `profile_image`, `aadhar_front`, `aadhar_back`, `payment_id`, `order_id`, `payment_proof`, `payment_method`, `registration_id`, `status`, `valid_until`, `user_type`, `created_by`, `created_at`, `working_area`, `id_card_photo`, `updated_at`, `valid_from`) VALUES
(303, 'MOHAMMEDRAFEEQ', 'rafeeq2160@gmail.com', '$2y$10$rP5zXSw.6GptXAvJ7ZWJbuw0aHUwfvH6MHESRR69O9mkiOqBK6IxG', '9622166339', 'Male', '1985-06-02', 'S/O', 'kanari', '', 'Active Member', 'B+', '277924810190', 'Karnataka', 'Vijayapura', 'WARD NO 15, BHARPET GALLI,BEHIND KAZI HOUSE INDI,586209', '586209', 'active', 'file_69ca1256ade260.95630106.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF83486', 'approved', '2027-03-30', 'member', 1, '2026-03-30 06:04:06', '', NULL, NULL, '2026-03-30'),
(304, 'VIJAY KUMAR JAISWAL', 'jaiswalvijayshaab1@gmail.com', '$2y$10$fr5cDW12cZZFD/Z3/z4e0O70iM8/K31jxpe8.ltqLUr/ljA7ku6Nu', '9983509006', 'Male', '1969-01-16', 'S/O', 'kanari', '', 'Active Member', 'B+', '709306769687', 'Rajasthan', 'Jaipur', 'G-2 6-D, ENGINEERS COLONY, JAIPUR,RAJASTHAN,302020', '302020', 'active', 'file_69ca146961b665.03521471.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF08294', 'approved', '2027-03-30', 'member', 1, '2026-03-30 06:12:57', '', NULL, NULL, '2026-03-30'),
(305, 'NILAMCHANDR NARAYAN WAKCHAURE', 'wackchourenilamchandra@gmail.com', '$2y$10$0komoPAVUQeCCl0i1Sk.AOpSvztrBwYwKThQdhowoJ.GgAKHbn8Wy', '7448154587', 'Male', '1969-01-02', 'S/O', 'kanari', '', 'Active Member', 'B+', '674437793539', 'Maharashtra', 'Nashik', 'BHAJI BAZAR,NASHIK VES,SINNAR,MAHARASHTRA,422103', '422103', 'active', 'file_69ca1738170e55.51499356.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF69367', 'approved', '2027-03-30', 'coordinator', 1, '2026-03-30 06:24:56', '', NULL, NULL, '2026-03-30'),
(306, 'SATENDRA RAI', 'satendrarairai3@gmail.com', '$2y$10$PxnH6GveFsDHt9h7DZh7BekKkhxtP/S/vXwhjZl96hzAj5KOmCP0a', '9621631540', 'Male', '1994-08-10', 'S/O', 'SANWALA', '', 'Active Member', 'O+', '775491377428', 'Uttar Pradesh', 'Jhansi', 'MAIN MARKET KATERA RURAL,JHANSI UTTAR PRADESH,284205', '284205', 'active', 'file_69ca188e609528.25237267.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF43575', 'approved', '2027-03-30', 'member', 1, '2026-03-30 06:30:38', '', NULL, NULL, '2026-03-30'),
(307, 'ASHWINI ABHIJEET KULKARNI', 'Ashwinikulkarni303@gmail.com', '$2y$10$WAwylCkHCs669XlJ3kqCcuAYpqtWuji4OnWIr4L9jwIo/k6QELxQG', '8308592157', 'Female', '1983-10-10', 'S/O', 'SANWALA', '', 'Active Member', 'AB+', '805650193338', 'Maharashtra', 'Pune', 'SURVEY NO,18/1/2,INDRAYANI COLONY,WARJE JAKAT NAKA,PUNE CITY,MAHARASHTRA,411058', '411058', 'active', 'file_69ccb9588892d5.97540189.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF13726', 'approved', '2027-04-01', 'member', 1, '2026-04-01 06:21:12', '', NULL, NULL, '2026-04-01'),
(308, 'Uttam Maheswari k', 'uttam@gmail.com', '$2y$10$OTPuUgWMRi7Kh76wr4IJHe3.oTCra6pJ98AXXIwUPHuKtsa5iV7Ke', '9166820731', 'Male', '1970-02-05', 'S/O', 'kanari', '', 'Active Member', 'O+', '712017442886', 'Rajasthan', 'Jodhpur', 'NEAR MAHESWARIKAST NEAR AGRSEN BHAWAN 1B 13 1ST PULIYA,CHOPSNI HOUSING BORD JODHPUR,RAJASTHAN,342008', '342008', 'active', 'file_69cccf66189757.08842006.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF42346', 'approved', '2027-04-01', 'member', 1, '2026-04-01 07:55:18', '', NULL, NULL, '2026-04-01'),
(309, 'LAJPAT RAI', 'lajpat@gmail.com', '$2y$10$d7bWgMUz7VbuZhSYHZqqcub.HL50PJlTpYVIFMVdnknCbxsTBpGNO', '8950180406', 'Male', '1976-02-12', 'S/O', 'kanari', '', 'Active Member', 'O+', '761287263571', 'Haryana', 'Kaithal', '497,ward no 14,kaithal,hariyana,136027', '136027', 'active', 'file_69d09c403038e7.46783047.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF09833', 'approved', '2027-04-03', 'coordinator', 1, '2026-04-04 05:06:08', '', NULL, NULL, '2026-04-03'),
(310, 'MAHABOOB ALI ADVANI', 'maadvani@gmail.com', '$2y$10$esTULnsyAcjnvtimYlVeWeaccu17sZ/1dpAASplUgXcgRgePRuHxG', '9844313494', 'Male', '1977-01-07', 'S/O', 'kanari', '', 'Active Member', 'B+', '729026399200', 'Karnataka', 'Davangere', '3rd cross behind church harihar davargere karnataka 577601', '577601', 'active', 'file_69d5e311a39c28.70365372.jfif', NULL, NULL, '', NULL, NULL, '', 'WPEWF79755', 'approved', '2027-04-07', 'member', 1, '2026-04-08 05:09:37', '', NULL, NULL, '2026-04-07');

-- --------------------------------------------------------

--
-- Table structure for table `venues_list`
--

CREATE TABLE `venues_list` (
  `id` int(11) NOT NULL,
  `venue_name` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `venues_list`
--

INSERT INTO `venues_list` (`id`, `venue_name`, `status`, `created_at`) VALUES
(1, 'GANJAM KALA PARISHAD, GANJAM, BERHAMPUR, GANJAM, ODISHA / 29TH DEC 2025', 'active', '2025-12-09 10:39:53'),
(2, 'ANNE CENTENARY LIBRARY GANDHI MAHDAPAM RD, SURYA NAGAR , KOTTURPURAM, CHENNAI, TAMIL NADU DATE- 12TH JANUARY 2026', 'active', '2025-12-09 10:57:05'),
(3, 'JAYADEV BHAVAN BHUBANESWAR ODISHA', 'active', '2026-01-08 11:01:41'),
(4, 'Volagiri Kalamandir 107,Netaji Subhas Road, Kadamtala, Howrah', 'active', '2026-01-08 11:02:04');

-- --------------------------------------------------------

--
-- Table structure for table `why_choose_us`
--

CREATE TABLE `why_choose_us` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(100) DEFAULT 'fas fa-check-circle',
  `sort_order` int(11) NOT NULL DEFAULT 1,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `youtube_videos`
--

CREATE TABLE `youtube_videos` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `video_id` varchar(50) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `award_letters`
--
ALTER TABLE `award_letters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `letter_no` (`letter_no`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `award_letters`
--
ALTER TABLE `award_letters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=311;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
