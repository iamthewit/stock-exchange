#!/bin/bash

# start mysql inside a container
docker run --name docker-event_streams_mysql_1 \
        -e MYSQL_ROOT_PASSWORD=root \
        -e MYSQL_DATABASE=event_streams \
        -e MYSQL_USER=user \
        -e MYSQL_PASSWORD=password \
        -p 3306:3306 \
        -d mysql/mysql-server:latest

# wait for db to become available
while ! docker exec -i docker-event_streams_mysql_1 mysql --user=root --password=root -e "SELECT 1" >/dev/null 2>&1; do
    echo "Waiting for database connection..."
    sleep 1
done

echo "Event Stream DB up and running!"