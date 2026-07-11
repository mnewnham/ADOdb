<?php

/**
 * ADOdb Session Management
 *
 * This file is part of ADOdb, a Database Abstraction Layer library for PHP.
 *
 * @package ADOdb
 * @link https://adodb.org Project's web site and documentation
 * @link https://github.com/ADOdb/ADOdb Source code and issue tracker
 *
 * The ADOdb Library is dual-licensed, released under both the BSD 3-Clause
 * and the GNU Lesser General Public Licence (LGPL) v2.1 or, at your option,
 * any later version. This means you can use it in proprietary products.
 * See the LICENSE.md file distributed with this source code for details.
 * @license BSD-3-Clause
 * @license LGPL-2.1-or-later
 *
 * @copyright 2000-2013 John Lim
 * @copyright 2014 Damien Regad, Mark Newnham and the ADOdb community
 */

if (!defined('_ADODB_LAYER')) {
    require realpath(dirname(__FILE__) . '/../adodb.inc.php');
}

if (defined('ADODB_SESSION')) {
    return 1;
}

define('ADODB_SESSION', dirname(__FILE__));
define('ADODB_SESSION2', ADODB_SESSION);

require_once ADODB_DIR . '/session/adodb-session-lib.inc.php';

/**compat
 * ADOdb Session v2 class.
 */
class ADODBSessionHandler implements SessionHandlerInterface
{
    /**
     * Session Connection's Database provider.
     *
     * Populated when opening the database connection.
     * @see $this->open()}.
     *
     * @var string
     */
    protected ?string $provider;


    /////////////////////
    // getter/setter methods
    /////////////////////

    /**
     * The ADOdb Driver
     *
     * @var string
     */
    protected string $_driver = 'mysqli';

    /**
     * The connection host or DSN
     *
     * @var string
     */
    protected string $_host = 'localhost';

    /**
     * The connection user
     *
     * @var string
     */
    protected string $_user = '';

    /**
     * The connection password
     *
     * @var string
     */
    protected string $_password = '';

    /**
     * The connection database name
     *
     * @var string
     */
    protected string $_database = '';

    /**
     * Type of connection
     * @example EMPTY or 'C' - standard, P - persistant, N - New
     *
     * @var string
     */
    protected string $_persist = '';

    /**
     * Stores the current CRC of the session object
     *
     * @var string
     */
    protected string $_crc = '';

    /**
     * The session lifetime
     *
     * @var integer
     */
    protected int $_lifetime = 0;

    /**
     * Internal session object debugger
     *
     * @var boolean
     */
    protected bool $_debug =  false;

    /**
     * Whether a driver-specific optimizer should be called`
     *
     * @var boolean
     */
    protected bool $_optimize =  false;

    /**
     * What BLOB/CLOB type is storing the session data
     *
     * @var string|null
     */
    protected ?string $_clob = null;

    /**
     * An identifier for the option destructior callback
     * The function name is hald in the second array element
     *
     * @example [ '' , 'someCallBackFunction' ]
     *
     * @var array
     */
    protected array $_expire_notify =  [];

    /**
     * An array of pre-processors
     * @example bz2, crypt
     *
     * @var array
     */
    protected array $_filter = [];

    /**
     * Any provided connection parameters
     *
     * @var array
     */
    protected array $_parameters = [];

    /**
     * The name of the table to store session data
     *
     * @var string
     */
    protected string $_table = 'sessions2';

    protected string $_encryption_key = 'CRYPTED ADODB SESSIONS ROCK!';

    /**
     * The fields to select from $_table
     *
     * @var string
     */
    protected string $selectFields = 'sessdata';

    /**
     * Can the session only read data
     *
     * @var boolean
     */
    protected bool $sessionIsReadOnly = false;

    /**
     * Activates the connection
     *
     */
    public function __construct()
    {

        global $ADODB_SESSION_SELECT_FIELDS;

        if (!$ADODB_SESSION_SELECT_FIELDS) {
            $ADODB_SESSION_SELECT_FIELDS = $this->selectFields;
        } else {
            $this->selectFields = $ADODB_SESSION_SELECT_FIELDS;
        }

        global $ADODB_SESSION_READONLY;

        if ($ADODB_SESSION_READONLY && is_bool($ADODB_SESSION_READONLY)) {
            $this->sessionIsReadOnly = $ADODB_SESSION_READONLY;
        }
    }

