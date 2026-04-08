-- ============================================
-- FitPulse - Data Migration for Render
-- Run this on the Render PostgreSQL database
-- after tables are created
-- ============================================

-- Disable foreign key checks temporarily
SET session_replication_role = 'replica';

-- Users data
INSERT INTO users (id, full_name, email, phone, password, role, security_question, security_answer, status, membership_plan, assigned_trainer, profile_photo, joined_date, updated_at) VALUES
(1, 'System Admin', 'admin@fitpulse.com', '+254700000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'What is the name of this system?', '$2y$10$e0MYzXyjpJS7Pd0RVkATkOh0se/3ID1o5c.Y.3oeX4fBMGMEyd2Ge', 'active', 'premium', NULL, NULL, '2026-04-06 13:09:33.011703', '2026-04-06 13:09:33.011703'),
(2, 'horace ayatta', 'horaceayatta@gmail.com', '0711191232', '$2y$10$PeA0UG8PL8kGmPXDpIatqOgN5kUci3EvJ2ZGup7RyTHE280ei4sSC', 'admin', 'What is the name of your first pet?', '$2y$10$NZYspQCsqNWHq4knlC4txeQzWSDbVuo21y.1rYWrBwLR5dH//ilX6', 'active', 'basic', NULL, NULL, '2026-04-08 09:10:05.21648', '2026-04-08 09:10:05.21648'),
(3, 'isaac mwangi', 'isaacwmwangi001@gmail.com', '0743658504', '$2y$10$An9x1AF5E1U4Yd9Hv4M/cOcUbRM.lwAyRfkC/fEg6rAttkNgHIYPK', 'member', 'What is the name of your first pet?', '$2y$10$vmWeI3WBxlyQJv/KYjRbtOOlBWEjRMKqBc8I9zIPu0w5PPzGg0hBy', 'active', 'basic', NULL, NULL, '2026-04-08 09:14:01.640325', '2026-04-08 09:14:01.640325')
ON CONFLICT (id) DO NOTHING;

-- Invoices data
INSERT INTO invoices (id, user_id, invoice_number, description, amount, tax, total, status, due_date, paid_date, payment_method, created_by, created_at, updated_at) VALUES
(1, 3, 'INV-20260408-3814', 'Equipment Rental', 5000.00, 0.00, 5000.00, 'pending', '2026-04-10', NULL, NULL, 2, '2026-04-08 09:16:53.062225', '2026-04-08 09:16:53.062225')
ON CONFLICT (id) DO NOTHING;

-- Reset sequences to correct values
SELECT setval('users_id_seq', (SELECT COALESCE(MAX(id), 0) FROM users) + 1, false);
SELECT setval('invoices_id_seq', (SELECT COALESCE(MAX(id), 0) FROM invoices) + 1, false);
SELECT setval('attendance_id_seq', (SELECT COALESCE(MAX(id), 0) FROM attendance) + 1, false);
SELECT setval('calories_id_seq', (SELECT COALESCE(MAX(id), 0) FROM calories) + 1, false);
SELECT setval('reports_id_seq', (SELECT COALESCE(MAX(id), 0) FROM reports) + 1, false);

-- Re-enable foreign key checks
SET session_replication_role = 'origin';
