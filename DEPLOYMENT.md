# ConSlot Deployment Guide

## 📋 Overview

This guide provides step-by-step instructions for deploying the ConSlot consultation booking portal to a production environment.

## 🏗️ System Requirements

### Minimum Requirements
- **PHP**: 8.0 or higher
- **MySQL**: 8.0 or higher (or MariaDB 10.5+)
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 512MB RAM minimum
- **Storage**: 1GB disk space minimum

### Recommended Requirements
- **PHP**: 8.1 or higher
- **MySQL**: 8.0+ with InnoDB engine
- **Web Server**: Apache 2.4+ with mod_rewrite or Nginx 1.20+
- **Memory**: 2GB RAM
- **Storage**: 5GB disk space
- **SSL Certificate**: Let's Encrypt or commercial certificate

## 🚀 Deployment Steps

### Step 1: Server Setup

#### 1.1 Update System Packages
```bash
# Ubuntu/Debian
sudo apt update && sudo apt upgrade -y

# CentOS/RHEL
sudo yum update -y
```

#### 1.2 Install Required Software

**For Ubuntu/Debian:**
```bash
# Install Apache, PHP, and MySQL
sudo apt install apache2 php8.1 php8.1-mysql php8.1-json php8.1-mbstring php8.1-xml php8.1-curl mysql-server -y

# Install additional PHP extensions
sudo apt install php8.1-gd php8.1-zip php8.1-intl -y
```

**For CentOS/RHEL:**
```bash
# Install Apache, PHP, and MariaDB
sudo yum install httpd php php-mysqlnd php-json php-mbstring php-xml php-curl mariadb-server -y

# Install additional PHP extensions
sudo yum install php-gd php-zip php-intl -y
```

#### 1.3 Configure Firewall
```bash
# Ubuntu (UFW)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable

# CentOS/RHEL (firewalld)
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --permanent --add-service=ssh
sudo firewall-cmd --reload
```

### Step 2: Database Setup

#### 2.1 Secure MySQL Installation
```bash
sudo mysql_secure_installation
```

#### 2.2 Create Database and User
```sql
-- Log in to MySQL
sudo mysql -u root -p

-- Create database
CREATE DATABASE conslot_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'conslot_user'@'localhost' IDENTIFIED BY 'strong_password_here';

-- Grant privileges
GRANT ALL PRIVILEGES ON conslot_db.* TO 'conslot_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Exit
EXIT;
```

#### 2.3 Import Database Schema
```bash
# Navigate to project directory
cd /var/www/html/conslot

# Import the database schema
mysql -u conslot_user -p conslot_db < sql/setup.sql

# Import sample data (optional)
mysql -u conslot_user -p conslot_db < sql/sample_data.sql
```

### Step 3: Application Deployment

#### 3.1 Clone Repository
```bash
# Navigate to web root
cd /var/www/html/

# Clone the repository
sudo git clone https://github.com/charlesevangeliojr/conslot.git

# Set ownership
sudo chown -R www-data:www-data conslot/
sudo chmod -R 755 conslot/
```

#### 3.2 Configure Application
```bash
# Navigate to config directory
cd /var/www/html/conslot/config

# Copy and edit database configuration
sudo cp database.php database.php.backup
sudo nano database.php
```

Update the database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'conslot_db');
define('DB_USER', 'conslot_user');
define('DB_PASS', 'strong_password_here');
```

#### 3.3 Configure Application Settings
```bash
# Edit config file
sudo nano config.php
```

Update production settings:
```php
// Debug mode (set to false in production)
define('DEBUG_MODE', false);

// Application URL
define('APP_URL', 'https://yourdomain.com');

// Session settings
define('SESSION_TIMEOUT', 3600); // 1 hour
```

### Step 4: Web Server Configuration

#### 4.1 Apache Configuration

Create virtual host file:
```bash
sudo nano /etc/apache2/sites-available/conslot.conf
```

Add the following configuration:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/html/conslot
    
    <Directory /var/www/html/conslot>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/conslot_error.log
    CustomLog ${APACHE_LOG_DIR}/conslot_access.log combined
</VirtualHost>
```

