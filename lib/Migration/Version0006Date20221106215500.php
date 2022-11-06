<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Migration;

use Closure;
use Doctrine\DBAL\Types\Types;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;

class Version0006Date20221106215500 extends \OCP\Migration\SimpleMigrationStep {
        public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
                /** @var ISchemaWrapper $schema */
                $schema = $schemaClosure();

                $table = $schema->getTable('gpodder_subscriptions');
                $table->changeColumn('url', ['length' => 1000]);

                return $schema;
        }
}