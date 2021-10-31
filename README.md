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

### CQRS (Command Query Responsibility Segregation)

CQRS stands for Command Query Responsibility Segregation. At its heart is the notion that you can use a different model to update information than the model you use to read information. - Martin Fowler

This project utilises CQRS to split its use cases between those that want to ask the system something (queries) and those that want to tell the system to do something (commands). Separate read and write repositories have also been included. 

Following CQRS allows the application use cases to be written clearly and integrate with the domain layer seamlessly.

Every Command and Query in this system is pushed to a message bus and then picked up by a handler. There is one handler for every command and query.

In general commands will not return anything, it is assumed that the command will be executed. In cases where commands fail an exception must be thrown. Queries will always return something or throw and exception.

See the `src/Application/Command` directory for all the current system commands. The `Query` directory at the same level stores the system queries.
The handlers for the commands and queries are in the `Handler` directory.

Greg Young's CQRS Introduction: https://cqrs.wordpress.com/documents/cqrs-introduction/

### Event Sourcing

Event sourcing is a method of storing state in an incremental fashion. Everytime an entities state is mutated we keep a log of the changes that were applied to the entity. 
This log gives us the entire history of an entity from creation to the present moment. We can use the event log to restore the state of an entity to any given point in time by simply replaying the events in the order they have been stored.
When we want to get a current view of an entity we pull all of the events for that entity out of the event store and apply each of them to a raw instance of the entity.

This project uses the Prooph PDO Event Store library to deal with the storing of events: http://docs.getprooph.org/event-store/

In this project a log of all of the `Exchange` aggregate root mutations are stored in a database. The `Exchange` class has a `restoreStateFromEvents` method that takes an array of events. 
This method can be given an ordered array of events in order to restore the `Exchange` to any of its previous states or its current state.

You can read more about event sourcing here: https://microservices.io/patterns/data/event-sourcing.html

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