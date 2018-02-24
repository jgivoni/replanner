#!/bin/bash

aws_access_key_id=$1
aws_access_key_secret=$2

# Making configuration changes to the enviroment after installation of components

usermod -a -G vagrant apache

# User home
cp /vagrant/provisioning/user/bashrc.sh /home/vagrant/.bashrc

# SELinux - relax restrictions to make it possible to serve files from other directory
setenforce 0

# Web app
ln -s /vagrant/provisioning/apache/replanner.conf /etc/httpd/conf.d
rm /etc/httpd/conf.d/welcome.conf

chmod -R 755 /replanner

mkdir /var/lib/php/session
chmod -R 777 /var/lib/php/session

mkdir /var/data
mkdir /var/data/cache
chmod -R 777 /var/data

su - vagrant -c "cd /replanner/app && composer install"

mkdir /home/vagrant/.aws
sed -e "s|<id>|$aws_access_key_id|g" -e "s|<secret>|$aws_access_key_secret|g" /vagrant/provisioning/aws/credentials.template > /home/vagrant/.aws/credentials
chown -R vagrant.vagrant /home/vagrant/.aws
chmod -R 750 /home/vagrant

# Autostart services
systemctl start httpd
systemctl start php-fpm
systemctl enable httpd
systemctl enable php-fpm

ntpdate pool.ntp.org
systemctl start ntpd.service
systemctl enable ntpd.service