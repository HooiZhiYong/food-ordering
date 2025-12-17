-- phpMyAdmin SQL Dump
-- Database: food_ordering_db

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- 1. Create Database (Safety check)
--
CREATE DATABASE IF NOT EXISTS food_ordering_db;
USE food_ordering_db;

--
-- 2. Table structure for table `users`
--
CREATE TABLE IF NOT EXISTS users (
  user_id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(50) NOT NULL,
  email varchar(100) NOT NULL,
  password varchar(255) NOT NULL,
  phone_number varchar(15) DEFAULT NULL,
  address text DEFAULT NULL,
  role enum('customer','admin') DEFAULT 'customer',
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (user_id),
  UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 3. Table structure for table `food_items`
--
CREATE TABLE IF NOT EXISTS food_items (
  food_id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(100) NOT NULL,
  description text DEFAULT NULL,
  price decimal(10,2) NOT NULL,
  image_path varchar(255) DEFAULT NULL,
  category varchar(50) DEFAULT NULL,
  is_active tinyint(1) DEFAULT 1,
  PRIMARY KEY (food_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 4. Table structure for table `orders`
--
CREATE TABLE IF NOT EXISTS orders (
  order_id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  total_amount decimal(10,2) NOT NULL,
  order_status enum('pending','completed','cancelled') DEFAULT 'pending',
  order_date timestamp NOT NULL DEFAULT current_timestamp(),
  delivery_address text NOT NULL,
  PRIMARY KEY (order_id),
  KEY user_id (user_id),
  CONSTRAINT orders_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 5. Table structure for table `order_details`
--
CREATE TABLE IF NOT EXISTS order_details (
  detail_id int(11) NOT NULL AUTO_INCREMENT,
  order_id int(11) NOT NULL,
  food_id int(11) NOT NULL,
  quantity int(11) NOT NULL,
  price_each decimal(10,2) NOT NULL,
  PRIMARY KEY (detail_id),
  KEY order_id (order_id),
  KEY food_id (food_id),
  CONSTRAINT order_details_ibfk_1 FOREIGN KEY (order_id) REFERENCES orders (order_id) ON DELETE CASCADE,
  CONSTRAINT order_details_ibfk_2 FOREIGN KEY (food_id) REFERENCES food_items (food_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 6. DUMP DATA: Food Items
--
INSERT INTO food_items (name, description, price, category, image_path, is_active) VALUES
('Classic Burger', 'Juicy beef patty with lettuce and tomato', 12.50, 'Western', 'burger.jpg', 1),
('Nasi Lemak', 'Fragrant rice with sambal and anchovies', 8.00, 'Local', 'nasilemak.jpg', 1),
('Chicken Chop', 'Fried chicken with black pepper sauce', 15.90, 'Western', 'chicken.jpg', 1),
('Teh Tarik', 'Malaysian pulled milk tea', 3.50, 'Beverage', 'teh.jpg', 1);

--
-- 7. DUMP DATA: Admin User
-- Name: Hooi Zhi Yong, Email: hooizhiyong@gmail.com, Pass: 12345678
--
INSERT INTO users (username, email, password, phone_number, address, role) VALUES
('Hooi Zhi Yong', 'hooizhiyong@gmail.com', '$2y$10$Yi9.G.h.m.n.o.p.q.r.s.TuUvWxYz1234567890abcdefghijklm', '01159543536', 'Campus canteen', 'admin');

COMMIT;