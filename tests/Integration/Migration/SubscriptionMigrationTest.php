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
class SubscriptionMigrationTest extends TestCase
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

    public function testCreateSubscriptionWithLongFeedURL()
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
        // feed with length of 999 characters
        $expectedUrl = "https://www.example.com/feed.rss?key=2d4851a4c6d7788e55e72d1865caa1e67f8d55b64f24ceaab519f9c31d03f4a52d5599008b889fb57aabb2ba19d052cd5c187311fb91ac4892891f5fba5cd6404d015d2cefb9f66c680a3c0ad1139a7d04c3029854ec5099bb7a45141f4a37c9e9db40e79e1eacc7f8e04b24ef90821ed6f6f6d822a856ea80fed6571788a539bea05f6bf2557a1850396efad52a24ed06e781c07983ae0c66b70d161e73ba332655de980b539dfb6520d94abbd54f4aa4640eacaaeb400d0801faa622d9eacfa3b7d6644cc22e4f7cf0d129536c3e76bfdccd5366dadf4a0efa034f08408094c3198fe5ec3d2ee1b13d1422418674d75d13e15ecb8b74929973cad00ba9bd4b31eaf9875eaaade75628fbcc94d6d035aa54b137b1a1bf7ba428b663a3555c43d27c079d4942000dd3088fd13bdf2cd9af34052ddfedc5561acf8100d1d7759b7981c6abcfcac097425a8289005a490aad99ead6f59fca3fea9b06ebdf238400895dc13adf0db7874e7fa06baf316f4fc63d911e3d2bdaff543d71362de271d295f8d86e4ede7c5a71cad7737aa6ab24fc54d2cba43f7fd35f8a195aee1a543fda67b5fd4a8ac99c4fb7f682bff3818be83df5bd41efaef6544caeefc218a2ef9f7d8a9da70846a64389d60cf131416fdee78fabe307aa7cdfc0c84b137097d94a";
		$subscriptionChangeEntity->setUrl($expectedUrl);
		$subscriptionChangeEntity->setSubscribed(true);
		$subscriptionChangeEntity->setUpdated(date("Y-m-d\TH:i:s", $mark+600));
		$subscriptionChangeEntity->setUserId($userId);

		$subscriptionChangeWriter->create($subscriptionChangeEntity);


		$response = $subscriptionChangeController->list($mark);
		self::assertCount(1, $response->getData()['add']);

		$subscriptionChangeInResponse = $response->getData()['add'][0];
		self::assertSame($expectedUrl, $subscriptionChangeInResponse);
        self::assertSame(strlen($subscriptionChangeInResponse), 999);
    }

	/**
	 * @after
	 */
	public function after(): void {
		$this->rollbackTransaction();
	}
}
