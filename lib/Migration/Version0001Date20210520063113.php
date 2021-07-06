<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Migration;

use Closure;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;

class Version0001Date20210520063113 extends \OCP\Migration\SimpleMigrationStep {
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('gpodder_episode_action')) {
			$table = $schema->createTable('gpodder_episode_action');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('podcast', 'string', [
				'notnull' => true,
				'length' => 500
			]);
			$table->addColumn('episode', 'string', [
				'notnull' => true,
				'length' => 500,
				'unique' => true,
			]);
			$table->addColumn('action', 'string', [
				'notnull' => true,
				'length' => 5
			]);
			$table->addColumn('position', 'integer', [
				'notnull' => true,
			]);

			$table->addColumn('started', Types::INTEGER, [
				'notnull' => true,
			]);
			$table->addColumn('total', Types::INTEGER, [
				'notnull' => true,
			]);
			$table->addColumn('timestamp', Types::DATETIME_MUTABLE, [
				'notnull' => true,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 200,
			]);

			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['episode', 'user_id'], 'gpodder_episode_user_id');
		}
		return $schema;
	}
}
