#!/bin/bash

# Start PHP-FPM
php-fpm -D

# Start nginx in foreground
nginx -g 'daemon off;'
