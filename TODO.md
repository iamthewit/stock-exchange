# Work In Progress
Sending messages to RabbitMQ
Currently sending all domain events (in the future change this to application level events ? what makes the most sense?)

# TODO
- Remove shares from Trader entity in the Exchange aggregate
  - add a new Trader aggregate that includes all of a traders shares
  - implement the owner id property on Share and have the Share entity track its owner
- In any 'apply' methods that require other related domain objects - check the collections that already exist on the exchange
  - i.e applyBidAddedToExchange uses a trader that already exists in the TraderCollection
- Add tests for all the new handlers
- Create specific entity id classes that extend uuid interface
- Create AbstractCollection
- Exchange Simulation
- Event Loop


## Thoughts / Ideas / Refactors

In reality traders operate via brokers. Every trader has a brokerage account and the broker acts on behalf of the trader to buy/sell securities.

Maybe the traders should not have a share collection that they can manipulate, instead the exchange has a collection of all shares and updates the owner id when a transfer is made.
A trader can then get their share collection from the exchange.
This would mean that the exchange needs a collection of all shares at all times (which makes sense) but would also become massively inefficient - maybe we need a domain service to deal with this rather than the exchange...?

---

## Additional Aggregates / Bounded Contexts

### Trader Context

Responsibilities:
- Trader registration
- Trader data (shares owned, share history, bid and ask history)


### Share Context

Responsibilities:
- Share history 
  - who owned what share at what point in time
  - what price was paid for the share at a given time
  

### Symbol Context

Responsibilities:
- Track current and historical price of symbols (stocks / securities) 