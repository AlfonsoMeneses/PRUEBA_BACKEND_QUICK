/* SQL Manager Lite for MySQL                              5.8.0.53936 */
/* ------------------------------------------------------------------- */
/* Host     : localhost                                                */
/* Port     : 3306                                                     */
/* Database : backend_quick_dev                                        */


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES 'utf8mb4' */;

SET FOREIGN_KEY_CHECKS=0;

CREATE DATABASE `backend_quick_dev`
    CHARACTER SET 'utf8'
    COLLATE 'utf8_general_ci';

USE `backend_quick_dev`;

/* Structure for the `users` table : */

CREATE TABLE `users` (
  `id` TINYINT(4) NOT NULL AUTO_INCREMENT COMMENT 'id del usuario',
  `first_name` VARCHAR(128) COLLATE utf8_general_ci NOT NULL COMMENT 'nombre del usuario',
  `last_name` VARCHAR(128) COLLATE utf8_general_ci NOT NULL,
  `email` VARCHAR(64) COLLATE utf8_general_ci NOT NULL,
  `password` VARCHAR(1000) COLLATE utf8_general_ci NOT NULL,
  `token` VARCHAR(1000) COLLATE utf8_general_ci NOT NULL DEFAULT '-1',
  `age` INTEGER(99) DEFAULT NULL,
  `image` VARCHAR(255) COLLATE utf8_general_ci DEFAULT NULL,
  `description` VARCHAR(255) COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `active` TINYINT(4) NOT NULL DEFAULT 1,
  PRIMARY KEY USING BTREE (`id`)
) ENGINE=InnoDB
AUTO_INCREMENT=28 ROW_FORMAT=DYNAMIC CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;