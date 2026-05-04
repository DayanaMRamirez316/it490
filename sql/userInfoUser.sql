CREATE USER IF NOT EXISTS 'userInfo'@'localhost' IDENTIFIED BY 'theBestPassword';
GRANT USAGE ON data.* TO 'userInfo'@'localhost';
GRANT SELECT, INSERT, DELETE, UPDATE ON data.Users TO 'userInfo'@'localhost';
GRANT SELECT, INSERT, DELETE, UPDATE ON data.Sessions TO 'userInfo'@'localhost';
GRANT SELECT, INSERT, DELETE, UPDATE ON data.User_Reviews TO 'userInfo'@'localhost';
GRANT SELECT, INSERT, DELETE, UPDATE ON data.User_Following TO 'userInfo'@'localhost';
GRANT SELECT, INSERT, DELETE, UPDATE ON data.Games TO 'userInfo'@'localhost';

FLUSH PRIVILEGES;