Enable the site and rewrite module:
```bash
sudo a2ensite conslot.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### 4.2 Nginx Configuration (Alternative)

Create server block:
```bash
sudo nano /etc/nginx/sites-available/conslot
```

Add the following configuration:
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/html/conslot;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
    
    error_log /var/log/nginx/conslot_error.log;
    access_log /var/log/nginx/conslot_access.log;
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/conslot /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Step 5: SSL Certificate Setup

#### 5.1 Install Let's Encrypt
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Obtain and install certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

#### 5.2 Auto-renewal Setup
```bash
# Test auto-renewal
sudo certbot renew --dry-run

# Add cron job for auto-renewal
sudo crontab -e
```

Add the following line:
```
0 12 * * * /usr/bin/certbot renew --quiet
```

### Step 6: Security Hardening

#### 6.1 File Permissions
```bash
# Secure sensitive files
sudo chmod 600 /var/www/html/conslot/config/database.php
sudo chmod 600 /var/www/html/conslot/config/config.php

# Set proper ownership
sudo chown -R www-data:www-data /var/www/html/conslot
sudo find /var/www/html/conslot -type f -exec chmod 644 {} \;
sudo find /var/www/html/conslot -type d -exec chmod 755 {} \;
```

#### 6.2 PHP Configuration
```bash
# Edit PHP configuration
sudo nano /etc/php/8.1/apache2/php.ini
```

Recommended settings:
```ini
; Hide PHP version
expose_php = Off

; Error reporting for production
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log

; File upload restrictions
file_uploads = On
upload_max_filesize = 2M
max_file_uploads = 20

; Memory and execution limits
memory_limit = 256M
max_execution_time = 30
max_input_time = 60

; Session security
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

#### 6.3 Create .htaccess Security Rules
```bash
sudo nano /var/www/html/conslot/.htaccess
```

Add security rules:
```apache
# Prevent directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "\.(sql|log|md|txt|ini)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Prevent access to config files
<FilesMatch "^config\.(php|ini)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options DENY
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com; img-src 'self' data: https:; connect-src 'self'"
</IfModule>

# URL rewriting
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### Step 7: Performance Optimization

#### 7.1 Enable PHP OPcache
```bash
# Edit PHP configuration
sudo nano /etc/php/8.1/apache2/php.ini
```

Add OPcache settings:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
```

#### 7.2 Configure MySQL for Performance
```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Add performance settings:
```ini
[mysqld]
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
innodb_flush_method = O_DIRECT
query_cache_size = 32M
query_cache_type = 1
```

#### 7.3 Enable Gzip Compression
```bash
# For Apache
sudo a2enmod deflate
sudo systemctl restart apache2

# Add to .htaccess
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

### Step 8: Backup Setup

#### 8.1 Database Backup Script
```bash
sudo nano /usr/local/bin/backup_conslot.sh
```

Add backup script:
```bash
#!/bin/bash

# Configuration
DB_NAME="conslot_db"
DB_USER="conslot_user"
DB_PASS="strong_password_here"
BACKUP_DIR="/var/backups/conslot"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Create database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Remove backups older than 30 days
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +30 -delete

echo "Database backup completed: $BACKUP_DIR/db_backup_$DATE.sql.gz"
```

Make script executable:
```bash
sudo chmod +x /usr/local/bin/backup_conslot.sh
```

#### 8.2 File Backup Script
```bash
sudo nano /usr/local/bin/backup_conslot_files.sh
```

Add file backup script:
```bash
#!/bin/bash

# Configuration
APP_DIR="/var/www/html/conslot"
BACKUP_DIR="/var/backups/conslot"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Create file backup
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz -C $APP_DIR .

# Remove backups older than 30 days
find $BACKUP_DIR -name "files_backup_*.tar.gz" -mtime +30 -delete

echo "File backup completed: $BACKUP_DIR/files_backup_$DATE.tar.gz"
```

Make script executable:
```bash
sudo chmod +x /usr/local/bin/backup_conslot_files.sh
```

#### 8.3 Schedule Backups with Cron
```bash
sudo crontab -e
```

