 -- Schema for the ADOdb session management feature for SQL Server
 
DROP TABLE IF EXISTS session;

CREATE TABLE session (
  sesskey VARCHAR( 64 ) NOT NULL DEFAULT '',
  expiry DATETIME NOT NULL ,
  expireref VARCHAR( 250 ) DEFAULT '',
  created DATETIME NOT NULL ,
  modified DATETIME NOT NULL ,
  sessdata VARCHAR(MAX),
  PRIMARY KEY ( sesskey )
);

CREATE INDEX sess2_expiry ON session( expiry );
CREATE INDEX sess2_expireref ON session( expireref );
