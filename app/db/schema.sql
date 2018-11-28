DROP DATABASE IF EXISTS reviewheroes;
CREATE DATABASE reviewheroes;
USE reviewheroes;

CREATE TABLE Users (
  id INT NOT NULL,
  username VARCHAR(80) NOT NULL,
  avatar_url VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX (username)
) ENGINE=INNODB;

CREATE TABLE Participations (
  pullRequestId INT NOT NULL,
  pullRequestOwnerId INT NOT NULL,
  pullRequestUrl VARCHAR(255) DEFAULT NULL,
  pullRequestTitle VARCHAR(255) DEFAULT NULL,
  userId INT,
  pullState VARCHAR(255) NOT NULL,
  type VARCHAR(255) NOT NULL,
  reviewState VARCHAR(255) NOT NULL,
  creationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=INNODB;

CREATE TABLE Builds (
  userId INT NOT NULL,
  commitId VARCHAR(255) NOT NULL,
  context VARCHAR(255) NOT NULL,
  state VARCHAR(80) NOT NULL,
  description VARCHAR(255) NOT NULL,
  target_url VARCHAR(255) NOT NULL,
  creationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=INNODB;

CREATE TABLE `Subscriptions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `creationDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;
