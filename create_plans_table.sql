-- ============================================================
-- NetServe — Dynamic Plan Management System
-- Run this once in your airtel_db database via phpMyAdmin
-- or MySQL command line.
-- ============================================================

USE airtel_db;

-- ── Create plans table ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS `plans` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `plan_name`   VARCHAR(150) NOT NULL,
    `category`    VARCHAR(50)  NOT NULL COMMENT 'Broadband | Mobile | DTH',
    `price`       VARCHAR(20)  NOT NULL,
    `description` TEXT         NULL,
    `validity`    VARCHAR(100) NULL,
    `status`      VARCHAR(20)  NOT NULL DEFAULT 'Active',
    `image`       VARCHAR(255) NULL,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Seed: Broadband Plans ────────────────────────────────────
INSERT INTO `plans` (`plan_name`, `category`, `price`, `description`, `validity`, `status`) VALUES
('Basic Plan',   'Broadband', '499',  '40 Mbps Speed | Unlimited Data | Free Landline | Wi-Fi Router Included', '1 Month', 'Active'),
('Family Plan',  'Broadband', '799',  '100 Mbps Speed | Unlimited Data | Xstream Premium | Free Landline',       '1 Month', 'Active'),
('Premium Plan', 'Broadband', '1199', '300 Mbps Speed | Unlimited Data | Xstream Premium + Disney+ Hotstar | Free Landline', '1 Month', 'Active');

-- ── Seed: Mobile Plans ───────────────────────────────────────
INSERT INTO `plans` (`plan_name`, `category`, `price`, `description`, `validity`, `status`) VALUES
('₹299 Prepaid',  'Mobile', '299', '1.5GB/Day | Unlimited Calls | 100 SMS/Day | Prepaid',  '28 Days', 'Active'),
('₹399 Prepaid',  'Mobile', '399', '2GB/Day | Unlimited Calls | 100 SMS/Day | Prepaid',    '56 Days', 'Active'),
('₹499 Prepaid',  'Mobile', '499', '2.5GB/Day | Unlimited Calls | 100 SMS/Day | Prepaid',  '56 Days', 'Active'),
('₹399 Postpaid', 'Mobile', '399', '75GB Data | Unlimited Calls | 100 SMS/Day | Postpaid', 'Monthly', 'Active'),
('₹499 Postpaid', 'Mobile', '499', '100GB Data | Unlimited Calls | 100 SMS/Day | Postpaid','Monthly', 'Active'),
('₹799 Postpaid', 'Mobile', '799', '150GB Data | Unlimited Calls | 100 SMS/Day | Postpaid','Monthly', 'Active');

-- ── Seed: DTH Plans ─────────────────────────────────────────
INSERT INTO `plans` (`plan_name`, `category`, `price`, `description`, `validity`, `status`) VALUES
('Basic Pack',   'DTH', '249', '200+ Channels | Standard Definition',             '1 Month', 'Active'),
('Family Pack',  'DTH', '349', '300+ Channels | Family Entertainment Pack',       '1 Month', 'Active'),
('Premium Pack', 'DTH', '499', '400+ Channels | HD Quality | Sports & Movies',   '1 Month', 'Active');
