<?php

if (!defined('PHPUNIT_RUN')) {
	define('PHPUNIT_RUN', 1);
}

require_once __DIR__ . '/../../../lib/base.php';
require_once __DIR__ . '/Helper/DatabaseTransaction.php';
require_once __DIR__ . '/Helper/Writer/TestWriter.php';
// Fix for "Autoload path not allowed: .../tests/lib/testcase.php"
OC::$loader->addValidRoot(OC::$SERVERROOT . '/tests');

// Fix for "Autoload path not allowed: .../gpoddersync/tests/testcase.php"
OC_App::loadApp('nextcloud-gpodder');

OC_Hook::clear();
