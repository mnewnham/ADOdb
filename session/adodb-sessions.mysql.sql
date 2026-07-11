-- Schema for MySQL Sessions
-- Supports Compression & Encryption


DROP TABLE IF EXISTS sessions;

CREATE TABLE /*! IF NOT EXISTS */ sessions (
	sesskey VARCHAR( 64 ) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
	expiry DATETIME NOT NULL ,
	expireref VARCHAR( 250 ) DEFAULT '',
	created DATETIME NOT NULL ,
	modified DATETIME NOT NULL ,
	sessdata LONGBLOB,
	PRIMARY KEY ( sesskey ) ,
	INDEX sess2_expiry( expiry ),
	INDEX sess2_expireref( expireref )
);
