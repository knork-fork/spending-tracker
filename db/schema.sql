CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    username VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(180),
    country CHAR(2),
    created_at TIMESTAMP NOT NULL DEFAULT now()
);
