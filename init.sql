CREATE TABLE users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       username VARCHAR(50) NOT NULL,
                       email VARCHAR(100) NOT NULL UNIQUE,
                       password VARCHAR(255) NOT NULL,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE server_sessions (
                                 id          INT AUTO_INCREMENT PRIMARY KEY,
                                 user_id     INT NOT NULL,
                                 server_key  VARCHAR(50) NOT NULL,  -- 'industrial', 'frozentech' и т.д.
                                 joined_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                 FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE donat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    server_key VARCHAR(50) NOT NULL,  -- 'industrial', 'frozentech' и т.д.
    donat VARCHAR(50) NOT NULL, -- Вип прем админ и тд
    started_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Когда была выдана, время присваивается автоматом
    expires_at  TIMESTAMP NOT NULL, -- Когда будет заканчиваться (Значение присваиваем в ручную)
    FOREIGN KEY (user_id) REFERENCES users(id)
);