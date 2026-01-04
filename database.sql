-- Create database and tables
CREATE DATABASE IF NOT EXISTS billing_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE billing_app;

-- documents: both bills and invoices
CREATE TABLE IF NOT EXISTS documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('bill','invoice') NOT NULL,
  number VARCHAR(64) NOT NULL UNIQUE,
  customer_name VARCHAR(255) NOT NULL,
  customer_email VARCHAR(255),
  date DATE NOT NULL,
  due_date DATE,
  status VARCHAR(32) DEFAULT 'unpaid',
  notes TEXT,
  subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  tax DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_type (type),
  INDEX idx_date (date)
) ENGINE=InnoDB;

-- line items per document
CREATE TABLE IF NOT EXISTS document_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  document_id INT NOT NULL,
  description VARCHAR(255) NOT NULL,
  quantity DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
  INDEX idx_document (document_id)
) ENGINE=InnoDB;