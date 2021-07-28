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

# run the event stream migrations
docker exec -i docker-event_streams_mysql_1 mysql event_streams --user=root --password=root < ./config/scripts/mysql/01_event_streams_table.sql
docker exec -i docker-event_streams_mysql_1 mysql event_streams --user=root --password=root < ./config/scripts/mysql/02_projections_table.sql

# seed the database
./bin/console seed:event-stream-db