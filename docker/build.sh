#!/bin/bash

# ensure running bash
if ! [ -n "$BASH_VERSION" ];then
    echo "this is not bash, calling self with bash....";
    SCRIPT=$(readlink -f "$0")
    /bin/bash $SCRIPT
    exit;
fi

##############
## Settings ##
##############
# Change REGISTRY to "" if you dont have a registry.
REGISTRY="programster"
PROJECT_NAME="job-scheduler"


# Get the path to script just in case executed from elsewhere.
SCRIPT=$(readlink -f "$0")
SCRIPTPATH=$(dirname "$SCRIPT")
cd $SCRIPTPATH

cp -f Dockerfile ../.
cd ../.

# Ask the user if they want to use the docker cache
read -p "Do you want to use a cached build (y/n)? " -n 1 -r
echo ""   # (optional) move to a new line
if [[ $REPLY =~ ^[Yy]$ ]]
then
    docker build --pull --tag "$REGISTRY/$PROJECT_NAME" .
else
    docker build --pull --tag "$REGISTRY/$PROJECT_NAME" --no-cache .
fi

# Clean up - Remove the dockerfile we moved up.
rm $SCRIPTPATH/../Dockerfile

# Push to our registry if registry is set
if [ -n "$REGISTRY" ]; then
    docker push $REGISTRY/$PROJECT_NAME
fi

echo "Run the container with the following command:"
echo "bash deploy.sh"