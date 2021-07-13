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
	 * @param string|array $urlsSubscribed
	 * @param string|array $urlsUnsubscribed
	 *
	 * @return SubscriptionChange[]
	 */
	public function createSubscriptionChangeList($urlsSubscribed, $urlsUnsubscribed): array {
		if (is_array($urlsSubscribed)) {
			$urlsToSubscribe = $this->subscriptionChangeReader->fromArray($urlsSubscribed, true);
		} else {
			$urlsToSubscribe = $this->subscriptionChangeReader->fromString($urlsSubscribed, true);
		}
		if (is_array($urlsUnsubscribed)) {
			$urlsToDelete = $this->subscriptionChangeReader->fromArray($urlsUnsubscribed, false);
		} else {
			$urlsToDelete = $this->subscriptionChangeReader->fromString($urlsUnsubscribed, false);
		}

		/** @var SubscriptionChange[] $subscriptionChanges */
		return array_merge($urlsToSubscribe, $urlsToDelete);
	}
}
