<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Keep one episode action per episode and action type, so that a download, delete or new action
 * no longer overwrites the play position of the same episode.
 */
class Version0008Date20261025120000 extends SimpleMigrationStep {
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		$table = $schema->getTable('gpodder_episode_action');
		if ($table->hasIndex('gpodder_episode_user_id')) {
			$table->dropIndex('gpodder_episode_user_id');
		}
		if ($table->hasIndex('gpodder_guid_user_id')) {
			$table->dropIndex('gpodder_guid_user_id');
		}
		$table->addUniqueIndex(['episode', 'action', 'user_id'], 'gpodder_episode_action_user');
		$table->addUniqueIndex(['guid', 'action', 'user_id'], 'gpodder_guid_action_user');

		return $schema;
	}
}
