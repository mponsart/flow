-- Flow – Schéma de base de données
-- Encodage : utf8mb4

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---
-- Utilisateurs
-- ---
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `google_id`  VARCHAR(100) NOT NULL DEFAULT '',
  `email`      VARCHAR(255) NOT NULL,
  `name`       VARCHAR(255) NOT NULL DEFAULT '',
  `avatar`     VARCHAR(500) NOT NULL DEFAULT '',
  `created_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_google` (`google_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---
-- Tiers (clients / fournisseurs)
-- ---
CREATE TABLE IF NOT EXISTS `tiers` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `dolibarr_id`  INT          NULL,
  `name`         VARCHAR(255) NOT NULL,
  `email`        VARCHAR(255) NOT NULL DEFAULT '',
  `phone`        VARCHAR(50)  NOT NULL DEFAULT '',
  `address`      VARCHAR(500) NOT NULL DEFAULT '',
  `is_active`    TINYINT(1)  NOT NULL DEFAULT 1,
  `risk_score`   TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `risk_level`   ENUM('low','medium','high') NOT NULL DEFAULT 'low',
  `created_at`   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tiers_dolibarr` (`dolibarr_id`),
  KEY `idx_tiers_risk` (`risk_level`),
  KEY `idx_tiers_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---
-- Produits
-- ---
CREATE TABLE IF NOT EXISTS `products` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `dolibarr_id`  INT          NULL,
  `ref`          VARCHAR(100) NOT NULL DEFAULT '',
  `label`        VARCHAR(255) NOT NULL DEFAULT '',
  `price`        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `type`         TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0=product, 1=service',
  `created_at`   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_products_dolibarr` (`dolibarr_id`),
  UNIQUE KEY `uq_products_ref_type` (`ref`, `type`),
  KEY `idx_products_ref` (`ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---
-- Factures
-- ---
CREATE TABLE IF NOT EXISTS `invoices` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `dolibarr_id`  INT          NULL,
  `ref`          VARCHAR(100) NOT NULL DEFAULT '',
  `tiers_id`     INT UNSIGNED NULL,
  `date_invoice` DATE         NULL,
  `date_due`     DATE         NULL,
  `date_paid`    DATE         NULL,
  `total_ht`     DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `total_ttc`    DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `status`       TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0=draft,1=validated,2=paid,3=abandoned',
  `is_overdue`   TINYINT(1)  NOT NULL DEFAULT 0,
  `created_at`   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invoices_dolibarr` (`dolibarr_id`),
  KEY `idx_invoices_tiers` (`tiers_id`),
  KEY `idx_invoices_status` (`status`),
  KEY `idx_invoices_date` (`date_invoice`),
  KEY `idx_invoices_overdue` (`is_overdue`),
  CONSTRAINT `fk_invoices_tiers`
    FOREIGN KEY (`tiers_id`) REFERENCES `tiers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---
-- Lignes de factures
-- ---
CREATE TABLE IF NOT EXISTS `invoice_lines` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id`   INT UNSIGNED NOT NULL,
  `product_id`   INT UNSIGNED NULL,
  `description`  VARCHAR(500) NOT NULL DEFAULT '',
  `qty`          DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  `unit_price`   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_ht`     DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_lines_invoice` (`invoice_id`),
  KEY `idx_lines_product` (`product_id`),
  CONSTRAINT `fk_lines_invoice`
    FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lines_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---
-- Paiements
-- ---
CREATE TABLE IF NOT EXISTS `payments` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `dolibarr_id`  INT          NULL,
  `invoice_id`   INT UNSIGNED NULL,
  `tiers_id`     INT UNSIGNED NULL,
  `amount`       DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `date_payment` DATE         NULL,
  `method`       ENUM('CB','virement','chèque','espèces','inconnu') NOT NULL DEFAULT 'inconnu',
  `method_label` VARCHAR(100) NOT NULL DEFAULT '',
  `created_at`   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payments_dolibarr` (`dolibarr_id`),
  KEY `idx_payments_invoice` (`invoice_id`),
  KEY `idx_payments_tiers` (`tiers_id`),
  KEY `idx_payments_date` (`date_payment`),
  CONSTRAINT `fk_payments_invoice`
    FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_payments_tiers`
    FOREIGN KEY (`tiers_id`) REFERENCES `tiers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---
-- Cache KPI
-- ---
CREATE TABLE IF NOT EXISTS `kpi_cache` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key_name`      VARCHAR(100) NOT NULL,
  `value`         MEDIUMTEXT   NOT NULL,
  `period`        VARCHAR(50)  NOT NULL DEFAULT 'current',
  `calculated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kpi_key_period` (`key_name`, `period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---
-- Journaux de synchronisation
-- ---
CREATE TABLE IF NOT EXISTS `sync_logs` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_type`       VARCHAR(50)  NOT NULL,
  `status`            ENUM('running','success','error','warning') NOT NULL DEFAULT 'running',
  `message`           TEXT         NULL,
  `records_processed` INT UNSIGNED NOT NULL DEFAULT 0,
  `records_failed`    INT UNSIGNED NOT NULL DEFAULT 0,
  `started_at`        DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at`      DATETIME    NULL,
  PRIMARY KEY (`id`),
  KEY `idx_logs_entity` (`entity_type`),
  KEY `idx_logs_status` (`status`),
  KEY `idx_logs_started` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---
-- Paramètres applicatifs
-- ---
CREATE TABLE IF NOT EXISTS `settings` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key_name`   VARCHAR(100) NOT NULL,
  `value`      TEXT         NOT NULL DEFAULT '',
  `updated_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
