<?php
declare(strict_types=1);

namespace tests\Integration\Migration;

use OC\AllConfig;
use OC\Log;
use OC\Migration\SimpleOutput;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionEntity;
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
	private EpisodeActionRepository $episodeActionRepository;
	private IDBConnection $dbConnection;
	private IConfig $migrationConfig;


	public function setUp(): void {
		parent::setUp();
		$app = new App('gpoddersync');
		$this->container = $app->getContainer();
		$this->episodeActionWriter = $this->container->get(EpisodeActionWriter::class);
		$this->episodeActionRepository = $this->container->get(EpisodeActionRepository::class);
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
		$episodeActionEntity = new EpisodeActionEntity();
		$episodeActionEntity->setPodcast("https://podcast_01.url");
		$episodeActionEntity->setEpisode(uniqid("https://episode_01.url"));
		$episodeActionEntity->setAction("PLAY");
		$episodeActionEntity->setPosition(5);
		$episodeActionEntity->setStarted(0);
		$episodeActionEntity->setTotal(123);
		$episodeActionEntity->setTimestamp("Mon Aug 23 01:58:56 GMT+02:00 2021");
		$episodeActionEntity->setUserId(self::ADMIN);
		$guid = uniqid("self::TEST_GUID_1234");
		$episodeActionEntity->setGuid($guid);
		$this->episodeActionWriter->save($episodeActionEntity);

		$episodeActionEntityBeforeConversion = $this->episodeActionRepository->findByEpisodeIdentifier($guid, self::ADMIN);
		$this->assertEquals(
			0,
			$episodeActionEntityBeforeConversion->getTimestampEpoch()
		);

		$timestampMigration = new TimestampMigration($this->dbConnection, $this->migrationConfig);
		$timestampMigration->run(new SimpleOutput(new Log(new TestWriter()), "gpoddersync"));

		$episodeActionEntityAfterConversion = $this->episodeActionRepository->findByEpisodeIdentifier($guid, self::ADMIN);
		$this->assertSame(
			(int)(new \DateTime($episodeActionEntity->getTimestamp()))->format("U"),
			$episodeActionEntityAfterConversion->getTimestampEpoch()
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
