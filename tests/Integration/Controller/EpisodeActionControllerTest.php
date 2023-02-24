<?php
declare(strict_types=1);

namespace tests\Integration\Controller;

use OCA\GPodderSync\Controller\EpisodeActionController;
use OCA\GPodderSync\Core\EpisodeAction\EpisodeActionSaver;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionEntity;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionMapper;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionWriter;
use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;
use OCP\IRequest;
use Test\TestCase;
use tests\Helper\DatabaseTransaction;

/**
 * @group DB
 */
class EpisodeActionControllerTest extends TestCase
{

	use DatabaseTransaction;

	const TEST_GUID = "test_guid_123q45345";
	private IAppContainer $container;

	public function setUp(): void {
		parent::setUp();
		$app = new App('gpoddersync');
		$this->container = $app->getContainer();
		$this->db = \OC::$server->getDatabaseConnection();
	}

	/**
	 * @before
	 */
	public function before()
	{
		$this->startTransaction();
	}


	public function testEpisodeActionListAction()
	{
		$userId = uniqid("test_user");
		$episodeActionController = new EpisodeActionController(
			"gpoddersync",
			$this->createMock(IRequest::class),
			$userId,
			$this->container->get(EpisodeActionRepository::class),
			$this->container->get(EpisodeActionSaver::class)
		);

		/** @var EpisodeActionWriter $episodeActionWriter */
		$episodeActionWriter = $this->container->get(EpisodeActionWriter::class);

		$mark = 1633520363;
		$episodeActionEntity = new EpisodeActionEntity();
		$expectedPodcast = uniqid("test");
		$episodeActionEntity->setPodcast($expectedPodcast);
		$expectedEpisode = uniqid("test");
		$episodeActionEntity->setEpisode($expectedEpisode);
		$episodeActionEntity->setAction("PLAY");
		$episodeActionEntity->setPosition(5);
		$episodeActionEntity->setStarted(0);
		$episodeActionEntity->setTotal(123);
		$episodeActionEntity->setTimestampEpoch($mark+600);
		$episodeActionEntity->setUserId($userId);
		$episodeActionEntity->setGuid(self::TEST_GUID);
		$episodeActionWriter->save($episodeActionEntity);

		$response = $episodeActionController->list($mark);
		self::assertCount(1, $response->getData()['actions']);

		$episodeActionInResponse = $response->getData()['actions'][0];
		self::assertSame("2021-10-06T11:49:23", $episodeActionInResponse['timestamp']);
		self::assertSame($expectedEpisode, $episodeActionInResponse['episode']);
		self::assertSame($expectedPodcast, $episodeActionInResponse['podcast']);
		self::assertSame(self::TEST_GUID, $episodeActionInResponse['guid']);
		self::assertSame(5, $episodeActionInResponse['position']);
		self::assertSame(0, $episodeActionInResponse['started']);
		self::assertSame(123, $episodeActionInResponse['total']);
		self::assertSame("PLAY", $episodeActionInResponse['action']);
	}

	public function testEpisodeActionListWithoutSinceAction()
	{
		$userId = uniqid("test_user");
		$episodeActionController = new EpisodeActionController(
			"gpoddersync",
			$this->createMock(IRequest::class),
			$userId,
			$this->container->get(EpisodeActionRepository::class),
			$this->container->get(EpisodeActionSaver::class)
		);

		/** @var EpisodeActionWriter $episodeActionWriter */
		$episodeActionWriter = $this->container->get(EpisodeActionWriter::class);

		$episodeActionEntity = new EpisodeActionEntity();
		$expectedPodcast = uniqid("test");
		$episodeActionEntity->setPodcast($expectedPodcast);
		$expectedEpisode = uniqid("test");
		$episodeActionEntity->setEpisode($expectedEpisode);
		$episodeActionEntity->setAction("PLAY");
		$episodeActionEntity->setPosition(5);
		$episodeActionEntity->setStarted(0);
		$episodeActionEntity->setTotal(123);
		$episodeActionEntity->setTimestampEpoch(1633520363);
		$episodeActionEntity->setUserId($userId);
		$episodeActionEntity->setGuid(self::TEST_GUID);
		$episodeActionWriter->save($episodeActionEntity);

		$response = $episodeActionController->list();
		self::assertCount(1, $response->getData()['actions']);

		$episodeActionInResponse = $response->getData()['actions'][0];
		self::assertSame("2021-10-06T11:39:23", $episodeActionInResponse['timestamp']);
		self::assertSame($expectedEpisode, $episodeActionInResponse['episode']);
		self::assertSame($expectedPodcast, $episodeActionInResponse['podcast']);
		self::assertSame(self::TEST_GUID, $episodeActionInResponse['guid']);
		self::assertSame(5, $episodeActionInResponse['position']);
		self::assertSame(0, $episodeActionInResponse['started']);
		self::assertSame(123, $episodeActionInResponse['total']);
		self::assertSame("PLAY", $episodeActionInResponse['action']);
	}

	public function testEpisodeActionCreateAction(): void {
		$time = time();
		$userId = uniqid('test_user', true);
		$payload = json_decode('[
  {
   "podcast": "https://example.com/feed.rss",
   "episode": "https://example.com/files/s01e20.mp3",
   "guid": "s01e20-example-org",
   "action": "PLAY",
   "timestamp": "2009-12-12T09:00:00",
   "started": 15,
   "position": 120,
   "total":  500
  }
]', true, 512, JSON_THROW_ON_ERROR);
		$request = $this->createMock(IRequest::class);
		$request->expects($this->once())
				->method('getParams')
				->will($this->returnValue($payload));
		$episodeActionController = new EpisodeActionController(
			"gpoddersync",
			$request,
			$userId,
			$this->container->get(EpisodeActionRepository::class),
			$this->container->get(EpisodeActionSaver::class)
		);
		$response = $episodeActionController->create();

		$this->assertArrayHasKey('timestamp', $response->getData());
		$this->assertGreaterThanOrEqual($time, $response->getData()['timestamp']);
		/** @var EpisodeActionMapper $mapper */
		$mapper = $this->container->query(EpisodeActionMapper::class);
		$episodeActionEntities = $mapper->findAll(0, $userId);
		/** @var EpisodeActionEntity $firstEntity */
		$firstEntity = $episodeActionEntities[0];
		$this->assertSame("https://example.com/feed.rss", $firstEntity->getPodcast());
		$this->assertSame("https://example.com/files/s01e20.mp3", $firstEntity->getEpisode());
		$this->assertSame("s01e20-example-org", $firstEntity->getGuid());
		$this->assertSame("PLAY", $firstEntity->getAction());
		$this->assertSame(120, $firstEntity->getPosition());
		$this->assertSame(15, $firstEntity->getStarted());
		$this->assertSame(1260608400, $firstEntity->getTimestampEpoch());
	}

	/**
	 * @after
	 */
	public function after(): void {
		$this->rollbackTransaction();
	}
}
