<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Migration;

use Closure;
use Doctrine\DBAL\Types\Types;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;

class Version0003Date20210524131313 extends \OCP\Migration\SimpleMigrationStep {
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		$schema->dropTable('gpoddersync_subscriptions');
		$schema->dropTable('gpoddersync_episode_action');
		return $schema;
	}
}
