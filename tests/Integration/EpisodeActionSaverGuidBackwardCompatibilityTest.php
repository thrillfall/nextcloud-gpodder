<?php
declare(strict_types=1);

namespace tests\Integration;

use OCA\GPodderSync\Core\EpisodeAction\EpisodeActionSaver;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCP\AppFramework\App;
use Test\TestCase;

/**
 * @group DB
 */
class EpisodeActionSaverGuidBackwardCompatibilityTest extends TestCase
{

    private const USER_ID_0 = "testuser0";

    private \OCP\AppFramework\IAppContainer $container;

    public function setUp(): void
    {
        parent::setUp();
        $app = new App('gpoddersync');
        $this->container = $app->getContainer();
    }

    public function testUpdateWithoutGuidDoesNotNullGuid(): void
    {
        /** @var EpisodeActionSaver $episodeActionSaver */
        $episodeActionSaver = $this->container->get(EpisodeActionSaver::class);

        $episodeUrl = uniqid("test_https://dts.podtrac.com/redirect.mp3/chrt.fm/track");
        $guid = uniqid("test_gid://art19-episode-locator/V0/Ktd");

        $savedEpisodeActionEntity = $episodeActionSaver->saveEpisodeActions(
            [["podcast" => 'https://rss.art19.com/dr-death-s3-miracle-man', "episode" => $episodeUrl, "guid" => $guid, "action" => "PLAY", "timestamp" => "2021-08-22T23:58:56", "started" => 47, "position" => 54, "total" => 2252]],
            self::USER_ID_0
        )[0];

        $savedEpisodeActionEntityWithoutGuidFromOldDevice = $episodeActionSaver->saveEpisodeActions(
            [["podcast" => 'https://rss.art19.com/dr-death-s3-miracle-man', "episode" => $episodeUrl, "action" => "PLAY", "timestamp" => "2021-08-22T23:58:56", "started" => 47, "position" => 54, "total" => 2252]],
            self::USER_ID_0
        )[0];

        self::assertSame($savedEpisodeActionEntity->getId(), $savedEpisodeActionEntityWithoutGuidFromOldDevice->getId());
        self::assertNotNull($savedEpisodeActionEntityWithoutGuidFromOldDevice->getGuid());
    }

    public function testDoNotFailToUpdateEpisodeActionByGuidIfThereIsAnotherWithTheSameValueForEpisodeUrl(): void
    {
        //arrange
        /** @var EpisodeActionSaver $episodeActionSaver */
        $episodeActionSaver = $this->container->get(EpisodeActionSaver::class);

        $url = uniqid("https://podcast-mp3.dradio.de/");
        $urlWithParameter = $url . "?ref=never_know_if_ill_be_removed";

        $podcastUrl = uniqid("https://podcast");

        $episodeActionSaver->saveEpisodeActions(
            [["podcast" => $podcastUrl, "episode" => $url, "guid" => $urlWithParameter, "action" => "PLAY", "timestamp" => "2021-08-22T23:58:56", "started" => 35, "position" => 100, "total" => 2252]],
            self::USER_ID_0
        )[0];

        $episodeActionSaver->saveEpisodeActions(
            [["podcast" => $podcastUrl, "episode" => $urlWithParameter, "guid" => $url, "action" => "PLAY", "timestamp" => "2021-08-22T23:58:56", "started" => 35, "position" => 100, "total" => 2252]],
            self::USER_ID_0
        )[0];

        //act
        $episodeActionSaver->saveEpisodeActions(
            [["podcast" => $podcastUrl, "episode" => $urlWithParameter, "guid" => $url, "action" => "PLAY", "timestamp" => "2021-08-22T23:58:56", "started" => 35, "position" => 100, "total" => 2252]],
            self::USER_ID_0
        )[0];

        //assert
        /** @var EpisodeActionRepository $episodeActionRepository */
        $episodeActionRepository = $this->container->get(EpisodeActionRepository::class);
        $this->assertSame(100, $episodeActionRepository->findByGuid($urlWithParameter, self::USER_ID_0)->getPosition());

        //act
        $episodeActionSaver->saveEpisodeActions(
            [["podcast" => $podcastUrl, "episode" => $urlWithParameter, "guid" => $urlWithParameter, "action" => "PLAY", "timestamp" => "2021-08-22T23:58:56", "started" => 35, "position" => 100, "total" => 2252]],
            self::USER_ID_0
        )[0];

        //assert
        /** @var EpisodeActionRepository $episodeActionRepository */
        $episodeActionRepository = $this->container->get(EpisodeActionRepository::class);
        $this->assertSame(100, $episodeActionRepository->findByGuid($urlWithParameter, self::USER_ID_0)->getPosition());
    }
}
