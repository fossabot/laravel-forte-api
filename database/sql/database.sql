CREATE TABLE `users` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `public_id` VARCHAR(255) NOT NULL UNIQUE COMMENT 'username',
    `points` BIGINT NOT NULL DEFAULT 0 COMMENT 'virtual currency balance'
);

CREATE TABLE `discords` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `discord_id` CHAR(18) NOT NULL UNIQUE COMMENT 'id of discord user account',
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);

CREATE TABLE `clients` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL UNIQUE COMMENT 'e.g. "skilebot" and "xsolla"',
    `token` VARCHAR(255) NOT NULL COMMENT 'authentication token'
);

CREATE TABLE `items` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NOT NULL COMMENT 'discord bot related to the item',
    `sku` VARCHAR(255) NOT NULL UNIQUE COMMENT 'unique item code from xsolla',
    `name` VARCHAR(255) NOT NULL,
    `image_url` VARCHAR(255) NOT NULL,
    `price` INT NOT NULL COMMENT 'item price in points',
    `enabled` BIT(1) NOT NULL DEFAULT b'1' COMMENT 'whether the item is on sale',
    `consumable` BIT(1) NOT NULL,
    `expiration_time` INT NULL COMMENT 'expiration time in seconds (NULL means permanent)',
    `purchase_limit` INT NULL COMMENT 'max purchase count per user (NULL means infinity)',
    FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
);

CREATE TABLE `user_items` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `item_id` INT NOT NULL,
    `expired` BIT(1) NOT NULL DEFAULT b'0' COMMENT 'whether expiration time has passed or the cash was refunded',
    `consumed` BIT(1) NOT NULL DEFAULT b'0',
    `sync` BIT(1) NOT NULL DEFAULT b'0' COMMENT 'whether bot(items.client_id) is notified of the change in this item',
    `create_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`item_id`) REFERENCES `items` (`id`)
);

CREATE TABLE `receipts` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `client_id` INT NOT NULL COMMENT 'where the payment/refund is completed (e.g. "xsolla")',
    `user_item_id` INT NULL,
    `about_cash` BIT(1) NOT NULL COMMENT 'whether the payment/refund is relate to real cash (not points)',
    `refund` BIT(1) NOT NULL COMMENT 'whether the process is refund (not payment)',
    `points_old` INT NOT NULL,
    `points_new` INT NOT NULL,
    `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
    FOREIGN KEY (`user_item_id`) REFERENCES `user_items` (`id`)
);
