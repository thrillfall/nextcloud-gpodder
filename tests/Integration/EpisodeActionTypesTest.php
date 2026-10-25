<?php
declare(strict_types=1);

namespace tests\Integration;

use OCA\GPodderSync\Controller\EpisodeActionController;
use OCA\GPodderSync\Core\EpisodeAction\EpisodeActionSaver;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;
use OCP\IRequest;
use Test\TestCase;

/**
 * Saving an action whose episode already has one goes through a failing insert, which would abort a surrounding
 * transaction on PostgreSQL, so these tests run without one (like EpisodeActionSaverGuidBackwardCompatibilityTest).
 *
 * @group DB
 */
class EpisodeActionTypesTest extends TestCase
{
    private IAppContainer $container;

    public function setUp(): void
    {
        parent::setUp();
        $app = new App("gpoddersync");
        $this->container = $app->getContainer();
    }

    public function testOtherActionsKeepThePlayPosition(): void
    {
        $userId = uniqid("test_user", true);
        $episode = [
            "podcast" => "https://example.com/feed.rss",
            "episode" => "https://example.com/files/s01e20.mp3",
            "guid" => "s01e20-example-org",
            "started" => -1,
            "position" => -1,
            "total" => -1,
        ];
        $this->createEpisodeActions($userId, [
            ["action" => "PLAY", "timestamp" => "2009-12-12T09:00:00", "started" => 15, "position" => 120, "total" => 500] + $episode,
            ["action" => "DOWNLOAD", "timestamp" => "2009-12-12T09:05:00"] + $episode,
            ["action" => "DELETE", "timestamp" => "2009-12-12T09:10:00"] + $episode,
            ["action" => "NEW", "timestamp" => "2009-12-12T09:15:00"] + $episode,
        ]);
        $this->createEpisodeActions($userId, [
            ["action" => "PLAY", "timestamp" => "2009-12-12T10:00:00", "started" => 120, "position" => 300, "total" => 500] + $episode,
        ]);

        $actions = $this->listEpisodeActionsByAction($userId);
        self::assertCount(4, $actions);
        self::assertSame(300, $actions["PLAY"]["position"]);
        self::assertSame(120, $actions["PLAY"]["started"]);
        self::assertSame("2009-12-12T10:00:00+00:00", $actions["PLAY"]["timestamp"]);
        self::assertSame("2009-12-12T09:05:00+00:00", $actions["DOWNLOAD"]["timestamp"]);
        self::assertSame("2009-12-12T09:10:00+00:00", $actions["DELETE"]["timestamp"]);
        self::assertSame("2009-12-12T09:15:00+00:00", $actions["NEW"]["timestamp"]);

        /** @var EpisodeActionRepository $episodeActionRepository */
        $episodeActionRepository = $this->container->get(EpisodeActionRepository::class);
        self::assertSame(300, $episodeActionRepository->findByGuid("s01e20-example-org", $userId)->getPosition());
        self::assertSame(
            "DOWNLOAD",
            $episodeActionRepository->findByGuid("s01e20-example-org", $userId, "DOWNLOAD")->getAction()
        );
    }

    public function testOtherActionsKeepThePlayPositionWithoutGuid(): void
    {
        $userId = uniqid("test_user", true);
        $episode = [
            "podcast" => "https://example.com/feed.rss",
            "episode" => "https://example.com/files/s01e22.mp3",
        ];
        $this->createEpisodeActions($userId, [
            ["action" => "PLAY", "timestamp" => "2009-12-12T09:00:00", "started" => 0, "position" => 60, "total" => 500] + $episode,
            ["action" => "DOWNLOAD", "timestamp" => "2009-12-12T09:05:00", "started" => -1, "position" => -1, "total" => -1] + $episode,
        ]);

        $actions = $this->listEpisodeActionsByAction($userId);
        self::assertCount(2, $actions);
        self::assertSame(60, $actions["PLAY"]["position"]);
        self::assertSame(-1, $actions["DOWNLOAD"]["position"]);
    }

    private function createEpisodeActions(string $userId, array $episodeActions): void
    {
        $request = $this->createMock(IRequest::class);
        $request->method("getParams")->willReturn($episodeActions);
        $episodeActionController = new EpisodeActionController(
            "gpoddersync",
            $request,
            $userId,
            $this->container->get(EpisodeActionRepository::class),
            $this->container->get(EpisodeActionSaver::class)
        );
        $episodeActionController->create();
    }

    private function listEpisodeActionsByAction(string $userId): array
    {
        $episodeActionController = new EpisodeActionController(
            "gpoddersync",
            $this->createMock(IRequest::class),
            $userId,
            $this->container->get(EpisodeActionRepository::class),
            $this->container->get(EpisodeActionSaver::class)
        );
        $actionsByType = [];
        foreach ($episodeActionController->list()->getData()["actions"] as $episodeAction) {
            $actionsByType[$episodeAction["action"]] = $episodeAction;
        }
        return $actionsByType;
    }
}
