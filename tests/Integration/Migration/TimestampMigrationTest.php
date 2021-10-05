<?php
declare(strict_types=1);

namespace tests\Integration\Migration;

use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use OC\AllConfig;
use OC\Log;
use OC\Migration\SimpleOutput;
use OC\OCS\Exception;
use OC\OCS\Result;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionEntity;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionMapper;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionWriter;
use OCA\GPodderSync\Migration\TimestampMigration;
use OCP\AppFramework\App;
use OCP\IConfig;
use OCP\IDBConnection;
use test\TestCase;
use tests\Helper\DatabaseTransaction;
use tests\Helper\Writer\TestWriter;

/**
 * @group DB
 */
class TimestampMigrationTest extends TestCase
{

	use DatabaseTransaction;

	const TEST_GUID_1234 = "test_uuid_1234";
	const ADMIN = "admin";
	private EpisodeActionWriter $episodeActionWriter;
	private EpisodeActionMapper $episodeActionMapper;
	private IDBConnection $dbConnection;
	private IConfig $migrationConfig;


	public function setUp(): void {
		parent::setUp();
		$app = new App('gpoddersync');
		$this->container = $app->getContainer();
		$this->episodeActionWriter = $this->container->get(EpisodeActionWriter::class);
		$this->episodeActionMapper = $this->container->get(EpisodeActionMapper::class);
		$this->dbConnection = $this->container->get(IDBConnection::class);
		$this->migrationConfig = $this->container->get(AllConfig::class );
	}

	/**
	 * @before
	 */
	public function before()
	{
		$this->startTransaction();
	}

	public function testTimestampConversionRepairStep()
	{
		if (!$this->dbConnection->getDatabasePlatform() instanceof PostgreSQL100Platform) {
			self::markTestSkipped("This test only works on postgres");
		}

		$episodeActionEntity = new EpisodeActionEntity();
		$episodeActionEntity->setPodcast("https://podcast_01.url");
		$episodeActionEntity->setEpisode(uniqid("https://episode_01.url"));
		$episodeActionEntity->setAction("PLAY");
		$episodeActionEntity->setPosition(5);
		$episodeActionEntity->setStarted(0);
		$episodeActionEntity->setTotal(123);
		$episodeActionEntity->setTimestamp("Sun Aug 22 23:58:56 GMT+00:00 2021");
		$episodeActionEntity->setUserId(self::ADMIN);
		$guid = uniqid("self::TEST_GUID_1234");
		$episodeActionEntity->setGuid($guid);
		$this->episodeActionWriter->save($episodeActionEntity);

		$episodeActionBeforeConversion = $this->episodeActionMapper->findByEpisodeIdentifier($guid, self::ADMIN);
		$this->assertEquals(
			0,
			$episodeActionBeforeConversion->getTimestampEpoch()
		);

		$timestampMigration = new TimestampMigration($this->dbConnection, $this->migrationConfig);
		$timestampMigration->run(new SimpleOutput(new Log(new TestWriter()), "gpoddersync"));

		$episodeActionAfterConversion = $this->episodeActionMapper->findByEpisodeIdentifier($guid, self::ADMIN);
		$this->assertSame(
			1629676736,
			$episodeActionAfterConversion->getTimestampEpoch()
		);
	}

	/**
	 * @after
	 */
	public function after()
	{
		$this->rollbackTransation();
	}
}
