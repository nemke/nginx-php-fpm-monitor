nginx-fpm-status
=====================

Nginx - PHP FPM Monitor

We couldn't find any similar script for Nginx and PHP FPM, so we writed one. It's simple and little bit crud but it does the job.

Installation:
- To use this monitor you need to setup Nginx and PHP FPM status pages and then change paths to them in config.php file.
- If section "Nginx connections per IP" is empty, that is because you are using different user for php-fpm and different for nginx 

License:
Use it freely on your own servers. If you want to do something with it commercially please contact us first via email nemanja@nmdesign.rs

Developed by NM Design Studio - www.nmdesign.rs

git clone https://github.com/zhushengwen/nginx-fpm-status.git
cd nginx-fpm-status
php -S localhost:8000
