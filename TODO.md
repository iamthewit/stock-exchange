# TODO

- Add tests for all the new handlers
- Read Models
- Rebuild exchange collections in applyExchangeCreated
- Add event meta data to all domain objects
- Event Streams
- Exchange Simulation
- Event Loop
- Docker container


## Thoughts / Ideas / Refactors

In reality traders operate via brokers. Every trader has a brokerage account and the broker acts on behalf of the trader to buy/sell securities.


Every share has to be owned by something as soon as it is created. The initial owner of a share is the share issuer.
At the moment the share object stores the ownerId and each Trader object has methods to add and remove shares, but they don;t effect the shares owner ID.
The add/remove Trader methods should be removed and the responsibility should be handed entirely to the exchange to transfer shares between Traders.

Maybe the traders should not have a share collection that they can manipulate, instead the exchange has a collection of all shares and updates the owner id when a transfer is made.
A trader can then get their share collection from the exchange.
This would mean that the exchange needs a collection of all shares at all times (which makes sense) but would also become massively inefficient - maybe we need a domain service to deal with this rather than the exchange...?

