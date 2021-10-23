# Stock Exchange

This project is a simple exercise in modelling a domain and creating a clean, layered architecture surrounding the domain to expose its logic. Besides DDD this project incorporates Hexagonal Architecture, CQRS and Event Sourcing.

The domain will evolve over time as my knowledge of real life stock exchanges evolve.

See the TODO.md file for more information in upcoming changes and ideas.

## About

### DDD (Domain Driven Design)

DDD is a process for modelling a solution to a given problem.

The problem that this domain is attempting to solve is that of trading stocks between two parties.

The entities that I have identified (so far) to solve this problem are:

- Exchange
- Trader
- Share
- Trade
- Bid
- Ask

The Exchange entity is the aggregate root for the entire domain. Every other entity sits within the Exchange aggregate.

If you want to Trade a Share you must do so via placing Bids and Asks on the Exchange.

### Hexagonal Architecture

Hexagonal Architecture is a layered architecture comprising three layers: Domain, Application, Infrastructure.

Imagine three concentric circles with the Domain in the middle surrounded by the Application which is then surrounded by the Infrastructure. 
This layering gives us our separation of concerns but also our dependency structure. 
The dependencies point inwards.

- The Domain layer depends on nothing
- The Application layer depends on the Domain layer
- The Infrastructure layer depends on the Application and Domain layers

- The Domain layer contains all of our business logic. It should not have any dependencies on any third party libraries (if possible).
- The Application layer contains our "use-cases" or what our application does.
- The Infrastructure layer is responsible for interaction with 3rd parties e.g end users via HTTP or CLI, database repositories, 3rd party APIs

Hexagonal architecture is also referred to as Ports and Adapters. Each side of the hexagon represent a port (an interface). 
The area outside a port is where you write add your adapters (concrete implementations). 
Just because a hexagon has six sides does not mean you are restricted to siz ports.

See here for a more detailed explanation of hexagonal architecture: https://blog.octo.com/en/hexagonal-architecture-three-principles-and-an-implementation-example/

### CQRS (Command Query Responsibility Separation)

TODO

### Event Sourcing

TODO

## Docker Container

TODO

## Install dependencies

`composer install`

## Run the tests

`./vendor/bin/phpunit tests`

_Note:_ Some tests require a database to be in place. See the [Database](#database) section below for more info. 

### Run tests with code coverage

`composer phpunit-cc`

## Check dependencies between software layers

`./vendor/bin/deptrac` 

## Database

Start the DB container:

`./start-mysql-event-streams.sh`

Seed / Re-seed the DB:

`./seed-mysql-event-streams.sh`


Stop the DB container:

`./stop-mysql-event-streams.sh`

### Migrations

#### Event Store

The script (above) that starts the DB container will run the event sotre migrations for you.

#### Read Models

TODO