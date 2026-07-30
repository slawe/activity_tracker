CREATE TABLE user_page_states (
    user_id BIGINT UNSIGNED NOT NULL,
    page VARCHAR(50) NOT NULL,
    action VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (user_id, page, action),
    CONSTRAINT fk_user_page_states_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
