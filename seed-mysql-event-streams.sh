#!/bin/bash

# drop the database
docker exec -i docker-event_streams_mysql_1 mysqladmin --user=root --password=root --force drop event_streams

# create the database
docker exec -i docker-event_streams_mysql_1 mysql --user=root --password=root -e "CREATE DATABASE event_streams"

# run the event stream migrations
docker exec -i docker-event_streams_mysql_1 mysql event_streams --user=root --password=root < ./config/scripts/mysql/01_event_streams_table.sql
docker exec -i docker-event_streams_mysql_1 mysql event_streams --user=root --password=root < ./config/scripts/mysql/02_projections_table.sql

# seed the database
./bin/console seed:event-stream-db