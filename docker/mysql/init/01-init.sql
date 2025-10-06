-- Initialize the database
CREATE DATABASE IF NOT EXISTS digital_wallet;
USE digital_wallet;

-- Grant privileges to the laravel user
GRANT ALL PRIVILEGES ON digital_wallet.* TO 'laravel'@'%';
FLUSH PRIVILEGES;