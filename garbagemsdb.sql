-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2025 at 10:13 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `garbagemsdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `payment_invoice`
--

CREATE TABLE `payment_invoice` (
  `id` int(11) NOT NULL,
  `ComplainID` int(10) NOT NULL,
  `User_name` varchar(100) DEFAULT NULL,
  `Paymentmode` varchar(50) DEFAULT NULL,
  `No_of_items` int(11) DEFAULT NULL,
  `Scrap_name` varchar(100) DEFAULT NULL,
  `Fixed_rate` decimal(10,2) DEFAULT NULL,
  `Kg` decimal(10,2) DEFAULT NULL,
  `Total` decimal(10,2) DEFAULT NULL,
  `Remark` text DEFAULT NULL,
  `PaymentDateTime` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_invoice`
--

INSERT INTO `payment_invoice` (`id`, `ComplainID`, `User_name`, `Paymentmode`, `No_of_items`, `Scrap_name`, `Fixed_rate`, `Kg`, `Total`, `Remark`, `PaymentDateTime`, `created_at`) VALUES
(64, 103, 'lotus', 'UPI', 2, 'Aluminium', 105.00, 2.00, 210.00, '1st', '2025-05-20 17:01:59', '2025-05-20 20:31:59'),
(65, 103, 'lotus', 'UPI', 2, 'CRT Monitor', 150.00, 3.00, 450.00, '2nd', '2025-05-20 17:01:59', '2025-05-20 20:31:59'),
(66, 106, 'lotus', 'UPI', 1, ' Others(mixed of Scrap) ', 40.00, 3.00, 120.00, 'we collected mixed of scraps', '2025-06-14 09:14:15', '2025-06-14 12:44:15'),
(67, 107, 'lotus', 'Cash', 1, ' Others(mixed of Scrap) ', 40.00, 1.00, 40.00, 'we collected mixed of scraps', '2025-06-14 19:02:49', '2025-06-14 22:32:49'),
(68, 126, 'lotus', 'UPI', 1, ' Others(mixed of Scrap) ', 20.00, 1.00, 20.00, 'done', '2025-06-24 10:11:27', '2025-06-24 13:41:27');

-- --------------------------------------------------------

--
-- Table structure for table `recycle_tb`
--

