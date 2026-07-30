CREATE TABLE activity_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action ENUM('login', 'logout', 'registration', 'view-page', 'button-click') NOT NULL,
    page VARCHAR(50) NULL,
    target VARCHAR(100) NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(512) NOT NULL,
    created_at DATETIME NOT NULL,
    KEY idx_activity_events_created_at (created_at),
    KEY idx_activity_events_user_created_at (user_id, created_at),
    KEY idx_activity_events_action_created_at (action, created_at),
    KEY idx_activity_events_page_target_created_at (page, target, created_at),
    CONSTRAINT fk_activity_events_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
