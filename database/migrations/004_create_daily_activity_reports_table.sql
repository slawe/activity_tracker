CREATE TABLE daily_activity_reports (
    report_date DATE PRIMARY KEY,
    page_a_views BIGINT UNSIGNED NOT NULL DEFAULT 0,
    page_b_views BIGINT UNSIGNED NOT NULL DEFAULT 0,
    buy_cow_clicks BIGINT UNSIGNED NOT NULL DEFAULT 0,
    download_clicks BIGINT UNSIGNED NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
