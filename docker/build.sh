#!/bin/bash

# Get absolute path to the script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

cp -f Dockerfile ../.
cd ../.
docker build .

echo "start the container with the following command:"
echo "bash start-container.sh"
