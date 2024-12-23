#!/bin/bash

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
wait-for-it db:3306 -t 60 -- echo "MySQL is up"

# Configure dnsmasq
echo "Configuring dnsmasq..."
# Ensure DNS port is available
if [ -e /var/run/dnsmasq.pid ]; then
    rm /var/run/dnsmasq.pid
fi

# Start dnsmasq
echo "Starting dnsmasq..."
dnsmasq --no-daemon --log-queries --log-facility=/var/log/dnsmasq.log &

# Start Apache in foreground
echo "Starting Apache..."
apache2-foreground