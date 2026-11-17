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

CREATE UNIQUE INDEX IF NOT EXISTS products_name_unique_idx ON products (name);

INSERT INTO products (name, description, price, image_url) VALUES
('Azure Hoodie', 'Áo hoodie cho nhóm Cloud Native', 499000, 'https://placehold.co/600x400?text=Azure+Hoodie'),
('Redis Mug', 'Ly giữ nhiệt Redis', 189000, 'https://placehold.co/600x400?text=Redis+Mug'),
('PostgreSQL Notebook', 'Sổ tay PostgreSQL', 99000, 'https://placehold.co/600x400?text=PostgreSQL+Notebook'),
('Kubernetes Sticker Pack', 'Bộ sticker Kubernetes', 59000, 'https://placehold.co/600x400?text=Kubernetes+Stickers'),
('Cloud Native Backpack', 'Balo laptop 15 inch', 799000, 'https://placehold.co/600x400?text=Cloud+Backpack'),
('Docker T-Shirt', 'Áo thun Docker dành cho lập trình viên', 279000, 'https://placehold.co/600x400?text=Docker+T-Shirt'),
('Spring Boot Cap', 'Nón lưỡi trai Spring Boot', 219000, 'https://placehold.co/600x400?text=Spring+Boot+Cap'),
('PHP Developer Bottle', 'Bình nước cho PHP Developer', 159000, 'https://placehold.co/600x400?text=PHP+Bottle'),
('Cosmos DB Mouse Pad', 'Lót chuột chủ đề Azure Cosmos DB', 129000, 'https://placehold.co/600x400?text=Cosmos+Mouse+Pad'),
('Azure Functions Keychain', 'Móc khóa Azure Functions', 49000, 'https://placehold.co/600x400?text=Functions+Keychain'),
('DevOps Desk Mat', 'Tấm lót bàn làm việc DevOps', 349000, 'https://placehold.co/600x400?text=DevOps+Desk+Mat'),
('Microservices Poster', 'Poster kiến trúc Microservices', 89000, 'https://placehold.co/600x400?text=Microservices+Poster'),
('Cloud Architecture Book', 'Sách nhập môn kiến trúc Cloud', 329000, 'https://placehold.co/600x400?text=Cloud+Book'),
('GitHub Actions Pin', 'Huy hiệu GitHub Actions', 39000, 'https://placehold.co/600x400?text=GitHub+Actions+Pin'),
('AKS Travel Pouch', 'Túi phụ kiện chủ đề Azure Kubernetes Service', 239000, 'https://placehold.co/600x400?text=AKS+Travel+Pouch')
ON CONFLICT (name) DO NOTHING;
