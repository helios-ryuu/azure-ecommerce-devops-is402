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

INSERT INTO products (name, description, price, image_url) VALUES
('Azure Hoodie', 'Áo hoodie cho nhóm Cloud Native', 499000, 'https://placehold.co/600x400?text=Azure+Hoodie'),
('Redis Mug', 'Ly giữ nhiệt Redis', 189000, 'https://placehold.co/600x400?text=Redis+Mug'),
('PostgreSQL Notebook', 'Sổ tay PostgreSQL', 99000, 'https://placehold.co/600x400?text=PostgreSQL+Notebook'),
('Kubernetes Sticker Pack', 'Bộ sticker Kubernetes', 59000, 'https://placehold.co/600x400?text=Kubernetes+Stickers'),
('Cloud Native Backpack', 'Balo laptop 15 inch', 799000, 'https://placehold.co/600x400?text=Cloud+Backpack')
ON CONFLICT DO NOTHING;
