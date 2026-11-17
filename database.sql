CREATE DATABASE IF NOT EXISTS repair_centers CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE repair_centers;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','receptionist','master') NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE clients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  phone VARCHAR(25) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE devices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  type ENUM('phone','laptop') NOT NULL,
  brand VARCHAR(50) NOT NULL,
  model VARCHAR(80) NOT NULL,
  serial_no VARCHAR(80) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (serial_no),
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  receipt_no VARCHAR(20) NOT NULL UNIQUE,
  client_id INT NOT NULL,
  device_id INT NOT NULL,
  fault_type VARCHAR(40) NOT NULL,
  fault_desc TEXT NOT NULL,
  received_by INT NOT NULL,
  master_id INT NULL,
  status ENUM('received','diagnosis','repair','ready','delivered') NOT NULL DEFAULT 'received',
  work_cost DECIMAL(10,2) NOT NULL DEFAULT 0,
  parts_cost DECIMAL(10,2) NOT NULL DEFAULT 0,
  total DECIMAL(10,2) NOT NULL DEFAULT 0,
  warranty_days INT NOT NULL DEFAULT 90,
  warranty_until DATE NULL,
  is_warranty TINYINT(1) NOT NULL DEFAULT 0,
  related_order_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  delivered_at DATETIME NULL,
  INDEX (status), INDEX (device_id, fault_type),
  FOREIGN KEY (client_id) REFERENCES clients(id),
  FOREIGN KEY (device_id) REFERENCES devices(id),
  FOREIGN KEY (received_by) REFERENCES users(id),
  FOREIGN KEY (master_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE order_status_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  status ENUM('received','diagnosis','repair','ready','delivered') NOT NULL,
  note VARCHAR(255) NULL,
  user_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE parts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  sku VARCHAR(40) NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  stock INT NOT NULL DEFAULT 0,
  min_stock INT NOT NULL DEFAULT 3
) ENGINE=InnoDB;

CREATE TABLE order_parts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  part_id INT NOT NULL,
  qty INT NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  added_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (part_id) REFERENCES parts(id)
) ENGINE=InnoDB;

-- Паролҳо: 123456
INSERT INTO users (full_name,username,password_hash,role) VALUES
('Администратор','admin','$2b$10$c.9tKlkYvHSkhtkvpH/DAuV.4Rgx91a0do.n/nfG81onk5U3U/7Ry','admin'),
('Қабулкунанда Нигина','reception','$2b$10$c.9tKlkYvHSkhtkvpH/DAuV.4Rgx91a0do.n/nfG81onk5U3U/7Ry','receptionist'),
('Усто Фарҳод','master1','$2b$10$c.9tKlkYvHSkhtkvpH/DAuV.4Rgx91a0do.n/nfG81onk5U3U/7Ry','master'),
('Усто Умед','master2','$2b$10$c.9tKlkYvHSkhtkvpH/DAuV.4Rgx91a0do.n/nfG81onk5U3U/7Ry','master');

INSERT INTO parts (name,sku,price,stock,min_stock) VALUES
('Экрани iPhone 11','SCR-IP11',450,6,3),
('Батареяи Samsung A52','BAT-SA52',120,10,4),
('Пайвасткунаки зарядкаи Type-C','CON-TC',35,25,8),
('Экрани ноутбук 15.6" FHD','SCR-LP156',520,3,2),
('Клавиатураи ноутбук','KB-LP',180,4,2),
('Хамираи гармӣ (паста)','PASTE',15,30,10);
