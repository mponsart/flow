-- Migration 001 – Table des dépenses manuelles
-- À exécuter via : mysql -u USER -p DB < database/migrations/001_create_expenses.sql

CREATE TABLE IF NOT EXISTS `expenses` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `label`       VARCHAR(255) NOT NULL,
  `amount`      DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `category`    VARCHAR(100) NOT NULL DEFAULT 'Autre',
  `recurrence`  ENUM('monthly','annual','one_time') NOT NULL DEFAULT 'monthly',
  `expense_date` DATE NULL COMMENT 'Date de référence (pour one_time)',
  `note`        TEXT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_expenses_recurrence` (`recurrence`),
  KEY `idx_expenses_category`   (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
