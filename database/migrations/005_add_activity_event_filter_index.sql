CREATE INDEX idx_activity_events_user_action_created_at
    ON activity_events (user_id, action, created_at);
