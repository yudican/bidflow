-- SQL Query to create contact_group_doh_settings table
-- Execute this manually in your PostgreSQL database

CREATE TABLE contact_group_doh_settings (
    id SERIAL PRIMARY KEY,
    contact_group_id INTEGER NOT NULL,
    doh_days INTEGER NOT NULL CHECK (doh_days IN (90, 30, 7)),
    notification_type VARCHAR(20) NOT NULL CHECK (notification_type IN ('email', 'whatsapp')),
    email_template TEXT,
    whatsapp_template TEXT,
    is_active BOOLEAN DEFAULT true,
    created_by CHAR(36),
    updated_by CHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    CONSTRAINT fk_contact_group_doh_settings_contact_group_id 
        FOREIGN KEY (contact_group_id) REFERENCES contact_groups(id) ON DELETE CASCADE,
    
    -- Unique constraint to prevent duplicate DOH days for same contact group
    CONSTRAINT unique_contact_group_doh_days 
        UNIQUE (contact_group_id, doh_days)
);

-- Create indexes for better performance
CREATE INDEX idx_contact_group_doh_settings_contact_group_id ON contact_group_doh_settings(contact_group_id);
CREATE INDEX idx_contact_group_doh_settings_doh_days ON contact_group_doh_settings(doh_days);
CREATE INDEX idx_contact_group_doh_settings_is_active ON contact_group_doh_settings(is_active);

-- Add comments for documentation
COMMENT ON TABLE contact_group_doh_settings IS 'DOH (Days on Hand) settings for contact groups with notification preferences';
COMMENT ON COLUMN contact_group_doh_settings.doh_days IS 'Days on hand threshold (90, 30, or 7 days)';
COMMENT ON COLUMN contact_group_doh_settings.notification_type IS 'Notification method: email or whatsapp (only one allowed per setting)';
COMMENT ON COLUMN contact_group_doh_settings.email_template IS 'Email template content for notifications';
COMMENT ON COLUMN contact_group_doh_settings.whatsapp_template IS 'WhatsApp template content for notifications';