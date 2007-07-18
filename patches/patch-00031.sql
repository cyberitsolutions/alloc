
-- Make config.name of type varchar(255)
alter table config change name name varchar(255);

-- Make config.name a unique key
alter table config add unique key (name);

-- Nuke existing person.username key
alter table person drop key username;

-- Make person.username a unique key
alter table person add unique key (username);

