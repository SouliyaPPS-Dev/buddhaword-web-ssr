#!/bin/sh
set -e

# Overwrite .env from environment variables so the app's custom loader picks them up
env > /var/www/html/.env

exec apache2-foreground
