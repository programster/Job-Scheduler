# Due to layout of this project, the dockerfile will be moved up two directories and run during
# the build. Thus when performing any ADD commands, remember that this is "where you are"
# The layout is this way so that the settings file sits one level above the project (trunk) so that
# each dev can have their own settings and they do not get merged into the trunk.

FROM ubuntu:14.04

RUN apt-get update
RUN apt-get dist-upgrade -y


# Install the relevant packages
RUN apt-get install vim apache2 libapache2-mod-php5 php5-cli php5-mysql curl php5-curl php5-mcrypt -y

# Enable the php mod we just installed
RUN a2enmod php5

# expose port 80 and 443 for the web requests
EXPOSE 80
EXPOSE 443


###### Update the php INI settings #########

# Increase php's max allowed memory size
RUN sed -i 's;memory_limit = .*;memory_limit = -1;' /etc/php5/apache2/php.ini
RUN sed -i 's;memory_limit = .*;memory_limit = -1;' /etc/php5/cli/php.ini

RUN sed -i 's;display_errors = .*;display_errors = On;' /etc/php5/apache2/php.ini
RUN sed -i 's;display_errors = .*;display_errors = On;' /etc/php5/cli/php.ini

####### END of updating php INI ########
########################################

# Manually set the apache environment variables in order to get apache to work immediately.
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2

# It appears that the new apache requires these env vars as well
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2/apache2.pid

# Set up url rewrite ability
RUN a2enmod rewrite
RUN php5enmod mcrypt

# Install the cron service which is used as the containers FG process
RUN apt-get install cron -y


# Add our websites files to the default apache directory (/var/www)
# This should be as close to the last step as possible for faster rebuilds
ADD settings.php /var/www/scheduler-gui/settings.php

# Add our websites files to the default apache directory (/var/www)
ADD project /var/www/scheduler-gui/project

# Update our apache sites available with the config we created
ADD project/docker/apache-config.conf /etc/apache2/sites-enabled/000-default.conf


# Update ownership
RUN chown www-data:www-data -R /var/www


# Execute the containers startup script which will start many processes/services
# The startup file was already added when we added "project"
CMD ["/bin/bash", "/var/www/scheduler-gui/project/docker/startup.sh"]
