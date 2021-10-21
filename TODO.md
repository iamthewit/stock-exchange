# TODO

- Write repo implementations for the event store
  - add the repo interface to the domain layer so that they can be depended upon by the domain and application layers
  - currently, the application layer is directly accessing the event store (which is effectively the infra layer)
- Read Models / Read Repos
- In any 'apply' methods that require other related domain objects - check the collections that already exist on the exchange
  - i.e applyBidAddedToExchange uses a trader that already exists in the TraderCollection
- Create consistency between toArray and asArray methods
- Add tests for all the new handlers
- Read Models
  - Mongo
  - MySQL / Postgres
- Exchange Simulation
- Event Loop
- Docker container 
- Add another bounded context / service


## Thoughts / Ideas / Refactors

In reality traders operate via brokers. Every trader has a brokerage account and the broker acts on behalf of the trader to buy/sell securities.

Maybe the traders should not have a share collection that they can manipulate, instead the exchange has a collection of all shares and updates the owner id when a transfer is made.
A trader can then get their share collection from the exchange.
This would mean that the exchange needs a collection of all shares at all times (which makes sense) but would also become massively inefficient - maybe we need a domain service to deal with this rather than the exchange...?

---

We could add another bounded context (or perhaps even a new service) to deal with share history
Everytime an event is emitted from the StockExchange context another service/context could listen for share traded events
This context could record the prices / no of trades and provide share stats over time 