#!/bin/bash
# Basic VPS setup
apt update && apt upgrade -y
apt install -y nodejs nginx sqlite3

# Configure firewall
ufw allow 22
ufw allow 80
ufw allow 443
ufw enable

# Install app dependencies
npm install
sqlite3 neighborhood.db < backend/database/init.sql

# Set up systemd service
cp deployment/systemd/neighborhood.service /etc/systemd/system/
systemctl enable neighborhood
systemctl start neighborhood
