CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS products (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL DEFAULT '',
    price NUMERIC(12,2) NOT NULL CHECK (price >= 0),
    image_url TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Seed tài khoản mặc định (id = 1, username = 'demo', password = 'password123')
INSERT INTO users (id, username, password_hash, email) VALUES
(1, 'demo', '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'demo@uit.edu.vn')
ON CONFLICT (id) DO NOTHING;

SELECT setval(pg_get_serial_sequence('users', 'id'), COALESCE((SELECT MAX(id) FROM users), 1));

-- Seed 15 sản phẩm mẫu chuẩn cho hệ thống E-Commerce (Task 2.3)
INSERT INTO products (id, name, description, price, image_url) VALUES
(1, 'Azure Cloud Hoodie', 'Áo hoodie nỉ cao cấp phiên bản Cloud-Native', 499000, 'https://placehold.co/600x400?text=Azure+Hoodie'),
(2, 'Redis In-Memory Mug', 'Ly giữ nhiệt inox 500ml tốc độ sub-millisecond', 189000, 'https://placehold.co/600x400?text=Redis+Mug'),
(3, 'PostgreSQL ACID Notebook', 'Sổ tay bìa cứng 200 trang cho Database Engineer', 99000, 'https://placehold.co/600x400?text=PostgreSQL+Notebook'),
(4, 'Kubernetes Sticker Pack', 'Bộ 25 sticker chống nước chủ đề K8s & DevOps', 59000, 'https://placehold.co/600x400?text=K8s+Stickers'),
(5, 'Cloud Native Backpack', 'Balo chống sốc laptop 15.6 inch đa ngăn', 799000, 'https://placehold.co/600x400?text=Cloud+Backpack'),
(6, 'Cosmos DB Desk Mat', 'Thảm lót chuột cỡ lớn 90x40cm họa tiết NoSQL Global', 249000, 'https://placehold.co/600x400?text=Cosmos+Desk+Mat'),
(7, 'FastAPI Mechanical Keycaps', 'Bộ keycap PBT dye-sub chủ đề Python & FastAPI', 350000, 'https://placehold.co/600x400?text=FastAPI+Keycaps'),
(8, 'Spring Boot Polo Shirt', 'Áo thun polo thoáng khí cho Java Microservices Dev', 320000, 'https://placehold.co/600x400?text=Spring+Boot+Polo'),
(9, 'Docker Container USB Hub', 'Hub Type-C 7-in-1 vỏ nhôm nguyên khối', 590000, 'https://placehold.co/600x400?text=Docker+USB+Hub'),
(10, 'NGINX Reverse Proxy Cap', 'Mũ lưỡi trai thêu nổi logo NGINX Ingress', 159000, 'https://placehold.co/600x400?text=NGINX+Cap'),
(11, 'Synapse Analytics Water Bottle', 'Bình nước thể thao 1L có vạch chia dung tích', 219000, 'https://placehold.co/600x400?text=Synapse+Bottle'),
(12, 'GitHub Actions Wireless Mouse', 'Chuột không dây silent click pin sạc Type-C', 420000, 'https://placehold.co/600x400?text=Actions+Mouse'),
(13, 'Serverless Functions Tote Bag', 'Túi vải canvas dày dặn in hình Azure Functions', 129000, 'https://placehold.co/600x400?text=Functions+Tote'),
(14, 'Power BI Data Glasses', 'Kính chống ánh sáng xanh bảo vệ mắt khi làm Dashboard', 450000, 'https://placehold.co/600x400?text=PowerBI+Glasses'),
(15, 'UIT Cloud Engineer Lanyard', 'Dây đeo thẻ sinh viên UIT phiên bản IS402', 49000, 'https://placehold.co/600x400?text=UIT+Lanyard')
ON CONFLICT (id) DO NOTHING;

SELECT setval(pg_get_serial_sequence('products', 'id'), COALESCE((SELECT MAX(id) FROM products), 1));
