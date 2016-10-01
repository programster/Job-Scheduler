#!/bin/bash

# ensure running bash
if ! [ -n "$BASH_VERSION" ];then
    echo "this is not bash, calling self with bash....";
    SCRIPT=$(readlink -f "$0")
    /bin/bash $SCRIPT
    exit;
fi

# Get the path to script just in case executed from elsewhere.
SCRIPT=$(readlink -f "$0")
SCRIPTPATH=$(dirname "$SCRIPT")
cd $SCRIPTPATH

cp -f Dockerfile ../.
cd ../.

PROJECT_NAME="scheduler"

# Ask the user if they want to use the docker cache
read -p "Do you want to use a cached build (y/n)? " -n 1 -r
echo ""   # (optional) move to a new line
if [[ $REPLY =~ ^[Yy]$ ]]
then
    docker build --pull --tag "$PROJECT_NAME" .
else
    docker build --pull --tag "$PROJECT_NAME" --no-cache .
fi

# Clean up - Remove the dockerfile we moved up.
rm $SCRIPTPATH/../Dockerfile

echo "Run the container with the following command:"
echo "bash run-container.sh"