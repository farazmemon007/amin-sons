-- ============================================================
-- Journal Voucher Table — Run this in phpMyAdmin / MySQL CLI
-- Database: final_Fahad_Bhai
-- ============================================================

CREATE TABLE IF NOT EXISTS `journal_vouchers` (
  `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `jvid`               VARCHAR(255)    NOT NULL UNIQUE COMMENT 'JVID-00001',
  `voucher_date`       DATE            NOT NULL,
  `entry_date`         DATE            NOT NULL,
  `remarks`            TEXT            NULL,
  `branch_id`          BIGINT UNSIGNED NULL,
  `created_by`         BIGINT UNSIGNED NULL,

  -- DEBIT SIDE (Vendor / Customer)
  `debit_party_type`   VARCHAR(50)     NOT NULL COMMENT 'vendor | customer | account',
  `debit_party_id`     BIGINT UNSIGNED NULL,

  -- CREDIT SIDE (Customer)
  `credit_party_type`  VARCHAR(50)     NOT NULL COMMENT 'vendor | customer | account',
  `credit_party_id`    BIGINT UNSIGNED NULL,

  `amount`             DECIMAL(15,2)   NOT NULL DEFAULT 0.00,

  -- Extra JSON fields
  `narration_id`       TEXT            NULL COMMENT 'JSON array of narration IDs',
  `reference_no`       TEXT            NULL COMMENT 'JSON array of reference numbers',

  `status`             ENUM('draft','posted') NOT NULL DEFAULT 'posted',

  `created_at`         TIMESTAMP       NULL,
  `updated_at`         TIMESTAMP       NULL,
  `deleted_at`         TIMESTAMP       NULL,

  PRIMARY KEY (`id`),
  INDEX `idx_branch_id`       (`branch_id`),
  INDEX `idx_debit_party_id`  (`debit_party_id`),
  INDEX `idx_credit_party_id` (`credit_party_id`),
  INDEX `idx_voucher_date`    (`voucher_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
