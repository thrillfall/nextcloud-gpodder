<?php
declare(strict_types=1);

namespace tests\Integration\Controller;

use OC\AppFramework\Http\Request;
use OC\Security\SecureRandom;
use OCA\GPodderSync\Controller\EpisodeActionController;
use OCA\GPodderSync\Core\EpisodeAction\EpisodeActionSaver;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionEntity;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionWriter;
use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;
use OCP\IConfig;
use tests\Helper\DatabaseTransaction;

/**
 * @group DB
 */
class EpisodeActionControllerTest extends \Test\TestCase
{

	use DatabaseTransaction;

	const TEST_GUID = "test_guid_123q45345";
	private IAppContainer $container;

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


	public function testEpisodeActionListAction()
	{
		$userId = uniqid("test_user");
		$episodeActionController = new EpisodeActionController(
			"gpoddersync",
			new Request([], new SecureRandom(), self::getMockBuilder(IConfig::class)->getMock()),
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

	/**
	 * @after
	 */
	public function after()
	{
		$this->rollbackTransation();
	}
}
