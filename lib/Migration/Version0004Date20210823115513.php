<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Migration;

use Closure;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Types;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;

class Version0004Date20210823115513 extends \OCP\Migration\SimpleMigrationStep {
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		$table = $schema->getTable('gpodder_episode_action');
		$table->addColumn('guid', Types::STRING, [
			'length' => 500,
			'notnull' => false
		]);

		$table->addUniqueIndex(['guid', 'user_id'], 'gpodder_guid_user_id');

		return $schema;
	}
}
