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

if (defined('ADODB_SESSION')) return 1;

define('ADODB_SESSION', dirname(__FILE__));
define('ADODB_SESSION2', ADODB_SESSION);

require_once ADODB_DIR.'/session/adodb-session-lib.inc.php';

/**compat
 * ADOdb Session v2 class.
 */
class ADODBSessionHandler implements SessionHandlerInterface {

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

	protected string $_driver = 'mysqli';
	protected string $_host = 'localhost';
	protected string $_user = 'root';
	protected string $_password = '';
	protected string $_database = '';
	protected string $_persist = '';
	protected string $_crc = '';
	protected int    $_lifetime = 0;
	protected bool   $_debug =  false;
	protected bool   $_optimize =  false;
	protected bool   $_clob =  false;
	protected array  $_expire_notify =  [];
	protected array  $_filter =  [];
	protected string $_table = 'sessions2';	
	protected string $_encryption_key = 'CRYPTED ADODB SESSIONS ROCK!';
	/**
	 * Get/Set Database driver.
	 *
	 * @param string $driver
	 * @return string
	 */
	public function driver(?string $driver = null) : string
	{
		
		if (!is_null($driver)) {
			$this->_driver = trim($driver);
			$set = true;
		}

		return $this->_driver;
	}

	/**
	 * Get/Set Database hostname.
	 *
	 * @param string $host
	 * 
	 * @return string
	 */
	public function host(?string $host = null) : string {
	
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
	public function user(?string $user = null) : string
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
	public function password(?string $password = null) : string
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
	public function database(?string $database = null) : string
	{
		
		if (!is_null($database)) {
			$this->_database = trim($database);
		} 
		return $this->_database;
	}

	/**
	 * Get/Set Connection's persistence mode.
	 *
	 * @param $persist
	 * 
	 * @return string|true
	 */
	public function persist(?string $persist = null) : bool
	{
		
		if (!is_null($persist)) {
			$this->_persist = trim($persist);
		}

		return $this->_persist;
	}

	/**
	 * Get/Set Connection's lifetime.
	 *
	 * @param int $lifetime
	 * 
	 * @return int
	 */
	public function lifetime(?int $lifetime = null) : int
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
	 * @return bool
	 */
	public function debug(?bool $debug = null) : bool
	{
		if (!is_null($debug)) {
			$this->_debug = (bool) $debug;
	
		} 

		return $this->_debug;
	}

	/**
	 * Get/Set garbage collection function.
	 *
	 * @param array $expire_notify [Expired Session ref, Callback function]
	 *
	 * @return array|false
	 */
	public function expireNotify(?array $expire_notify = null) : array
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
	 * @return string
	 */
	public function table(?string $table = null) : string
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
	public function optimize(?bool $optimize = null) : bool
	{
		if (!is_null($optimize)) {
			$this->_optimize = (bool) $optimize;
		} 

		return $this->_optimize;
	}

	
	/**
	 * Get/Set if CLOBs are available to store session data.
	 */
	public function clob(?bool $clob = null) : bool {
	
		if (!is_null($clob)) {
			$this->_clob = $clob;

		} 

		return $this->_clob;
	}

	/**
	 * Get/Set session data filter.
	 *
	 * @param array $filter
	 * @return array
	 */
	public function filter(?array $filter = null) : array {
		
		if (!is_null($filter)) {
			$this->_filter = $filter;
		}

		return $this->_filter;
	}

	/**
	 * Get/Set the encryption key if encrypted sessions are in use.
	 *
	 * @param string $encryption_key
	 * @return string
	 */
	public function encryptionKey(?string $encryption_key = null) : string {
		
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
	public function _conn(?object $conn=null) : mixed {
		return isset($GLOBALS['ADODB_SESS_CONN']) ? $GLOBALS['ADODB_SESS_CONN'] : false;
	}

	/**
	 * @param $crc
	 * @return false|mixed
	 */
	public function _crc(?string $crc = null) :string {
		
		if (!is_null($crc)) {
			$this->_crc = $crc;
		}

		return $this->_crc;
	}

	/**
	 * Initialize session handler.
	 */
	public function _init() : void {
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
	 */
	public function _sessionKey() : string {
		return crypt($this->encryptionKey(), session_id());
	}

	/**
	 * Dump recordset.
	 */
	public function _dumprs(object &$rs) : void {
		$conn	= $this->_conn();
		$debug	= $this->debug();

		if (!$conn) {
			return;
		}

		if (!$debug) {
			return;
		}

		if (!$rs) {
			echo "<br />\$rs is null or false<br />\n";
			return;
		}

		//echo "<br />\nAffected_Rows=",$conn->Affected_Rows(),"<br />\n";

		if (!is_object($rs)) {
			return;
		}
		$rs = $conn->_rs2rs($rs);

		require_once ADODB_SESSION.'/../tohtml.inc.php';
		rs2html($rs);
		$rs->MoveFirst();
	}

	/**
	 * Check if Session Connection's DB type is MySQL.
	 *
	 * @return bool
	 */
	protected function isConnectionMysql() : bool {
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
	protected function isConnectionPostgres() : bool {
		return $this->provider == 'postgres';
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
	public function config($driver, $host, $user, $password, $database=false,$options=false)
	{
		$this->driver($driver);
		$this->host($host);
		$this->user($user);
		$this->password($password);
		$this->database($database);

		if (strncmp($driver, 'oci8', 4) == 0) $options['lob'] = 'CLOB';

		if (isset($options['table'])) $this->table($options['table']);
		if (isset($options['lob'])) $this->clob($options['lob']);
		if (isset($options['debug'])) $this->debug($options['debug']);
	}

	/**
	 * Create the connection to the database.
	 *
	 * If $conn already exists, reuse that connection.
	 *
	 * @param string $save_path
	 * @param string $session_name
	 * @param bool $persist
	 *
	 * @return bool
	 */
	//function open($save_path, $session_name, $persist = null)
	public function open($savePath, $sessionName): bool {
	
		$conn = $this->_conn();

		if ($conn) {
			return true;
		}

		$database	= $this->database();
		$debug		= $this->debug();
		$driver		= $this->driver();
		$host		= $this->host();
		$password	= $this->password();
		$user		= $this->user();

		$ok = false;

		if (strpos($driver, 'pdo_') === 0){
			$conn = ADONewConnection('pdo');
			$driver = str_replace('pdo_', '', $driver);
			$dsn = $driver.':'.'hostname='.$host.';dbname='.$database.';';
			if ($this->persist()) {
				switch($this->persist()) {
				default:
				case 'P': $ok = $conn->PConnect($dsn,$user,$password); break;
				case 'C': $ok = $conn->Connect($dsn,$user,$password); break;
				case 'N': $ok = $conn->NConnect($dsn,$user,$password); break;
				}
			} else {
				$ok = $conn->Connect($dsn,$user,$password);
			}
		}else{
			$conn = ADONewConnection($driver);
			if ($debug) {
				$conn->debug = true;
				ADOConnection::outp( " driver=$driver user=$user db=$database ");
			}

			if (empty($conn->_connectionID)) { // not dsn
				if ($this->persist()) {
					switch($this->persist()) {
					default:
					case 'P': $ok = $conn->PConnect($host, $user, $password, $database); break;
					case 'C': $ok = $conn->Connect($host, $user, $password, $database); break;
					case 'N': $ok = $conn->NConnect($host, $user, $password, $database); break;
					}
				} else {
					$ok = $conn->Connect($host, $user, $password, $database);
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
		}
		else
			ADOConnection::outp('<p>Session: connection failed</p>', false);


		return $ok;
	}

	/**
	 * Close the connection
	 * 
	 * @return bool
	 */
	public function close(): bool {
/*
		$conn = $this->_conn();
		if ($conn) $conn->Close();
*/
		return true;
	}

	/**
	 * Slurp in the session variables and return the serialized string.
	 *
	 * @param string $key
	 * @return string
	 */
	public function read($key): string {
	
		$conn	= $this->_conn();
		$filter	= $this->filter();
		$table	= $this->table();

		if (!$conn) {
			return '';
		}

		global $ADODB_SESSION_SELECT_FIELDS;
		if (!isset($ADODB_SESSION_SELECT_FIELDS)) $ADODB_SESSION_SELECT_FIELDS = 'sessdata';
		$sql = "SELECT $ADODB_SESSION_SELECT_FIELDS FROM $table "
			. "WHERE sesskey = " . $this->castBinary($conn->Param(0))
			. " AND expiry >= " . $conn->sysTimeStamp;

		/* Lock code does not work as it needs to hold transaction within whole page, and we don't know if
		  developer has committed elsewhere... :(
		 */
		#if ($this->Lock())
		#	$rs = $conn->RowLock($table, "sesskey = " . $this->castBinary($qkey). " AND expiry >= " . time(), sessdata);
		#else
			$rs = $conn->Execute($sql, array($key));
		//$this->_dumprs($rs);
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
	 * @param string $key
	 * @param string $oval
	 *
	 * @return bool
	 */
	public function write($key, $oval): bool {
	
		global $ADODB_SESSION_READONLY;
		if (!empty($ADODB_SESSION_READONLY)) {
			return false;
		}

		$clob			= $this->clob();
		$conn			= $this->_conn();
		$crc			= $this->_crc();
		$debug			= $this->debug();
		$driver			= $this->driver();
		$expire_notify	= $this->expireNotify();
		$filter			= $this->filter();
		$lifetime		= $this->lifetime();
		$table			= $this->table();

		if (!$conn) {
			return false;
		}
		if ($debug) $conn->debug = 1;
		$sysTimeStamp = $conn->sysTimeStamp;

		$expiry = $conn->OffsetDate($lifetime/(24*3600),$sysTimeStamp);
		$expireref = $expire_notify ? $GLOBALS[$expire_notify[0]] ?? '' : '';

		// crc32 optimization since adodb 2.1
		// now we only update expiry date, thx to sebastian thom in adodb 2.32
		if ($crc !== '00' && $crc !== false && $crc == (strlen($oval) . crc32($oval))) {
			if ($debug) {
				echo '<p>Session: Only updating date - crc32 not changed</p>';
			}

			$sql = "UPDATE $table SET expiry = $expiry, expireref=" . $conn->Param('0')
				. ", modified = $sysTimeStamp WHERE sesskey = " . $this->castBinary($conn->Param('1'))
				. " AND expiry >= $sysTimeStamp";
			$rs = $conn->execute($sql,array($expireref, $key));
			return true;
		}
		$val = rawurlencode($oval);
		foreach ($filter as $f) {
			if (is_object($f)) {
				$val = $f->write($val, $this->_sessionKey());
			}
		}

		if (!$clob) {
			// no lobs, simply use replace()
			$rs = $conn->execute(
				"SELECT COUNT(*) AS cnt FROM $table WHERE sesskey = " . $this->castBinary($conn->Param(0)),
				array($key)
			);
			if ($rs) $rs->Close();

			if ($rs && reset($rs->fields) > 0) {
				$sql = "UPDATE $table SET expiry=$expiry, sessdata=".$conn->Param(0).", expireref= ".$conn->Param(1).",modified=$sysTimeStamp WHERE sesskey = ".$conn->Param(2);

			} else {
				$sql = "INSERT INTO $table (expiry, sessdata, expireref, sesskey, created, modified)
					VALUES ($expiry,".$conn->Param('0').", ". $conn->Param('1').", ".$conn->Param('2').", $sysTimeStamp, $sysTimeStamp)";
			}

			$rs = $conn->Execute($sql,array($val,$expireref,$key));

		} else {
			// what value shall we insert/update for lob row?
			if (strncmp($driver, 'oci8', 4) == 0) $lob_value = sprintf('empty_%s()', strtolower($clob));
			else $lob_value = 'null';

			$conn->StartTrans();

			$rs = $conn->execute(
				"SELECT COUNT(*) AS cnt FROM $table WHERE sesskey = " . $this->castBinary($conn->Param(0)),
				array($key)
			);

			if ($rs && reset($rs->fields) > 0) {
				$sql = "UPDATE $table SET expiry=$expiry, sessdata=$lob_value, expireref= ".$conn->Param(0).",modified=$sysTimeStamp WHERE sesskey = ".$conn->Param('1');

			} else {
				$sql = "INSERT INTO $table (expiry, sessdata, expireref, sesskey, created, modified)
					VALUES ($expiry,$lob_value, ". $conn->Param('0').", ".$conn->Param('1').", $sysTimeStamp, $sysTimeStamp)";
			}

			$conn->Execute($sql,array($expireref,$key));

			$qkey = $conn->qstr($key);
			$conn->UpdateBlob($table, 'sessdata', $val, " sesskey=$qkey", strtoupper($clob));
			if ($debug) echo "<hr>",htmlspecialchars($oval), "<hr>";
			$rs = @$conn->CompleteTrans();
		}

		if (!$rs) {
			ADOConnection::outp('<p>Session Replace: ' . $conn->ErrorMsg() . '</p>', false);
			return false;
		}  else {
			// bug in access driver (could be odbc?) means that info is not committed
			// properly unless select statement executed in Win2000
			if ($conn->databaseType == 'access') {
				$sql = "SELECT sesskey FROM $table WHERE sesskey = " . $this->castBinary($qkey);
				$rs = $conn->Execute($sql);
				$this->_dumprs($rs);
				if ($rs) {
					$rs->Close();
				}
			}
		}/*
		if ($this->Lock()) {
			$conn->CommitTrans();
		}*/
		return $rs ? true : false;
	}

	/**
	 * Destroy session.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function destroy($key) : bool {
		$conn			= $this->_conn();
		$table			= $this->table();
		$expire_notify	= $this->expireNotify();

		if (!$conn) {
			return false;
		}
		$debug			= $this->debug();
		if ($debug) $conn->debug = 1;

		$qkey = $conn->quote($key);

		if ($expire_notify) {
			$fn = $expire_notify[1];
			$savem = $conn->SetFetchMode(ADODB_FETCH_NUM);
			$sql = "SELECT expireref, sesskey FROM $table WHERE sesskey = " . $this->castBinary($qkey);
			$rs = $conn->Execute($sql);
			$this->_dumprs($rs);
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

		$sql = "DELETE FROM $table WHERE sesskey = " . $this->castBinary($qkey);
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
	public function gc($maxlifetime): int|false {
	
		$conn			= $this->_conn();
		$debug			= $this->debug();
		$expire_notify	= $this->expireNotify();
		$optimize		= $this->optimize();
		$table			= $this->table();

		if (!$conn) {
			return false;
		}

		if ($debug) {
			$conn->debug = 1;
			$COMMITNUM = 2;
		} else {
			$COMMITNUM = 20;
		}

		$time = $conn->OffsetDate(-$maxlifetime/24/3600,$conn->sysTimeStamp);

		$fn = $expire_notify[1] ?? false;

		$savem = $conn->SetFetchMode(ADODB_FETCH_NUM);
		$sql = "SELECT expireref, sesskey FROM $table WHERE expiry < $time ORDER BY 2"; # add order by to prevent deadlock
		$rs = $conn->SelectLimit($sql,1000);
		if ($debug) $this->_dumprs($rs);
		$conn->SetFetchMode($savem);
		if ($rs) {
			$tr = $conn->hasTransactions;
			if ($tr) $conn->BeginTrans();
			$ccnt = 0;
			while (!$rs->EOF) {
				$ref = $rs->fields[0];
				$key = $rs->fields[1];
				if ($fn) $fn($ref, $key);
				$conn->execute(
					"DELETE FROM $table WHERE sesskey = " . $this->castBinary($conn->Param('0') ),
					array($key)
				);
				$rs->MoveNext();
				$ccnt += 1;
				if ($tr && $ccnt % $COMMITNUM == 0) {
					if ($debug) echo "Commit<br>\n";
					$conn->CommitTrans();
					$conn->BeginTrans();
				}
			}
			$rs->Close();

			if ($tr) $conn->CommitTrans();
		}


		// suggested by Cameron, "GaM3R" <gamr@outworld.cx>
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
}

require_once ADODB_DIR.'/session/adodb-session-compat.inc.php';
