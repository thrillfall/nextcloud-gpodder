<?php

namespace tests\Helper;

use OC;
use OCP\IDBConnection;

trait DatabaseTransaction {

	public function startTransaction() {
		/* @var $db IDBConnection */
		$db = OC::$server->get(IDBConnection::class);

		$db->beginTransaction();
	}

	public function rollbackTransation() {
		/* @var $db IDBConnection */
		$db = OC::$server->get(IDBConnection::class);

		$db->rollBack();
	}

}
