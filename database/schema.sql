CREATE DATABASE IF NOT EXISTS `immo` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `immo`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS property_photos;
DROP TABLE IF EXISTS properties;
DROP TABLE IF EXISTS agents;
DROP TABLE IF EXISTS offices;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE offices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL,
    legal_name VARCHAR(190) NULL,
    email VARCHAR(190) NULL,
    phone VARCHAR(60) NULL,
    website VARCHAR(255) NULL,
    address_line1 VARCHAR(190) NULL,
    postal_code VARCHAR(20) NULL,
    city VARCHAR(120) NULL,
    country_code CHAR(2) NOT NULL DEFAULT 'FR',
    logo_path VARCHAR(255) NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_offices_status (status),
    INDEX idx_offices_city (city)
) ENGINE=InnoDB;

CREATE TABLE agents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    office_id BIGINT UNSIGNED NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NULL,
    phone VARCHAR(60) NULL,
    photo_path VARCHAR(255) NULL,
    password VARCHAR(255) NULL,
    role ENUM('admin','agent') NOT NULL DEFAULT 'agent',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_agents_office FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_agents_office_status (office_id, status),
    INDEX idx_agents_name (last_name, first_name),
    INDEX idx_agents_email (email)
) ENGINE=InnoDB;

CREATE TABLE properties (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    office_id BIGINT UNSIGNED NOT NULL,
    agent_id BIGINT UNSIGNED NOT NULL,
    reference_code VARCHAR(80) NOT NULL,
    property_type VARCHAR(80) NOT NULL DEFAULT 'house',
    listing_status ENUM('draft','published','under_offer','sold','rented','archived') NOT NULL DEFAULT 'draft',
    transaction_type ENUM('sale','rent') NOT NULL DEFAULT 'sale',
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description LONGTEXT NULL,
    price DECIMAL(15,2) NULL,
    currency CHAR(3) NOT NULL DEFAULT 'EUR',
    living_area_m2 DECIMAL(10,2) NULL,
    land_area_m2 DECIMAL(12,2) NULL,
    bedrooms SMALLINT UNSIGNED NULL,
    bathrooms SMALLINT UNSIGNED NULL,
    address_line1 VARCHAR(190) NULL,
    postal_code VARCHAR(20) NULL,
    city VARCHAR(120) NULL,
    country_code CHAR(2) NOT NULL DEFAULT 'FR',
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_properties_office FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_properties_agent FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY uq_properties_reference (reference_code),
    UNIQUE KEY uq_properties_slug (slug),
    INDEX idx_properties_office_status (office_id, listing_status),
    INDEX idx_properties_agent (agent_id),
    INDEX idx_properties_city (city),
    INDEX idx_properties_price (price)
) ENGINE=InnoDB;

CREATE TABLE property_photos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_name VARCHAR(255) NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size INT UNSIGNED NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    is_hero TINYINT(1) NOT NULL DEFAULT 0,
    width INT UNSIGNED NULL,
    height INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_property_photos_property FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_property_photos_order (property_id, sort_order),
    INDEX idx_property_photos_hero (property_id, is_hero)
) ENGINE=InnoDB;

-- Seed data for immediate testing.
INSERT INTO offices (name, email, phone, city, country_code) VALUES
('Demo Immobilier', 'demo@example.com', '+33 5 00 00 00 00', 'Cahors', 'FR');

-- Admin user for Demo Immobilier
INSERT INTO agents (office_id, first_name, last_name, email, phone, password, role)
SELECT id, 'Admin', 'User', 'admin@demo-immo.com', '+33 6 00 00 00 00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'
FROM offices WHERE name = 'Demo Immobilier' LIMIT 1;

-- Regular agent
INSERT INTO agents (office_id, first_name, last_name, email, phone, password, role)
SELECT id, 'Claire', 'Martin', 'claire@demo-immo.com', '+33 6 00 00 00 01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agent'
FROM offices WHERE name = 'Demo Immobilier' LIMIT 1;

INSERT INTO properties (office_id, agent_id, reference_code, property_type, listing_status, transaction_type, title, slug, description, price, living_area_m2, land_area_m2, bedrooms, bathrooms, city, postal_code, country_code)
SELECT o.id, a.id, 'DEMO-001', 'house', 'published', 'sale', 'Maison de caractère avec vue', 'maison-de-caractere-avec-vue', 'Exemple de propriété pour tester le back-office.', 495000, 185, 12000, 5, 2, 'Cahors', '46000', 'FR'
FROM offices o JOIN agents a ON a.office_id = o.id
WHERE o.name = 'Demo Immobilier' AND a.first_name = 'Claire' LIMIT 1;
