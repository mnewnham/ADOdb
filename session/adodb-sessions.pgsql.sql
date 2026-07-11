 -- PGSQL Schema for  ADOdb session management feature
 
DROP TABLE IF EXISTS session;

CREATE TABLE session (
  sesskey VARCHAR( 64 ) NOT NULL DEFAULT '',
  expiry TIMESTAMP NOT NULL ,
  expireref VARCHAR( 250 ) DEFAULT '',
  created TIMESTAMP NOT NULL ,
  modified TIMESTAMP NOT NULL ,
  sessdata BYTEA,
  PRIMARY KEY ( sesskey )

);
CREATE INDEX sess2_expiry ON session( expiry );
CREATE INDEX sess2_expireref ON session( expireref );
