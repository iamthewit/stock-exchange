#!/bin/bash

# run the event stream migrations
docker exec -i stock-exchange_mysql-database_1 mysql event_streams --user=root --password=root < ./config/scripts/mysql/01_event_streams_table.sql
docker exec -i stock-exchange_mysql-database_1 mysql event_streams --user=root --password=root < ./config/scripts/mysql/02_projections_table.sql
