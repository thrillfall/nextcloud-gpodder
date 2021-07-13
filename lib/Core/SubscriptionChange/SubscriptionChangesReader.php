<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\SubscriptionChange;

class SubscriptionChangesReader {

	/**
	 * @param array $urls
	 * @param bool $subscribed
	 *
	 * @return SubscriptionChange[]
	 */
	public static function mapToSubscriptionsChanges(array $urls, bool $subscribed): array {
		$subscriptionChanges = [];
		foreach ($urls as $url) {
			$subscriptionChanges[] = new SubscriptionChange($url, $subscribed);
		}

		return $subscriptionChanges;
	}

}
