-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 01, 2025 at 05:11 PM
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
-- Database: `employee`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_info`
--

CREATE TABLE IF NOT EXISTS `admin_info` (
  `admin_username` varchar(25) NOT NULL DEFAULT 'root',
  `admin_password` varchar(25) NOT NULL DEFAULT 'password',
  PRIMARY KEY (`admin_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_info`
--

INSERT IGNORE INTO `admin_info` (`admin_username`, `admin_password`) VALUES
('root', 'password');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE IF NOT EXISTS `departments` (
  `dept_no` CHAR(4) NOT NULL,
  `dept_name` VARCHAR(40) NOT NULL,
  PRIMARY KEY (`dept_no`),
  UNIQUE KEY `dept_name` (`dept_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
DROP TABLE IF EXISTS `titles`;

CREATE TABLE IF NOT EXISTS `employees` (
  `emp_no` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `birth_date` DATE NOT NULL,
  `first_name` VARCHAR(14) NOT NULL,
  `last_name` VARCHAR(16) NOT NULL,
  `gender` ENUM('M','F','O') NOT NULL,
  `hire_date` DATE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`emp_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `titles` (
  `emp_no` INT(11) UNSIGNED NOT NULL,
  `title` VARCHAR(50) NOT NULL,
  `from_date` DATE NOT NULL,
  `to_date` DATE DEFAULT NULL,
  PRIMARY KEY (`emp_no`, `title`, `from_date`),
  FOREIGN KEY (`emp_no`) REFERENCES `employees` (`emp_no`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------

--
-- Table structure for table `salaries`
--

CREATE TABLE IF NOT EXISTS `salaries` (
  `emp_no` INT(11) UNSIGNED NOT NULL,
  `salary` INT(11) NOT NULL,
  `from_date` DATE NOT NULL,
  `to_date` DATE DEFAULT NULL,
  PRIMARY KEY (`emp_no`, `from_date`),
  FOREIGN KEY (`emp_no`) REFERENCES `employees` (`emp_no`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dept_emp`
--

CREATE TABLE IF NOT EXISTS `dept_emp` (
  `emp_no` INT(11) UNSIGNED NOT NULL,
  `dept_no` CHAR(4) NOT NULL,
  `from_date` DATE NOT NULL,
  `to_date` DATE DEFAULT NULL,
  PRIMARY KEY (`emp_no`, `dept_no`),
  FOREIGN KEY (`emp_no`) REFERENCES `employees` (`emp_no`) ON DELETE CASCADE,
  FOREIGN KEY (`dept_no`) REFERENCES `departments` (`dept_no`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dept_manager`
--

CREATE TABLE IF NOT EXISTS `dept_manager` (
  `dept_no` CHAR(4) NOT NULL,
  `emp_no` INT(11) UNSIGNED NOT NULL,
  `from_date` DATE NOT NULL,
  `to_date` DATE DEFAULT NULL,
  PRIMARY KEY (`dept_no`, `emp_no`),
  FOREIGN KEY (`emp_no`) REFERENCES `employees` (`emp_no`) ON DELETE CASCADE,
  FOREIGN KEY (`dept_no`) REFERENCES `departments` (`dept_no`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT IGNORE INTO `departments` (`dept_no`, `dept_name`) VALUES
('d001', 'Executive'),
('d002', 'Human Resources'),
('d003', 'Accounting'),
('d004', 'R&D'),
('d005', 'IT'),
('d006', 'Sales'),
('d007', 'Marketing'),
('d008', 'Customer Support');


-- Add gender enum values if needed (run only if you want to ensure correct enum)
ALTER TABLE employees
  MODIFY COLUMN gender ENUM('M','F','O') NOT NULL;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE IF NOT EXISTS `attendance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `emp_no` INT(11) UNSIGNED NOT NULL,
    `date` DATE NOT NULL,
    `check_in` DATETIME DEFAULT NULL,
    `check_out` DATETIME DEFAULT NULL,
    FOREIGN KEY (`emp_no`) REFERENCES `employees` (`emp_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
