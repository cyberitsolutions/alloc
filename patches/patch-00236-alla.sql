-- rebuild bad client search index
INSERT INTO indexQueue (entity,entityID) SELECT "client",clientID FROM client;
