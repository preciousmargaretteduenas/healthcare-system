USE healthcare_system;

-- Use this file if you already created the database before adding tracking codes.

ALTER TABLE appointments
    ADD COLUMN IF NOT EXISTS tracking_code VARCHAR(20) NULL AFTER appointment_id;

UPDATE appointments
SET tracking_code = CONCAT('OLD-', LPAD(appointment_id, 6, '0'))
WHERE tracking_code IS NULL OR tracking_code = '';

ALTER TABLE appointments
    MODIFY tracking_code VARCHAR(20) NOT NULL;

ALTER TABLE appointments
    ADD UNIQUE KEY IF NOT EXISTS uq_appointment_tracking (tracking_code);

ALTER TABLE queue
    ADD UNIQUE KEY IF NOT EXISTS uq_queue_appointment (appointment_id);
\nCREATE TABLE IF NOT EXISTS notifications (\n    notification_id INT AUTO_INCREMENT PRIMARY KEY,\n    title VARCHAR(150) NOT NULL,\n    message TEXT NOT NULL,\n    appointment_id INT NULL,\n    is_read TINYINT(1) NOT NULL DEFAULT 0,\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    CONSTRAINT fk_notification_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL\n);\n