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

#While we are working on it allow ssh
sudo ufw allow ssh

#rabbit specific rules
sudo ufw allow 5672
sudo ufw allow 5671

sudo ufw enable 

sudo ufw status verbose
