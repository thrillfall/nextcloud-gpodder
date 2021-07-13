<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Tests\Unit\Core\SubscriptionChange;

use OCA\GPodderSync\Core\SubscriptionChange\SubscriptionChangeRequestParser;
use OCA\GPodderSync\Core\SubscriptionChange\SubscriptionChangesReader;
use Test\TestCase;

class SubscriptionChangeRequestParserTest extends TestCase {
	public function testSubscriptionRequestConvertsToSubscriptionChangeList() {
		$subscriptionChangesParser = new SubscriptionChangeRequestParser(
			new SubscriptionChangesReader(),
		);

		$subscriptionChanges = $subscriptionChangesParser->createSubscriptionChangeList(["https://feeds.simplecast.com/54nAGcIl", "https://feeds.simplecast.com/another"],["https://i.am-removed/GcIl"]);
		$this->assertCount(3, $subscriptionChanges);
		$this->assertSame("https://feeds.simplecast.com/54nAGcIl", $subscriptionChanges[0]->getUrl());
		$this->assertSame("https://feeds.simplecast.com/another", $subscriptionChanges[1]->getUrl());
		$this->assertSame("https://i.am-removed/GcIl", $subscriptionChanges[2]->getUrl());
		$this->assertTrue($subscriptionChanges[0]->isSubscribed());
		$this->assertFalse($subscriptionChanges[2]->isSubscribed());
	}

}
