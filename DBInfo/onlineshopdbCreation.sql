CREATE TABLE brand (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE category (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);


CREATE TABLE product (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    img VARCHAR(255),
    brand_id INT UNSIGNED,
    category_id INT UNSIGNED,
    date_of_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    current_price DECIMAL(10,2) UNSIGNED NOT NULL,
    description TEXT DEFAULT NULL,
    FOREIGN KEY (brand_id) REFERENCES brand(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES category(id) ON DELETE SET NULL
);

CREATE TABLE price_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    price DECIMAL(10,2) UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    date_from TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_to TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE
);

CREATE TRIGGER `insert_price_history` AFTER INSERT ON `product`
 FOR EACH ROW BEGIN INSERT INTO price_history (product_id, price, date_from) VALUES (NEW.id, NEW.current_price, NOW()); END

CREATE TRIGGER `update_price_history` AFTER UPDATE ON `product`
 FOR EACH ROW BEGIN
    IF (OLD.current_price <> NEW.current_price OR OLD.current_price IS NULL) THEN
        UPDATE price_history SET date_to = NOW() WHERE product_id = NEW.id AND date_to IS NULL;
        INSERT INTO price_history (product_id, price, date_from) 
        VALUES (NEW.id, NEW.current_price, NOW());
    END IF;
END


-- CREATE TABLE discount_history (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     discount DECIMAL(5,2) UNSIGNED NOT NULL,
--     product_id INT UNSIGNED NOT NULL,
--     date_from TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
--     date_to TIMESTAMP,
--     FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE
-- );

CREATE TABLE information (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE
);

CREATE TABLE user (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(30) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(30) UNIQUE DEFAULT NULL,
    phone VARCHAR(30) UNIQUE DEFAULT NULL,
    first_name VARCHAR(255) DEFAULT NULL,
    last_name VARCHAR(255) DEFAULT NULL, 
    img VARCHAR(255) DEFAULT NULL,
    isAdmin BOOLEAN NOT NULL DEFAULT FALSE
);


CREATE TABLE orrder (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    order_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    endDate TIMESTAMP,
    status VARCHAR(50) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    comment TEXT,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE RESTRICT
);


CREATE TABLE product_in_order (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    order_id INT UNSIGNED NOT NULL,
    amount INT UNSIGNED NOT NULL DEFAULT 1,
    FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orrder(id) ON DELETE CASCADE
);

CREATE TABLE product_in_cart (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    amount INT UNSIGNED NOT NULL DEFAULT 1 CHECK (amount!=0),
    FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);


CREATE TABLE product_in_wishlist (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);

CREATE TABLE review (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  rate INT UNSIGNED NOT NULL CHECK (rate >= 1 AND rate <= 5),
  text TEXT,
  date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE,
  CONSTRAINT uc_user_product UNIQUE (user_id, product_id)
);



