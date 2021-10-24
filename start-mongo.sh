#!/bin/bash

docker run --name docker-mongo_1 \
  -p 27017:27017 \
  -d mongo:latest