Add backup schedules:
```
# Database backup daily at 2 AM
0 2 * * * /usr/local/bin/backup_conslot.sh

# File backup weekly on Sunday at 3 AM
0 3 * * 0 /usr/local/bin/backup_conslot_files.sh
```

### Step 9: Monitoring and Logging

#### 9.1 Application Logging
```bash
# Create log directory
sudo mkdir -p /var/log/conslot
sudo chown www-data:www-data /var/log/conslot

# Update config.php to use custom log
sudo nano /var/www/html/conslot/config/config.php
```

Add logging configuration:
```php
// Custom error log
ini_set('error_log', '/var/log/conslot/error.log');
ini_set('log_errors', 1);
```

#### 9.2 Log Rotation
```bash
sudo nano /etc/logrotate.d/conslot
```

Add log rotation rules:
```
/var/log/conslot/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload apache2
    endscript
}
```

### Step 10: Testing and Validation

#### 10.1 Functionality Tests
- Test user registration and login
- Test slot creation and booking
- Test email notifications
- Test file uploads
- Test responsive design

#### 10.2 Security Tests
- Test SQL injection protection
- Test XSS protection
- Test CSRF protection
- Test file upload security
- Test session security

#### 10.3 Performance Tests
- Test page load times
- Test database query performance
- Test concurrent user handling
- Test file upload performance

## 🔧 Maintenance

### Regular Tasks

#### Daily
- Check error logs
- Monitor server resources
- Verify backup completion

#### Weekly
- Review security logs
- Update system packages
- Check SSL certificate expiry

#### Monthly
- Database optimization
- Log cleanup
- Performance review
- Security audit

### Updates and Upgrades

#### Application Updates
```bash
# Navigate to application directory
cd /var/www/html/conslot

# Pull latest changes
sudo git pull origin main

# Update dependencies if using Composer
sudo composer install --no-dev --optimize-autoloader

# Clear caches if using any
sudo rm -rf cache/*
```

#### System Updates
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Restart services
sudo systemctl restart apache2
sudo systemctl restart mysql
```

## 🚨 Troubleshooting

### Common Issues

#### 1. Database Connection Errors
```bash
# Check MySQL status
sudo systemctl status mysql

# Check database credentials
mysql -u conslot_user -p conslot_db

# Check PHP error logs
sudo tail -f /var/log/conslot/error.log
```

#### 2. Permission Issues
```bash
# Check file permissions
ls -la /var/www/html/conslot/

# Fix ownership
sudo chown -R www-data:www-data /var/www/html/conslot/

# Fix permissions
sudo find /var/www/html/conslot -type f -exec chmod 644 {} \;
sudo find /var/www/html/conslot -type d -exec chmod 755 {} \;
```

#### 3. Apache/Nginx Issues
```bash
# Check web server status
sudo systemctl status apache2

# Check configuration
sudo apache2ctl configtest

# Check error logs
sudo tail -f /var/log/apache2/error.log
```

#### 4. SSL Certificate Issues
```bash
# Check certificate status
sudo certbot certificates

# Renew certificate manually
sudo certbot renew

# Test auto-renewal
sudo certbot renew --dry-run
```

### Performance Issues

#### 1. Slow Database Queries
```bash
# Enable slow query log
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Add to configuration
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Restart MySQL
sudo systemctl restart mysql
```

#### 2. High Memory Usage
```bash
# Check memory usage
free -h

# Check PHP memory usage
sudo nano /etc/php/8.1/apache2/php.ini

# Adjust memory_limit if needed
memory_limit = 256M
```

## 📞 Support

For deployment support:
- Check the error logs first
- Review the troubleshooting section
- Create an issue on GitHub
- Contact the development team

## 🔒 Security Considerations

- Regularly update all software components
- Use strong, unique passwords
- Enable two-factor authentication where possible
- Monitor access logs regularly
- Implement rate limiting
- Use Web Application Firewall (WAF)
- Regular security audits
- Keep backups secure and tested

---

**Note**: This deployment guide assumes a Linux server environment. Adjustments may be needed for Windows or macOS servers.