    /**
     * Get/Set Database driver.
     *
     * @param string $driver The ADOdb driver identifier
     *
     * @example mysqli
     *
     * @return string
     */
    public function driver(?string $driver = null): string
    {

        if (!is_null($driver)) {
            $this->_driver = trim($driver);
            $set = true;
        }

        return $this->_driver;
    }

    /**
     * Get/Set Database hostname
     * If the connection is via DSN, that is stored in this value
     *
     * @param string $host
     *
     * @return string
     */
    public function host(?string $host = null): string
    {

        if (!is_null($host)) {
            $this->_host = trim($host);
        }

        return $this->_host;
    }

    /**
     * Get/Set Database connection user.
     *
     * @param string $user
     *
     * @return string
     */
    public function user(?string $user = null): string
    {

        if (!is_null($user)) {
            $this->_user = trim($user);
        }
        return $this->_user;
    }

    /**
     * Get/Set Database connection password.
     *
     * @param null $password
     * @return string
     */
    public function password(?string $password = null): string
    {
        if (!is_null($password)) {
            $this->_password = $password;
        }

        return $this->_password;
    }

    /**
     * Get/Set Database name.
     *
     * @param null $database
     *
     * @return string
     */
    public function database(?string $database = null): string
    {

        if (!is_null($database)) {
            $this->_database = trim($database);
        }
        return $this->_database;
    }

    /**
     * Get/Set Connection's persistence mode.
     *
     * @param string $persist A connection signifier
     *
     * @return string|null
     */
    public function persist(?string $persist = null): mixed
    {

        if (!is_null($persist)) {
            if (!in_array(strtoupper($persist), ['C', 'P', 'N' ])) {
                if ($this->debug()) {
                    ADOConnection::outp('Persist should be one of C,P,N');
                }
                return $this->_persist;
            }
            $this->_persist = strtoupper($persist[0]);
        }

        return $this->_persist;
    }

    /**
     * Get/Set Connection's lifetime.
     *
     * @param int $lifetime The lifetime in seconds
     *
     * @return int
     */
    public function lifetime(?int $lifetime = null): int
    {

        if (!is_null($lifetime)) {
            $this->_lifetime = (int) $lifetime;
        }

        if (!$this->_lifetime) {
            $_lifetime = ini_get('session.gc_maxlifetime');
            if ($this->_lifetime <= 1) {
                $this->_lifetime = 1440;
            }
        }

        return $this->_lifetime;
    }

    /**
     * Get/Set Connection's debug mode.
     *
     * @param bool $debug
     *
     * @return bool
     */
    public function debug(?bool $debug = null): bool
    {
        if (!is_null($debug)) {
            $this->_debug = (bool) $debug;
        }

        return $this->_debug;
    }

    /**
     * Get/Set garbage collection trigger function.
     *
     * @param mixed $expire_notify [Expired Session ref, Callback function]
     *
     * @return array|false
     */
    public function expireNotify(?array $expire_notify = null): mixed
    {
        if (!is_null($expire_notify)) {
            $this->_expire_notify = $expire_notify;
        }



        return $this->_expire_notify;
    }

