-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 19, 2014 at 06:31 PM
-- Server version: 1.0.8
-- PHP Version: 5.4.4-14+deb7u7

--
-- Database: `lan-seats`
--

-- --------------------------------------------------------

--
-- Table structure for table `floorplan`
--

CREATE TABLE IF NOT EXISTS `floorplan` (
      `x` int(11) NOT NULL,
      `y` int(11) NOT NULL,
      `type` int(11) NOT NULL,
      `ticket` int(11) DEFAULT NULL,
      `reservation_date` timestamp NULL DEFAULT NULL,
      `row` int(11) DEFAULT NULL,
      `seat` int(11) DEFAULT NULL,
      PRIMARY KEY (`x`,`y`),
      UNIQUE KEY `ticket` (`ticket`)

) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `floortypes`
--

CREATE TABLE IF NOT EXISTS `floortypes` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `codename` varchar(50) NOT NULL,
      `displayname` varchar(50) DEFAULT NULL,
      `color` varchar(7) DEFAULT NULL,
      `hovercolor` varchar(7) DEFAULT NULL,
      `border` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`)

) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE IF NOT EXISTS `tickets` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `ticket_code` text NOT NULL,
      `ticket_password` text NOT NULL,
      `holder_name` varchar(256) DEFAULT NULL,
      `holder_mail` varchar(256) DEFAULT NULL,
      `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)

) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `floorplan`
--
ALTER TABLE `floorplan`
  ADD CONSTRAINT `floorplan_ibfk_1` FOREIGN KEY (`ticket`) REFERENCES `tickets` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;


--
-- Dumping data for table `floortypes`
--

INSERT INTO `floortypes` (`id`, `codename`, `displayname`, `color`, `hovercolor`, `border`) VALUES
(0, 'void', NULL, NULL, NULL, 0),
(1, 'wall', 'Vägg', '#000000', NULL, 0),
(2, 'floor', 'Golv', '#FFFFFF', NULL, 0),
(3, 'exit', 'Utgång', '#666666', NULL, 0),
(4, 'entrance', 'Ingång', '#6699CD', NULL, 0),
(5, 'stage', 'Scenen', '#AA00AA', NULL, 0),
(6, 'seat_available', 'Ledig plats', '#00FF00', '#00aa00', 1),
(7, 'seat_taken', 'Bokad plats', '#FF0000', '#aa0000', 1),
(8, 'seat_yours', 'Din plats', '#FFFF00', NULL, 1);



