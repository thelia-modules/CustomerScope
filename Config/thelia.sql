
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- scope
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `scope`;

CREATE TABLE `scope`
(
    `scope_group_id` INTEGER NOT NULL,
    `entity` VARCHAR(255),
    `entity_class` VARCHAR(255),
    `position` INTEGER,
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `FI_scope_scope_group` (`scope_group_id`),
    CONSTRAINT `fk_scope_scope_group`
        FOREIGN KEY (`scope_group_id`)
        REFERENCES `scope_group` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- scope_group
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `scope_group`;

CREATE TABLE `scope_group`
(
    `code` VARCHAR(255),
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- customer_scope
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `customer_scope`;

CREATE TABLE `customer_scope`
(
    `customer_id` INTEGER NOT NULL,
    `scope_id` INTEGER NOT NULL,
    `entity_id` INTEGER NOT NULL,
    `scope_entity` VARCHAR(255),
    PRIMARY KEY (`customer_id`,`scope_id`,`entity_id`),
    INDEX `idx_customer_scope_entity` (`entity_id`),
    INDEX `FI_customer_scope_scope` (`scope_id`),
    CONSTRAINT `fk_customer_scope_customer`
        FOREIGN KEY (`customer_id`)
        REFERENCES `customer` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,
    CONSTRAINT `fk_customer_scope_scope`
        FOREIGN KEY (`scope_id`)
        REFERENCES `scope` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- scope_i18n
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `scope_i18n`;

CREATE TABLE `scope_i18n`
(
    `id` INTEGER NOT NULL,
    `locale` VARCHAR(5) DEFAULT 'en_US' NOT NULL,
    `title` VARCHAR(255),
    `description` LONGTEXT,
    PRIMARY KEY (`id`,`locale`),
    CONSTRAINT `scope_i18n_FK_1`
        FOREIGN KEY (`id`)
        REFERENCES `scope` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- scope_group_i18n
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `scope_group_i18n`;

CREATE TABLE `scope_group_i18n`
(
    `id` INTEGER NOT NULL,
    `locale` VARCHAR(5) DEFAULT 'en_US' NOT NULL,
    `title` VARCHAR(255),
    `description` LONGTEXT,
    PRIMARY KEY (`id`,`locale`),
    CONSTRAINT `scope_group_i18n_FK_1`
        FOREIGN KEY (`id`)
        REFERENCES `scope_group` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
