CREATE TABLE IF NOT EXISTS balcony_move_index_observations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    observed_at DATE NOT NULL,
    close_value DECIMAL(8,4) NOT NULL,
    day_change DECIMAL(8,4) NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY balcony_move_observed_at_unique (observed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
