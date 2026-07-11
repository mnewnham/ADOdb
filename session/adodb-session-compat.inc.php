<?php

/**
 * ADOdb Session Wrapper for new class.
 */
class ADODB_Session
{
    /**
     * Session Connection's Database provider.
     *
     * Populated when opening the database connection.
     * @see ADODB_Session::open()}.
     *
     * @var string
     */
    protected static $provider;

    /////////////////////
    // getter/setter methods
    /////////////////////

    /**
     * Get/Set Database driver.
     *
     * @param string $driver
     * @return string
     */
    static function driver($driver = null)
    {

        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->driver($driver);
    }

    /**
     * Get/Set Database hostname.
     *
     * @param string $host
     * @return string
     */
    static function host($host = null)
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->host($host);
    }

    /**
     * Get/Set Database connection user.
     *
     * @param string $user
     * @return string
     */
    static function user($user = null)
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->user($user);
    }

    /**
     * Get/Set Database connection password.
     *
     * @param null $password
     * @return string
     */
    static function password($password = null)
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->password($password);
    }

    /**
     * Get/Set Database name.
     *
     * @param null $database
     *
     * @return string
     */
    static function database($database = null)
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->database($database);
    }

    /**
     * Get/Set Connection's persistence mode.
     *
     * @param string $persist The connection signifier
     *
     * @return string|true
     */
    static function persist($persist = null)
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return null;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->persist($persist);
    }

    /**
     * Get/Set Connection's lifetime.
     *
     * @param int $lifetime In seconds
     *
     * @return int
     */
    static function lifetime($lifetime = null)
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->lifetime($lifetime);
    }

    /**
     * Get/Set Connection's debug mode.
     *
     * @param bool $debug
     * @return bool
     */
    static function debug($debug = null)
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->debug($debug);
    }

    /**
     * Get/Set garbage collection function.
     *
     * @param array $expire_notify [Expired Session ref, Callback function]
     *
     * @return array|false
     */
    static function expireNotify(?array $expire_notify = null): mixed
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->expireNotify($expire_notify);
    }

    /**
     * Get/Set Sessions table name.
     *
     * @param string $table Session table name (defaults to 'sessions2')
     * @return string
     */
    static function table($table = null)
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->table($table);
    }

    /**
     * Get/Set table optimization mode.
     *
     * If true, with MySQL and PostgreSQL databases, the Sessions table will
     * be optimized when garbage collection is performed.
     *
     * @param bool $optimize
     * @return bool
     */
    static function optimize($optimize = null)
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->optimize($optimize);
    }

    /**
     * No longer used, kept for backwards-compatibility only.
     *
     * @param int $sync_seconds
     * @return int
     *
     * @deprecated
     */
    static function syncSeconds($sync_seconds = null)
    {
        return 0;
    }

    /**
     * Get/Set if CLOBs are available to store session data.
     */
    static function clob($clob = null)
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->clob($clob);
    }

    /**
     * No longer used, kept for backwards-compatibility only.
     *
     * @param string $data_field_name
     * @return string
     *
     * @deprecated
     */
    static function dataFieldName($data_field_name = null)
    {
        return '';
    }

    /**
     * Get/Set session data filter.
     *
     * @param array $filter
     * @return array
     */
    static function filter($filter = null)
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->filter($filter);
    }

    /**
     * Get/Set the encryption key if encrypted sessions are in use.
     *
     * @param string $encryption_key
     * @return string
     */
    static function encryptionKey($encryption_key = null)
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->encryptionKey($encryption_key);
    }

    /////////////////////
    // private methods
    /////////////////////

    /**
     * Returns the Session's Database Connection.
     *
     * @return ADOConnection|false
     */
    static function _conn($conn = null)
    {
        return isset($GLOBALS['ADODB_SESS_CONN']) ? $GLOBALS['ADODB_SESS_CONN'] : false;
    }

    /**
     * Initialize session handler.
     */
    static function _init()
    {
        $GLOBALS['ADODB_SESSION_OBJECT'] = $handler = new ADODBSessionHandler();
        session_set_save_handler($handler, true);
    }


    /**
     * Create the encryption key for crypted sessions.
     *
     * Crypt the used key, ADODB_Session::encryptionKey() as key and
     * session_id() as salt.
     */
    static function _sessionKey()
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->_sessionKey();
    }

    /**
     * Dump recordset.
     */
    static function _dumprs(&$rs)
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->_dumprs($rs);
    }

    /**
     * Check if Session Connection's DB type is MySQL.
     *
     * @return bool
     */
    protected static function isConnectionMysql()
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->isConnectionMysql();
    }

    /**
     * Returns a MySQL "CAST(... AS BINARY)" function for the given value.
     *
     * For other DB types, the value is returned as-is.
     *
     * @param string $value
     * @return string
     */
    protected static function castBinary(string $value): string
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->castBinary($value);
    }

    /**
     * Check if Session Connection's DB type is PostgreSQL.
     *
     * @return bool
     */
    protected static function isConnectionPostgres()
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->isConnectionPostgres();
    }

    /////////////////////
    // public methods
    /////////////////////

    /**
     * Establishes a connection to the database for session management.
     *
     * @param string $host
     * @param string $driver
     * @param string $user
     * @param string $password
     * @param string $database
     * @param array $options
     * @return void
     */
    static function config($driver, $host, $user, $password, $database = false, $options = false)
    {

        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->config(
            $driver,
            $host,
            $user,
            $password,
            $database,
            $options
        );
    }

    /**
     * Create the connection to the database. This does not match
     * the signature for a session::open method
     *
     * If $conn already exists, reuse that connection.
     *
     * @param string $save_path
     * @param string $session_name
     * @param bool $persist
     *
     * @return bool
     */
    static function open($save_path, $session_name, $persist = null)
    {

        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        if ($persist) {
            $GLOBALS['ADODB_SESSION_OBJECT']->persist($persist);
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->open(
            $save_path,
            $session_name
        );
    }

    /**
     * Close the connection
     */
    static function close()
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->close();
    }

    /**
     * Slurp in the session variables and return the serialized string.
     *
     * @param string $key
     * @return string
     */
    static function read($key)
    {
        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->read($key);
    }

    /**
     * Write the serialized data to a database.
     *
     * If the data has not been modified since the last read(), we do not write.
     *
     * @param string $key
     * @param string $oval
     *
     * @return bool
     */
    static function write($key, $oval)
    {
        global $ADODB_SESSION_READONLY;
        if (!empty($ADODB_SESSION_READONLY)) {
            return false;
        }

        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->write($key, $oval);
    }

    /**
     * Destroy session.
     *
     * @param string $key
     * @return bool
     */
    static function destroy($key)
    {



        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->destroy($key);
    }

    /**
     * Perform garbage collection.
     *
     * @param int $maxlifetime
     * @return bool
     */
    static function gc($maxlifetime)
    {

        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return false;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->gc($maxlifetime);
    }

    /**
     * Accepts connection parameters
     *
     * @param array|null $parameters The passed parameters
     *
     * @return mixed
     */
    static function parameters(?array $parameters = null): ?array
    {

        if (!isset($GLOBALS['ADODB_SESSION_OBJECT'])) {
            return null;
        }

        return $GLOBALS['ADODB_SESSION_OBJECT']->parameters($parameters);
    }
}


ADODB_Session::_init();
if (empty($ADODB_SESSION_READONLY)) {
    register_shutdown_function('session_write_close');
}
