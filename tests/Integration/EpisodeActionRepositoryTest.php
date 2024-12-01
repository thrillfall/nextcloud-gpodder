<?php
declare(strict_types=1);

namespace tests\Integration;

use OCA\GPodderSync\Core\EpisodeAction\EpisodeActionSaver;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;

class EpisodeActionRepositoryTest extends \Test\TestCase
{
    private const USER_ID_0 = "testuser0";

    private IAppContainer $container;

    public function setUp(): void
    {
        parent::setUp();
        $app = new App("gpoddersync");
        $this->container = $app->getContainer();
    }

    public function testTimestampOutputIsUTCHumandReadable(): void
    {
        /** @var EpisodeActionSaver $episodeActionSaver */
        $episodeActionSaver = $this->container->get(EpisodeActionSaver::class);

        $episodeUrl = uniqid("test_https://dts.podtrac.com/");

        $timestampHumanReadable = "2021-08-22T23:58:56";
        $guid = uniqid("test_gid://art19-episode-locator/V0/Ktd");

        $savedEpisodeActionEntity = $episodeActionSaver->saveEpisodeActions(
            [
                [
                    "podcast" =>
                        "https://rss.art19.com/dr-death-s3-miracle-man",
                    "episode" => $episodeUrl,
                    "guid" => $guid,
                    "action" => "PLAY",
                    "timestamp" => "2021-08-22T23:58:56",
                    "started" => 47,
                    "position" => 54,
                    "total" => 2252,
                ],
            ],
            self::USER_ID_0
        )[0];

        self::assertSame(
            1629676736,
            $savedEpisodeActionEntity->getTimestampEpoch()
        );

        $timestampOutputFormatted = \DateTime::createFromFormat(
            "U",
            (string) $savedEpisodeActionEntity->getTimestampEpoch()
        )
            ->setTimezone(new \DateTimeZone("UTC"))
            ->format("Y-m-d\TH:i:s");
        self::assertSame($timestampHumanReadable, $timestampOutputFormatted);

        /** @var $episodeActionRepository EpisodeActionRepository */
        $episodeActionRepository = $this->container->get(
            EpisodeActionRepository::class
        );

        $retrievedEpisodeActionEntity = $episodeActionRepository->findByGuid(
            $guid,
            self::USER_ID_0
        );
        self::assertSame(
            "2021-08-22T23:58:56",
            $retrievedEpisodeActionEntity->getTimestamp()
        );
    }

    public function testTimestampsWithTimezones(): void
    {
        /** @var EpisodeActionSaver $episodeActionSaver */
        $episodeActionSaver = $this->container->get(EpisodeActionSaver::class);

        $podcast = "https://example.com/rss";
        $episodeUrl = uniqid("test_https://dts.podtrac.com/");
        $guid = uniqid("test_gid://art19-episode-locator/V0/Ktd");

        $timestamps = [
            ["2024-12-01T16:29:56", 1733070596],
            ["2024-12-01T16:29:56Z", 1733070596],
            ["2024-12-01T16:29:56+01", 1733066996],
            ["2024-12-01T16:29:56+00:00", 1733070596],
            ["2024-12-01T16:29:56+02:00", 1733063396],
        ];

        foreach ($timestamps as [$str, $epoch]) {
            $savedEpisodeActionEntity = $episodeActionSaver->saveEpisodeActions(
                [
                    [
                        "podcast" => $podcast,
                        "episode" => $episodeUrl,
                        "guid" => $guid,
                        "action" => "PLAY",
                        "timestamp" => $str,
                        "started" => 47,
                        "position" => 54,
                        "total" => 2252,
                    ],
                ],
                self::USER_ID_0
            )[0];

            self::assertSame(
                $epoch,
                $savedEpisodeActionEntity->getTimestampEpoch()
            );
        }
    }
}
