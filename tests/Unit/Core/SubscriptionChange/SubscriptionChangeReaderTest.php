<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Tests\Unit\Core\SubscriptionChange;

use OCA\GPodderSync\Core\SubscriptionChange\SubscriptionChangesReader;
use Test\TestCase;

class SubscriptionChangeReaderTest extends TestCase {
	private SubscriptionChangesReader $reader;

	protected function setUp(): void {
		$this->reader = new SubscriptionChangesReader();
	}

	public function testCreateFromString(): void {
		$subscriptionChange = $this->reader->fromString('[https://feeds.megaphone.fm/HSW8286374095]', true);
		$this->assertCount(1, $subscriptionChange);
		$this->assertSame("https://feeds.megaphone.fm/HSW8286374095", $subscriptionChange[0]->getUrl());
	}

	public function testCreateFromEmptyString(): void {
		$subscriptionChange = $this->reader->fromString('[]', true);
		$this->assertCount(0, $subscriptionChange);
	}

	public function testCreateFromArray(): void {
		$subscriptionChange = $this->reader->fromArray(['https://example.com/feed.rss', 'https://example.org/podcast.php'], true);
		$this->assertCount(2, $subscriptionChange);
		$this->assertSame("https://example.com/feed.rss", $subscriptionChange[0]->getUrl());
		$this->assertSame("https://example.org/podcast.php", $subscriptionChange[1]->getUrl());
	}
}
