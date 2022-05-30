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
Refactor towards smaller aggregates 
The Exchange aggregate knows too much, it should only care about exchanging shares between traders.
It doesn't need to know about all the shares a trader owns in order to execute a trade.

## Additional Aggregates / Bounded Contexts

Started work on smaller aggregates:
- Exchange
- BidAsk
- Trader
- Share

Currently, moving logic from original exchange aggregate to the new one