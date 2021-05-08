# TODO

- Combine Buyer/Seller into Trader class
    - remove Seller/Buyer from Ask/Bid classes
    - add bid / ask collections to trader class
    - update ask/bid methods on exchange to require trader object + price + symbol
        - the exchange will create the bid/ask object on behalf of the trader
        - the traders bid / ask collections will be updated by the exchange
    
- Domain Events
- AggregateRoot
- Event Streams
- Exchange Simulation
- Event Loop
- Layers (infra, application)