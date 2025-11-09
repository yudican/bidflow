-- Create activities table
CREATE TABLE IF NOT EXISTS activities (
    id BIGSERIAL PRIMARY KEY,
    activity_type VARCHAR(191) NOT NULL DEFAULT 'other',
    title VARCHAR(191) NOT NULL,
    description TEXT NULL,
    user_name VARCHAR(191) NULL,
    user_id VARCHAR(191) NULL,
    reference_id VARCHAR(191) NULL,
    reference_type VARCHAR(191) NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS activities_activity_type_index ON activities (activity_type);
CREATE INDEX IF NOT EXISTS activities_user_id_index ON activities (user_id);
CREATE INDEX IF NOT EXISTS activities_reference_id_index ON activities (reference_id);
CREATE INDEX IF NOT EXISTS activities_created_at_index ON activities (created_at);

-- Insert sample activities data
INSERT INTO activities (activity_type, title, description, user_name, user_id, reference_id, reference_type, metadata, created_at, updated_at) VALUES
('sales_order', 'Input data Sales Order [Sales Order Number]', 'User melakukan input data sales order baru', '[User Input]', 'user_001', 'SO_001', 'sales_order', '{"order_number": "SO_001", "customer": "Customer A"}', '2025-07-03 00:00:00', '2025-07-03 00:00:00'),

('stock_count', 'Input data Stock Count [Stock Count Number]', 'User melakukan input data stock count baru', '[User Input]', 'user_002', 'SC_001', 'stock_count', '{"count_id": "SC_001", "location": "Warehouse A"}', '2025-07-03 00:00:00', '2025-07-03 00:00:00'),

('other', 'User mengubah profile', 'User melakukan perubahan data profile', '[User Input]', 'user_003', 'profile_001', 'user_profile', '{"field_changed": "email", "old_value": "old@email.com", "new_value": "new@email.com"}', '2025-07-03 00:00:00', '2025-07-03 00:00:00'),

('stock_count', 'Update data Stock Count [Stock Count Number 2]', 'User melakukan update data stock count', '[User Input]', 'user_004', 'SC_002', 'stock_count', '{"count_id": "SC_002", "action": "update"}', '2025-07-02 00:00:00', '2025-07-02 00:00:00'),

('sales_order', 'Delete data Sales Order [Sales Order Number 2]', 'User menghapus data sales order', '[User Input]', 'user_005', 'SO_002', 'sales_order', '{"order_number": "SO_002", "action": "delete"}', '2025-07-02 00:00:00', '2025-07-02 00:00:00'),

('other', 'User login ke sistem', 'User berhasil login ke sistem', '[User Input]', 'user_006', 'login_001', 'authentication', '{"ip_address": "192.168.1.1", "browser": "Chrome"}', '2025-07-01 00:00:00', '2025-07-01 00:00:00'),

('stock_count', 'Input data Stock Count [Stock Count Number 3]', 'User melakukan input data stock count untuk warehouse B', '[User Input]', 'user_007', 'SC_003', 'stock_count', '{"count_id": "SC_003", "location": "Warehouse B"}', '2025-07-01 00:00:00', '2025-07-01 00:00:00'),

('other', 'Export data customer', 'User melakukan export data customer ke Excel', '[User Input]', 'user_008', 'export_001', 'data_export', '{"file_type": "excel", "record_count": 150}', '2025-06-30 00:00:00', '2025-06-30 00:00:00'),

('sales_order', 'Input data Sales Order [Sales Order Number 3]', 'User melakukan input data sales order untuk customer baru', '[User Input]', 'user_009', 'SO_003', 'sales_order', '{"order_number": "SO_003", "customer": "Customer B", "amount": 1500000}', '2025-06-30 00:00:00', '2025-06-30 00:00:00'),

('stock_count', 'Approve data Stock Count [Stock Count Number 4]', 'User melakukan approval data stock count', '[User Input]', 'user_010', 'SC_004', 'stock_count', '{"count_id": "SC_004", "action": "approve", "approved_by": "Manager A"}', '2025-06-29 00:00:00', '2025-06-29 00:00:00');

-- Verification query to check data
-- SELECT COUNT(*) as total_activities FROM activities;
-- SELECT activity_type, COUNT(*) as count FROM activities GROUP BY activity_type; 