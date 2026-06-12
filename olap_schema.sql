-- ============================================================
-- olap_schema.sql
-- Star Schema for OLAP / Analytics
-- Run this AFTER fundraising_db.sql
-- ============================================================

-- -------------------------------------------------------
-- DIMENSION TABLE: Time
-- Hierarchy: Year > Quarter > Month > Day (rubric requires this)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS dim_time (
    time_id      INT AUTO_INCREMENT PRIMARY KEY,
    full_date    DATE NOT NULL,
    day_of_month TINYINT NOT NULL,
    day_name     VARCHAR(10) NOT NULL,
    month_num    TINYINT NOT NULL,
    month_name   VARCHAR(10) NOT NULL,
    quarter      TINYINT NOT NULL,
    year         YEAR NOT NULL,
    is_weekend   TINYINT(1) DEFAULT 0,
    UNIQUE KEY (full_date)
);

-- -------------------------------------------------------
-- DIMENSION TABLE: Campaign
-- Descriptive attributes for campaign context
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS dim_campaign (
    campaign_sk  INT AUTO_INCREMENT PRIMARY KEY,  -- surrogate key
    campaign_id  INT NOT NULL,                    -- links back to OLTP
    title        VARCHAR(200) NOT NULL,
    category     VARCHAR(100) NOT NULL,
    goal_amount  DECIMAL(12,2) NOT NULL,
    start_date   DATE NOT NULL,
    end_date     DATE NOT NULL,
    status       VARCHAR(50) NOT NULL
);

-- -------------------------------------------------------
-- DIMENSION TABLE: Donor
-- Descriptive attributes for donor context
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS dim_donor (
    donor_sk     INT AUTO_INCREMENT PRIMARY KEY,  -- surrogate key
    user_id      INT NOT NULL,                    -- links back to OLTP
    username     VARCHAR(100) NOT NULL,
    full_name    VARCHAR(200) NOT NULL,
    region_state VARCHAR(100) DEFAULT NULL,
    user_role    VARCHAR(50) DEFAULT NULL,
    joined_date  DATE DEFAULT NULL
);

-- -------------------------------------------------------
-- DIMENSION TABLE: Payment Method
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS dim_payment_method (
    payment_method_id INT AUTO_INCREMENT PRIMARY KEY,
    method_name       VARCHAR(50) NOT NULL,
    method_type       VARCHAR(50) NOT NULL,
    UNIQUE KEY (method_name)
);

-- Pre-fill payment methods
INSERT IGNORE INTO dim_payment_method (method_name, method_type) VALUES
('Credit_Card',   'Online'),
('PayPal',        'Online'),
('G_Cash',        'Online'),
('Bank_Transfer', 'Online'),
('Cash',          'Offline'),
('Check',         'Offline'),
('Manual',        'Offline');

-- -------------------------------------------------------
-- FACT TABLE: Donations
-- Center of the star - holds measurable metrics
-- Grain: one row per completed donation
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS fact_donations (
    fact_id               INT AUTO_INCREMENT PRIMARY KEY,
    time_id               INT NOT NULL,
    campaign_sk           INT NOT NULL,
    donor_sk              INT NOT NULL,
    payment_method_id     INT NOT NULL,
    donation_amount       DECIMAL(12,2) NOT NULL,
    transaction_reference VARCHAR(50) DEFAULT NULL,
    donation_id           INT NOT NULL,
    loaded_at             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (time_id)           REFERENCES dim_time(time_id),
    FOREIGN KEY (campaign_sk)       REFERENCES dim_campaign(campaign_sk),
    FOREIGN KEY (donor_sk)          REFERENCES dim_donor(donor_sk),
    FOREIGN KEY (payment_method_id) REFERENCES dim_payment_method(payment_method_id)
);

-- -------------------------------------------------------
-- FACT TABLE: Campaign Performance
-- Pre-aggregated monthly summary for faster reporting
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS fact_campaign_performance (
    perf_id        INT AUTO_INCREMENT PRIMARY KEY,
    time_id        INT NOT NULL,
    campaign_sk    INT NOT NULL,
    total_raised   DECIMAL(12,2) DEFAULT 0,
    donor_count    INT DEFAULT 0,
    donation_count INT DEFAULT 0,
    avg_donation   DECIMAL(12,2) DEFAULT 0,
    goal_amount    DECIMAL(12,2) DEFAULT 0,
    progress_pct   DECIMAL(5,2) DEFAULT 0,
    UNIQUE KEY (time_id, campaign_sk),
    FOREIGN KEY (time_id)     REFERENCES dim_time(time_id),
    FOREIGN KEY (campaign_sk) REFERENCES dim_campaign(campaign_sk)
);