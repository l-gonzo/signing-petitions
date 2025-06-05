CREATE DATABASE IF NOT EXISTS computadoras;
USE computadoras;

CREATE TABLE IF NOT EXISTS computadoras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marca VARCHAR(100),
    cpu VARCHAR(100),
    gpu VARCHAR(100),
    ram INT,
    disco VARCHAR(50)
);

INSERT INTO computadoras (marca, cpu, gpu, ram, disco) VALUES
('Dell', 'Intel i7', 'NVIDIA GTX 1650', 16, '512GB SSD'),
('HP', 'Ryzen 5', 'AMD Radeon', 8, '1TB HDD'),
('Lenovo', 'Intel i5', 'Intel UHD', 8, '256GB SSD');