CREATE TABLE `recycle_tb` (
  `id` int(11) NOT NULL,
  `Center_name` varchar(255) NOT NULL,
  `Address` text NOT NULL,
  `Phone_no` varchar(20) NOT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Owner_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` tinyint(4) DEFAULT 1 COMMENT '1=Active, 0=Inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recycle_tb`
--

INSERT INTO `recycle_tb` (`id`, `Center_name`, `Address`, `Phone_no`, `Email`, `Owner_name`, `created_at`, `updated_at`, `status`) VALUES
(13, 'recycle center 1', 'pondy', '1234567899', 'raja@gmail.com', 'aravidh', '2025-05-20 15:55:04', '2025-05-20 15:55:04', 1);

-- --------------------------------------------------------

--
-- Table structure for table `scrap_collection_history`
--

CREATE TABLE `scrap_collection_history` (
  `id` int(11) NOT NULL,
  `scrap_name` varchar(255) NOT NULL,
  `fixed_rate` decimal(10,2) NOT NULL,
  `total_kg` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `current_kg` decimal(10,2) NOT NULL COMMENT 'Current kg value from total_kg',
  `current_total` decimal(10,2) NOT NULL COMMENT 'Current total amount from total_amount',
  `collection_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scrap_collection_history`
--

INSERT INTO `scrap_collection_history` (`id`, `scrap_name`, `fixed_rate`, `total_kg`, `total_amount`, `current_kg`, `current_total`, `collection_date`, `created_at`, `updated_at`) VALUES
(27, 'Aluminium', 105.00, 2.00, 210.00, 0.00, 0.00, '2025-05-20', '2025-05-20 15:08:18', '2025-06-16 16:20:32'),
(28, 'CRT Monitor', 150.00, 3.00, 450.00, 0.00, 0.00, '2025-05-20', '2025-05-20 15:08:18', '2025-06-16 16:20:32'),
(29, ' Others(mixed of Scrap) ', 40.00, 4.00, 160.00, 0.00, 0.00, '2025-06-14', '2025-06-14 07:20:02', '2025-06-16 16:20:32');

-- --------------------------------------------------------

--
-- Table structure for table `scrap_price_list`
--

CREATE TABLE `scrap_price_list` (
  `id` int(11) NOT NULL,
  `scrap_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `unit_type` varchar(50) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scrap_price_list`
--

INSERT INTO `scrap_price_list` (`id`, `scrap_name`, `price`, `unit_type`, `image`) VALUES
(65, 'Aluminium', 105.00, 'Per/', 'top-view-tin-can-sticker-white-background_1308-63955.avif'),
(66, 'Battery(used with inverters)', 81.00, 'Per/', 'car-battery-charger-with-jump-starter-connection-wire-kit-illustration_1284-53950.avif'),
(67, 'Bike', 2100.00, 'Piece not-', 'isolated-scooter-cartoon-design_1308-115357.avif'),
(68, 'Brass', 305.00, 'Per/', 'three-bars-chinese-gold_1308-38181.avif'),
(69, 'Car', 200000.00, 'Piece not-', 'vintage-red-car-white-background_1308-102188.avif'),
(70, 'Cardboard', 8.00, 'Per/', 'sticker-empty-box-opened-white-background_1308-68243.avif'),
(71, 'Ceiling Fan', 35.00, 'Per/', 'object-household-cooling-light-motion_1203-4644.avif'),
(72, 'Clothes(Accepted only when given with other scrap items)', 2.00, 'Per/', 'isolated-set-clothes_1308-38983.avif'),
(73, 'Computer CPU', 225.00, 'Piece not-', 'retro-computer-desk-arrangement_23-2150244338.avif'),
(74, 'Copies/Books', 12.00, 'Per/', 'isolated-bundle-books_1308-46573.avif'),
(75, 'Copper', 425.00, 'Per/', 'metal-orange-wire-spool-coils-260nw-1783221305.webp'),
(76, 'CRT TV ', 200.00, 'Piece not-', 'retro-television-set-illustration_1308-163978.avif'),
(77, 'CRT Monitor', 150.00, 'Piece not-', 'retro-vintage-computer-realistic-composition-with-isolated-front-view-personal-computer-with-display-mainframe-vector-illustration_1284-84867.avif'),
(78, 'Double Door Fridge ', 1350.00, 'Piece not-', 'refrigerator-with-lots-food_1308-105555.avif'),
(79, 'Front Load Fully Automatic Washing Machine', 1350.00, 'Piece not-', 'washing-machine-isolated-white-background_1308-61579.avif'),
(80, 'Geyser', 740.00, 'Piece not-', 'modern-boiler-icon-flat-illustration-modern-boiler-vector-icon-web-design_98402-29424.avif'),
(81, 'Inverter/Stabilizer (Copper Coil)', 40.00, 'Per/', 'battery-with-crocodile-clips-white-background_1308-87588.avif'),
(82, 'Glass bottles ', 2.00, 'Per/', 'hand-drawn-mezcal-illustration_23-2149189393.avif'),
(83, 'Iron', 26.00, 'Per/', 'metal-rolled-rail-piece-construction-works_726294-183.avif'),
(84, 'Iron Cooler', 30.00, 'Per/', 'flat-design-sunbed-illustration_23-2149455710.avif'),
(85, 'LCD Monitor', 20.00, 'Per/', 'television-white-background_1308-81507.avif'),
(86, 'Metal E-waste', 28.00, 'Per/', 'trash-can-bin_1308-25190.avif'),
(87, 'Microwave', 350.00, 'Piece not-', 'microwave-oven-isolated-white-background_1308-64506.avif'),
(88, 'Motors (Copper wiring', 35.00, 'Per/', 'electric-engine-copper-windings-rotor-260nw-2250215123.webp'),
(89, 'Newpaper', 14.00, 'Per/', 'sticker-newspaper-white-background_1308-67441.avif'),
(90, 'Office Paper (A3/A4)', 14.00, 'Per/', 'pile-papers-files_1308-73161.avif'),
(91, 'PET Bottles/ Other Plastic', 8.00, 'Per/', 'plastic-glass-cups-bottles-plastic-bottle-other-containers-vector-illustration_24640-66113.avif'),
(92, 'Plastic E-waste', 15.00, 'Per/', 'electronic-garbage-items-isometric-illustration_1284-57400.avif'),
(93, 'Printer/scanner/fax machine', 20.00, 'Per/', 'color-printer-machine-white-background_1308-81979.avif'),
(94, 'Scrap Laptop', 300.00, 'Piece not-', 'e-waste-concept-illustration_114360-26283.avif'),
(95, 'Single Door Fridge', 1000.00, 'Piece not-', 'grey-refrigerator-cartoon-style-isolated_1308-65005.avif'),
(96, 'Split AC Copper Coil 1.5 Ton (Indoor + Outdoor)', 4150.00, 'Piece not-', '3d-rendering-hotel-icon_23-2150102380.avif'),
(97, 'SPLIT/WINDOW AC 1 Ton (Copper Coil)', 3000.00, 'Piece not-', 'air-conditioner-with-cold-wind-remote-control_107791-2881.avif'),
(98, 'Stainless steel', 1000.00, 'Piece not-', 'broken-steel-iron-pipe-illustration_1308-159453.avif'),
(99, 'Top Load Fully Automatic Washing Machine', 1000.00, 'Piece not-', 'washing-machine-with-front-door_1308-74400.avif'),
(100, 'UPS', 180.00, 'Piece not-', 'hand-drawn-flat-design-hard-drive-illustration_23-2149363192.avif'),
(101, 'Window AC 1.5 Ton (Copper Coil)', 4050.00, 'Piece not-', 'air-conditioner-with-cold-wind_107791-2884.avif'),
(102, 'WINDOW/SPLIT AC 2 Ton (Copper Coil)', 5600.00, 'Piece not-', 'air-conditioner-with-cold-wind-remote-control_107791-2881.avif'),
(103, ' Others(mixed of Scrap) ', 0.00, 'To be Evaluated by Driver Piece or ', 'Screenshot_2025-06-14_121844.png');

-- --------------------------------------------------------

--
-- Table structure for table `sell_report`
--

CREATE TABLE `sell_report` (
  `id` int(11) NOT NULL,
  `Center_name` varchar(100) NOT NULL,
  `Owner_name` varchar(100) NOT NULL,
  `Phone` varchar(20) NOT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Address` text NOT NULL,
  `Report_date` date NOT NULL,
  `Scrap_name` varchar(50) NOT NULL,
  `Total_kg` decimal(10,2) NOT NULL,
  `Fixed_amount` decimal(10,2) NOT NULL,
  `Current_total_price` decimal(10,2) NOT NULL,
  `Profit` decimal(10,2) NOT NULL,
  `loss` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sell_report`
--

INSERT INTO `sell_report` (`id`, `Center_name`, `Owner_name`, `Phone`, `Email`, `Address`, `Report_date`, `Scrap_name`, `Total_kg`, `Fixed_amount`, `Current_total_price`, `Profit`, `loss`, `created_at`) VALUES
(47, 'recycle center 1', 'aravidh', '1234567899', 'raja@gmail.com', 'pondy', '2025-05-20', 'Aluminium', 2.00, 105.00, 10.00, 0.00, 200.00, '2025-05-20 15:57:04'),
(48, 'recycle center 1', 'aravidh', '1234567899', 'raja@gmail.com', 'pondy', '2025-05-20', 'CRT Monitor', 3.00, 150.00, 550.00, 100.00, 0.00, '2025-05-20 15:57:04'),
(49, 'recycle center 1', 'aravidh', '1234567899', 'raja@gmail.com', 'pondy', '2025-06-14', ' Others(mixed of Scrap) ', 3.00, 40.00, 220.00, 100.00, 0.00, '2025-06-14 07:22:00'),
(50, 'recycle center 1', 'aravidh', '1234567899', 'raja@gmail.com', 'pondy', '2025-06-14', ' Others(mixed of Scrap) ', 1.00, 40.00, 50.00, 10.00, 0.00, '2025-06-14 17:09:10');

-- --------------------------------------------------------

--
-- Table structure for table `tbladmin`
--

CREATE TABLE `tbladmin` (
  `ID` int(10) NOT NULL,
  `AdminName` varchar(120) DEFAULT NULL,
  `UserName` varchar(120) DEFAULT NULL,
  `MobileNumber` bigint(10) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `Password` varchar(200) DEFAULT NULL,
  `AdminRegdate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbladmin`
--

INSERT INTO `tbladmin` (`ID`, `AdminName`, `UserName`, `MobileNumber`, `Email`, `Password`, `AdminRegdate`) VALUES
(1, 'Admin', 'admin', 8979555557, 'adminuser@gmail.com', '202cb962ac59075b964b07152d234b70', '2022-08-02 12:23:36');

-- --------------------------------------------------------

--
-- Table structure for table `tblcomtracking`
--

CREATE TABLE `tblcomtracking` (
  `ID` int(5) NOT NULL,
  `ComplainNumber` int(10) DEFAULT NULL,
  `Remark` varchar(250) DEFAULT NULL,
  `Status` varchar(250) DEFAULT NULL,
  `RemarkDate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblcomtracking`
--

INSERT INTO `tblcomtracking` (`ID`, `ComplainNumber`, `Remark`, `Status`, `RemarkDate`) VALUES
(192, 941161545, 'from admin', 'Approved', '2025-05-20 14:45:25'),
(193, 941161545, 'done', 'Completed', '2025-05-20 15:01:59'),
(194, 620010061, 'accepted', 'Approved', '2025-06-14 07:04:11'),
(195, 620010061, 'we will come on your pick up date', 'On the way', '2025-06-14 07:07:48'),
(196, 620010061, 'scrap pickup successfully', 'Completed', '2025-06-14 07:14:15'),
(197, 928097942, 'accepted', 'Approved', '2025-06-14 16:58:09'),
(198, 928097942, 'we will come one 21', 'On the way', '2025-06-14 17:01:03'),
(199, 928097942, 'we collected your scrap', 'Completed', '2025-06-14 17:02:49'),
(200, 740327014, 'done', 'Approved', '2025-06-23 04:10:02'),
(201, 235003161, 'aasda', 'Approved', '2025-06-23 05:19:03'),
(202, 940471692, 'dfsdf', 'Approved', '2025-06-23 05:21:00'),
(203, 492730641, 'sdfsf', 'Approved', '2025-06-23 07:51:28'),
(204, 492730641, 'sdfsf', 'Approved', '2025-06-23 07:51:49'),
(205, 339545032, 'sds', 'Approved', '2025-06-23 07:52:50'),
(206, 977406647, 'xcv', 'Approved', '2025-06-23 07:56:18'),
(207, 856611152, 'mnmn', 'Approved', '2025-06-23 08:22:57'),
(208, 648122994, 'hello', 'Approved', '2025-06-23 09:14:58'),
(209, 508681088, 'cvxvx', 'Approved', '2025-06-23 09:18:15'),
(210, 813913545, 'dd', 'Approved', '2025-06-23 09:32:32'),
(211, 126773170, 'zxz', 'Approved', '2025-06-23 09:37:58'),
(212, 121712437, 'i do not want to sell this ', 'Rejected', '2025-06-23 10:35:37'),
(213, 121712437, 'i do not want to sell this ', 'Rejected', '2025-06-23 10:37:42'),
(214, 711609223, 'done', 'Approved', '2025-06-23 10:40:36'),
(215, 711609223, 'nope', 'Rejected', '2025-06-23 10:41:32'),
(216, 448297782, 'done', 'Approved', '2025-06-23 10:49:19'),
(217, 448297782, 'rejected', 'Rejected', '2025-06-23 10:51:00'),
(218, 427109560, 'can', 'Rejected', '2025-06-24 06:27:48'),
(219, 354704581, 'm', 'Approved', '2025-06-24 06:30:15'),
(220, 932698736, 'mm', 'Approved', '2025-06-24 08:10:05'),
(221, 932698736, 'compleeted', 'Completed', '2025-06-24 08:11:27');

-- --------------------------------------------------------

--
-- Table structure for table `tbldailyprecheck`
--

CREATE TABLE `tbldailyprecheck` (
  `ID` int(11) NOT NULL,
  `DriverDBID` int(10) NOT NULL,
  `DriverLoginID` varchar(20) DEFAULT NULL,
  `DriverName` varchar(200) DEFAULT NULL,
  `DriverMobileNumber` bigint(10) DEFAULT NULL,
  `TiresChecked` tinyint(1) DEFAULT 0,
  `LightsWorking` tinyint(1) DEFAULT 0,
  `BrakesFunctioning` tinyint(1) DEFAULT 0,
  `FluidLevelsChecked` tinyint(1) DEFAULT 0,
  `WindshieldMirrorsChecked` tinyint(1) DEFAULT 0,
  `HornWorking` tinyint(1) DEFAULT 0,
  `WipersFunctioning` tinyint(1) DEFAULT 0,
  `SafetyGearPresent` tinyint(1) DEFAULT 0,
  `IssuesComments` text DEFAULT NULL,
  `IsAllOk` tinyint(1) DEFAULT 0,
  `CheckDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbldailyprecheck`
--

INSERT INTO `tbldailyprecheck` (`ID`, `DriverDBID`, `DriverLoginID`, `DriverName`, `DriverMobileNumber`, `TiresChecked`, `LightsWorking`, `BrakesFunctioning`, `FluidLevelsChecked`, `WindshieldMirrorsChecked`, `HornWorking`, `WipersFunctioning`, `SafetyGearPresent`, `IssuesComments`, `IsAllOk`, `CheckDate`) VALUES
(6, 16, 'd2', 'raman', 1234567899, 1, 1, 1, 1, 1, 1, 1, 1, 'wqeq', 1, '2025-06-23 17:47:49'),
(7, 16, 'd2', 'raman', 1234567899, 1, 1, 0, 1, 1, 1, 1, 1, 'break is not working', 0, '2025-06-24 06:40:51');

-- --------------------------------------------------------

--
-- Table structure for table `tbldriver`
--

CREATE TABLE `tbldriver` (
  `ID` int(10) NOT NULL,
  `DriverID` varchar(20) DEFAULT NULL,
  `Name` varchar(200) DEFAULT NULL,
  `MobileNumber` bigint(10) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `Address` mediumtext DEFAULT NULL,
  `Password` varchar(200) DEFAULT NULL,
  `JoiningDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbldriver`
--

INSERT INTO `tbldriver` (`ID`, `DriverID`, `Name`, `MobileNumber`, `Email`, `Address`, `Password`, `JoiningDate`) VALUES
(15, 'd1', 'rajakumar', 1234567892, 'raja@gmail.com', 'chennai', '202cb962ac59075b964b07152d234b70', '2025-05-20 14:43:32'),
(16, 'd2', 'raman', 1234567899, 'thamaraiselvan042002@gmail.com', 'chennai', '202cb962ac59075b964b07152d234b70', '2025-06-14 06:56:26');

-- --------------------------------------------------------

--
-- Table structure for table `tblincident_reports`
--

CREATE TABLE `tblincident_reports` (
  `ID` int(11) NOT NULL,
  `DriverDBID` int(10) NOT NULL,
  `DriverLoginID` varchar(20) DEFAULT NULL,
  `DriverName` varchar(200) DEFAULT NULL,
  `DriverMobileNumber` bigint(10) DEFAULT NULL,
  `RelatedComplainID` int(10) DEFAULT NULL,
  `IncidentType` varchar(100) NOT NULL,
  `IncidentLocationDescription` varchar(255) DEFAULT NULL,
  `IncidentDetails` text DEFAULT NULL,
  `PhotoPaths` text DEFAULT NULL,
  `IncidentDateTime` datetime NOT NULL,
  `AdminStatus` varchar(50) DEFAULT 'New',
  `ReportedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblincident_reports`
--

INSERT INTO `tblincident_reports` (`ID`, `DriverDBID`, `DriverLoginID`, `DriverName`, `DriverMobileNumber`, `RelatedComplainID`, `IncidentType`, `IncidentLocationDescription`, `IncidentDetails`, `PhotoPaths`, `IncidentDateTime`, `AdminStatus`, `ReportedAt`) VALUES
(3, 16, 'd2', 'raman', 1234567899, NULL, 'Weather Related', 'nmnm', 'sdfs', '[\"uploads\\/incident_photos\\/incident_685a536d7c8949.05733913.png\"]', '2025-06-28 16:57:00', 'New', '2025-06-24 07:27:41'),
(4, 16, 'd2', 'raman', 1234567899, NULL, 'Accident - Major', 'asd', 'asd', '[]', '2025-06-17 17:14:00', 'New', '2025-06-24 07:44:36'),
(5, 16, 'd2', 'raman', 1234567899, NULL, 'Accident - Major', 'asd', 'asd', '[]', '2025-06-17 17:14:00', 'New', '2025-06-24 07:48:36');

-- --------------------------------------------------------

--
-- Table structure for table `tbllodgedcomplain`
--

CREATE TABLE `tbllodgedcomplain` (
  `ID` int(10) NOT NULL,
  `UserID` int(10) DEFAULT NULL,
  `ComplainNumber` int(10) DEFAULT NULL,
  `Area` varchar(250) DEFAULT NULL,
  `Locality` varchar(250) DEFAULT NULL,
  `Landmark` varchar(250) DEFAULT NULL,
  `Address` mediumtext DEFAULT NULL,
  `Paymentmode` varchar(50) DEFAULT NULL,
  `Photo` varchar(250) DEFAULT NULL,
  `Note` mediumtext DEFAULT NULL,
  `Upi` int(15) DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Scraptype` varchar(250) DEFAULT NULL,
  `Pin` int(50) DEFAULT NULL,
  `Time` time DEFAULT NULL,
  `ComplainDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `Remark` varchar(250) DEFAULT NULL,
  `Status` varchar(100) DEFAULT NULL,
  `AssignTo` varchar(100) DEFAULT NULL,
  `AssignDate` date DEFAULT NULL,
  `UpdationDate` timestamp NULL DEFAULT NULL,
  `DriverName` varchar(250) DEFAULT NULL,
  `DriverMobile` varchar(15) DEFAULT NULL,
  `Amount` int(15) DEFAULT NULL,
  `PaymentDateTime` datetime DEFAULT NULL,
  `LocationStatus` varchar(50) DEFAULT 'unverified'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbllodgedcomplain`
--

INSERT INTO `tbllodgedcomplain` (`ID`, `UserID`, `ComplainNumber`, `Area`, `Locality`, `Landmark`, `Address`, `Paymentmode`, `Photo`, `Note`, `Upi`, `Date`, `Scraptype`, `Pin`, `Time`, `ComplainDate`, `Remark`, `Status`, `AssignTo`, `AssignDate`, `UpdationDate`, `DriverName`, `DriverMobile`, `Amount`, `PaymentDateTime`, `LocationStatus`) VALUES
(103, 21, 941161545, 'chennai', 'chennai', 'GH hospital', '12 anna nagar chennai', 'UPI', '95409ba62101f6bb9efdfab4d47cc4f91747752095.png', 'we have lots of metal scrap so kept big size of vehicle', 1231414242, '2025-05-24', 'iron cooler', 123124, '13:00:00', '2025-05-20 14:41:35', 'done', 'Completed', 'd1', NULL, NULL, 'rajakumar', '1234567892', 660, '2025-05-20 17:01:59', 'unverified'),
(106, 21, 620010061, 'cuddalore', 'cuddalore', 'GH hospital', 'anna nagar cuddalore', 'UPI', 'aea062e095704938e4eb6054669f4ed31749884013.png', 'null', 1231414242, '2025-06-21', ' others(mixed of scrap) ', 1231, '12:30:00', '2025-06-14 06:53:33', 'scrap pickup successfully', 'Completed', 'd2', NULL, NULL, 'raman', '1234567899', 120, '2025-06-14 09:14:15', 'unverified'),
(107, 21, 928097942, 'cuddalore', 'cuddaloe', 'sffs', 'sdf', 'Cash', '786cf250ffe17eb3d708ae55fac33d741749920004.png', 'sdf', 0, '2025-06-21', ' others(mixed of scrap) ', 2443, '11:00:00', '2025-06-14 16:53:24', 'we collected your scrap', 'Completed', 'd2', NULL, NULL, 'raman', '1234567899', 40, '2025-06-14 19:02:49', 'unverified'),
(108, 21, 592264943, 'cuddalore', 'cuddalore', 'cudd', 'cuddalore', 'Cash', '1f7ae6e2d8fcd6aaa15e48303f34a01f1750080088.png', 'null', 0, '2025-06-28', 'aluminium', 123, '08:00:00', '2025-06-16 13:21:28', NULL, 'Approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified'),
(109, 21, 447492054, 'pondicherry', 'pondicherry', 'near beach', 'WRPM+8F7, Rue Manakkula Vinayakar Covil, White Town, Puducherry', 'Cash', 'c26be60cfd1ba40772b5ac48b95ab19b1750440102.png', 'no', 0, '2025-06-28', ' others(mixed of scrap) ', 605001, '11:00:00', '2025-06-20 17:21:42', NULL, 'Approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified'),
(110, 21, 740327014, 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Cash', 'fe710d2d57fbccfa05502be5a0612f23.png', 'asd', 0, '2025-07-01', 'bike', 600045, '02:37:00', '2025-06-22 12:04:59', 'done', 'Approved', 'd2', NULL, NULL, 'raman', '1234567899', NULL, NULL, 'unverified'),
(111, 21, 235003161, 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu ', 'Cash', 'd18292ff331a61622ccba39c29189165.png', 'asd', 0, '2025-06-27', 'aluminium', 600045, '11:32:00', '2025-06-22 15:00:52', 'aasda', 'Approved', 'd2', NULL, NULL, 'raman', '1234567899', NULL, NULL, 'unverified'),
(112, 21, 940471692, 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', '', '', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'UPI', 'af0370a7ab002946492c07cde54d5143.png', 'asda', 0, '2025-06-28', 'cardboard', 600045, '22:38:00', '2025-06-22 15:06:46', 'dfsdf', 'Approved', 'd2', NULL, NULL, 'raman', '1234567899', NULL, NULL, 'unverified'),
(113, 21, 339545032, 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'cuddalore', 'UPI', '0da525b88fffa46c8475d92393b4d1af.png', 'ads', 1231, '2025-07-01', 'aluminium', 607004, '08:00:00', '2025-06-22 15:34:05', 'sds', 'Approved', 'd2', NULL, NULL, 'raman', '1234567899', NULL, NULL, 'unverified'),
(114, 21, 492730641, 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'West Tambaram Chennai, Tamil Nadu 600045', 'Cash', 'f9af50faddd549cc63438b456ae70e61.png', 'dsad', 0, '2025-06-30', 'aluminium', 600045, '14:05:00', '2025-06-23 05:31:52', 'sdfsf', 'Approved', 'd2', NULL, NULL, 'raman', '1234567899', NULL, NULL, 'unverified'),
(115, 21, 977406647, 'ssd', '', '', 'West Tambaram Chennai, Tamil Nadu ', 'Cash', '93433fb0ae6287310d6cb8fdb39455b5.png', 'vcxv', 0, '2025-06-30', 'aluminium', 600045, '15:25:00', '2025-06-23 07:55:40', 'xcv', 'Approved', 'd2', NULL, NULL, 'raman', '1234567899', NULL, NULL, 'unverified'),
(116, 21, 856611152, 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'chennai', 'Cash', '15dff6fb0a6428ad55a0fcb6f677695f.png', 'ssaa', 0, '2025-07-01', ' others(mixed of scrap) ', 600045, '16:42:00', '2025-06-23 08:09:31', 'mnmn', 'Approved', 'd2', '2025-07-01', NULL, 'raman', '1234567899', NULL, NULL, 'unverified'),
(117, 21, 813913545, 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'West Tambaram Chennai, Tamil Nadu 600045', 'UPI', '664fe7387c5cb1deb6d5e8b47c5ef8e3.png', 'afsa', 0, '2025-07-12', 'double door fridge ', 600045, '14:03:00', '2025-06-23 08:58:57', 'dd', 'Approved', 'd2', '2025-07-12', NULL, 'raman', '1234567899', NULL, NULL, 'unverified'),
(118, 21, 648122994, 'asda', 'sa', '', 'chennai', 'UPI', '533c6b637a2dced240045ff85f3c5da1.png', 'asda', 21313, '2025-07-11', 'aluminium', 600045, '12:03:00', '2025-06-23 09:00:54', 'hello', 'Approved', 'd2', '2025-07-11', NULL, 'raman', '1234567899', NULL, NULL, 'unverified'),
(119, 21, 508681088, 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'West Tambaram Chennai, Tamil Nadu 600045', 'UPI', 'c5a5ba534f2b3f8c061e46af5e1fae6f.png', 'ssdfs', 11234, '2025-07-01', 'aluminium', 600045, '17:48:00', '2025-06-23 09:17:16', 'cvxvx', 'Approved', 'd2', '2025-07-01', NULL, 'raman', '1234567899', NULL, NULL, 'unverified'),
(120, 21, 126773170, 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'West Tambaram Chennai, Tamil Nadu ', 'UPI', 'bd6da76928a7f918dc057660218a861d.png', 'sda', 555, '2025-07-10', 'bike', 600045, '12:03:00', '2025-06-23 09:35:07', 'zxz', 'Approved', 'd2', '2025-07-10', NULL, 'raman', '1234567899', NULL, NULL, 'unverified'),
(121, 21, 121712437, 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'West Tambaram Chennai, Tamil Nadu ', 'UPI', '0fbd918aacfade07fcf570d1ef6a7c4d.png', 'sdas', 123, '2025-07-01', 'bike', 600045, '12:01:00', '2025-06-23 10:25:34', 'i do not want to sell this ', 'Rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified'),
(122, 21, 711609223, 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'West Tambaram Chennai, Tamil Nadu ', 'UPI', '34c0736dbdf957de1dbc15945ace1729.png', 'zz', 321, '2025-07-12', 'car, cardboard', 600045, '12:32:00', '2025-06-23 10:39:42', 'nope', 'Rejected', 'd2', '2025-07-12', NULL, 'raman', '1234567899', NULL, NULL, 'unverified'),
(123, 21, 448297782, 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu ', 'chennai', 'UPI', '69d09d54b379c6ded09967fd093b9273.png', 'null', 12345, '2025-07-01', 'glass bottles , iron cooler', 600045, '10:00:00', '2025-06-23 10:46:52', 'rejected', 'Rejected', 'd2', '2025-07-01', NULL, 'raman', '1234567899', NULL, NULL, 'unverified'),
(124, 21, 354704581, 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'West Tambaram Chennai, Tamil Nadu ', 'UPI', '603ff872be2ed0331026c715f40442f5.png', 'nothing', 0, '2025-06-28', 'lcd monitor', 600045, '11:30:00', '2025-06-24 05:36:14', 'm', 'Approved', 'd2', '2025-06-28', NULL, 'raman', '1234567899', NULL, NULL, 'unverified'),
(125, 21, 427109560, 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', 'West Tambaram Chennai, Tamil Nadu ', 'UPI', 'd3a42d7702f360618e3dcdd2ba6af198.png', 'zxc', 12354, '2025-07-05', 'battery(used with inverters)', 600045, '12:03:00', '2025-06-24 06:27:10', 'can', 'Rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified'),
(126, 21, 932698736, 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram Chennai, Tamil Nadu 600045', '', '', 'West Tambaram Chennai, Tamil Nadu ', 'UPI', 'a2249f6bd1e15a3e7a0666dcb9ac75a2.png', 'asd', 98, '2025-06-25', 'aluminium, copper', 600045, '12:02:00', '2025-06-24 06:36:06', 'compleeted', 'Completed', 'd2', '2025-06-25', NULL, 'raman', '1234567899', 20, '2025-06-24 10:11:27', 'unverified');

-- --------------------------------------------------------

--
-- Table structure for table `tblpage`
--

CREATE TABLE `tblpage` (
  `ID` int(10) NOT NULL,
  `PageType` varchar(200) DEFAULT NULL,
  `PageTitle` mediumtext DEFAULT NULL,
  `PageDescription` mediumtext DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `MobileNumber` bigint(10) DEFAULT NULL,
  `UpdationDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblpage`
--

INSERT INTO `tblpage` (`ID`, `PageType`, `PageTitle`, `PageDescription`, `Email`, `MobileNumber`, `UpdationDate`) VALUES
(1, 'aboutus', 'About Us ', '<h2><font color=\"#000000\" face=\"arial, sans-serif\"><span style=\"font-size: 16px;\">Smart Scrap management system</span></font></h2><div><font color=\"#000000\" face=\"arial, sans-serif\"><span style=\"font-size: 16px;\">The Smart Scrap Management System is a web-based application that helps users request scrap pickups easily. Admins manage these requests by assigning drivers, while drivers update the status and payment details after collecting scrap.</span></font></div>', NULL, NULL, NULL),
(2, 'contactus', 'Contact Us', 'Plot No.8, Thandai Periyar Nagar, 2nd Street, Irumbuliyur, West Tambaram\r\nChennai, Tamil Nadu 600045', 'removed', 7896541236, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbluser`
--

CREATE TABLE `tbluser` (
  `ID` int(10) NOT NULL,
  `FullName` varchar(250) DEFAULT NULL,
  `UserName` varchar(250) DEFAULT NULL,
  `MobileNumber` bigint(20) DEFAULT NULL,
  `Email` varchar(250) DEFAULT NULL,
  `Password` varchar(250) DEFAULT NULL,
  `RegDate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbluser`
--

INSERT INTO `tbluser` (`ID`, `FullName`, `UserName`, `MobileNumber`, `Email`, `Password`, `RegDate`) VALUES
(21, 'lotus', 'lotus', 1234567899, 'lotustomcat2002@gmail.com', '202cb962ac59075b964b07152d234b70', '2025-05-20 14:38:22');

-- --------------------------------------------------------

--
-- Table structure for table `tbmail`
--

CREATE TABLE `tbmail` (
  `Name` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Email` varchar(150) DEFAULT NULL,
  `Subject` varchar(150) DEFAULT NULL,
  `Message` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbmail`
--

INSERT INTO `tbmail` (`Name`, `Email`, `Subject`, `Message`) VALUES
('raja', 'lotustomcat2002@gmail.com', 'scrapp', 'sdasa'),
('selva', 'lotustomcat2002@gmail.com', 'request for scrap', 'we have lots of metal scrap');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `payment_invoice`
--
ALTER TABLE `payment_invoice`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ComplainID` (`ComplainID`);

--
-- Indexes for table `recycle_tb`
--
ALTER TABLE `recycle_tb`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scrap_collection_history`
--
ALTER TABLE `scrap_collection_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scrap_price_list`
--
ALTER TABLE `scrap_price_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sell_report`
--
ALTER TABLE `sell_report`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbladmin`
--
ALTER TABLE `tbladmin`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblcomtracking`
--
ALTER TABLE `tblcomtracking`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbldailyprecheck`
--
ALTER TABLE `tbldailyprecheck`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `DriverDBID` (`DriverDBID`);

--
-- Indexes for table `tbldriver`
--
ALTER TABLE `tbldriver`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblincident_reports`
--
ALTER TABLE `tblincident_reports`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `DriverDBID` (`DriverDBID`);

--
-- Indexes for table `tbllodgedcomplain`
--
ALTER TABLE `tbllodgedcomplain`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblpage`
--
ALTER TABLE `tblpage`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbluser`
--
ALTER TABLE `tbluser`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `payment_invoice`
--
ALTER TABLE `payment_invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `recycle_tb`
--
ALTER TABLE `recycle_tb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `scrap_collection_history`
--
ALTER TABLE `scrap_collection_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `scrap_price_list`
--
ALTER TABLE `scrap_price_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `sell_report`
--
ALTER TABLE `sell_report`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `tbladmin`
--
ALTER TABLE `tbladmin`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblcomtracking`
--
ALTER TABLE `tblcomtracking`
  MODIFY `ID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=222;

--
-- AUTO_INCREMENT for table `tbldailyprecheck`
--
ALTER TABLE `tbldailyprecheck`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbldriver`
--
ALTER TABLE `tbldriver`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tblincident_reports`
--
ALTER TABLE `tblincident_reports`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbllodgedcomplain`
--
ALTER TABLE `tbllodgedcomplain`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `tblpage`
--
ALTER TABLE `tblpage`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbluser`
--
ALTER TABLE `tbluser`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payment_invoice`
--
ALTER TABLE `payment_invoice`
  ADD CONSTRAINT `payment_invoice_ibfk_1` FOREIGN KEY (`ComplainID`) REFERENCES `tbllodgedcomplain` (`ID`);

--
-- Constraints for table `tbldailyprecheck`
--
ALTER TABLE `tbldailyprecheck`
  ADD CONSTRAINT `fk_driver_db_id` FOREIGN KEY (`DriverDBID`) REFERENCES `tbldriver` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tblincident_reports`
--
ALTER TABLE `tblincident_reports`
  ADD CONSTRAINT `fk_incident_driver_id` FOREIGN KEY (`DriverDBID`) REFERENCES `tbldriver` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
