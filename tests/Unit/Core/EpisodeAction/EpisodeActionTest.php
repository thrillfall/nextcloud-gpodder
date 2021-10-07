<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Tests\Unit\Core\EpisodeAction;

use OCA\GPodderSync\Core\EpisodeAction\EpisodeAction;
use Test\TestCase;

class EpisodeActionTest extends TestCase {
	public function testToArray(): void {
		$episodeAction = new EpisodeAction('podcast1', 'episode1', 'PLAY', '2021-10-07T13:27:14', 15, 120, 500, 'podcast1guid', null);
		$expected = [
			'podcast' => 'podcast1',
			'episode' => 'episode1',
			'timestamp' => '2021-10-07T13:27:14',
			'guid' => 'podcast1guid',
			'position' => 120,
			'started' => 15,
			'total' => 500,
			'action' => 'PLAY',
		];
		$this->assertSame($expected, $episodeAction->toArray());
	}
}
