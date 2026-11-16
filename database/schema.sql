-- ot-taller · esquema compatible con MySQL 8 y MariaDB 10.x
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'STAFF',
    active TINYINT(1) NOT NULL DEFAULT 1,
    must_change_password TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NULL,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(160) NOT NULL,
    phone VARCHAR(40) NULL,
    email VARCHAR(190) NULL,
    notes TEXT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_customers_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS suppliers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(160) NOT NULL,
    contact VARCHAR(160) NULL,
    phone VARCHAR(40) NULL,
    email VARCHAR(190) NULL,
    notes TEXT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_suppliers_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS motorcycles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_id INT UNSIGNED NOT NULL,
    plate VARCHAR(30) NULL,
    brand VARCHAR(80) NOT NULL,
    model VARCHAR(80) NOT NULL,
    year SMALLINT UNSIGNED NULL,
    chassis VARCHAR(80) NULL,
    engine VARCHAR(80) NULL,
    notes TEXT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_motorcycles_customer (customer_id),
    KEY idx_motorcycles_brand_model (brand, model),
    CONSTRAINT fk_motorcycles_customer FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS parts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    description VARCHAR(200) NOT NULL,
    sku VARCHAR(80) NULL,
    brand VARCHAR(80) NULL,
    purchase_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    sale_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    min_stock INT NOT NULL DEFAULT 0,
    supplier_id INT UNSIGNED NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    notes TEXT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_parts_description (description),
    KEY idx_parts_supplier (supplier_id),
    CONSTRAINT fk_parts_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS work_orders (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    motorcycle_id INT UNSIGNED NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    entry_date DATE NOT NULL,
    km INT NULL,
    problem TEXT NOT NULL,
    diagnosis TEXT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'INGRESO',
    labor DECIMAL(12,2) NOT NULL DEFAULT 0,
    payment_method VARCHAR(30) NULL,
    invoice_number VARCHAR(80) NULL,
    notes TEXT NULL,
    delivery_date DATE NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_orders_status (status),
    KEY idx_orders_entry (entry_date),
    KEY idx_orders_customer (customer_id),
    KEY idx_orders_motorcycle (motorcycle_id),
    CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_orders_motorcycle FOREIGN KEY (motorcycle_id) REFERENCES motorcycles (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id INT UNSIGNED NOT NULL,
    part_id INT UNSIGNED NULL,
    description VARCHAR(200) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    status VARCHAR(30) NOT NULL DEFAULT 'PRESUPUESTADO',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_items_order (order_id),
    KEY idx_items_part (part_id),
    CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES work_orders (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_items_part FOREIGN KEY (part_id) REFERENCES parts (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('workshop_name', 'Taller'),
    ('workshop_contact', ''),
    ('currency_symbol', '$'),
    ('locale', 'es-AR'),
    ('pdf_footer', 'Gracias por confiar en {workshop}'),
    ('whatsapp_template', 'Hola {cliente}, te envío el presupuesto de la orden {orden} ({moto}). Total: {total}.'),
    ('whatsapp_quote_template', 'Hola {cliente}, el presupuesto de la orden {orden} ({moto}) está listo para aprobar. Total: {total}. Cualquier duda, respondé este mensaje.'),
    ('whatsapp_ready_template', 'Hola {cliente}, tu moto {moto} ya está lista para retirar. Te esperamos en {taller}. Orden {orden}.'),
    ('logo_path', ''),
    ('demo_seeded', '0'),
    ('smtp_host', ''),
    ('smtp_port', '587'),
    ('smtp_crypto', 'tls'),
    ('smtp_user', ''),
    ('smtp_pass', ''),
    ('smtp_from_email', ''),
    ('smtp_from_name', '');

SET FOREIGN_KEY_CHECKS = 1;
