ALTER TABLE s_core_auth ADD UNIQUE (
username
);
-- //@UNDO
ALTER TABLE s_core_auth DROP INDEX username;
