 -- DB2 Schema for the ADOdb session management feature
 -- Supports compression and encryption
 -- BLOB support not required in ADOdb Session Feature
 
DROP TABLE IF EXISTS session;

CREATE TABLE session (
  sesskey VARCHAR( 64 ) NOT NULL DEFAULT '',
  expiry TIMESTAMP NOT NULL ,
  expireref VARCHAR( 250 ) DEFAULT '',
  created TIMESTAMP NOT NULL ,
  modified TIMESTAMP NOT NULL ,
  sessdata BLOB,
  PRIMARY KEY ( sesskey )

);
CREATE INDEX sess2_expiry ON session( expiry );
CREATE INDEX sess2_expireref ON session( expireref );
