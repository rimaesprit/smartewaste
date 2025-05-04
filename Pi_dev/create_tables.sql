-- Créer la table user
CREATE TABLE IF NOT EXISTS `user` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `email` VARCHAR(180) NOT NULL,
  `roles` JSON NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(255) NOT NULL,
  `last_name` VARCHAR(255) NOT NULL,
  `is_verified` TINYINT(1) NOT NULL,
  `reset_token` VARCHAR(255) DEFAULT NULL,
  `verification_token` VARCHAR(255) DEFAULT NULL,
  `email_verification_token` VARCHAR(255) DEFAULT NULL,
  `temp_email` VARCHAR(180) DEFAULT NULL,
  UNIQUE INDEX `UNIQ_8D93D649E7927C74` (`email`),
  PRIMARY KEY(`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Créer la table temp_email
CREATE TABLE IF NOT EXISTS `temp_email` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `user_id` INT NOT NULL,
  `email` VARCHAR(180) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  INDEX `IDX_8D93D649A76ED395` (`user_id`),
  PRIMARY KEY(`id`),
  CONSTRAINT `FK_TEMP_EMAIL_USER` FOREIGN KEY (`user_id`)
  REFERENCES `user` (`id`) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB; 