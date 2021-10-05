<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Migration;

use Closure;
use Doctrine\DBAL\Types\Types;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version0005Date20211004110900 extends SimpleMigrationStep {
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		$table = $schema->getTable('gpodder_episode_action');
		$table->changeColumn('timestamp', ['notnull' => false]);
		$table->addColumn('timestamp_epoch', Types::INTEGER, [
			'notnull' => false,
			'default' => 0,
			'unsigned' => true,
		]);

		return $schema;
	}
}
