setup:
	./start-mongo.sh && ./start-mysql-event-streams.sh && ./create-and-migrate-event-streams.sh && ./seed-mysql-event-streams.sh

reset-and-rerun:
	./stop-mongo.sh && ./start-mongo.sh && ./start-mysql-event-streams.sh && ./create-and-migrate-event-streams.sh && ./seed-mysql-event-streams.sh

stop-databases:
	./stop-mysql-event-streams.sh && ./stop-mongo.sh