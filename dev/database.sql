SET time_zone = '+08:00';
CREATE DATABASE IF NOT EXISTS `db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_name`;


CREATE TABLE `Product` (
	`id` INT UNSIGNED AUTO_INCREMENT,
	`type` ENUM('item', 'group', 'model', 'collection'),
	`isVariantOf` INT UNSIGNED,
	`position` TINYINT UNSIGNED,

	`name` TINYTEXT,
	`sku` TINYTEXT,
	`slogan` TINYTEXT,
	`description` TEXT,

	`keywords` TINYTEXT,
	`color` TINYTEXT,
	`pattern` TINYTEXT,
	`material` TINYTEXT,

	-- QuantitativeValue, only for items and groups
	`weight` INT UNSIGNED,
	`depth` INT UNSIGNED, -- 「長度」，受限於 Schema.org
	`width` INT UNSIGNED,
	`height` INT UNSIGNED,

	-- offers: Offer, for items, groups, and collections
	`price` INT UNSIGNED,
	`availability` ENUM('BackOrder', 'InStock', 'OutOfStock', 'PreOrder'),

	-- `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	-- `updated` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
	-- `deleted` TINYINT UNSIGNED NOT NULL DEFAULT 0,

	PRIMARY KEY (`id`),
	FOREIGN KEY (`isVariantOf`) REFERENCES `Product` (`id`),
	-- UNIQUE KEY (`sku`)
 );


CREATE TABLE `ProductCollection_includesObject`(
	`collection` INT UNSIGNED,
	`typeOfGood` INT UNSIGNED,
	`amountOfThisGood` INT UNSIGNED,

	PRIMARY KEY (`collection`, `typeOfGood`),
	FOREIGN KEY (`collection`) REFERENCES `Product` (`id`),
	FOREIGN KEY (`typeOfGood`) REFERENCES `Product` (`id`)
);


CREATE TABLE `ImageObject` (
	`id` INT UNSIGNED AUTO_INCREMENT,
	`contentUrl` TEXT,
	`headline` TINYTEXT,
	`description` TEXT,

	PRIMARY KEY (`id`)
);


CREATE TABLE `Product_image` (
	`product` INT UNSIGNED,
	`imageObject` INT UNSIGNED,
	`position` TINYINT UNSIGNED,

	PRIMARY KEY (`product`, `imageObject`),
	FOREIGN KEY (`product`) REFERENCES `Product` (`id`),
	FOREIGN KEY (`imageObject`) REFERENCES `ImageObject` (`id`)
);


CREATE TABLE `Person` (
	`identifier` VARCHAR(255),
	`email` TINYTEXT,
	`givenName` TINYTEXT,
	`familyName` TINYTEXT,

	`role` ENUM('guest', 'admin'),

	PRIMARY KEY (`identifier`),
	-- UNIQUE KEY (`email`)
);


CREATE TABLE `log_login` (
	`person` VARCHAR(255),
	`action` ENUM(
	    'login', -- login.php
	    'refresh', -- lib/start.php
	    'logout-by-user', -- login.php
	    'logout-by-expiration', -- lib/start.php
	    'logout-by-refresh-failure' -- lib/start.php
	) NOT NULL,
	`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`remote_addr` VARCHAR(39),
	`request_headers` JSON,

	FOREIGN KEY (`person`) REFERENCES `Person` (`identifier`)
);
