# Stock Exchange

This project is a simple exercise in modelling a domain and creating a clean, layered architecture surrounding the domain to expose its logic.

The domain will evolve over time as my knowledge of real life stock exchanges evolve.

See the TODO.md file for more information in upcoming changes and ideas.

## Docker Container

TODO

## Install dependencies

`composer install`

## Run the tests

`./vendor/bin/phpunit tests`

### Run tests with code coverage

`composer phpunit-cc`

## Check dependencies between software layers

`./vendor/bin/deptrac` 

## Database

Start the DB container:

`./start-mysql-event-streams.sh`

Stop the DB container:

`./stop-mysql-event-streams.sh`

Drop and Re-seed the DB:

`./reseed-mysql-event-streams.sh`

### Migrations

#### Event Store

The script (above) that starts the DB container will run the event sotre migrations for you.

#### Read Models

TODO