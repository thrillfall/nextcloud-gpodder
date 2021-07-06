<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Migration;

use Closure;
use Doctrine\DBAL\Types\Types;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;

class Version0002Date20210524131313 extends \OCP\Migration\SimpleMigrationStep {
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('gpodder_subscriptions')) {
			$table = $schema->createTable('gpodder_subscriptions');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('url', Types::STRING, [
				'notnull' => true,
				'length' => 500
			]);

			$table->addColumn('subscribed', Types::BOOLEAN, [
				'notnull' => true,
			]);

			$table->addColumn('updated', Types::DATETIME_MUTABLE, [
				'notnull' => true,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 200,
			]);

			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['url', "user_id"], 'subscriptions_url_user');

		}
		return $schema;
	}
}
