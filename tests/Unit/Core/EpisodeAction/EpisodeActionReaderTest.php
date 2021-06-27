<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Tests\Unit\Core\EpisodeAction;

use OCA\GPodderSync\Core\EpisodeAction\EpisodeActionReader;
use PHPUnit_Framework_TestCase;

class EpisodeActionReaderTest extends PHPUnit_Framework_TestCase {
	public function testCreateFromString(): void {
		$reader = new EpisodeActionReader();
		$episodeAction = $reader->fromString('[EpisodeAction{podcast=\'https://feeds.simplecast.com/wEl4UUJZ\', episode=\'https://chrt.fm/track/47G541/injector.simplecastaudio.com/f16c3da7-cf46-4a42-99b7-8467255c6086/episodes/e8e24c01-6157-40e8-9b5a-45d539aeb7e6/audio/128/default.mp3?aid=rss_feed&awCollectionId=f16c3da7-cf46-4a42-99b7-8467255c6086&awEpisodeId=e8e24c01-6157-40e8-9b5a-45d539aeb7e6&feed=wEl4UUJZ\', action=PLAY, timestamp=Tue May 18 23:45:11 GMT+02:00 2021, started=31, position=36, total=2474}]');
		$this->assertSame("https://feeds.simplecast.com/wEl4UUJZ",  $episodeAction->getPodcast());
		$this->assertSame("https://chrt.fm/track/47G541/injector.simplecastaudio.com/f16c3da7-cf46-4a42-99b7-8467255c6086/episodes/e8e24c01-6157-40e8-9b5a-45d539aeb7e6/audio/128/default.mp3?aid=rss_feed&awCollectionId=f16c3da7-cf46-4a42-99b7-8467255c6086&awEpisodeId=e8e24c01-6157-40e8-9b5a-45d539aeb7e6&feed=wEl4UUJZ", $episodeAction->getEpisode());
		$this->assertSame("PLAY", $episodeAction->getAction());
		$this->assertSame("Tue May 18 23:45:11 GMT+02:00 2021", $episodeAction->getTimestamp());
		$this->assertSame(31, $episodeAction->getStarted());
		$this->assertSame(36, $episodeAction->getPosition());
		$this->assertSame(2474, $episodeAction->getTotal());

	}

}
