#!/bin/bash

sudo docker run -d -p 3901:3901 `docker images -q | sed -n 2p`
