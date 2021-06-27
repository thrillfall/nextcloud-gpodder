<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Tests\Unit\Core\SubscriptionChange;

use OCA\GPodderSync\Core\SubscriptionChange\SubscriptionChangeRequestParser;
use OCA\GPodderSync\Core\SubscriptionChange\SubscriptionChangesReader;
use PHPUnit_Framework_TestCase;

class SubscriptionChangeRequestParserTest extends PHPUnit_Framework_TestCase {
	public function testSubscriptionRequestConvertsToSubscriptionChangeList() {
		$subscriptionChangesParser = new SubscriptionChangeRequestParser(
			new SubscriptionChangesReader(),
		);

		$subscriptionChanges = $subscriptionChangesParser->createSubscriptionChangeList('[https://feeds.simplecast.com/54nAGcIl]','[]');
		$this->assertCount(1, $subscriptionChanges);
		$this->assertSame('https://feeds.simplecast.com/54nAGcIl', $subscriptionChanges[0]->getUrl());
		$this->assertTrue($subscriptionChanges[0]->isSubscribed());
	}

	public function testSubscriptionRequestWithMultipleChangesConvertsToSubscriptionChangeList() {
		$subscriptionChangesParser = new SubscriptionChangeRequestParser(
			new SubscriptionChangesReader(),
		);

		$subscriptionChanges = $subscriptionChangesParser->createSubscriptionChangeList(
			'[https://podcastfeeds.nbcnews.com/dateline-nbc,https://feeds.megaphone.fm/ADL9840290619]',
			'[]');
		$this->assertCount(2, $subscriptionChanges);
	}
}
