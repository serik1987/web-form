CREATE TABLE IF NOT EXISTS orders (
	order_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	client_name VARCHAR(100) NOT NULL,
	client_phone VARCHAR(100) NOT NULL,
	client_mail VARCHAR(100),
	good_name VARCHAR(100),
	good_size VARCHAR(75),
	good_color VARCHAR(100),
	order_comments VARCHAR(512)
);