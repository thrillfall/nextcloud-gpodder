<?php
declare(strict_types=1);

namespace tests\Integration;

use OCA\GPodderSync\Core\EpisodeAction\EpisodeActionSaver;
use OCP\AppFramework\App;
use Test\TestCase;

/**
 * @group DB
 */
class EpisodeActionSaverGuidBackwardCompatbilityTest extends TestCase
{

	private const USER_ID_0 = "testuser0";

	private \OCP\AppFramework\IAppContainer $container;

	public function setUp(): void {
		parent::setUp();
		$app = new App('gpoddersync');
		$this->container = $app->getContainer();
	}

	public function testUpdateWithoutGuidDoesNotNullGuid() : void
	{
		/** @var EpisodeActionSaver $episodeActionSaver */
		$episodeActionSaver = $this->container->get(EpisodeActionSaver::class);

		$episodeUrl = uniqid("test_https://dts.podtrac.com/redirect.mp3/chrt.fm/track");
		$guid = uniqid("test_gid://art19-episode-locator/V0/Ktd");

		$savedEpisodeActionEntity = $episodeActionSaver->saveEpisodeActions(
			[["podcast" => 'https://rss.art19.com/dr-death-s3-miracle-man',	"episode" => $episodeUrl, "guid" => $guid, "action" => "PLAY", "timestamp" => "2021-08-22T23:58:56", "started" => 47, "position" => 54, "total" => 2252]],
			self::USER_ID_0
		)[0];

		$savedEpisodeActionEntityWithoutGuidFromOldDevice = $episodeActionSaver->saveEpisodeActions(
			[["podcast" => 'https://rss.art19.com/dr-death-s3-miracle-man',	"episode" => $episodeUrl, "action" => "PLAY", "timestamp" => "2021-08-22T23:58:56", "started" => 47, "position" => 54, "total" => 2252]],
			self::USER_ID_0
		)[0];

		self::assertSame($savedEpisodeActionEntity->getId(), $savedEpisodeActionEntityWithoutGuidFromOldDevice->getId());
		self::assertNotNull($savedEpisodeActionEntityWithoutGuidFromOldDevice->getGuid());
	}

}
