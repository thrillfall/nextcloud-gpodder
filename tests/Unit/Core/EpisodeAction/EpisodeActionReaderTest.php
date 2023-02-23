<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Tests\Unit\Core\EpisodeAction;

use OCA\GPodderSync\Core\EpisodeAction\EpisodeActionReader;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionMapper;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCP\Http\Client\IClientService;
use OCP\ICacheFactory;
use Test\TestCase;

class EpisodeActionReaderTest extends TestCase {
    /** @var IClientService */
    private $clientService;

    /** @var ICacheFactory */
    private $iCacheFactory;

    /** @var EpisodeActionRepository */
    private $episodeActionRepository;

    protected function setUp(): void {
        parent::setUp();
        $this->clientService = $this->createMock(IClientService::class);
        $this->episodeActionRepository = $this->createMock(EpisodeActionRepository::class);
        $this->iCacheFactory = $this->createMock(ICacheFactory::class);
    }

    public function testCreateFromArray(): void {
		$reader = new EpisodeActionReader($this->clientService, $this->episodeActionRepository, $this->iCacheFactory);
		$episodeActions = $reader->fromArray([["podcast" => "https://example.org/feed.xml", "episode" => "https://example.org/episode1.mp3", "action" => "PLAY", "timestamp" => "2021-10-03T12:03:17", "started" => 0, "position" => 50, "total"=> 3422]]);

		$this->assertSame("https://example.org/feed.xml",  $episodeActions[0]->getPodcast());
		$this->assertSame("https://example.org/episode1.mp3", $episodeActions[0]->getEpisode());
		$this->assertSame("PLAY", $episodeActions[0]->getAction());
		$this->assertSame("2021-10-03T12:03:17", $episodeActions[0]->getTimestamp());
		$this->assertSame(0, $episodeActions[0]->getStarted());
		$this->assertSame(50, $episodeActions[0]->getPosition());
		$this->assertSame(3422, $episodeActions[0]->getTotal());
	}

	public function testCreateFromMultipleEpisodesArray(): void {
        $reader = new EpisodeActionReader($this->clientService, $this->episodeActionRepository, $this->iCacheFactory);
		$episodeActions = $reader->fromArray([
			["podcast" => "https://example.org/feed.xml", "episode" => "https://example.org/episode1.mp3", "guid" => "episode1", "action" => "PLAY", "timestamp" => "2021-10-03T12:03:17", "started" => 0, "position" => 50, "total"=> 3422],
			["podcast" => "https://example.org/feed.xml", "episode" => "https://example.org/episode2.mp3", "guid" => "episode2", "action" => "download", "timestamp" => "2021-10-03T12:03:17"],
			["podcast" => "https://example.com/feed.xml", "episode" => "https://chrt.fm/track/47G541/injector.simplecastaudio.com/f16c3da7-cf46-4a42-99b7-8467255c6086/episodes/e8e24c01-6157-40e8-9b5a-45d539aeb7e6/audio/128/default.mp3?aid=rss_feed&awCollectionId=f16c3da7-cf46-4a42-99b7-8467255c6086&awEpisodeId=e8e24c01-6157-40e8-9b5a-45d539aeb7e6&feed=wEl4UUJZ", "guid" => "EPISODE-001-EXAMPLE-COM", "action" => "PLAY", "timestamp" => "2021-10-03T12:03:17", "started" => 50, "position" => 221, "total"=> 450]
		]);

		$this->assertSame("https://example.org/feed.xml",  $episodeActions[0]->getPodcast());
		$this->assertSame("https://example.org/episode1.mp3", $episodeActions[0]->getEpisode());
		$this->assertSame("episode1", $episodeActions[0]->getGuid());
		$this->assertSame("PLAY", $episodeActions[0]->getAction());
		$this->assertSame("2021-10-03T12:03:17", $episodeActions[0]->getTimestamp());
		$this->assertSame(0, $episodeActions[0]->getStarted());
		$this->assertSame(50, $episodeActions[0]->getPosition());
		$this->assertSame(3422, $episodeActions[0]->getTotal());

		$this->assertSame("https://example.org/feed.xml",  $episodeActions[1]->getPodcast());
		$this->assertSame("https://example.org/episode2.mp3", $episodeActions[1]->getEpisode());
		$this->assertSame("episode2", $episodeActions[1]->getGuid());
		$this->assertSame("DOWNLOAD", $episodeActions[1]->getAction());
		$this->assertSame("2021-10-03T12:03:17", $episodeActions[1]->getTimestamp());
		$this->assertSame(-1, $episodeActions[1]->getStarted());
		$this->assertSame(-1, $episodeActions[1]->getPosition());
		$this->assertSame(-1, $episodeActions[1]->getTotal());

		$this->assertSame("https://example.com/feed.xml",  $episodeActions[2]->getPodcast());
		$this->assertSame("https://chrt.fm/track/47G541/injector.simplecastaudio.com/f16c3da7-cf46-4a42-99b7-8467255c6086/episodes/e8e24c01-6157-40e8-9b5a-45d539aeb7e6/audio/128/default.mp3?aid=rss_feed&awCollectionId=f16c3da7-cf46-4a42-99b7-8467255c6086&awEpisodeId=e8e24c01-6157-40e8-9b5a-45d539aeb7e6&feed=wEl4UUJZ", $episodeActions[2]->getEpisode());
		$this->assertSame("EPISODE-001-EXAMPLE-COM", $episodeActions[2]->getGuid());
		$this->assertSame("PLAY", $episodeActions[2]->getAction());
		$this->assertSame("2021-10-03T12:03:17", $episodeActions[2]->getTimestamp());
		$this->assertSame(50, $episodeActions[2]->getStarted());
		$this->assertSame(221, $episodeActions[2]->getPosition());
		$this->assertSame(450, $episodeActions[2]->getTotal());
	}

	public function testCreateWithFaultyData(): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Client sent incomplete or invalid data: {"podcast":"https:\/\/example.org\/feed.xml","action":"download","timestamp":"2021-10-03T12:03:17"}');
		(new EpisodeActionReader($this->clientService, $this->episodeActionRepository, $this->iCacheFactory))->fromArray([
			["podcast" => "https://example.org/feed.xml", "action" => "download", "timestamp" => "2021-10-03T12:03:17"],
			["podcast" => "https://example.org/feed.xml", "episode" => "https://example.org/episode2.mp3", "guid" => "episode2", "action" => "download", "timestamp" => "2021-10-03T12:03:17"],
		]);
	}

}
