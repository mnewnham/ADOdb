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

use Horde\Secret\SecretManager;
use Horde\Crypt\BlowFish;

if (!defined('HORDE_BASE')) {
    @define('HORDE_BASE', dirname(dirname(dirname(__FILE__))) . '/Horde');
}

if (!is_dir(HORDE_BASE)) {
	exit(sprintf('Directory not found: \'%s\'', HORDE_BASE));
}

//include_once HORDE_BASE . '/Secret.php';

class ADODB_Encrypt_Secret {

	public mixed $hordeObject = false;

	public function initializeHorde($key)
	{

		$this->hordeObject = SecretManager::create($key);
		
	}
    
	/**
	 * Writes session data
	 *
	 * @param string $data
	 * @param string $key
	 * 
	 * @return void
	 */
	function write($data, $key) {

		if (!$this->hordeObject) {
			$this->initializeHorde($key);
		}
		//return Horde_Secret::write($key, $data);
		return $this->hordeObject->write($key, $data);
	}

	/**
	 * Reads session data
	 *
	 * @param string $data
	 * @param string $key
	 * 
	 * @return void
	 */
	function read($data, $key) {
		
		if (!$this->hordeObject) {
			$this->initializeHorde($key);
		}
		
		return $this->hordeObject->read($key, $data);
	}

}

return 1;
