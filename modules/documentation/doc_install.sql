--
-- Таблица разделов документации
--
DROP TABLE IF EXISTS `documentation_sections`;
CREATE TABLE `documentation_sections` (
    `id`          INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `parent_id`   INT(10) UNSIGNED    NOT NULL DEFAULT 1,      -- привязка к родительскому разделу для подразделов
    `name`        VARCHAR(255)        NOT NULL DEFAULT '',     -- название
    `description` TEXT                NULL,                    -- какое-то описание
    `sort`        INT(5) UNSIGNED     NOT NULL DEFAULT 100,    -- поле для указания сортировки
    PRIMARY KEY (`id`),
    KEY `parent_id` (`parent_id`)
)
ENGINE = MyISAM
DEFAULT CHARSET = utf8mb4;

--
-- Таблица со статьями документации
--
DROP TABLE IF EXISTS `documentation_articles`;
CREATE TABLE `documentation_articles` (
    `id`               INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`             VARCHAR(255)        NOT NULL DEFAULT '',     -- название
    `description`      TEXT                NOT NULL,                -- описание
    `parent_id`        INT(10) UNSIGNED    NOT NULL DEFAULT 1,      -- привязка к разделу
    `status`           TINYINT(1)          NOT NULL DEFAULT 0,      -- опубликовано или нет
    `author`           VARCHAR(255)        NOT NULL DEFAULT '',     -- автор
    `date_of_creation` INT(10) UNSIGNED    NOT NULL DEFAULT 0,      -- дата создания
    `date_of_change`   INT(10) UNSIGNED    NOT NULL DEFAULT 0,      -- дата изменения
    `cms_version`      VARCHAR(10)         NOT NULL DEFAULT '',     -- версия CMS
    `text`             LONGTEXT            NOT NULL,                -- текст статьи
    PRIMARY KEY (`id`),
    KEY `parent_id` (`parent_id`)
)
ENGINE = MyISAM
DEFAULT CHARSET = utf8mb4;
