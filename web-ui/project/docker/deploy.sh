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
PROJECT_NAME="scheduler_gui"

CONTAINER_IMAGE="$PROJECT_NAME"

docker kill $PROJECT_NAME
docker rm $PROJECT_NAME

docker run -d \
-p 80:80 -p 443:443 \
--name="$PROJECT_NAME" \
--privileged \
$CONTAINER_IMAGE

