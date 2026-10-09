-- TastyBites — database schema and seed data
-- Re-runnable: drops and recreates the database from scratch.

DROP DATABASE IF EXISTS tastybites;
CREATE DATABASE tastybites
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;
USE tastybites;

-- ============================================================
-- Tables
-- ============================================================

CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    phone         VARCHAR(20)  NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE menu_categories (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(80) NOT NULL UNIQUE,
    display_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE menu_items (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id  INT UNSIGNED NOT NULL,
    name         VARCHAR(120) NOT NULL,
    description  TEXT NULL,
    price        DECIMAL(8,2) NOT NULL,
    image_url    VARCHAR(255) NULL,
    is_available TINYINT(1) DEFAULT 1,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tables (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    table_number  INT NOT NULL UNIQUE,
    capacity      INT NOT NULL,
    location_note VARCHAR(80) NULL,
    is_active     TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reservations (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          INT UNSIGNED NOT NULL,
    table_id         INT UNSIGNED NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    party_size       INT NOT NULL,
    special_request  TEXT NULL,
    status           ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reservation_lookup (table_id, reservation_date, reservation_time),
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE messages (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    subject    VARCHAR(150) NOT NULL,
    body       TEXT NOT NULL,
    is_read    TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Seed Data
-- ============================================================

-- IMPORTANT: Replace the two REPLACE_WITH_BCRYPT_HASH_* placeholders below
-- with the output of:
--   php -r "echo password_hash('Admin123!', PASSWORD_DEFAULT);"   (for admin)
--   php -r "echo password_hash('User123!', PASSWORD_DEFAULT);"    (for user)

INSERT INTO users (full_name, email, password_hash, role) VALUES
    ('Restaurant Admin', 'admin@tastybites.local', 'REPLACE_WITH_BCRYPT_HASH_ADMIN', 'admin'),
    ('John Doe',         'john@example.com',       'REPLACE_WITH_BCRYPT_HASH_USER',  'user');

INSERT INTO menu_categories (name, display_order) VALUES
    ('Starters',      1),
    ('Main Courses',  2),
    ('Desserts',      3),
    ('Beverages',     4);

INSERT INTO menu_items (category_id, name, description, price, image_url, is_available) VALUES
    -- Starters (category_id = 1)
    (1, 'Bruschetta',     'Toasted bread topped with fresh tomatoes, garlic, and basil.',         6.50, 'assets/images/menu/bruschetta.jpg',     1),
    (1, 'Caesar Salad',   'Crisp romaine lettuce with Caesar dressing, croutons, and parmesan.',  8.00, 'assets/images/menu/caesar-salad.jpg',   1),
    (1, 'Tomato Soup',    'Velvety roasted tomato soup served with a crusty bread roll.',          5.50, 'assets/images/menu/tomato-soup.jpg',    1),
    -- Main Courses (category_id = 2)
    (2, 'Grilled Salmon', 'Pan-seared Atlantic salmon fillet served with seasonal vegetables.',   18.50, 'assets/images/menu/grilled-salmon.jpg', 1),
    (2, 'Margherita Pizza','Classic wood-fired pizza with San Marzano tomato and mozzarella.',   12.00, 'assets/images/menu/margherita-pizza.jpg',1),
    (2, 'Beef Steak',     'Prime-cut beef steak grilled to your preference with herb butter.',   24.00, 'assets/images/menu/beef-steak.jpg',      1),
    -- Desserts (category_id = 3)
    (3, 'Tiramisu',            'Classic Italian dessert with espresso-soaked ladyfingers and mascarpone.',  7.00, 'assets/images/menu/tiramisu.jpg',             1),
    (3, 'Cheesecake',          'Creamy New York-style cheesecake on a buttery graham-cracker crust.',       6.50, 'assets/images/menu/cheesecake.jpg',           1),
    (3, 'Chocolate Lava Cake', 'Warm chocolate cake with a gooey molten centre, served with ice cream.',    7.50, 'assets/images/menu/chocolate-lava-cake.jpg',  1),
    -- Beverages (category_id = 4)
    (4, 'Fresh Lemonade', 'House-made lemonade with fresh-squeezed lemons and a hint of mint.',   3.50, 'assets/images/menu/fresh-lemonade.jpg', 1),
    (4, 'Espresso',       'Rich, full-bodied single-origin espresso shot.',                        2.50, 'assets/images/menu/espresso.jpg',       1),
    (4, 'Iced Tea',       'Freshly brewed black tea served chilled with lemon slices.',            3.00, 'assets/images/menu/iced-tea.jpg',       1);

INSERT INTO tables (table_number, capacity, location_note, is_active) VALUES
    (1, 2, 'Window side',  1),
    (2, 2, 'Window side',  1),
    (3, 4, 'Main hall',    1),
    (4, 4, 'Main hall',    1),
    (5, 6, 'Main hall',    1),
    (6, 6, 'Terrace',      1),
    (7, 8, 'Private room', 1),
    (8, 4, 'Bar area',     1);

INSERT INTO reservations (user_id, table_id, reservation_date, reservation_time, party_size, status) VALUES
    (2, 3, CURDATE() + INTERVAL 7 DAY,   '19:00', 2, 'confirmed'),
    (2, 5, CURDATE() - INTERVAL 14 DAY,  '20:00', 4, 'completed');
