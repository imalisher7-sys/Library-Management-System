-- phpMyAdmin SQL Dump
-- BlockShelf Library Database

CREATE DATABASE IF NOT EXISTS `blockshelf_db`;
USE `blockshelf_db`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

CREATE TABLE `author` (
  `Author_ID` int(11) NOT NULL,
  `Author_Name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE `category` (
  `Category_ID` int(11) NOT NULL,
  `Category_Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE `member` (
  `Member_ID` int(11) NOT NULL,
  `Name` varchar(150) NOT NULL,
  `Email` varchar(200) NOT NULL,
  `Phone_No` varchar(20) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `Membership_Date` date NOT NULL,
  `Membership_Status` enum('Active','Inactive') DEFAULT 'Active',
  `Role` enum('Admin','Member') DEFAULT 'Member'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE `book` (
  `Book_ID` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `ISBN` varchar(20) DEFAULT NULL,
  `Publication_Year` year(4) DEFAULT NULL,
  `Author_ID` int(11) NOT NULL,
  `Category_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE `borrow_record` (
  `Borrow_ID` int(11) NOT NULL,
  `Member_ID` int(11) NOT NULL,
  `Book_ID` int(11) NOT NULL,
  `Issue_Date` date NOT NULL,
  `Due_Date` date NOT NULL,
  `Return_Date` date DEFAULT NULL,
  `Fine_Amount` decimal(8,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

ALTER TABLE `author`
  ADD PRIMARY KEY (`Author_ID`);

ALTER TABLE `book`
  ADD PRIMARY KEY (`Book_ID`),
  ADD UNIQUE KEY `ISBN` (`ISBN`),
  ADD KEY `Author_ID` (`Author_ID`),
  ADD KEY `Category_ID` (`Category_ID`);

ALTER TABLE `borrow_record`
  ADD PRIMARY KEY (`Borrow_ID`),
  ADD KEY `Member_ID` (`Member_ID`),
  ADD KEY `Book_ID` (`Book_ID`);

ALTER TABLE `category`
  ADD PRIMARY KEY (`Category_ID`),
  ADD UNIQUE KEY `Category_Name` (`Category_Name`);

ALTER TABLE `member`
  ADD PRIMARY KEY (`Member_ID`),
  ADD UNIQUE KEY `Email` (`Email`);

ALTER TABLE `author`
  MODIFY `Author_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `book`
  MODIFY `Book_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `borrow_record`
  MODIFY `Borrow_ID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `category`
  MODIFY `Category_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `member`
  MODIFY `Member_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `book`
  ADD CONSTRAINT `book_ibfk_1` FOREIGN KEY (`Author_ID`) REFERENCES `author` (`Author_ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `book_ibfk_2` FOREIGN KEY (`Category_ID`) REFERENCES `category` (`Category_ID`) ON UPDATE CASCADE;

ALTER TABLE `borrow_record`
  ADD CONSTRAINT `borrow_record_ibfk_1` FOREIGN KEY (`Member_ID`) REFERENCES `member` (`Member_ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `borrow_record_ibfk_2` FOREIGN KEY (`Book_ID`) REFERENCES `book` (`Book_ID`) ON UPDATE CASCADE;

-- Sample data
INSERT INTO `author` (`Author_Name`) VALUES
('F. Scott Fitzgerald'),
('George Orwell'),
('Harper Lee');

INSERT INTO `category` (`Category_Name`) VALUES
('Fiction'),
('Classic'),
('Drama');

INSERT INTO `book` (`Title`, `ISBN`, `Publication_Year`, `Author_ID`, `Category_ID`) VALUES
('The Great Gatsby', '978-0743273565', 1925, 1, 2),
('1984', '978-0451524935', 1949, 2, 1),
('To Kill a Mockingbird', '978-0061120084', 1960, 3, 3);

INSERT INTO `member` (`Name`, `Email`, `Password`, `Membership_Date`, `Membership_Status`, `Role`) VALUES
('Admin User', 'admin@blockshelf.com', 'admin123', CURDATE(), 'Active', 'Admin');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
