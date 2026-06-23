-- Session managment Schema for SQLite3
-- Supports Compression & Encryption

USE adodb_sessions;
 
DROP TABLE IF EXISTS sessions;

CREATE TABLE sessions (
  sesskey VARCHAR( 64 ) NOT NULL DEFAULT '',
  expiry DATETIME NOT NULL ,
  expireref VARCHAR( 250 ) DEFAULT '',
  created DATETIME NOT NULL ,
  modified DATETIME NOT NULL ,
  sessdata LONGBLOB,
  PRIMARY KEY ( sesskey )

);
CREATE INDEX sess2_expiry ON sessions( expiry );
CREATE INDEX sess2_expireref ON sessions( expireref );
