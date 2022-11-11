<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;

class Version0007Date202211111100 extends \OCP\Migration\SimpleMigrationStep {
        public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
                /** @var ISchemaWrapper $schema */
                $schema = $schemaClosure();

                $table = $schema->getTable('gpodder_subscriptions');
                $table->dropIndex('subscriptions_url_user');
                $table->addUniqueIndex(['url', "user_id"], 'subscriptions_url_user', [ 
                        'lengths' => [ 500, 200 ]
                ]);

                return $schema;
        }
}