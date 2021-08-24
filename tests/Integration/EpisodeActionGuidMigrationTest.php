<?php
declare(strict_types=1);

namespace tests\Integration;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionEntity;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionWriter;
use OCP\AppFramework\App;
use Test\TestCase;
use tests\Helper\DatabaseTransaction;

/**
 * @group DB
 */
class EpisodeActionGuidMigrationTest extends TestCase
{
	use DatabaseTransaction;

	private \OCP\AppFramework\IAppContainer $container;

	public function setUp(): void {
		parent::setUp();
		$app = new App('gpoddersync');
		$this->container = $app->getContainer();
	}

	/**
	 * @before
	 */
	public function before()
	{
		$this->startTransaction();
	}

	public function testCreateSameEpisodeActionTriggersUniqueConstraintViolationException()
	{
		self::expectException(UniqueConstraintViolationException::class);

		/** @var EpisodeActionWriter $episodeActionWriter */
		$episodeActionWriter = $this->container->get('OCA\GPodderSync\Db\EpisodeAction\EpisodeActionWriter');

		$episodeActionEntity = new EpisodeActionEntity();
		$episodeActionEntity->setPodcast("https://podcast_01.url");
		$episodeActionEntity->setEpisode("https://episode_01.url");
		$episodeActionEntity->setAction("PLAY");
		$episodeActionEntity->setPosition(5);
		$episodeActionEntity->setStarted(0);
		$episodeActionEntity->setTotal(123);
		$episodeActionEntity->setTimestamp("Mon Aug 23 01:58:56 GMT+02:00 2021");
		$episodeActionEntity->setUserId("user0@127.0.0.1");
		$episodeActionWriter->save($episodeActionEntity);

		$episodeActionWriter->save($episodeActionEntity);

	}

	/**
	 * @after
	 */
	public function after()
	{
		$this->rollbackTransation();
	}
}
