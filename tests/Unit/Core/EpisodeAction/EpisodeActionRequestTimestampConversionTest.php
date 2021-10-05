<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Tests\Unit\Core\EpisodeAction;

use Test\TestCase;

class EpisodeActionRequestTimestampConversionTest extends TestCase
{
	public function testDateTimeFormatIsEnsured(): void
	{
		$episodeActionTimestamp = "2021-05-18T23:45:11";
		$datetime = \DateTime::createFromFormat('Y-m-d\TH:i:s', $episodeActionTimestamp)
		->format('Y-m-d\TH:i:s');
		$this->assertEquals($episodeActionTimestamp, $datetime);
	}

}
