#!/bin/bash

# 1. Install Dependencies
sudo apt update
sudo apt install -y apache2 mysql-server php php-mysql php-mbstring php-gd

# 2. Configure Apache for Dynamic IP
# Get the system's IP address (assuming eth0 interface)
IP_ADDRESS=$(ip addr show eth0 | grep "inet\b" | awk '{print $2}' | cut -d/ -f1)
# Extract the first three octets of the IP address
IP_PREFIX=$(echo "$IP_ADDRESS" | cut -d. -f1-3)

# Create a custom Apache virtual host configuration
cat << EOF > /etc/apache2/sites-available/clinic.conf
<VirtualHost *:80>
    ServerName $IP_PREFIX.*
    DocumentRoot /var/www/html/clinic/public # Update with your project's public directory
    <Directory /var/www/html/clinic/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
EOF

# Enable the virtual host and restart Apache
sudo a2ensite clinic.conf
sudo a2dissite 000-default.conf
sudo systemctl reload apache2

# 3. Database Setup and Population
# (This part is similar to the previous response, but adapted for the script)

# Database credentials (replace with your actual values)
DB_HOST="localhost"
DB_NAME="clinic"
DB_USER="kali"
DB_PASS="your_db_password"

# Create the database
mysql -u root -p << EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME;
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
FLUSH PRIVILEGES;
EXIT
EOF

# Populate the database (replace table and column names as needed)
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << EOF

# ... (Insert SQL statements from the previous response to add doctors, patients, appointments)

EOF

GIT_REPO="https://github.com/FancybearIN/clinic.git"
PROJECT_DIR="/var/www/html/clinic"  # Adjust if needed

# Clone the repository if it doesn't exist
if [ ! -d "$PROJECT_DIR" ]; then
  sudo git clone "$GIT_REPO" "$PROJECT_DIR"
else
  echo "Project directory already exists. Skipping cloning."
fi

# Set proper permissions (important for security)
sudo chown -R www-data:www-data "$PROJECT_DIR"
sudo chmod -R 755 "$PROJECT_DIR"

echo "Setup complete! Access your clinic application at http://$IP_ADDRESS/" 
