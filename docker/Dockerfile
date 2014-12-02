# Due to layout of this project, the dockerfile will be moved up two directories and run during
# the build. Thus when performing any ADD commands, remember that this is "where you are"
# The layout is this way so that the settings file sits one level above the project (trunk) so that
# each dev can have their own settings and they do not get merged into the trunk.

# This is a customized version of ubuntu 14 that allows things like ssh server etc to run
# http://phusion.github.io/baseimage-docker/
FROM phusion/baseimage

# Set correct environment variables.
ENV HOME /root

# Regenerate SSH host keys. baseimage-docker does not contain any, so you
# have to do that yourself. You may also comment out this instruction; the
# init system will auto-generate one during boot.
RUN /etc/my_init.d/00_regen_ssh_host_keys.sh

# Use baseimage-docker's init system.
CMD ["/sbin/my_init"]


#============ Normal instructions below this line=====================

RUN apt-get update
RUN apt-get dist-upgrade -y


# Add repo for java
RUN apt-key adv --keyserver keyserver.ubuntu.com --recv-keys C2518248EEA14886
RUN echo 'deb http://ppa.launchpad.net/webupd8team/java/ubuntu trusty main' > /etc/apt/sources.list.d/webupd8team-java-trusty.list

# Upgrade the system
RUN apt-get update
RUN apt-get dist-upgrade -y

# This prevents the acceptance of the licence agreement preventing java install
RUN echo oracle-java7-installer shared/accepted-oracle-license-v1-1 select true | /usr/bin/debconf-set-selections

# Install java 7
RUN apt-get install oracle-java7-installer -y

# This is strictly not necessary but sure makes debugging easier.
RUN apt-get install vim.tiny

# Add our java source files (will build based on these later)
ADD src /root/scheduler/src
ADD docker /root/scheduler/docker

# expose port 3901 which php listens on for socket requests.
EXPOSE 3901

# Build the jar within the container
RUN /bin/bash /root/scheduler/docker/compile_java.sh

# Execute the containers startup script which will start many processes/services
# The startup file was already added when we added "project"
CMD ["/bin/bash", "/root/scheduler/docker/startup.sh"]

#================ Dont touch below this line ===============================
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
