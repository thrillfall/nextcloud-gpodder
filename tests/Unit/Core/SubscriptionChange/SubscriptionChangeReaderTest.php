<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Tests\Unit\Core\SubscriptionChange;

use OCA\GPodderSync\Core\SubscriptionChange\SubscriptionChangesReader;
use Test\TestCase;

class SubscriptionChangeReaderTest extends TestCase {
	public function testCreateFromString(): void {
		$reader = new SubscriptionChangesReader();
		$subscriptionChange = $reader->fromString('[https://feeds.megaphone.fm/HSW8286374095]', true);
		$this->assertCount(1, $subscriptionChange);
		$this->assertSame("https://feeds.megaphone.fm/HSW8286374095", $subscriptionChange[0]->getUrl());
	}

	public function testCreateFromEmptyString(): void {
		$reader = new SubscriptionChangesReader();
		$subscriptionChange = $reader->fromString('[]', true);
		$this->assertCount(0, $subscriptionChange);
	}

}
