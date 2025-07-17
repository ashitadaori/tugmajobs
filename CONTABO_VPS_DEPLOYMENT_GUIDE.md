# Deploy Laravel KYC App to Contabo VPS - Complete Guide

## 🚀 Step-by-Step Deployment to Your Contabo VPS

### **Prerequisites**
- ✅ Contabo VPS running (Ubuntu/CentOS)
- ✅ SSH access to your VPS
- ✅ Domain name (optional but recommended)

## **Step 1: Connect to Your VPS**

```bash
# SSH into your Contabo VPS
ssh root@YOUR_VPS_IP_ADDRESS

# Or if you have a domain
ssh root@yourdomain.com
```

## **Step 2: Update System & Install Required Software**

### **For Ubuntu/Debian:**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx mysql-server php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-gd php8.2-curl php8.2-mbstring php8.2-zip php8.2-bcmath php8.2-tokenizer php8.2-json php8.2-fileinfo unzip git curl

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Node.js (for asset compilation)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### **For CentOS/RHEL:**
```bash
# Update system
sudo yum update -y

# Install EPEL repository
sudo yum install -y epel-release

# Install required packages
sudo yum install -y nginx mysql-server php php-fpm php-mysql php-xml php-gd php-curl php-mbstring php-zip php-bcmath php-json unzip git curl

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

## **Step 3: Configure MySQL Database**

```bash
# Start and enable MySQL
sudo systemctl start mysql
sudo systemctl enable mysql

# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
-- In MySQL console
CREATE DATABASE job_portal;
CREATE USER 'jobportal_user'@'localhost' IDENTIFIED BY 'your_strong_password_here';
GRANT ALL PRIVILEGES ON job_portal.* TO 'jobportal_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## **Step 4: Upload Your Laravel Application**

### **Option A: Using Git (Recommended)**
```bash
# Navigate to web directory
cd /var/www

# Clone your repository
sudo git clone https://github.com/yourusername/your-repo.git job-portal
# OR upload your files via SCP/SFTP

# Set ownership
sudo chown -R www-data:www-data /var/www/job-portal
sudo chmod -R 755 /var/www/job-portal
sudo chmod -R 775 /var/www/job-portal/storage
sudo chmod -R 775 /var/www/job-portal/bootstrap/cache
```

### **Option B: Upload via SCP (from your local machine)**
```bash
# From your local machine
scp -r /path/to/your/job-portal root@YOUR_VPS_IP:/var/www/
```

## **Step 5: Configure Laravel Application**

```bash
# Navigate to your app directory
cd /var/www/job-portal

# Install PHP dependencies
sudo -u www-data composer install --optimize-autoloader --no-dev

# Copy environment file
sudo cp .env.example .env

# Generate application key
sudo -u www-data php artisan key:generate

# Edit environment file
sudo nano .env
```

### **Update .env file:**
```env
APP_NAME=TugmaJobs
APP_ENV=production
APP_KEY=base64:your_generated_key_here
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=job_portal
DB_USERNAME=jobportal_user
DB_PASSWORD=your_strong_password_here

# Didit KYC Configuration
DIDIT_BASE_URL=https://verification.didit.me
DIDIT_AUTH_URL=https://verification.didit.me
DIDIT_API_KEY=yYsWheBZkfdVqzrYfE9zqC4deY8GZUVS_khrK6ADTko
DIDIT_CLIENT_ID=vYknVd0qu0OmY7Emjd_Jhw
DIDIT_CLIENT_SECRET=yYsWheBZkfdVqzrYfE9zqC4deY8GZUVS_khrK6ADTko
DIDIT_WORKFLOW_ID=8e25fe08-eeeb-415d-9c49-a5b4ebfaf5f0
DIDIT_CALLBACK_URL=${APP_URL}/kyc/webhook
DIDIT_REDIRECT_URL=${APP_URL}/kyc/success
DIDIT_WEBHOOK_SECRET=u_cOKe9R0ddstny5U3dGa_wfD7FYA-djhB1fmlT3PWE
```

```bash
# Run database migrations
sudo -u www-data php artisan migrate --force

# Cache configuration
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Install and build frontend assets (if needed)
npm install
npm run build
```

## **Step 6: Configure Nginx**

```bash
# Create Nginx configuration
sudo nano /etc/nginx/sites-available/job-portal
```

### **Nginx Configuration:**
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com YOUR_VPS_IP;
    root /var/www/job-portal/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Enable the site
sudo ln -s /etc/nginx/sites-available/job-portal /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test Nginx configuration
sudo nginx -t

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo systemctl enable nginx
sudo systemctl enable php8.2-fpm
```

## **Step 7: Install SSL Certificate (Let's Encrypt)**

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Get SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo crontab -e
# Add this line:
0 12 * * * /usr/bin/certbot renew --quiet
```

## **Step 8: Configure Firewall**

```bash
# Install and configure UFW
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

## **Step 9: Test Your Deployment**

### **Basic Tests:**
```bash
# Test PHP
php -v

# Test database connection
sudo -u www-data php artisan tinker
# In tinker: DB::connection()->getPdo();

# Test application
curl -I http://yourdomain.com
```

### **KYC Integration Test:**
```bash
# Test Didit integration
sudo -u www-data php artisan didit:test
```

## **Step 10: Update Didit Business Console**

1. **Login to**: https://business.didit.me
2. **Update URLs**:
   - **Callback URL**: `https://yourdomain.com/kyc/webhook`
   - **Redirect URL**: `https://yourdomain.com/kyc/success`

## **🎯 Quick Commands Summary**

```bash
# Connect to VPS
ssh root@YOUR_VPS_IP

# Navigate to app
cd /var/www/job-portal

# Update application
git pull origin main
sudo -u www-data composer install --optimize-autoloader --no-dev
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

## **🔧 Troubleshooting**

### **Common Issues:**

1. **Permission Issues:**
```bash
sudo chown -R www-data:www-data /var/www/job-portal
sudo chmod -R 755 /var/www/job-portal
sudo chmod -R 775 /var/www/job-portal/storage
sudo chmod -R 775 /var/www/job-portal/bootstrap/cache
```

2. **PHP Extensions Missing:**
```bash
sudo apt install php8.2-extension-name
sudo systemctl restart php8.2-fpm
```

3. **Database Connection Issues:**
```bash
# Check MySQL status
sudo systemctl status mysql

# Check database credentials
mysql -u jobportal_user -p job_portal
```

4. **Nginx Issues:**
```bash
# Check Nginx status
sudo systemctl status nginx

# Check error logs
sudo tail -f /var/log/nginx/error.log
```

## **🎉 Final Result**

After completing these steps, you'll have:
- ✅ **Live Laravel app** at `https://yourdomain.com`
- ✅ **SSL certificate** for secure connections
- ✅ **Real KYC integration** with Didit
- ✅ **Professional hosting** on your Contabo VPS
- ✅ **No more ngrok issues**!

Your KYC flow will work perfectly:
1. Users visit: `https://yourdomain.com/kyc/start`
2. Complete verification on Didit
3. Get redirected to: `https://yourdomain.com/kyc/success`
4. ✅ Everything works smoothly!