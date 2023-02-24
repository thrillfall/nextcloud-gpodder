<?php
declare(strict_types=1);

namespace tests\Integration\Migration;

use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use OC\AllConfig;
use OC\Migration\SimpleOutput;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionEntity;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionMapper;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionWriter;
use OCA\GPodderSync\Migration\TimestampMigration;
use OCP\AppFramework\App;
use OCP\IConfig;
use OCP\IDBConnection;
use test\TestCase;
use tests\Helper\DatabaseTransaction;

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
		$container = $app->getContainer();
		$this->episodeActionWriter = $container->get(EpisodeActionWriter::class);
		$this->episodeActionMapper = $container->get(EpisodeActionMapper::class);
		$this->dbConnection = $container->get(IDBConnection::class);
		$this->migrationConfig = $container->get(AllConfig::class );
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

		$scienceEpisodeActionEntity = new EpisodeActionEntity();
		$scienceEpisodeActionEntity->setPodcast("https://podcast_01.url");
		$scienceEpisodeActionEntity->setEpisode(uniqid("https://episode_01.url"));
		$scienceEpisodeActionEntity->setAction("PLAY");
		$scienceEpisodeActionEntity->setPosition(5);
		$scienceEpisodeActionEntity->setStarted(0);
		$scienceEpisodeActionEntity->setTotal(123);
		$scienceEpisodeActionEntity->setTimestamp("2021-08-22T23:58:56");
		$scienceEpisodeActionEntity->setUserId(self::ADMIN);
		$scienceEpisodeActionEntity->setGuid(uniqid("self::TEST_GUID_1234"));
		$this->episodeActionWriter->save($scienceEpisodeActionEntity);

		$trueCrimeEpisodeActionEntity = new EpisodeActionEntity();
		$trueCrimeEpisodeActionEntity->setPodcast(uniqid("podcast"));
		$trueCrimeEpisodeActionEntity->setEpisode(uniqid("episode_url"));
		$trueCrimeEpisodeActionEntity->setAction("PLAY");
		$trueCrimeEpisodeActionEntity->setPosition(5);
		$trueCrimeEpisodeActionEntity->setStarted(0);
		$trueCrimeEpisodeActionEntity->setTotal(123);
		$trueCrimeEpisodeActionEntity->setTimestamp("2021-10-22T12:00:00");
		$trueCrimeEpisodeActionEntity->setUserId(self::ADMIN);
		$trueCrimeEpisodeActionEntity->setGuid(uniqid("self::TEST_GUID_1234"));
		$this->episodeActionWriter->save($trueCrimeEpisodeActionEntity);

		$episodeActionBeforeConversion = $this->episodeActionMapper->findByGuid(
			$scienceEpisodeActionEntity->getGuid(),
			self::ADMIN
		);

		$this->assertEquals(
			0,
			$episodeActionBeforeConversion->getTimestampEpoch()
		);

		$timestampMigration = new TimestampMigration($this->dbConnection, $this->migrationConfig);
		$timestampMigration->run($this->createMock(SimpleOutput::class));

		$scienceEpisodeActionAfterConversion = $this->episodeActionMapper->findByGuid(
			$scienceEpisodeActionEntity->getGuid(),
			self::ADMIN
		);
		$this->assertSame(
			1629676736,
			$scienceEpisodeActionAfterConversion->getTimestampEpoch()
		);

		$trueCrimeEpisodeActionAfterConversion = $this->episodeActionMapper->findByGuid(
			$trueCrimeEpisodeActionEntity->getGuid(),
			self::ADMIN
		);
		$this->assertSame(
			1634904000,
			$trueCrimeEpisodeActionAfterConversion->getTimestampEpoch()
		);
	}

	/**
	 * @after
	 */
	public function after()
	{
		$this->rollbackTransaction();
	}
}
