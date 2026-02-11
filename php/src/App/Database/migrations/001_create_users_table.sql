CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (nom, email, password, role)
VALUES (
    'Super Admin', 
    'admin@fyh.com', 
    '$2y$10$BeY/WJzQ1xR5aJqM1bEZO.hX5xJj5.u4A5aJqM1bEZO.hX5xJj5',
    'admin'
) 
ON CONFLICT (email) DO NOTHING; 
