CREATE TABLE IF NOT EXISTS consent_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id INT UNSIGNED NULL,
    channel VARCHAR(40) NOT NULL,
    consent_value TINYINT UNSIGNED NOT NULL DEFAULT 0,
    source VARCHAR(40) NOT NULL,
    content TEXT NOT NULL,
    email VARCHAR(190) NULL,
    phone VARCHAR(32) NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_consent_client_channel (client_id, channel),
    KEY idx_consent_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS personal_data_requests (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id INT UNSIGNED NULL,
    request_type VARCHAR(32) NOT NULL,
    details TEXT NOT NULL,
    source VARCHAR(40) NOT NULL DEFAULT 'account',
    status VARCHAR(24) NOT NULL DEFAULT 'new',
    identity_verified TINYINT UNSIGNED NOT NULL DEFAULT 1,
    admin_note TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    processed_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_privacy_client_status (client_id, status),
    KEY idx_privacy_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
