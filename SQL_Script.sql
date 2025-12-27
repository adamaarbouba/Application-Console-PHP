DROP DATABASE IF EXISTS payment_system;

CREATE DATABASE payment_system;

USE payment_system;

CREATE TABLE
    Customers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL
    );

CREATE TABLE
    Orders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        customer_id INT,
        total_amount DECIMAL(10, 2),
        status ENUM ('Pending', 'Cancel', 'Paid'),
        FOREIGN KEY (customer_id) REFERENCES Customers (id) ON DELETE CASCADE
    );

CREATE TABLE
    Payments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT UNIQUE,
        amount DECIMAL(10, 2),
        status BOOLEAN,
        payment_date DATE,
        payment_type ENUM ('PayPal', 'BankTransfer', 'CreditCard'),
        FOREIGN KEY (order_id) REFERENCES Orders (id) ON DELETE CASCADE
    );

CREATE TABLE
    PayPal_Payments (
        payment_id INT PRIMARY KEY,
        email VARCHAR(255),
        username VARCHAR(255),
        password VARCHAR(255),
        FOREIGN KEY (payment_id) REFERENCES Payments (id) ON DELETE CASCADE
    );

CREATE TABLE
    BankTransfer_Payments (
        payment_id INT PRIMARY KEY,
        rib VARCHAR(100),
        password VARCHAR(255),
        FOREIGN KEY (payment_id) REFERENCES Payments (id) ON DELETE CASCADE
    );

CREATE TABLE
    CreditCard_Payments (
        payment_id INT PRIMARY KEY,
        bank_account VARCHAR(100),
        zip_code VARCHAR(20),
        password VARCHAR(255),
        FOREIGN KEY (payment_id) REFERENCES Payments (id) ON DELETE CASCADE
    );

INSERT INTO
    Customers (name, email)
VALUES
    ('John Doe', 'john@example.com'),
    ('Jane Smith', 'jane.s@provider.net'),
    ('Ahmed Hassan', 'ahmed.h@company.org'),
    ('Elena Rodriguez', 'elena.rod@service.com');

INSERT INTO
    Orders (customer_id, total_amount, status)
VALUES
    (1, 120.50, 'Paid'),
    (2, 450.00, 'Paid'),
    (3, 75.25, 'Paid'),
    (4, 1000.00, 'Paid');

INSERT INTO
    Payments (
        order_id,
        amount,
        status,
        payment_date,
        payment_type
    )
VALUES
    (1, 120.50, 1, '2023-10-10', 'PayPal'),
    (2, 450.00, 1, '2023-10-11', 'BankTransfer'),
    (3, 75.25, 1, '2023-10-12', 'CreditCard'),
    (4, 1000.00, 1, '2023-10-13', 'PayPal');

INSERT INTO
    PayPal_Payments (payment_id, email, username, password)
VALUES
    (
        1,
        'john_pay@example.com',
        'jdoe_pay',
        'hash_secure_99'
    ),
    (
        4,
        'elena_finance@service.com',
        'erodriguez',
        'hash_secure_88'
    );

INSERT INTO
    BankTransfer_Payments (payment_id, rib, password)
VALUES
    (2, 'US76CHASE1234567890', 'hash_bank_77');

INSERT INTO
    CreditCard_Payments (payment_id, bank_account, zip_code, password)
VALUES
    (3, '4532-XXXX-XXXX-1122', '90210', 'hash_cc_66');