    /**
     * Get/Set Sessions table name.
     *
     * @param string $table Session table name (defaults to 'sessions2')
     *
     * @return string
     */
    public function table(?string $table = null): string
    {

        if (!is_null($table)) {
            $this->_table = trim($table);
        }
        return $this->_table;
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
    public function optimize(?bool $optimize = null): bool
    {
        if (!is_null($optimize)) {
            $this->_optimize = (bool) $optimize;
        }

        return $this->_optimize;
    }


    /**
     * Get/Set if CLOB handling is require to store session data.
     * This sets the type of field if not null e.g. CLOB
     * Generally, this is only needed if the compression or encryption
     * is used but also depends on the DBMS
     *
     * @param string|null $clob The indictor
     *
     * @return string|null
     */
    public function clob(?string $clob = null): ?string
    {

        if (!is_null($clob)) {
            if (!in_array(strtoupper($clob), ['BLOB', 'CLOB'])) {
                if ($this->debug()) {
                    ADOConnection::outp('$clob setting must be one of either [CLOB, BLOB]');
                }
                return null;
            }
            $this->_clob = $clob;
        }

        return $this->_clob;
    }

    /**
     * Get/Set session data filters into the _filter list.
     * Must be a valid compression or encryption filter
     *
     * @param ?object $filter A filtering Object
     *
     * @return array A list of filters
     */
    public function filter(?object $filter = null): array
    {

        if (!is_null($filter)) {
            $this->_filter[] = $filter;
        }

        return $this->_filter;
    }

    /**
     * Get/Set the encryption key if encrypted sessions are in use.
     *
     * @param string $encryption_key
     *
     * @return string
     */
    public function encryptionKey(?string $encryption_key = null): string
    {

        if (!is_null($encryption_key)) {
            $this->_encryption_key = $encryption_key;
        }

        return $this->_encryption_key;
    }

    /////////////////////
    // private methods
    /////////////////////

    /**
     * Returns the Session's Database Connection.
     *
     * @return ADOConnection|false
     */
    public function _conn(?object $conn = null): mixed
    {
        return isset($GLOBALS['ADODB_SESS_CONN']) ? $GLOBALS['ADODB_SESS_CONN'] : false;
    }

    /**
     * Calculates the current CRC of the session for later comparison
     *
     * @param string $crc The new CRC
     *
     * @return mixed
     */
    protected function _crc(?string $crc = null): string
    {

        if (!is_null($crc)) {
            $this->_crc = $crc;
        }

        return $this->_crc;
    }

    /**
     * Initialize session handler
     *
     * @return void
     */
    public function _init(): void
    {
        session_set_save_handler(
            array('ADODB_Session', 'open'),
            array('ADODB_Session', 'close'),
            array('ADODB_Session', 'read'),
            array('ADODB_Session', 'write'),
            array('ADODB_Session', 'destroy'),
            array('ADODB_Session', 'gc')
        );
    }


    /**
     * Create the encryption key for crypted sessions.
     *
     * Crypt the used key, $this->encryptionKey() as key and
     * session_id() as salt.
     *
     * @return string
     */
    protected function _sessionKey(): string
    {
        return crypt($this->encryptionKey(), session_id());
    }

    /**
     * Dump recordset.
     *
     * @param object $rs The recordset to dump
     *
     * @return void
     */
    protected function _dumprs(object &$rs): void
    {
        $conn   = $this->_conn();
        $debug  = $this->debug();

        if (!$conn) {
            return;
        }

        if (!$debug) {
            return;
        }

        if (!$rs) {
            ADOConnection::outp('$rs is null or false');
            return;
        }


        if (!is_object($rs)) {
            return;
        }
        $rs = $conn->_rs2rs($rs);

        $rsPrint = print_r($rs, true);

        ADOConnection::outp($rsPrint);
        //require_once ADODB_SESSION.'/../tohtml.inc.php';
        //rs2html($rs);
        $rs->MoveFirst();
    }

    /**
     * Check if Session Connection's DB type is MySQL.
     *
     * @return bool
     */
    protected function isConnectionMysql(): bool
    {
        return $this->provider == 'mysql';
    }

    /**
     * Returns a MySQL "CAST(... AS BINARY)" function for the given value.
     *
     * For other DB types, the value is returned as-is.
     *
     * @param string $value
     * @return string
     */
    protected function castBinary(string $value): string
    {
        if ($this->isConnectionMysql()) {
            return "CAST($value AS BINARY)";
        }
        return $value;
    }

    /**
     * Check if Session Connection's DB type is PostgreSQL.
     *
     * @return bool
     */
    protected function isConnectionPostgres(): bool
    {
        return $this->provider == 'postgres';
    }

    /**
     * Establishes a connection to the database for session management.
     *
     * @param string  $host     The database host
     * @param string  $driver   The database driver
     * @param string  $user     The user code
     * @param string  $password The password
     * @param ?string $database Database connection
     * @param ?array $options  Any additional options
     *
     * @return void
     */
    public function config(
        string $driver,
        ?string $host,
        ?string $user,
        ?string $password,
        ?string $database = null,
        ?array $options = null
    ): void {

        $this->driver($driver);
        $this->host($host);
        $this->user($user);
        $this->password($password);
        $this->database($database);

        if (strncmp($driver, 'oci8', 4) == 0) {
            //$options['lob'] = 'CLOB';
        }

        if (isset($options['table'])) {
            $this->table($options['table']);
        }

        if (isset($options['lob'])) {
            $this->clob($options['lob']);
        }

        if (isset($options['debug'])) {
            $this->debug($options['debug']);
        }

        if (isset($options['persist'])) {
            $this->persist($options['persist']);
        }

        if (isset($options['parameters'])) {
            $this->parameters($options['parameters']);
        }
    }

    /**
     * Create the connection to the database.
     *
     * If $conn already exists, reuse that connection.
     *
     * @param string $savePath    Not user
     * @param string $sessionName Not used
     *
     * @return bool
     */
    public function open(string $savePath, string $sessionName): bool
    {

        $conn = $this->_conn();

        if ($conn) {
            return true;
        }

        $database   = $this->database();
        $debug      = $this->debug();
        $driver     = $this->driver();
        $host       = $this->host();
        $password   = $this->password();
        $user       = $this->user();

        $ok = false;

        if (strpos($driver, 'pdo_') === 0 || $driver == 'sqlite3' || $driver == 'db2') {
            if ($driver == 'sqlite3') {
                $conn = NewADOConnection('sqlite3');
                $dsn = $host;
            } elseif ($driver == 'db2') {
                $conn = NewADOConnection('db2');
                $dsn = $host;
            } elseif (strpos('host=', $host) !== false) {
                $conn = ADONewConnection('pdo');
                $driver = str_replace('pdo_', '', $driver);
                $dsn = $host;
            } else {
                $conn = ADONewConnection('pdo');
                $driver = str_replace('pdo_', '', $driver);
                $dsn = $driver . ':' . 'hostname=' . $host . ';dbname=' . $database . ';';
            }
            $this->setSessionConnectionParameters($conn);

            if ($this->persist()) {
                switch ($this->persist()) {
                    /*
                    * Default behavior
                    */
                    default:
                    case 'P':
                        $ok = $conn->pConnect($dsn, $user, $password);
                        break;
                    case 'C':
                        $ok = $conn->connect($dsn, $user, $password);
                        break;
                    case 'N':
                        $ok = $conn->nConnect($dsn, $user, $password);
                        break;
                }
            } else {
                $ok = $conn->Connect($dsn, $user, $password, $database);
            }
        } else {
            $conn = ADONewConnection($driver);
            if ($debug) {
                $conn->debug = true;
                ADOConnection::outp("Session management: driver=$driver user=$user db=$database ");
            }

            $this->setSessionConnectionParameters($conn);

            if (empty($conn->_connectionID)) { // not dsn
                if ($this->persist()) {
                    switch ($this->persist()) {
                        default:
                        case 'P':
                            $ok = $conn->pConnect($host, $user, $password, $database);
                            break;
                        case 'C':
                            $ok = $conn->connect($host, $user, $password, $database);
                            break;
                        case 'N':
                            $ok = $conn->connect($host, $user, $password, $database);
                            break;
                    }
                } else {
                    /*
                    * Default
                    */
                    $ok = $conn->connect($host, $user, $password, $database);
                }
            } else {
                $ok = true; // $conn->_connectionID is set after call to ADONewConnection
            }
        }

        if ($ok) {
            $GLOBALS['ADODB_SESS_CONN'] = $conn;

            // Initialize Session data provider
            $this->provider = $conn->dataProvider;
            if ($this->provider == 'pdo') {
                $this->provider = $conn->dsnType == 'pgsql' ? 'postgres' : $conn->dsnType;
            }
        } else {
            ADOConnection::outp('Session: connection failed', false);
        }

        return $ok;
    }

    /**
     * Sets any connection parameters priot to connect
     *
     * @param ADOConnection $conn An ADOdb Connection object
     *
     * @return void
     */
    protected function setSessionConnectionParameters(object $conn): void
    {

        $parameters = $this->parameters();

        foreach ($parameters as $param => $value) {
            if (is_array($value)) {
                foreach ($value as $subParam => $subValue) {
                    $conn->setConnectionParameter(
                        $subParam,
                        $subValue
                    );
                }
                continue;
            }

            if (preg_match('/^[0-9]+$/', $param)) {
                /*
                * Any integer value is deliberately cast as
                * strings representing numbers are not excepted
                */
                $param = (int)$param;
            }
            if (preg_match('/^[0-9]+$/', $value)) {
                $value = (int)$value;
            }

            $conn->setConnectionParameter($param, $value);
        }
    }

    /**
     * Close the connection
     *
     * @return bool
     */
    public function close(): bool
    {

        return true;
    }

    /**
     * Slurp in the session variables and return the serialized string.
     *
     * @param string $key The session key
     *
     * @return string
     */
    public function read(string $key): string
    {

        $conn   = $this->_conn();
        $filter = $this->filter();
        $table  = $this->table();

        if (!$conn) {
            return '';
        }

        /*
        $conn->debug = true;

        $sql = "SELECT {$this->selectFields} FROM $table ";
        print_r($conn->getAll($sql));
        */
        $reset = $conn->param(false);
        $p1 = $conn->param('p1');

        $sql = "SELECT {$this->selectFields} FROM $table "
            . "WHERE sesskey = " . $this->castBinary($p1)
            . " AND expiry >= " . $conn->sysTimeStamp;
        $bind = [ 'p1' => $key ];


        /* Lock code does not work as it needs to hold transaction within whole page, and we don't know if
          developer has committed elsewhere... :(
         */
        #if ($this->Lock())
        #   $rs = $conn->RowLock($table, "sesskey = " . $this->castBinary($qkey). " AND expiry >= " . time(), sessdata);
        #else
        $rs = $conn->Execute($sql, $bind);

        if ($rs) {
            if ($rs->EOF) {
                $v = '';
            } else {
                $v = reset($rs->fields);

                $filter = array_reverse($filter);
                foreach ($filter as $f) {
                    if (is_object($f)) {
                        $v = $f->read($v, $this->_sessionKey());
                    }
                }
                $v = rawurldecode($v);
            }

            $rs->Close();

            $this->_crc(strlen($v) . crc32($v));
            return $v;
        }

        return '';
    }

    /**
     * Write the serialized data to a database.
     *
     * If the data has not been modified since the last read(), we do not write.
     *
     * @param string $key  The session key
     * @param string $oval The session string
     *
     * @return bool
     */
    public function write(string $key, string $oval): bool
    {


        if ($this->sessionIsReadOnly) {
            return false;
        }

        $clob           = $this->clob();
        $conn           = $this->_conn();
        $crc            = $this->_crc();
        $debug          = $this->debug();
        $driver         = $this->driver();
        $expire_notify  = $this->expireNotify();
        $filter         = $this->filter();
        $lifetime       = $this->lifetime();
        $table          = $this->table();

        if (!$conn) {
            return false;
        }
        if ($debug) {
            $conn->debug = 1;
        }

        //$conn->debug = $debug = true;

        $sysTimeStamp = $conn->sysTimeStamp;

        $expiry = $conn->OffsetDate(
            $lifetime / (24 * 3600),
            $sysTimeStamp
        );

        $expireref = $expire_notify ? $GLOBALS[$expire_notify[0]] ?? '' : '';

        if ($crc !== '00' && $crc !== false && $crc == (strlen($oval) . crc32($oval))) {
            if ($debug) {
                ADOConnection::outp('Session: Only updating date - crc32 not changed');
            }

            /*
            * Postgres reset
            */
            $reset = $conn->param(false);

            $p1 = $conn->param('p1');
            $p2 = $conn->param('p2');

            $bind = [
                'p1' => $expireref,
                'p2' => $key
            ];

            $sql = "UPDATE $table 
                       SET expiry = $expiry, expireref=$p1, modified = $sysTimeStamp 
                     WHERE sesskey = {$this->castBinary($p2)} 
                       AND expiry >= $sysTimeStamp";

            $rs = $conn->execute($sql, $bind);

            return true;
        }

        $val = rawurlencode($oval);
        foreach ($filter as $f) {
            if (is_object($f)) {
                $val = $f->write($val, $this->_sessionKey());
            }
        }

        if (!$clob) {
            /*
            * Postgres reset
            */
            $reset = $conn->param(false);

            $p1 = $conn->param('p1');
            $bind = [ 'p1' => $key ];

            // no lobs, simply use replace()
            $rs = $conn->execute(
                "SELECT COUNT(*) AS cnt 
                   FROM $table 
                  WHERE sesskey={$this->castBinary($p1)}",
                $bind
            );

            if ($rs) {
                $rs->Close();
            }

            /*
            * Postgres reset
            */
            $reset = $conn->param(false);

            $p1 = $conn->param('p1');
            $p2 = $conn->param('p2');
            $p3 = $conn->param('p3');

            $bind = [
                'p1' => $val,
                'p2' => $expireref,
                'p3' => $key
            ];

            if ($rs && reset($rs->fields) > 0) {
                $sql = "UPDATE $table 
                           SET expiry=$expiry, sessdata=$p1, expireref=$p2,modified=$sysTimeStamp 
                         WHERE sesskey=$p3";
            } else {
                $sql = "INSERT INTO $table (expiry, sessdata, expireref, sesskey, created, modified)
                             VALUES ($expiry,$p1, $p2, $p3, $sysTimeStamp, $sysTimeStamp)";
            }

            $conn->StartTrans();

            $rs = $conn->execute($sql, $bind);

            $conn->CompleteTrans();
        } else {
            // what value shall we insert/update for lob row?
            if (strncmp($driver, 'oci8', 4) == 0) {
                $lob_value = sprintf(
                    'empty_%s()',
                    strtolower($clob)
                );
            } else {
                $lob_value = 'null';
            }

            $conn->StartTrans();

            /*
            * Postgres reset
            */
            $reset = $conn->param(false);

            $p1 = $conn->param('p1');
            $bind = [ 'p1' => $key ];

            $rs = $conn->execute(
                "SELECT COUNT(*) AS cnt 
                   FROM $table 
                  WHERE sesskey = {$this->castBinary($p1)}",
                $bind
            );

            /*
            * Postgres reset
            */
            $reset = $conn->param(false);

            $p1 = $conn->param('p1');
            $p2 = $conn->param('p2');

            $bind = [
                'p1' => $expireref,
                'p2' => $key
            ];

            if ($rs && reset($rs->fields) > 0) {
                $sql = "UPDATE $table 
                           SET expiry=$expiry, sessdata=$lob_value, expireref=$p1,modified=$sysTimeStamp
                         WHERE sesskey=$p2";
            } else {
                $sql = "INSERT INTO $table (expiry, sessdata, expireref, sesskey, created, modified)
                        VALUES ($expiry,$lob_value, $p1, $p2, $sysTimeStamp, $sysTimeStamp)";
            }

            $conn->Execute($sql, $bind);

            $qkey = $conn->qstr($key);

            $conn->UpdateBlob($table, 'sessdata', $val, " sesskey=$qkey", strtoupper($clob));
            if ($debug) {
                ADOConnection::outp($oval);
            }

            $rs = $conn->CompleteTrans();
        }

        if (!$rs) {
            ADOConnection::outp('<p>Session Replace: ' . $conn->ErrorMsg() . '</p>', false);
            return false;
        } else {
            // bug in access driver (could be odbc?) means that info is not committed
            // properly unless select statement executed in Win2000
            if ($conn->databaseType == 'access') {
                $sql = "SELECT sesskey 
                          FROM $table 
                         WHERE sesskey={$this->castBinary($qkey)}";

                $rs = $conn->Execute($sql);

                $this->_dumprs($rs);
                if ($rs) {
                    $rs->Close();
                }
            }
        }
        /*
        if ($this->Lock()) {
            $conn->CommitTrans();
        }*/
        return $rs ? true : false;
    }

    /**
     * Destroy session.
     *
     * @param string $key The session identifier
     *
     * @return boolS
     */
    public function destroy(string $key): bool
    {
        $conn           = $this->_conn();
        $table          = $this->table();
        $expire_notify  = $this->expireNotify();

        if (!$conn) {
            return false;
        }
        $debug          = $this->debug();
        if ($debug) {
            $conn->debug = 1;
        }

        $reset = $conn->param(false);
        $p1 = $conn->param('p1');
        $bind = ['p1' => $key];


        if ($expire_notify) {
            /*
            * If there is a defined shutdown function, then action it
            */
            $fn = $expire_notify[1];
            $savem = $conn->SetFetchMode(ADODB_FETCH_NUM);
            $sql = "SELECT expireref, sesskey 
                      FROM $table 
                     WHERE sesskey={$this->castBinary($p1)}";

            $rs = $conn->Execute($sql, $bind);

            $conn->SetFetchMode($savem);
            if (!$rs) {
                return false;
            }
            if (!$rs->EOF) {
                $ref = $rs->fields[0];
                $key = $rs->fields[1];
                $fn($ref, $key);
            }
            $rs->Close();
        }

        $reset = $conn->param(false);
        $p1 = $conn->param('p1');
        $bind = ['p1' => $key];

        $sql = "DELETE FROM $table 
                 WHERE sesskey = {$this->castBinary($p1)}";

        $rs = $conn->Execute($sql);
        if ($rs) {
            $rs->Close();
        }

        return (bool)$rs;
    }

    /**
     * Perform garbage collection.
     *
     * @param int $maxlifetime
     * @return bool
     */
    public function gc(int $maxlifetime): int|false
    {

        $conn           = $this->_conn();
        $debug          = $this->debug();
        $expire_notify  = $this->expireNotify();
        $optimize       = $this->optimize();
        $table          = $this->table();

        if (!$conn) {
            return false;
        }

        if ($debug) {
            $conn->debug = 1;
            $COMMITNUM = 2;
        } else {
            $COMMITNUM = 20;
        }

        $time = $conn->OffsetDate(-$maxlifetime / 24 / 3600, $conn->sysTimeStamp);

        $fn = $expire_notify[1] ?? false;

        $savem = $conn->SetFetchMode(ADODB_FETCH_NUM);

        $sql = "SELECT expireref, sesskey 
                  FROM $table 
                 WHERE expiry < $time 
              ORDER BY 2"; # add order by to prevent deadlock

        $rs = $conn->SelectLimit($sql, 1000);
        if ($debug) {
            $this->_dumprs($rs);
        }

        $conn->SetFetchMode($savem);

        if ($rs) {
            $tr = $conn->hasTransactions;
            if ($tr) {
                $conn->BeginTrans();
            }
            $ccnt = 0;
            while (!$rs->EOF) {
                $ref = $rs->fields[0];
                $key = $rs->fields[1];
                if ($fn) {
                    $fn($ref, $key);
                }
                $reset = $conn->param(false);
                $p1 = $conn->param('p1');
                $bind = ['p1' => $key];

                $conn->execute(
                    "DELETE FROM $table 
                    WHERE sesskey = " . $this->castBinary($p1),
                    $bind
                );
                $rs->MoveNext();
                $ccnt += 1;
                if ($tr && $ccnt % $COMMITNUM == 0) {
                    if ($debug) {
                        ADOConnection::outp("Commit GC Cleanup");
                    }
                    $conn->CommitTrans();
                    $conn->BeginTrans();
                }
            }

            $rs->Close();

            if ($tr) {
                $conn->CommitTrans();
            }
        }

        if ($optimize) {
            if ($this->isConnectionMysql()) {
                $sql = "OPTIMIZE TABLE $table";
            } elseif ($this->isConnectionPostgres()) {
                $sql = "VACUUM $table";
            }
            if (!empty($sql)) {
                $conn->Execute($sql);
            }
        }

        return true;
    }

    /**
     * Accepts and stores connection parameters
     *
     * @param mixed|null $parameters An array of parameters
     *
     * @return array
     */
    public function parameters(mixed $parameters = null): array
    {

        if (!is_null($parameters)) {
            if (!is_array($parameters)) {
                $oldp = $parameters;
                $parameters = [];
                $base = array_filter(explode(';', $oldp));
                foreach ($base as $p) {
                    $e = explode('=', $p);
                    $parameters[$e[0]] = $e[1];
                }
            }

            $this->_parameters = $parameters;
        }

        return $this->_parameters;
    }
}

/**
 * Adds in the original static class which directs back here
 * for compatibility
 */
require_once ADODB_DIR . '/session/adodb-session-compat.inc.php';
