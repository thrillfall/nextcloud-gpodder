<?php
declare(strict_types=1);

namespace tests\Integration;

use OCA\GPodderSync\Core\EpisodeAction\EpisodeActionSaver;
use OCP\AppFramework\App;
use Test\TestCase;

/**
 * @group DB
 */
class EpisodeActionSaverGuidMigrationTest extends TestCase
{

	private const USER_ID_0 = "user0@127.0.0.1";

	private \OCP\AppFramework\IAppContainer $container;

	public function setUp(): void {
		parent::setUp();
		$app = new App('gpoddersync');
		$this->container = $app->getContainer();
	}

	public function testCreateEpisodeActionWithoutGuidThenCreateAgainWithGuid() : void
	{
		/** @var EpisodeActionSaver $episodeActionSaver */
		$episodeActionSaver = $this->container->get(EpisodeActionSaver::class);

		$episodeUrl = uniqid("https://dts.podtrac.com/redirect.mp3/chrt.fm/track");
		$guid = uniqid("gid://art19-episode-locator/V0/Ktd");

		$savedEpisodeActionEntityWithoutGuid = $episodeActionSaver->saveEpisodeActions(
			"[EpisodeAction{podcast='https://rss.art19.com/dr-death-s3-miracle-man', episode='{$episodeUrl}', action=PLAY, timestamp=Mon Aug 23 01:58:56 GMT+02:00 2021, started=47, position=54, total=2252}]",
			self::USER_ID_0
		)[0];

		$savedEpisodeActionEntityWithGuid = $episodeActionSaver->saveEpisodeActions(
			"[EpisodeAction{podcast='https://rss.art19.com/dr-death-s3-miracle-man', episode='{$episodeUrl}', guid='{$guid}', action=PLAY, timestamp=Mon Aug 23 01:58:56 GMT+02:00 2021, started=47, position=54, total=2252}]",
			self::USER_ID_0
		)[0];

		self::assertSame($savedEpisodeActionEntityWithoutGuid->getId(), $savedEpisodeActionEntityWithGuid->getId());
	}
}
