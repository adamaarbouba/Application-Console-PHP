DROP DATABASE IF EXISTS payment_system;
CREATE DATABASE payment_system;
USE payment_system;
CREATE TABLE Customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL
);

CREATE TABLE Orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    total_amount DECIMAL(10, 2),
    status BOOLEAN,
    FOREIGN KEY (customer_id) REFERENCES Customers(id) ON DELETE CASCADE
);

CREATE TABLE Payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT UNIQUE, 
    amount DECIMAL(10, 2),
    status BOOLEAN,
    payment_date DATE,
    payment_type ENUM('PayPal', 'BankTransfer', 'CreditCard'),
    FOREIGN KEY (order_id) REFERENCES Orders(id)
);

CREATE TABLE PayPal_Payments (
    payment_id INT PRIMARY KEY,
    email VARCHAR(255),
    username VARCHAR(255),
    password VARCHAR(255),
    FOREIGN KEY (payment_id) REFERENCES Payments(id) ON DELETE CASCADE
);

CREATE TABLE BankTransfer_Payments (
    payment_id INT PRIMARY KEY,
    rib VARCHAR(100),
    password VARCHAR(255),
    FOREIGN KEY (payment_id) REFERENCES Payments(id) ON DELETE CASCADE
);

CREATE TABLE CreditCard_Payments (
    payment_id INT PRIMARY KEY,
    bank_account VARCHAR(100),
    zip_code VARCHAR(20),
    password VARCHAR(255),
    FOREIGN KEY (payment_id) REFERENCES Payments(id) ON DELETE CASCADE
);


-- --- EXAMPLE 1: PAYPAL TRANSACTION ---
INSERT INTO Customers (name, email) VALUES ('John Doe', 'john@example.com'); -- ID: 1
INSERT INTO Orders (customer_id, total_amount, status) VALUES (1, 450.00, true); -- ID: 1
INSERT INTO Payments (order_id, amount, status, payment_date, payment_type) 
VALUES (1, 450.00, true, '2023-10-27', 'PayPal'); -- ID: 1
INSERT INTO PayPal_Payments (payment_id, email, username, password) 
VALUES (1, 'john_pay@email.com', 'johndoe88', 'secret123');

-- --- EXAMPLE 2: BANK TRANSFER TRANSACTION ---
INSERT INTO Customers (name, email) VALUES ('Jane Smith', 'jane@test.org'); -- ID: 2
INSERT INTO Orders (customer_id, total_amount, status) VALUES (2, 1200.75, true); -- ID: 2
INSERT INTO Payments (order_id, amount, status, payment_date, payment_type) 
VALUES (2, 1200.75, true, '2023-10-28', 'BankTransfer'); -- ID: 2
INSERT INTO BankTransfer_Payments (payment_id, rib, password) 
VALUES (2, 'FR76-1234-5678-9012', 'bank_pass_99');

-- --- EXAMPLE 3: CREDIT CARD TRANSACTION ---
INSERT INTO Customers (name, email) VALUES ('Robert Brown', 'rob@web.com'); -- ID: 3
INSERT INTO Orders (customer_id, total_amount, status) VALUES (3, 85.20, true); -- ID: 3
INSERT INTO Payments (order_id, amount, status, payment_date, payment_type) 
VALUES (3, 85.20, true, '2023-10-29', 'CreditCard'); -- ID: 3
INSERT INTO CreditCard_Payments (payment_id, bank_account, zip_code, password) 
VALUES (3, 'ACCT-987654321', '90210', 'cc_secure_456');