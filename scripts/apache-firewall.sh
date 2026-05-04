#!/bin/bash

#Installs ufw if doesn't exsist
if ! command -v ufw &> /dev/null
then
    echo "ufw not found, installing..."

    sudo apt update
    sudo apt install -y ufw
fi
sudo ufw --force reset
sudo ufw default deny incoming
sudo ufw default allow outgoing

sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https


sudo ufw enable

sudo ufw status verbose
