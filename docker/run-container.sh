#!/bin/bash

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
PROJECT_NAME="scheduler"

CONTAINER_IMAGE="$PROJECT_NAME"

docker kill $PROJECT_NAME
docker rm $PROJECT_NAME

docker run -d \
-p 3901:3901 \
-e "ADDRESS=172.17.0.2" \
--name="$PROJECT_NAME" \
$CONTAINER_IMAGE
