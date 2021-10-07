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
	 * @param array $urlsSubscribed
	 * @param array $urlsUnsubscribed
	 *
	 * @return SubscriptionChange[]
	 */
	public function createSubscriptionChangeList(array $urlsSubscribed, array $urlsUnsubscribed): array {
		$urlsToSubscribe = $this->subscriptionChangeReader::mapToSubscriptionsChanges($urlsSubscribed, true);
		$urlsToDelete = $this->subscriptionChangeReader::mapToSubscriptionsChanges($urlsUnsubscribed, false);

		/** @var SubscriptionChange[] $subscriptionChanges */
		return array_merge($urlsToSubscribe, $urlsToDelete);
	}
}
