-- init database
CREATE DATABASE mama_db CHARACTER SET UTF8;
CREATE USER mama_user@localhost IDENTIFIED BY 'mama_password';
GRANT ALL PRIVILEGES ON mama_db.* TO mama_user@localhost;
FLUSH PRIVILEGES;

-- init database for unit tests
 CREATE DATABASE mama_test CHARACTER SET UTF8;
 CREATE USER mama_test_user@localhost IDENTIFIED BY 'XSaMyduyvpmLenNn'; 
 GRANT ALL PRIVILEGES ON mama_test.* TO mama_test_user@localhost;
 FLUSH PRIVILEGES;    