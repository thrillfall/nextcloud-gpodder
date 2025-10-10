<?php

if (!defined('PHPUNIT_RUN')) {
	define('PHPUNIT_RUN', 1);
}

require_once __DIR__ . '/../../../lib/base.php';
require_once __DIR__ . '/../../../tests/autoload.php';
require_once __DIR__ . '/Helper/DatabaseTransaction.php';
require_once __DIR__ . '/Helper/Writer/TestWriter.php';

// Load the app using the modern approach
use OCP\App\IAppManager;
use OCP\Server;

Server::get(IAppManager::class)->loadApp('gpoddersync');

OC_Hook::clear();
