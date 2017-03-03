#!/bin/bash 

# Set default variables
DBHOST=${DBHOST:-localhost}
DBNAME=${DBNAME:-lan_seats}
DBUSER=${DBUSER:-www-data}
DBPASS=${DBPASS:-secret}
DBCHARSET=${DBCHARSET:-utf8}

# Start MariaDB server
service mysql start

# Create database
mysql -e "create database ${DBNAME};"
mysql -e "create user '${DBUSER}'@'localhost' identified by '${DBPASS}';"
mysql -e "grant all privileges on ${DBNAME}.* to '${DBUSER}'@'localhost';"
mysql ${DBNAME} < database.sql

# Setup config
sed -i -e "s/^\$config->databaseHost = .*/\$config->databaseHost = \'${DBHOST}\';/gi" config.php
sed -i -e "s/^\$config->databaseName = .*/\$config->databaseName = \'${DBNAME}\';/gi" config.php
sed -i -e "s/^\$config->databaseUser = .*/\$config->databaseUser = \'${DBUSER}\';/gi" config.php
sed -i -e "s/^\$config->databasePass = .*/\$config->databasePass = \'${DBPASS}\';/gi" config.php
sed -i -e "s/^\$config->databaseCharset = .*/\$config->databaseCharset = \'${DBCHARSET}\';/gi" config.php

# Import floorplan
php -f import_floorplan.php

cat <<EOF >/etc/nginx/sites-available/default
server {
    listen 80;
    server_tokens off;
    root /var/www;
    index index.php;
    
    location ~* \.(css|js)$ {
        try_files \$uri =404;
    }

    location ~* \.php$ {
        fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_split_path_info ^(.+\.php)(.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    } 
}
EOF

# Start PHP & NGINX
service php7.0-fpm start
service nginx start


bash
