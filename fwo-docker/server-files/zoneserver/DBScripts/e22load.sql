DROP TABLE IF EXISTS events_copy;

CREATE TABLE `events_copy` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `EventID` smallint(5) unsigned default NULL,
  `ServerID` smallint(5) unsigned default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

INSERT INTO events_copy SELECT id, EventID, ServerID FROM events;

INSERT INTO events (ServerID) SELECT ServerID FROM events_copy WHERE EventID = 49179;

UPDATE events SET ID = 22, Type = 5, Occurance = 1, Params = 1, NumericDay = 5, Day = 4, Month = 5, Year = 0, Hour = 0, Minute = 0, MInterval = 43200000, NumOccurance = 0, EventID = 33285, LastTime = 0 WHERE ID = last_insert_id();

DROP TABLE events_copy;

update npcattribdyn set IsDead=0 where AttribID=1675;
