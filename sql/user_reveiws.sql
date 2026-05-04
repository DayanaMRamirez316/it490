CREATE TABLE IF NOT EXISTS `data`.`Games` (
        `game_id` INT NOT NULL AUTO_INCREMENT,
        `game` VARCHAR(100) NOT NULL,
        `genre` VARCHAR(30) NOT NULL,
	`release_date` VARCHAR(20) NOT NULL,
        `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

        PRIMARY KEY(`game_id`),
	UNIQUE(`game`)
);


CREATE TABLE IF NOT EXISTS `data`.`User_Reviews` (
	`user_id` INT NOT NULL,
	`game_id` INT NOT NULL,
	`rating` INT NOT NULL,
	`text` VARCHAR(5000),
	`is_private` TINYINT(1) NOT NULL DEFAULT 0,
	`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

	PRIMARY KEY(`user_id`, `game_id`),


        CONSTRAINT `chk_rating_range`
        CHECK (`rating` BETWEEN 1 AND 100),

	CONSTRAINT `fk_user_review`
		FOREIGN KEY(user_id)
		REFERENCES `Users`(`id`)
		ON DELETE CASCADE,

	
   	CONSTRAINT `fk_game_review`
        	FOREIGN KEY (`game_id`)
        	REFERENCES `Games` (`game_id`)
        	ON DELETE CASCADE

);

CREATE TABLE IF NOT EXISTS `data`.`User_Following`(
	`user_id` INT NOT NULL,
	`following_id` INT NOT NULL,
	
	PRIMARY KEY(`user_id`, `following_id`),

    	CONSTRAINT `fk_user_follows`
        	FOREIGN KEY (`user_id`)
        	REFERENCES `Users` (`id`)
        	ON DELETE CASCADE

);


