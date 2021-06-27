<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\SubscriptionChange;

class SubscriptionChangeRequestParser {

	/**
	 * @var SubscriptionChangesReader
	 */
	private SubscriptionChangesReader $subscriptionChangeReader;

	public function __construct(SubscriptionChangesReader $subscriptionChangeReader) {
		$this->subscriptionChangeReader = $subscriptionChangeReader;
	}

	/**
	 * @param string $urlsSubscribed
	 * @param string $urlsUnsubscribed
	 *
	 * @return SubscriptionChange[]
	 */
	public function createSubscriptionChangeList(string $urlsSubscribed, string $urlsUnsubscribed): array {
		$urlsToSubscribe = $this->subscriptionChangeReader->fromString($urlsSubscribed, true);
		$urlsToDelete = $this->subscriptionChangeReader->fromString($urlsUnsubscribed, false);

		/** @var \OCA\GPodderSync\Core\SubscriptionChange\SubscriptionChange[] $subscriptionChanges */
		return array_merge($urlsToSubscribe, $urlsToDelete);
	}
}
