<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Tests\Unit\Core\EpisodeAction;

use OCA\GPodderSync\Core\EpisodeAction\EpisodeActionReader;
use Test\TestCase;

class EpisodeActionReaderTest extends TestCase {
	public function testCreateFromArray(): void {
		$reader = new EpisodeActionReader();
		$episodeActions = $reader->fromArray([["podcast" => "https://example.org/feed.xml", "episode" => "https://example.org/episode1.mp3", "action" => "PLAY", "timestamp" => "Sun Oct 03 14:03:17 GMT+02:00 2021", "started" => 0, "position" => 50, "total"=> 3422]]);

		$this->assertSame("https://example.org/feed.xml",  $episodeActions[0]->getPodcast());
		$this->assertSame("https://example.org/episode1.mp3", $episodeActions[0]->getEpisode());
		$this->assertSame("PLAY", $episodeActions[0]->getAction());
		$this->assertSame("Sun Oct 03 14:03:17 GMT+02:00 2021", $episodeActions[0]->getTimestamp());
		$this->assertSame(0, $episodeActions[0]->getStarted());
		$this->assertSame(50, $episodeActions[0]->getPosition());
		$this->assertSame(3422, $episodeActions[0]->getTotal());
	}

	public function testCreateFromMultipleEpisodesArray(): void {
		$reader = new EpisodeActionReader();
		$episodeActions = $reader->fromArray([
			["podcast" => "https://example.org/feed.xml", "episode" => "https://example.org/episode1.mp3", "guid" => "episode1", "action" => "PLAY", "timestamp" => "Sun Oct 03 14:03:17 GMT+02:00 2021", "started" => 0, "position" => 50, "total"=> 3422], 
			["podcast" => "https://example.org/feed.xml", "episode" => "https://example.org/episode2.mp3", "guid" => "episode2", "action" => "DOWNLOAD", "timestamp" => "Sat Oct 02 11:06:28 GMT+02:00 2021", "started" => -1, "position" => -1, "total"=> -1], 
			["podcast" => "https://example.com/feed.xml", "episode" => "https://chrt.fm/track/47G541/injector.simplecastaudio.com/f16c3da7-cf46-4a42-99b7-8467255c6086/episodes/e8e24c01-6157-40e8-9b5a-45d539aeb7e6/audio/128/default.mp3?aid=rss_feed&awCollectionId=f16c3da7-cf46-4a42-99b7-8467255c6086&awEpisodeId=e8e24c01-6157-40e8-9b5a-45d539aeb7e6&feed=wEl4UUJZ", "guid" => "EPISODE-001-EXAMPLE-COM", "action" => "PLAY", "timestamp" => "Sun Oct 03 14:07:15 GMT+02:00 2021", "started" => 50, "position" => 221, "total"=> 450]
		]);

		$this->assertSame("https://example.org/feed.xml",  $episodeActions[0]->getPodcast());
		$this->assertSame("https://example.org/episode1.mp3", $episodeActions[0]->getEpisode());
		$this->assertSame("episode1", $episodeActions[0]->getGuid());
		$this->assertSame("PLAY", $episodeActions[0]->getAction());
		$this->assertSame("Sun Oct 03 14:03:17 GMT+02:00 2021", $episodeActions[0]->getTimestamp());
		$this->assertSame(0, $episodeActions[0]->getStarted());
		$this->assertSame(50, $episodeActions[0]->getPosition());
		$this->assertSame(3422, $episodeActions[0]->getTotal());

		$this->assertSame("https://example.org/feed.xml",  $episodeActions[1]->getPodcast());
		$this->assertSame("https://example.org/episode2.mp3", $episodeActions[1]->getEpisode());
		$this->assertSame("episode2", $episodeActions[1]->getGuid());
		$this->assertSame("DOWNLOAD", $episodeActions[1]->getAction());
		$this->assertSame("Sat Oct 02 11:06:28 GMT+02:00 2021", $episodeActions[1]->getTimestamp());
		$this->assertSame(-1, $episodeActions[1]->getStarted());
		$this->assertSame(-1, $episodeActions[1]->getPosition());
		$this->assertSame(-1, $episodeActions[1]->getTotal());

		$this->assertSame("https://example.com/feed.xml",  $episodeActions[2]->getPodcast());
		$this->assertSame("https://chrt.fm/track/47G541/injector.simplecastaudio.com/f16c3da7-cf46-4a42-99b7-8467255c6086/episodes/e8e24c01-6157-40e8-9b5a-45d539aeb7e6/audio/128/default.mp3?aid=rss_feed&awCollectionId=f16c3da7-cf46-4a42-99b7-8467255c6086&awEpisodeId=e8e24c01-6157-40e8-9b5a-45d539aeb7e6&feed=wEl4UUJZ", $episodeActions[2]->getEpisode());
		$this->assertSame("EPISODE-001-EXAMPLE-COM", $episodeActions[2]->getGuid());
		$this->assertSame("PLAY", $episodeActions[2]->getAction());
		$this->assertSame("Sun Oct 03 14:07:15 GMT+02:00 2021", $episodeActions[2]->getTimestamp());
		$this->assertSame(50, $episodeActions[2]->getStarted());
		$this->assertSame(221, $episodeActions[2]->getPosition());
		$this->assertSame(450, $episodeActions[2]->getTotal());
	}

}
