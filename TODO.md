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
- Rebuild exchange collections in applyExchangeCreated
- Add event meta data to all domain objects
- Exchange Simulation
- Event Loop
- Docker container 


## Thoughts / Ideas / Refactors

In reality traders operate via brokers. Every trader has a brokerage account and the broker acts on behalf of the trader to buy/sell securities.

Maybe the traders should not have a share collection that they can manipulate, instead the exchange has a collection of all shares and updates the owner id when a transfer is made.
A trader can then get their share collection from the exchange.
This would mean that the exchange needs a collection of all shares at all times (which makes sense) but would also become massively inefficient - maybe we need a domain service to deal with this rather than the exchange...?

---

Instead of having to query for the exchange after every state change again (to make sure you have the updated state) maybe we could return the exchange after every state change?...

---

Recently I went through and started to make sure that the exchanges entire state and state of all objects it knows about had all of their own events in their respective `appliedEvents` array (I'm not sure if i totally finished this, need to write some tests!). I also modified the `nextAggregateVersion` method to take into account un-applied (dispatahcable) events within the same namespace - maybe this would have solved my original problem as it was surfacing as the event store complaining that events were being added with the same no. (aggregate version) - needs more testing, i'm too tired to think about it now.

---

If exchange is the aggregate root it should be the only entity that has a version...
There should be no need to store the other entities in any other stream other than the exchanges stream...
NEed to do some more reading....