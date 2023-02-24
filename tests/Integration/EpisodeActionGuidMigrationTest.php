<?php
declare(strict_types=1);

namespace tests\Integration;

use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionEntity;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionWriter;
use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;
use Test\TestCase;
use tests\Helper\DatabaseTransaction;

/**
 * @group DB
 */
class EpisodeActionGuidMigrationTest extends TestCase
{
	use DatabaseTransaction;

	private const USER_ID_0 = "user0@127.0.0.1";

	private IAppContainer $container;

	private EpisodeActionWriter $episodeActionWriter;

	private EpisodeActionRepository $episodeActionRepository;

	public function setUp(): void {
		parent::setUp();
		$app = new App('nextcloud-gpodder');
		$this->container = $app->getContainer();
		$this->episodeActionWriter = $this->container->get(EpisodeActionWriter::class);
		$this->episodeActionRepository = $this->container->get(EpisodeActionRepository::class);
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
		self::expectExceptionMessageMatches(
			"/(unique constraint|Integrity constraint violation)/"
		);

		$episodeActionEntity = new EpisodeActionEntity();
		$episodeActionEntity->setPodcast("https://podcast_01.url");
		$episodeActionEntity->setEpisode("https://episode_01.url");
		$episodeActionEntity->setAction("PLAY");
		$episodeActionEntity->setPosition(5);
		$episodeActionEntity->setStarted(0);
		$episodeActionEntity->setTotal(123);
		$episodeActionEntity->setTimestampEpoch(1629676736);
		$episodeActionEntity->setUserId(self::USER_ID_0);
		$this->episodeActionWriter->save($episodeActionEntity);

		//and save again
		$this->episodeActionWriter->save($episodeActionEntity);

	}

	public function testFindEpisodeActionByEpisodeUrlAndThenGuid()
	{
		$episodeActionEntity = new EpisodeActionEntity();
		$episodeActionEntity->setPodcast("https://podcast_01.url");
		$episodeActionEntity->setEpisode("https://episode_01.url");
		$episodeActionEntity->setAction("PLAY");
		$episodeActionEntity->setPosition(5);
		$episodeActionEntity->setStarted(0);
		$episodeActionEntity->setTotal(123);
		$episodeActionEntity->setTimestampEpoch(1629676736);
		$episodeActionEntity->setUserId(self::USER_ID_0);
		$savedEpisodeActionEntity = $this->episodeActionWriter->save($episodeActionEntity);

		self::assertSame(
			$savedEpisodeActionEntity->getId(),
			$this->episodeActionRepository->findByEpisodeUrl($episodeActionEntity->getEpisode(), self::USER_ID_0)->getId()
		);

		//update same episode action again this time with guid

		$episodeActionEntityWithGuid = clone $episodeActionEntity;
		$episodeActionEntityWithGuid->setGuid("guid:dadsaf4f4v");
		$savedEpisodeActionEntityWithGuid = $this->episodeActionWriter->update($episodeActionEntityWithGuid);

		self::assertSame(
			$savedEpisodeActionEntityWithGuid->getId(),
			$this->episodeActionRepository->findByEpisodeUrl($episodeActionEntityWithGuid->getEpisode(), self::USER_ID_0)->getId()
		);

		self::assertSame(
			$savedEpisodeActionEntityWithGuid->getId(),
			$this->episodeActionRepository->findByGuid($episodeActionEntityWithGuid->getGuid(), self::USER_ID_0)->getId()
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
