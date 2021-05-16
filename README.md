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

### Migrations

#### Event Store

Check the `config/scripts` directory

#### Read Models

TODO

### Run a MySQL database in a docker container

```
ddocker run --name docker-mysql_1 \
    -e MYSQL_ROOT_PASSWORD=root \
    -e MYSQL_DATABASE=event_streams \
    -e MYSQL_USER=user \
    -e MYSQL_PASSWORD=password \
    -p 3306:3306 \
    -d mysql/mysql-server:latest

```
