# create databases
CREATE DATABASE IF NOT EXISTS `solawi`;
CREATE DATABASE IF NOT EXISTS `solawi_ut`;

# create root user and grant rights
CREATE USER 'root'@'localhost' IDENTIFIED BY 'local';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%';
