-- Categories table
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  image VARCHAR(255)
);

-- Countries table
CREATE TABLE countries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  image VARCHAR(255)
);

-- Products table
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) DEFAULT 0.00,
  image VARCHAR(255),
  category_id INT,
  country_id INT,
  is_featured TINYINT(1) DEFAULT 0,
  is_new_arrival TINYINT(1) DEFAULT 0,
  is_bestseller TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL
);

-- Admin users table
CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, password) VALUES ('admin', '$2y$10$HA4w0Ctr4Xvy1g9m9ZHQAeHCkW9ie3KbuoNiHzZDbE5Y8PcrdcRfC');