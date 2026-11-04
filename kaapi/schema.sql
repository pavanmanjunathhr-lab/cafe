CREATE DATABASE IF NOT EXISTS kaapi_katte CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kaapi_katte;

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(255) NOT NULL DEFAULT '',
  price DECIMAL(8,2) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ref VARCHAR(12) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  reserve_date DATE NOT NULL,
  reserve_time TIME NOT NULL,
  guests TINYINT NOT NULL,
  notes VARCHAR(500) NOT NULL DEFAULT '',
  status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO categories (id, name, sort_order) VALUES
 (1,'Kaapi',1),(2,'Pour-over',2),(3,'Cold',3),(4,'Bites',4);

INSERT INTO menu_items (category_id, name, description, price, sort_order) VALUES
 (1,'Filter Kaapi','Strong decoction, hot milk and sugar, in a steel tumbler and davara.',70,1),
 (1,'Meter Kaapi','Pulled high between two tumblers until it froths.',90,2),
 (1,'Madras Degree Coffee','Pure full-fat milk, thick and sweet, the Chennai way.',95,3),
 (1,'Cardamom Jaggery Kaapi','Palm jaggery and crushed elaichi. No refined sugar.',110,4),
 (2,'Chikmagalur V60','Washed arabica with orange blossom and brown sugar.',180,1),
 (2,'Coorg Chemex','Peaberry, dark chocolate and black pepper. Serves two.',320,2),
 (2,'Monsooned Malabar','Low acid, earthy and sweet. Brewed in a French press.',200,3),
 (3,'Coorg Cold Brew','Steeped 18 hours, poured over one large ice cube.',160,1),
 (3,'Kaapi Frappe','Filter coffee blended with ice and a little jaggery.',180,2),
 (3,'Tender Coconut Tonic','Double espresso over coconut water and tonic.',190,3),
 (4,'Bun Maska','Soft bun, white butter. Made for dunking.',90,1),
 (4,'Ragi Chocolate Cookie','Finger millet, dark chocolate, baked daily.',80,2),
 (4,'Maddur Vada','Crisp onion and semolina fritter, served warm.',90,3),
 (4,'Mysore Pak Brownie','Gram flour fudge, ghee and cocoa.',130,4);
