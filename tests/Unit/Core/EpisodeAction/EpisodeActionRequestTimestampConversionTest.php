<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Tests\Unit\Core\EpisodeAction;

use Test\TestCase;

class EpisodeActionRequestTimestampConversionTest extends TestCase
{
	public function testTimestampConversion()
	{
		$episodeActionTimestamp = "Tue May 18 23:45:11 GMT+02:00 2021";
		$datetime = \DateTime::createFromFormat('D F d H:i:s T Y', $episodeActionTimestamp);
		$this->assertEquals("2021-05-18T23:45:11", $datetime->format("Y-m-d\TH:i:s"));
	}
}
