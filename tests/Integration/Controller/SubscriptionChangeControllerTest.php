<?php
declare(strict_types=1);

namespace tests\Integration\Controller;

use OC\Security\SecureRandom;
use OCA\GPodderSync\Controller\SubscriptionChangeController;
use OCA\GPodderSync\Core\SubscriptionChange\SubscriptionChangeSaver;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeRepository;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeEntity;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeWriter;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeMapper;
use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;
use OCP\IConfig;
use OCP\IRequest;
use DateTime;
use Test\TestCase;
use tests\Helper\DatabaseTransaction;

/**
 * @group DB
 */
class SubscriptionChangeControllerTest extends TestCase
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

    public function testSubscriptionChangeListAction()
    {
        $userId = uniqid("test_user");
        $subscriptionChangeController = new SubscriptionChangeController(
            "gpoddersync",
            $this->createMock(IRequest::class),
			$userId,
            $this->container->get(SubscriptionChangeSaver::class),
            $this->container->get(SubscriptionChangeRepository::class)
        );

		/** @var SubscriptionChangeWriter $subscriptionChangeWriter*/
		$subscriptionChangeWriter = $this->container->get(SubscriptionChangeWriter::class);

		$mark = 1633520363;
		$subscriptionChangeEntity = new SubscriptionChangeEntity();
		$expectedUrl1 = uniqid("test");
		$subscriptionChangeEntity->setUrl($expectedUrl1);
		$subscriptionChangeEntity->setSubscribed(true);
		$subscriptionChangeEntity->setUpdated(date("Y-m-d\TH:i:s", $mark+600));
		$subscriptionChangeEntity->setUserId($userId);

		$subscriptionChangeWriter->create($subscriptionChangeEntity);

		$subscriptionChangeEntity = new SubscriptionChangeEntity();
		$expectedUrl2 = uniqid("test");
		$subscriptionChangeEntity->setUrl($expectedUrl2);
		$subscriptionChangeEntity->setSubscribed(false);
		$subscriptionChangeEntity->setUpdated(date("Y-m-d\TH:i:s", $mark+1200));
		$subscriptionChangeEntity->setUserId($userId);

		$subscriptionChangeWriter->create($subscriptionChangeEntity);

		$response = $subscriptionChangeController->list($mark);
		self::assertCount(1, $response->getData()['add']);
		self::assertCount(1, $response->getData()['remove']);

		$subscriptionChangeInResponse1 = $response->getData()['add'][0];
		$subscriptionChangeInResponse2 = $response->getData()['remove'][0];
		self::assertSame($expectedUrl1, $subscriptionChangeInResponse1);
		self::assertSame($expectedUrl2, $subscriptionChangeInResponse2);
    }

	public function testSubscriptionChangeListWithoutSinceAction()
    {
        $userId = uniqid("test_user");
        $subscriptionChangeController = new SubscriptionChangeController(
            "gpoddersync",
            $this->createMock(IRequest::class),
			$userId,
            $this->container->get(SubscriptionChangeSaver::class),
            $this->container->get(SubscriptionChangeRepository::class)
        );

		/** @var SubscriptionChangeWriter $subscriptionChangeWriter*/
		$subscriptionChangeWriter = $this->container->get(SubscriptionChangeWriter::class);

		$subscriptionChangeEntity = new SubscriptionChangeEntity();
		$expectedUrl1 = uniqid("test");
		$subscriptionChangeEntity->setUrl($expectedUrl1);
		$subscriptionChangeEntity->setSubscribed(true);
		$subscriptionChangeEntity->setUpdated("2021-10-06T11:39:23");
		$subscriptionChangeEntity->setUserId($userId);

		$subscriptionChangeWriter->create($subscriptionChangeEntity);

		$subscriptionChangeEntity = new SubscriptionChangeEntity();
		$expectedUrl2 = uniqid("test");
		$subscriptionChangeEntity->setUrl($expectedUrl2);
		$subscriptionChangeEntity->setSubscribed(false);
		$subscriptionChangeEntity->setUpdated("2021-10-06T11:49:23");
		$subscriptionChangeEntity->setUserId($userId);

		$subscriptionChangeWriter->create($subscriptionChangeEntity);

		$response = $subscriptionChangeController->list();
		self::assertCount(1, $response->getData()['add']);
		self::assertCount(1, $response->getData()['remove']);

		$subscriptionChangeInResponse1 = $response->getData()['add'][0];
		$subscriptionChangeInResponse2 = $response->getData()['remove'][0];
		self::assertSame($expectedUrl1, $subscriptionChangeInResponse1);
		self::assertSame($expectedUrl2, $subscriptionChangeInResponse2);
    }

	public function testSubscriptionChangeCreateAction(): void {
		$time = time();
		$userId = uniqid('test_user');

		$subscriptionChangeController = new SubscriptionChangeController(
            "gpoddersync",
            $this->createMock(IRequest::class),
			$userId,
            $this->container->get(SubscriptionChangeSaver::class),
            $this->container->get(SubscriptionChangeRepository::class)
        );

		$expectedAdd1 = "https://example.com/feed.rss";
		$expectedAdd2 = "https://example.org/feed.xml";
		$expectedRemove1 = "https://www.example.com/feed.rss";
		$expectedRemove2 = "https://www.example.com/feed.xml";
		
		$response = $subscriptionChangeController->create(
			[$expectedAdd1, $expectedAdd2],
			[$expectedRemove1,$expectedRemove2]
		);

		$this->assertArrayHasKey('timestamp', $response->getData());
		$this->assertGreaterThanOrEqual($time, $response->getData()['timestamp']);

		/** @var SubscriptionChangeMapper $mapper */
		$mapper = $this->container->query(SubscriptionChangeMapper::class);
		$subscriptionChangeAddEntities = $mapper->findAllSubscriptionState(true, (new DateTime)->setTimestamp(0), $userId);
		$subscriptionChangeRemoveEntities = $mapper->findAllSubscriptionState(false, (new DateTime)->setTimestamp(0), $userId);

		$firstAdd = $subscriptionChangeAddEntities[0]->getUrl();
		$secondAdd = $subscriptionChangeAddEntities[1]->getUrl();
		$firstRemove = $subscriptionChangeRemoveEntities[0]->getUrl();
		$secondRemove = $subscriptionChangeRemoveEntities[1]->getUrl();

		$this->assertSame($expectedAdd1, $firstAdd);
		$this->assertSame($expectedAdd2, $secondAdd);
		$this->assertSame($expectedRemove1, $firstRemove);
		$this->assertSame($expectedRemove2, $secondRemove);

	}

	/**
	 * @after
	 */
	public function after(): void {
		$this->rollbackTransaction();
	}
}
