CREATE TABLE `games` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `player_x` int(11) DEFAULT NULL,
  `player_o` int(11) DEFAULT NULL,
  `winner` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8;

INSERT INTO `games` (`id`, `player_x`, `player_o`, `winner`, `created_at`)
VALUES
	(100, 1, 2, NULL, '2014-06-02 18:06:23');
