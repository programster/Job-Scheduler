#!/bin/bash

# ensure running bash
if ! [ -n "$BASH_VERSION" ];then
    echo "this is not bash, calling self with bash....";
    SCRIPT=$(readlink -f "$0")
    /bin/bash $SCRIPT
    exit;
fi

SCRIPT=$(readlink -f "$0")
SCRIPTPATH=$(dirname "$SCRIPT") 
cd $SCRIPTPATH

# load the variables
#source ../../settings/docker_settings.sh

##############
## Settings ##
##############
# Change REGISTRY to "" if you dont have a registry.
IMAGE_NAME="job-scheduler"
CONTAINER_NAME="scheduler"


docker kill $IMAGE_NAME
docker rm $IMAGE_NAME

docker run -d \
  -p 3901:3901 \
  -e "ADDRESS=172.17.0.2" \
  --name="$CONTAINER_NAME" \
  $IMAGE_NAME
