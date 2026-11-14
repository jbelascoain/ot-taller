-- Upgrade para instalaciones existentes: contraseña obligatoria + SMTP
ALTER TABLE users
    ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER active;

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('smtp_host', ''),
    ('smtp_port', '587'),
    ('smtp_crypto', 'tls'),
    ('smtp_user', ''),
    ('smtp_pass', ''),
    ('smtp_from_email', ''),
    ('smtp_from_name', '');
