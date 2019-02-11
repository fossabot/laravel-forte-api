CREATE TABLE `users` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `public_id` VARCHAR(255) NOT NULL UNIQUE,
    `points` BIGINT NOT NULL DEFAULT 0
);

CREATE TABLE `discords` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `discord_id` CHAR(18) NOT NULL UNIQUE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);

CREATE TABLE `clients` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL UNIQUE,
    `token` VARCHAR(255) NOT NULL
);

CREATE TABLE `items` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NOT NULL,
    `sku` VARCHAR(255) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `image_url` VARCHAR(255) NOT NULL,
    `price` INT NOT NULL,
    `enabled` BIT(1) NOT NULL DEFAULT b'1',
    `consumable` BIT(1) NOT NULL,
    `expiration_time` INT NULL,
    `purchase_limit` INT NULL,
    FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
);

CREATE TABLE `user_items` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `item_id` INT NOT NULL,
    `expired` BIT(1) NOT NULL DEFAULT b'0',
    `consumed` BIT(1) NOT NULL DEFAULT b'0',
    `sync` BIT(1) NOT NULL DEFAULT b'0',
    `create_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`item_id`) REFERENCES `items` (`id`)
);

CREATE TABLE `receipts` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `client_id` INT NOT NULL,
    `user_item_id` INT NULL,
    `about_cash` BIT(1) NOT NULL,
    `refund` BIT(1) NOT NULL,
    `points_old` INT NOT NULL,
    `points_new` INT NOT NULL,
    `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
    FOREIGN KEY (`user_item_id`) REFERENCES `user_items` (`id`)
);
